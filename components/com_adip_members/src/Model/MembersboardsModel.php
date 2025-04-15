<?php
/**
 * @version    CVS: 2.0.0
 * @package    Com_Adip_members
 * @author     Tasos Triantis <tasos.tr@gmail.com>
 * @copyright  2024 Tasos Triantis
 * @license    GNU General Public License version 2 ou version ultérieure ; Voir LICENSE.txt
 */

namespace Adipmembers\Component\Adip_members\Site\Model;
// No direct access.
defined('_JEXEC') or die;

use \Joomla\CMS\Factory;
use \Joomla\CMS\Language\Text;
use \Joomla\CMS\MVC\Model\ListModel;
use \Joomla\Component\Fields\Administrator\Helper\FieldsHelper;
use \Joomla\CMS\Uri\Uri;
use \Joomla\CMS\Helper\TagsHelper;
use \Joomla\CMS\Layout\FileLayout;
use \Joomla\Database\ParameterType;
use \Joomla\Utilities\ArrayHelper;
use \Adipmembers\Component\Adip_members\Site\Helper\Adip_membersHelper;


/**
 * Methods supporting a list of Adip_members records.
 *
 * @since  2.0.0
 */
class MembersboardsModel extends ListModel
{
	/**
	 * Constructor.
	 *
	 * @param   array  $config  An optional associative array of configuration settings.
	 *
	 * @see    JController
	 * @since  2.0.0
	 */
	public function __construct($config = array())
	{
		if (empty($config['filter_fields']))
		{
			$config['filter_fields'] = array(
				'id', 'a.id',
				'ordering', 'a.ordering',
				'state', 'a.state',
				'created_by', 'a.created_by',
				'modified_by', 'a.modified_by',
				'title_id', 'a.title_id',
				'firstname', 'a.firstname',
				'lastname', 'a.lastname',
				'sex', 'a.sex',
				'departement', 'a.departement',
				'url_more', 'a.url_more',
				'email', 'a.email',
				'image', 'a.image',
				'type_id', 'a.type_id',
				'active', 'a.active',
				'comments', 'a.comments',
				'modified', 'a.modified',
				'created', 'a.created',
				'lastname_en', 'a.lastname_en',
				'firstname_en', 'a.firstname_en',
				'departement_en', 'a.departement_en',
				'comments_en', 'a.comments_en',
				'cv', 'a.cv',
				'cv_en', 'a.cv_en',
			);
		}

		parent::__construct($config);
	}

	

	/**
	 * Method to auto-populate the model state.
	 *
	 * Note. Calling getState in this method will result in recursion.
	 *
	 * @param   string  $ordering   Elements order
	 * @param   string  $direction  Order direction
	 *
	 * @return  void
	 *
	 * @throws  Exception
	 *
	 * @since   2.0.0
	 */
	protected function populateState($ordering = null, $direction = null)
	{
		// List state information.
		parent::populateState("a.id", "ASC");

		$app = Factory::getApplication();
        $this->setState('filter.boardtype', $app->input->getInt('boardtype'));

		$list = $app->getUserState($this->context . '.list');
		$value = $app->getUserState($this->context . '.list.limit', $app->get('list_limit', 25));
		$list['limit'] = $value;
		
		$this->setState('list.limit', $value);

		$value = $app->input->get('limitstart', 0, 'uint');
		$this->setState('list.start', $value);

		$ordering  = $this->getUserStateFromRequest($this->context .'.filter_order', 'filter_order', "a.id");
		$direction = strtoupper($this->getUserStateFromRequest($this->context .'.filter_order_Dir', 'filter_order_Dir', "ASC"));
		
		if(!empty($ordering) || !empty($direction))
		{
			$list['fullordering'] = $ordering . ' ' . $direction;
		}

		$app->setUserState($this->context . '.list', $list);

		

		$context = $this->getUserStateFromRequest($this->context.'.filter.search', 'filter_search');
		$this->setState('filter.search', $context);

		// Split context into component and optional section
		if (!empty($context))
		{
			$parts = FieldsHelper::extract($context);

			if ($parts)
			{
				$this->setState('filter.component', $parts[0]);
				$this->setState('filter.section', $parts[1]);
			}
		}
	}

	/**
	 * Build an SQL query to load the list data.
	 *
	 * @return  DatabaseQuery
	 *
	 * @since   2.0.0
	 */
	protected function getListQuery()
	{
			// Create a new query object.
			$db    = $this->getDatabase();
			$query = $db->getQuery(true);

			// Select the required fields from the table.
			$query->select(
						$this->getState(
								'list.select', 'DISTINCT a.*'
						)
				);

			$query->from('`#__adip_members_board` AS a');

        // Join over the foreign key 'title_id'
        $query->select('t.`title` as prefix,t.`title_en` as prefix_en');
        $query->join('LEFT', '#__adip_members_titles AS t ON t.`id` = a.`title_id`');
        // Join over the foreign key 'type_id'
        $query->join('LEFT', '#__adip_members_board_roles_asc AS mbr ON a.id = mbr.member_id');
        $query->select('tp.`title` AS type,tp.`title_en` AS type_en');
        $query->join('LEFT', '#__adip_members_types AS tp ON tp.`id` = mbr.`role_id`');


        $query->where('a.state = 1');

        // Filtering active
        $filter_active = $this->state->get("filter.active");
        if ($filter_active != '') {
            $query->where("a.`active` = '".$db->escape($filter_active)."'");
        }

        // Filtering Report Type
        $filter_boardtype = $this->getState('filter.boardtype');
        if ($filter_boardtype !== null && !empty($filter_boardtype)) {
            $query->where("mbr.board_id = '".$db->escape($filter_boardtype)."'");
        }


        $query->order('mbr.role_id, a.ordering, a.lastname,a.lastname_en ASC');
//        Factory::getApplication()->enqueueMessage($db->replacePrefix((string) $query), 'notice');
			return $query;
	}

	/**
	 * Method to get an array of data items
	 *
	 * @return  mixed An array of data on success, false on failure.
	 */
	public function getItems()
	{
		$items = parent::getItems();

        $lang = Factory::getApplication()->getLanguage();
        $l = explode("-",$lang->getTag());
        $default_image[1] = URI::root()."images/adip_board_members/man.png";
        $default_image[2] = URI::root()."images/adip_board_members/woman.png";

        foreach ($items as $item)
        {
            $item->image =  (!empty($item->image)) ? $item->image : $default_image[$item->sex];
            $item->fullname = $item->firstname.' '.$item->lastname;
            $item->fullname = (!empty($item->prefix)) ? $item->prefix." ".$item->fullname : $item->fullname;
            $item->member_type = $item->type;
            $item->link = "member-data-".(int) $item->id;
            $item->univ = (!empty($item->departement)) ? $item->departement :"";
            if (isset($l[0]) && $l[0] == 'en') {
                $item->fullname = $item->firstname_en.' '.$item->lastname_en;
                $item->fullname = (!empty($item->prefix_en)) ? $item->prefix_en." ".$item->fullname : $item->fullname;
                $item->member_type = $item->type_en;
                $item->cv = $item->cv_en;
                $item->univ = (!empty($item->departement_en)) ? $item->departement_en :"";
            }

            $item->full_member_type = (!empty($item->univ)) ?  $item->member_type. " - ". $item->univ : $item->member_type;

        }


		return $items;
	}

	/**
	 * Overrides the default function to check Date fields format, identified by
	 * "_dateformat" suffix, and erases the field if it's not correct.
	 *
	 * @return void
	 */
	protected function loadFormData()
	{
		$app              = Factory::getApplication();
		$filters          = $app->getUserState($this->context . '.filter', array());
		$error_dateformat = false;

		foreach ($filters as $key => $value)
		{
			if (strpos($key, '_dateformat') && !empty($value) && $this->isValidDate($value) == null)
			{
				$filters[$key]    = '';
				$error_dateformat = true;
			}
		}

		if ($error_dateformat)
		{
			$app->enqueueMessage(Text::_("COM_ADIP_MEMBERS_SEARCH_FILTER_DATE_FORMAT"), "warning");
			$app->setUserState($this->context . '.filter', $filters);
		}

		return parent::loadFormData();
	}

	/**
	 * Checks if a given date is valid and in a specified format (YYYY-MM-DD)
	 *
	 * @param   string  $date  Date to be checked
	 *
	 * @return bool
	 */
	private function isValidDate($date)
	{
		$date = str_replace('/', '-', $date);
		return (date_create($date)) ? Factory::getDate($date)->format("Y-m-d") : null;
	}
}
