<?php
/**
 * @version    CVS: 2.1.0
 * @package    Com_Adip
 * @author     Tasos Triantis <tasos.tr@gmail.com>
 * @copyright  2024 Tasos Triantis
 * @license    GNU General Public License version 2 or later; see LICENSE.txt
 */

namespace Adip\Component\Adip\Administrator\Model;
// No direct access.
defined('_JEXEC') or die;

use Joomla\CMS\HTML\HTMLHelper;
use Joomla\CMS\Language\LanguageHelper;
use \Joomla\CMS\MVC\Model\ListModel;
use \Joomla\Component\Fields\Administrator\Helper\FieldsHelper;
use \Joomla\CMS\Factory;
use \Joomla\CMS\Language\Text;
use \Joomla\CMS\Helper\TagsHelper;
use \Joomla\Database\ParameterType;
use \Joomla\Utilities\ArrayHelper;
use Adip\Component\Adip\Administrator\Helper\AdipHelper;

/**
 * Methods supporting a list of Reports records.
 *
 * @since  2.1.0
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
				'fk_reporttype_id', 'a.fk_reporttype_id',
				'fk_institute_id', 'a.fk_institute_id',
				'fk_deprtement_id', 'a.fk_deprtement_id',
				'fk_programme_id', 'a.fk_programme_id',
				'academic_greek', 'a.academic_greek',
				'session_num', 'a.session_num',
				'report_year', 'a.report_year',
				'session_date', 'a.session_date',
		'session_date.from', 'session_date.to',
				'valid_from', 'a.valid_from',
		'valid_from.from', 'valid_from.to',
				'valid_to', 'a.valid_to',
		'valid_to.from', 'valid_to.to',
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
		parent::populateState('created', 'DESC');

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
	 * @since   2.1.0
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
	 * @since   2.1.0
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
		$query->from('`#__adip_reports` AS a');
		
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
		$query->select('`#__adip_report_types_4045897`.`name` AS reporttypes_fk_value_4045897');
		$query->join('LEFT', '#__adip_report_types AS #__adip_report_types_4045897 ON #__adip_report_types_4045897.`id` = a.`fk_reporttype_id`');
		// Join over the foreign key 'fk_institute_id'
		$query->select('`#__adip_institutions_3336769`.`title` AS institutions_fk_value_3336769');
		$query->join('LEFT', '#__adip_institutions AS #__adip_institutions_3336769 ON #__adip_institutions_3336769.`id` = a.`fk_institute_id`');
		// Join over the foreign key 'fk_deprtement_id'
		$query->select('`#__adip_departements_3336770`.`title` AS departments_fk_value_3336770');
		$query->join('LEFT', '#__adip_departements AS #__adip_departements_3336770 ON #__adip_departements_3336770.`id` = a.`fk_deprtement_id`');
		// Join over the foreign key 'fk_programme_id'
		$query->select('`#__adip_departements_3336771`.`title` AS departments_fk_value_3336771');
		$query->join('LEFT', '#__adip_departements AS #__adip_departements_3336771 ON #__adip_departements_3336771.`id` = a.`fk_programme_id`');
		

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
				$query->where('( a.session_num LIKE ' . $search . '  OR  a.report_year LIKE ' . $search . ' )');
			}
		}
		

		// Filtering fk_reporttype_id
		$filter_fk_reporttype_id = $this->state->get("filter.fk_reporttype_id");

		if ($filter_fk_reporttype_id !== null && !empty($filter_fk_reporttype_id))
		{
			$query->where("a.`fk_reporttype_id` = '".$db->escape($filter_fk_reporttype_id)."'");
		}

		// Filtering fk_institute_id
		$filter_fk_institute_id = $this->state->get("filter.fk_institute_id");

		if ($filter_fk_institute_id !== null && !empty($filter_fk_institute_id))
		{
			$query->where("a.`fk_institute_id` = '".$db->escape($filter_fk_institute_id)."'");
		}

		// Filtering fk_deprtement_id
		$filter_fk_deprtement_id = $this->state->get("filter.fk_deprtement_id");

		if ($filter_fk_deprtement_id !== null && !empty($filter_fk_deprtement_id))
		{
			$query->where("a.`fk_deprtement_id` = '".$db->escape($filter_fk_deprtement_id)."'");
		}

		// Filtering fk_programme_id
		$filter_fk_programme_id = $this->state->get("filter.fk_programme_id");

		if ($filter_fk_programme_id !== null && !empty($filter_fk_programme_id))
		{
			$query->where("a.`fk_programme_id` = '".$db->escape($filter_fk_programme_id)."'");
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
		// Add the list ordering clause.
		$orderCol  = $this->state->get('list.ordering', 'created');
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

        $db = Factory::getContainer()->get('DatabaseDriver');

		$items = parent::getItems();
		
		foreach ($items as $oneItem)
		{

			if (isset($oneItem->fk_reporttype_id))
			{
				$values    = explode(',', $oneItem->fk_reporttype_id);
				$textValue = array();

				foreach ($values as $value)
				{
					$query = $db->getQuery(true);
					$query
						->select('`#__adip_report_types_4045897`.`name`')
						->from($db->quoteName('#__adip_report_types', '#__adip_report_types_4045897'))
						->where($db->quoteName('#__adip_report_types_4045897.id') . ' = '. $db->quote($db->escape($value)));

					$db->setQuery($query);
					$results = $db->loadObject();

					if ($results)
					{
						$textValue[] = $results->name;
					}
				}

				$oneItem->fk_reporttype_id = !empty($textValue) ? implode(', ', $textValue) : $oneItem->fk_reporttype_id;
			}

			if (isset($oneItem->fk_institute_id))
			{
				$values    = explode(',', $oneItem->fk_institute_id);
				$textValue = array();

				foreach ($values as $value)
				{
					$query = $db->getQuery(true);
					$query
						->select('`#__adip_institutions_3336769`.`title`')
						->from($db->quoteName('#__adip_institutions', '#__adip_institutions_3336769'))
						->where($db->quoteName('#__adip_institutions_3336769.id') . ' = '. $db->quote($db->escape($value)));

					$db->setQuery($query);
					$results = $db->loadObject();

					if ($results)
					{
						$textValue[] = $results->title;
					}
				}

				$oneItem->fk_institute_id = !empty($textValue) ? implode(', ', $textValue) : $oneItem->fk_institute_id;
			}

			if (isset($oneItem->fk_deprtement_id))
			{
				$values    = explode(',', $oneItem->fk_deprtement_id);
				$textValue = array();

				foreach ($values as $value)
				{
					$query = $db->getQuery(true);
					$query
						->select('`#__adip_departements_3336770`.`title`')
						->from($db->quoteName('#__adip_departements', '#__adip_departements_3336770'))
						->where($db->quoteName('#__adip_departements_3336770.id') . ' = '. $db->quote($db->escape($value)));

					$db->setQuery($query);
					$results = $db->loadObject();

					if ($results)
					{
						$textValue[] = $results->title;
					}
				}

				$oneItem->fk_deprtement_id = !empty($textValue) ? implode(', ', $textValue) : $oneItem->fk_deprtement_id;
			}

            if (isset($oneItem->fk_programme_id) && intval($oneItem->fk_programme_id) > 0)
            {
                $values    = explode(',', $oneItem->fk_programme_id);
                $textValue = array();

                foreach ($values as $value)
                {
                    $query = $db->getQuery(true);
                    $query
                        ->select("CONCAT(pl.`prog_type`,' :: ',pl.`prog_name`) as title")
                        ->from($db->quoteName('#__adip_programmes_list', 'pl'))
                        ->where($db->quoteName('pl.prog_id') . ' = '. $db->quote($db->escape($value)));

                    $db->setQuery($query);
                    $results = $db->loadObject();

                    if ($results)
                    {
                        $textValue[] = $results->title;
                    }
                }

                $oneItem->fk_programme_id = !empty($textValue) ? implode(', ', $textValue) : $oneItem->fk_programme_id;
            } else {
                $oneItem->fk_programme_id = "";
            }
					$oneItem->academic_greek = !empty($oneItem->academic_greek) ? Text::_('COM_ADIP_REPORTS_ACADEMIC_GREEK_OPTION_' . preg_replace('/[^A-Za-z0-9\_-]/', '',strtoupper(str_replace(' ', '_',$oneItem->academic_greek)))) : '';
            $oneItem->files = $this->getFiles($oneItem->id);
        }

        return $items;
	}

    public function getFiles($report_id)
    {

        $db = Factory::getContainer()->get('DatabaseDriver');
        $query = $db->getQuery(true);
        $query
            ->select(array('f.*', 't.name as ftype'))
            ->from($db->quoteName('#__adip_attachments_files', 'f'))
            ->join('LEFT', '#__adip_attachements_filetype AS t ON t.`id` = f.`image`')
            ->where($db->quoteName('item_id') . ' = ' . $db->quote($db->escape($report_id)))
            ->where($db->quoteName('publish') . ' = ' . $db->quote($db->escape(1)))
            ->order('f.`image` ASC');

        $db->setQuery($query);
        $items = $db->loadObjectList();

        foreach ($items as &$item) {
            $languages = LanguageHelper::getLanguages('lang_code');
            $sefTag = $languages[$item->language]->sef;
            $item->langImage = HTMLHelper::_('image', 'mod_languages/' . $sefTag . '.gif', $languages[$item->language]->title_native, ['title' => $languages[$item->language]->title_native], true);
        }

        return $items;

    }
}
