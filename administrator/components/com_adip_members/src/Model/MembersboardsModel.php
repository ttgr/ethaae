<?php
/**
 * @version    CVS: 2.0.0
 * @package    Com_Adip_members
 * @author     Tasos Triantis <tasos.tr@gmail.com>
 * @copyright  2024 Tasos Triantis
 * @license    GNU General Public License version 2 ou version ultérieure ; Voir LICENSE.txt
 */

namespace Adipmembers\Component\Adip_members\Administrator\Model;
// No direct access.
defined('_JEXEC') or die;

use \Joomla\CMS\MVC\Model\ListModel;
use \Joomla\Component\Fields\Administrator\Helper\FieldsHelper;
use \Joomla\CMS\Factory;
use \Joomla\CMS\Language\Text;
use \Joomla\CMS\Helper\TagsHelper;
use \Joomla\Database\ParameterType;
use \Joomla\Utilities\ArrayHelper;
use Adipmembers\Component\Adip_members\Administrator\Helper\Adip_membersHelper;

/**
 * Methods supporting a list of Membersboards records.
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
	* @see        JController
	* @since      1.6
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
	 * @return void
	 *
	 * @throws Exception
	 */
	protected function populateState($ordering = null, $direction = null)
	{
		// List state information.
		parent::populateState("a.id", "ASC");

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
	 * Method to get a store id based on model configuration state.
	 *
	 * This is necessary because the model is used by the component and
	 * different modules that might need different sets of data or different
	 * ordering requirements.
	 *
	 * @param   string  $id  A prefix for the store id.
	 *
	 * @return  string A store id.
	 *
	 * @since   2.0.0
	 */
	protected function getStoreId($id = '')
	{
		// Compile the store id.
		$id .= ':' . $this->getState('filter.search');
		$id .= ':' . $this->getState('filter.state');

		
		return parent::getStoreId($id);
		
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
        $query->select('`t`.`title`');
        $query->join('LEFT', '#__adip_members_titles AS t ON t.`id` = a.`title_id`');

        $query->select('bt.`name` AS board_type');
        $query->join('LEFT', '#__adip_members_boardtypes AS bt ON bt.`id` = a.`board_type_id`');

        $query->join('LEFT', '#__adip_members_board_roles_asc AS mbr ON a.id = mbr.member_id');
		

		// Filter by published state
		$published = $this->getState('filter.state');

		if (is_numeric($published))
		{
			$query->where('a.state = ' . (int) $published);
		}
		elseif (empty($published))
		{
			$query->where('(a.state IN (0, 1))');
		}

		// Filter by search in title
		$search = $this->getState('filter.search');

		if (!empty($search))
		{
			if (stripos($search, 'id:') === 0)
			{
				$query->where('a.id = ' . (int) substr($search, 3));
			}
			else
			{
				$search = $db->Quote('%' . $db->escape($search, true) . '%');
				$query->where('( a.firstname LIKE ' . $search . '  OR  a.lastname LIKE ' . $search . '  OR  a.departement LIKE ' . $search . ' )');
			}
		}


        // Filtering type_id
        $filter_type_id = $this->state->get("filter.type_id");

        if ($filter_type_id !== null && !empty($filter_type_id))
        {
            $query->where("mbr.role_id = '".$db->escape($filter_type_id)."'");
        }

        // Filtering board_type_id
        $filter_board_type_id = $this->state->get("filter.board_type_id");

        if ($filter_board_type_id !== null && !empty($filter_board_type_id))
        {
            $query->where("mbr.board_id = '".$db->escape($filter_board_type_id)."'");
        }

        // Filtering active
        $filter_active = $this->state->get("filter.active");

        if ($filter_active !== null && (is_numeric($filter_active) || !empty($filter_active)))
        {
            $query->where("a.`active` = '".$db->escape($filter_active)."'");
        }
        // Add the list ordering clause.
        $orderCol  = $this->state->get('list.ordering', "a.id");
        $orderDirn = $this->state->get('list.direction', "ASC");

        if ($orderCol && $orderDirn)
        {
            $query->order($db->escape($orderCol . ' ' . $orderDirn));
        }

        //Factory::getApplication()->enqueueMessage($db->replacePrefix((string) $query), 'notice');
		return $query;
	}

	/**
	 * Get an array of data items
	 *
	 * @return mixed Array of data items on success, false on failure.
	 */
    public function getItems()
    {
        $items = parent::getItems();

        foreach ($items as $oneItem)
        {
            $oneItem->boards_roles = $this->getBoardsRoles($oneItem->id);
            $oneItem->active = Text::_('COM_ADIP_MEMBERS_MEMBERSBOARDS_ACTIVE_OPTION_' . strtoupper($oneItem->active));
        }

        return $items;
    }


    public function getBoardsRoles($memberID,$separator = "</br>") {
        $db    = $this->getDatabase();
        $query = $db->getQuery(true);
        $query->select("b.`name`, r.title");
        $query->from('`#__adip_members_board_roles_asc` AS a');
        $query->join('LEFT', '#__adip_members_boardtypes AS b ON b.`id` = a.board_id');
        $query->join('LEFT', '#__adip_members_types AS r ON r.`id` = a.role_id');
        $query->where('member_id =' .$db->quote($memberID));
        $db->setQuery($query);
        $rows = $db->loadObjectList();
        $rstl = array();
        foreach ($rows as $row) {
            $rstl[] = $row->name. " :: ". $row->title;
        }
        if (count($rstl) > 0 ) {
            return implode($separator,$rstl);
        } else {
            return "";
        }
    }

}
