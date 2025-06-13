<?php
/**
 * @version    CVS: 2.0.7
 * @package    Com_Adipstaff
 * @author     Tasos Triantis <tasos.tr@gmail.com>
 * @copyright  2024 Tasos Triantis
 * @license    GNU General Public License version 2 or later; see LICENSE.txt
 */

namespace Adipstaff\Component\Adipstaff\Site\Model;
// No direct access.
defined('_JEXEC') or die;

use \Joomla\CMS\Factory;
use \Joomla\CMS\Language\Text;
use \Joomla\CMS\MVC\Model\ListModel;
use \Joomla\Component\Fields\Administrator\Helper\FieldsHelper;
use \Joomla\CMS\Helper\TagsHelper;
use \Joomla\CMS\Layout\FileLayout;
use \Joomla\Database\ParameterType;
use \Joomla\Utilities\ArrayHelper;
use \Adipstaff\Component\Adipstaff\Site\Helper\AdipstaffHelper;


/**
 * Methods supporting a list of Adipstaff records.
 *
 * @since  2.0.7
 */
class PersonnelsModel extends ListModel
{
	/**
	 * Constructor.
	 *
	 * @param   array  $config  An optional associative array of configuration settings.
	 *
	 * @see    JController
	 * @since  2.0.7
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
				'department_id', 'a.department_id',
				'position_el', 'a.position_el',
				'fullname_el', 'a.fullname_el',
				'position_en', 'a.position_en',
				'fullname_en', 'a.fullname_en',
				'email', 'a.email',
				'display_mail', 'a.display_mail',
				'phone1', 'a.phone1',
				'phone2', 'a.phone2',
				'display_phones', 'a.display_phones',
				'created', 'a.created',
				'modified', 'a.modified',
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
	 * @since   2.0.7
	 */
	protected function populateState($ordering = null, $direction = null)
	{
		// List state information.
		parent::populateState('a.ordering', 'ASC');

		$app = Factory::getApplication();
		$list = $app->getUserState($this->context . '.list');

		//$value = $app->getUserState($this->context . '.list.limit', $app->get('list_limit', 25));
		$list['limit'] = 1000;
		$this->setState('list.limit', 1000);

		$value = $app->input->get('limitstart', 0, 'uint');
		$this->setState('list.start', $value);

		$ordering  = $this->getUserStateFromRequest($this->context .'.filter_order', 'filter_order', 'a.ordering');
		$direction = strtoupper($this->getUserStateFromRequest($this->context .'.filter_order_Dir', 'filter_order_Dir', 'ASC'));
		
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
	 * @since   2.0.7
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

        $query->from('#__adipstaff_personnel AS a');

        // Join over the foreign key 'department_id'
        $query->select('d.name_el');
        $query->select('d.name_en');
        $query->join('LEFT', '#__adipstaff_departments AS d ON d.`id` = a.`department_id`');
        $query->where('a.state = 1');

        $query->order($db->escape('d.ordering ASC'));
        $query->order($db->escape('a.ordering ASC'));
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
        $app = Factory::getApplication();
		$items = parent::getItems();

        $lang = $app->getLanguage();
        $l = explode("-", $lang->getTag());
        $pre = 0;
        $rows = array();
        foreach ($items as $item)
        {
            $row = new \stdClass();
            $row->direction = trim($item->{'name_'.$l[0]});
            $row->dep = trim($item->{'position_'.$l[0]});
            $row->fullname = trim($item->{'fullname_'.$l[0]});
            $row->mail = ($item->display_mail == 1) ? $item->email : "&nbsp;";
            $phones = array();
            if (!empty($item->phone1)) $phones[] = $item->phone1;
            if (!empty($item->phone2)) $phones[] = $item->phone2;
            $row->phones = ($item->display_phones == 1 && count($phones) > 0) ? implode("</br>",$phones) : "&nbsp;";

            if ($pre !== $item->department_id) {
                $pre = $item->department_id;
            }
            $rows[$pre][] = $row;
        }

        return $rows;
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
			$app->enqueueMessage(Text::_("COM_ADIPSTAFF_SEARCH_FILTER_DATE_FORMAT"), "warning");
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
