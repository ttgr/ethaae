<?php
/**
 * @version    CVS: 1.1.0
 * @package    Com_Ethaae_reports
 * @author     Tasos Triantis <tasos.tr@gmail.com>
 * @copyright  2024 Tasos Triantis
 * @license    GNU General Public License version 2 or later; see LICENSE.txt
 */

namespace Ethaaereports\Component\Ethaae_reports\Site\Model;
// No direct access.
defined('_JEXEC') or die;

use \Joomla\CMS\Factory;
use \Joomla\CMS\Language\Text;
use \Joomla\CMS\MVC\Model\ListModel;
use \Joomla\Component\Fields\Administrator\Helper\FieldsHelper;
use \Ethaaereports\Component\Ethaae_reports\Site\Helper\Ethaae_reportsHelper;
use Joomla\CMS\Pagination\Pagination;


/**
 * Methods supporting a list of Ethaae_reports records.
 *
 * @since  1.1.0
 */
class ReportsModel extends ListModel
{


    var $resetState = false;
	/**
	 * Constructor.
	 *
	 * @param   array  $config  An optional associative array of configuration settings.
	 *
	 * @see    JController
	 * @since  1.1.0
	 */
	public function __construct($config = array())
	{
		if (empty($config['filter_fields']))
		{
			$config['filter_fields'] = array(
                'unit_title_el','unit_title_en',
                'institute_title_el','institute_title_en',
                'parent_title_el','parent_title_en',
				'report_year', 'a.report_year',
				'title', 'a.title',
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
	 * @since   1.1.0
	 */
	protected function populateState($ordering = null, $direction = null)
	{
		// List state information.
		parent::populateState('a.id', 'ASC');

		$app = Factory::getApplication();
		$list = $app->getUserState($this->context . '.list');

		$value = $this->getUserStateFromRequest($this->context .'.list_limit', 'list_limit', '25');
		$list['limit'] = $value;
		
		$this->setState('list.limit', $value);
		$value = $app->input->get('limitstart', 0, 'uint');
		$this->setState('list.start', $value);
		$ordering  = $this->getUserStateFromRequest($this->context .'.filter_order', 'filter_order', 'a.id');
		$direction = strtoupper($this->getUserStateFromRequest($this->context .'.filter_order_Dir', 'filter_order_Dir', 'ASC'));



        $filter_fk_unit_id  = $this->getUserStateFromRequest($this->context .'.filter_fk_unit_id', 'filter_fk_unit_id', '0');
        $this->setState('filter.fk_unit_id', $filter_fk_unit_id);

        $filter_fk_institute_id  = $this->getUserStateFromRequest($this->context .'.filter_fk_institute_id', 'filter_fk_institute_id', '0');
        $this->setState('filter.fk_institute_id', $filter_fk_institute_id);

        $filter_fk_dept_id  = $this->getUserStateFromRequest($this->context .'.filter_fk_dept_id', 'filter_fk_dept_id', '0');
        $this->setState('filter.fk_dept_id', $filter_fk_dept_id);

        $filter_fk_reporttype_id  = $this->getUserStateFromRequest($this->context .'.filter_fk_reporttype_id', 'filter_fk_reporttype_id', '0');
        $this->setState('filter.fk_reporttype_id', $filter_fk_reporttype_id);

        $filter_report_year  = $this->getUserStateFromRequest($this->context .'.filter_report_year', 'filter_report_year', '0');
        $this->setState('filter.report_year', $filter_report_year);

        if($this->resetState) {
            $this->setState('filter.fk_unit_id', '');
            $this->setState('filter.fk_institute_id', '');
            $this->setState('filter.fk_dept_id', '');
            $this->setState('filter.fk_reporttype_id', '');
        }

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
	 * @since   1.1.0
	 */
	protected function getListQuery()
	{
			// Create a new query object.
			$db = Factory::getContainer()->get('DatabaseDriver');
			$query = $db->getQuery(true);

			// Select the required fields from the table.
			$query->select(
						$this->getState(
								'list.select', 'DISTINCT a.*'
						)
				);

			$query->from('`#__ethaae_reports` AS a');
            $query->join('INNER', '#__ethaae_reports_files AS f ON a.`id` = f.`fk_report_id`');
		// Join over the users for the checked out user.
		// Join over the foreign key 'fk_reporttype_id'
		$query->select('`t`.`name` AS report_type');
		$query->join('LEFT', '#__ethaae_report_types AS t ON t.`id` = a.`fk_reporttype_id`');

		$query->select('`s`.`title_el` AS unit_title_el');
		$query->select('`s`.`title_en` AS unit_title_en');
        $query->select('`s`.`short_code_el` AS unit_short_code_el');
        $query->select('`s`.`short_code_en` AS unit_short_code_en');
        $query->join('LEFT', '#__ethaae_institutes_structure AS s ON s.`id` = a.`fk_unit_id`');

		$query->select('`i`.`title_el` AS institute_title_el');
		$query->select('`i`.`title_en` AS institute_title_en');
		$query->join('LEFT', '#__ethaae_institutes_structure AS i ON i.`id` = a.`fk_institute_id`');

        $query->select('`p`.`title_el` AS parent_title_el');
        $query->select('`p`.`title_en` AS parent_title_en');
        $query->join('LEFT', '#__ethaae_institutes_structure AS p ON p.`id` = a.`fk_parent_id`');


        $query->where('a.state = 1');

		// Filtering fk_reporttype_id
		$filter_fk_reporttype_id = $this->state->get("filter.fk_reporttype_id");

		if ($filter_fk_reporttype_id)
		{
			$query->where("a.`fk_reporttype_id` = '".$db->escape($filter_fk_reporttype_id)."'");
		}

		// Filtering academic_greek
		$filter_academic_greek = $this->state->get("filter.academic_greek");
		if ($filter_academic_greek != '') {
			$query->where("a.`academic_greek` = '".$db->escape($filter_academic_greek)."'");
		}

		// Filtering session_date
		$filter_session_date_from = $this->state->get("filter.session_date.from");

		if ($filter_session_date_from !== null && !empty($filter_session_date_from))
		{
			$query->where("a.`session_date` >= '".$db->escape($filter_session_date_from)."'");
		}
		$filter_session_date_to = $this->state->get("filter.session_date.to");

		if ($filter_session_date_to !== null  && !empty($filter_session_date_to))
		{
			$query->where("a.`session_date` <= '".$db->escape($filter_session_date_to)."'");
		}

		// Filtering valid_from
		$filter_valid_from_from = $this->state->get("filter.valid_from.from");

		if ($filter_valid_from_from !== null && !empty($filter_valid_from_from))
		{
			$query->where("a.`valid_from` >= '".$db->escape($filter_valid_from_from)."'");
		}
		$filter_valid_from_to = $this->state->get("filter.valid_from.to");

		if ($filter_valid_from_to !== null  && !empty($filter_valid_from_to))
		{
			$query->where("a.`valid_from` <= '".$db->escape($filter_valid_from_to)."'");
		}

		// Filtering valid_to
		$filter_valid_to_from = $this->state->get("filter.valid_to.from");

		if ($filter_valid_to_from !== null && !empty($filter_valid_to_from))
		{
			$query->where("a.`valid_to` >= '".$db->escape($filter_valid_to_from)."'");
		}
		$filter_valid_to_to = $this->state->get("filter.valid_to.to");

		if ($filter_valid_to_to !== null  && !empty($filter_valid_to_to))
		{
			$query->where("a.`valid_to` <= '".$db->escape($filter_valid_to_to)."'");
		}

		// Filtering fk_unit_id
		$filter_fk_unit_id = $this->state->get("filter.fk_unit_id");
		if ($filter_fk_unit_id)
		{
			$query->where("a.`fk_unit_id` = '".$db->escape($filter_fk_unit_id)."'");
		}

		// Filtering fk_unit_id
        $filter_fk_institute_id = $this->state->get("filter.fk_institute_id");
		if ($filter_fk_institute_id)
		{
			$query->where("a.`fk_institute_id` = '".$db->escape($filter_fk_institute_id)."'");
		}

		// Filtering fk_parent_id
        $fk_parent_id = $this->state->get("filter.fk_dept_id");
		if ($fk_parent_id)
		{
			$query->where("a.`fk_parent_id` = '".$db->escape($fk_parent_id)."'");
		}

        // Filtering report_year
        $filter_report_year = $this->state->get("filter.report_year");
		if ($filter_report_year > 0)
		{
			$query->where("a.`report_year` = '".$db->escape($filter_report_year)."'");
		}
			// Add the list ordering clause.
			$orderCol  = $this->state->get('list.ordering', 'a.id');
			$orderDirn = $this->state->get('list.direction', 'ASC');

			if ($orderCol && $orderDirn)
			{
				$query->order($db->escape($orderCol . ' ' . $orderDirn));
			}
        //Factory::getApplication()->enqueueMessage($db->replacePrefix((string) $query), 'notice');
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
		
		foreach ($items as $item)
		{
            $item->files =  Ethaae_reportsHelper::getFiles($item);
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
			$app->enqueueMessage(Text::_("COM_ETHAAE_REPORTS_SEARCH_FILTER_DATE_FORMAT"), "warning");
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


    public function getTotal()
    {
            $db = Factory::getContainer()->get('DatabaseDriver');
            $query = $this->getListQuery();
            $db->setQuery($query);
            $db->execute();
            $this->_total = $db->getNumRows();
            return $this->_total;
    }

    public function getPagination()
    {
        if (empty($this->_pagination))
        {
            $this->_pagination = new Pagination($this->getTotal(), $this->getStart(), (int) $this->getState('list.limit'));
        }

        return $this->_pagination;
    }

}
