<?php
/**
 * @version    CVS: 1.1.0
 * @package    Com_Ethaae_reports
 * @author     Tasos Triantis <tasos.tr@gmail.com>
 * @copyright  2024 Tasos Triantis
 * @license    GNU General Public License version 2 or later; see LICENSE.txt
 */

namespace Ethaaereports\Component\Ethaae_reports\Administrator\Model;
// No direct access.
defined('_JEXEC') or die;

use \Joomla\CMS\MVC\Model\ListModel;
use \Joomla\Component\Fields\Administrator\Helper\FieldsHelper;
use \Joomla\CMS\Factory;
use \Joomla\CMS\Language\Text;
use \Joomla\CMS\Helper\TagsHelper;
use \Joomla\Database\ParameterType;
use \Joomla\Utilities\ArrayHelper;
use Ethaaereports\Component\Ethaae_reports\Administrator\Helper\Ethaae_reportsHelper;

/**
 * Methods supporting a list of Reports records.
 *
 * @since  1.1.0
 */
class ReportsModel extends ListModel
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
				'created', 'a.created',
				'modified', 'a.modified',
				'params', 'a.params',
				'fk_reporttype_id', 'a.fk_reporttype_id',
				'academic_greek', 'a.academic_greek',
				'session_num', 'a.session_num',
				'report_year', 'a.report_year',
				'session_date', 'a.session_date',
		'session_date.from', 'session_date.to',
				'valid_from', 'a.valid_from',
		'valid_from.from', 'valid_from.to',
				'valid_to', 'a.valid_to',
		'valid_to.from', 'valid_to.to',
				'title', 'a.title',
				'fk_unit_id', 'a.fk_unit_id',
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
		parent::populateState('id', 'DESC');

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
	 * @since   1.1.0
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
	 * @since   1.1.0
	 */
	protected function getListQuery()
	{
		// Create a new query object.
		$db    = $this->getDbo();
		$query = $db->getQuery(true);

		// Select the required fields from the table.
		$query->select(
			$this->getState(
				'list.select', 'DISTINCT a.*'
			)
		);
		$query->from('`#__ethaae_reports` AS a');
		
		// Join over the users for the checked out user
		$query->select("uc.name AS uEditor");
		$query->join("LEFT", "#__users AS uc ON uc.id=a.checked_out");

		// Join over the user field 'created_by'
		$query->select('`created_by`.name AS `created_by`');
		$query->join('LEFT', '#__users AS `created_by` ON `created_by`.id = a.`created_by`');

		// Join over the user field 'modified_by'
		$query->select('`modified_by`.name AS `modified_by`');
		$query->join('LEFT', '#__users AS `modified_by` ON `modified_by`.id = a.`modified_by`');
		// Join over the foreign key 'fk_reporttype_id'
		$query->select('`#__ethaae_report_types_4052118`.`name` AS reporttypes_fk_value_4052118');
		$query->join('LEFT', '#__ethaae_report_types AS #__ethaae_report_types_4052118 ON #__ethaae_report_types_4052118.`id` = a.`fk_reporttype_id`');
		// Join over the foreign key 'fk_unit_id'

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
				$query->where('( a.title LIKE ' . $search . ' )');
			}
		}
		

		// Filtering fk_reporttype_id
		$filter_fk_reporttype_id = $this->state->get("filter.fk_reporttype_id");

		if ($filter_fk_reporttype_id !== null && !empty($filter_fk_reporttype_id))
		{
			$query->where("a.`fk_reporttype_id` = '".$db->escape($filter_fk_reporttype_id)."'");
		}

		// Filtering academic_greek
		$filter_academic_greek = $this->state->get("filter.academic_greek");

		if ($filter_academic_greek !== null && (is_numeric($filter_academic_greek) || !empty($filter_academic_greek)))
		{
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

		// Filtering fk_institute_id
		$filter_fk_institute_id = $this->state->get("filter.fk_institute_id");

		if ($filter_fk_institute_id !== null && !empty($filter_fk_institute_id))
		{
			$query->where("a.`fk_institute_id` = '".$db->escape($filter_fk_institute_id)."'");
		}
		// Add the list ordering clause.
		$orderCol  = $this->state->get('list.ordering', 'id');
		$orderDirn = $this->state->get('list.direction', 'DESC');

		if ($orderCol && $orderDirn)
		{
			$query->order($db->escape($orderCol . ' ' . $orderDirn));
		}

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

			if (isset($oneItem->fk_reporttype_id))
			{

				$oneItem->fk_reporttype = Ethaae_reportsHelper::getReportTypeInfo($oneItem->fk_reporttype_id)->name;
			}

            $oneItem->academic_greek = !empty($oneItem->academic_greek) ? Text::_('COM_ETHAAE_REPORTS_REPORTS_ACADEMIC_GREEK_OPTION_' . preg_replace('/[^A-Za-z0-9\_-]/', '',strtoupper(str_replace(' ', '_',$oneItem->academic_greek)))) : '';

			if (isset($oneItem->fk_institute_id))
			{
                $oneItem->fk_institute = Ethaae_reportsHelper::getUnitInfo($oneItem->fk_institute_id)->title;
			}
            $oneItem->files =  Ethaae_reportsHelper::getFiles($oneItem->id);
		}

		return $items;
	}
}
