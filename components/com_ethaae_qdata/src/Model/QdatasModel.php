<?php
/**
 * @version    CVS: 1.0.2
 * @package    Com_Ethaae_qdata
 * @author     Tasos Triantis <tasos.tr@gmail.com>
 * @copyright  2025 Tasos Triantis
 * @license    GNU General Public License version 2 or later; see LICENSE.txt
 */

namespace Ethaaeqdata\Component\Ethaae_qdata\Site\Model;
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
use \Ethaaeqdata\Component\Ethaae_qdata\Site\Helper\Ethaae_qdataHelper;


/**
 * Methods supporting a list of Ethaae_qdata records.
 *
 * @since  1.0.2
 */
class QdatasModel extends ListModel
{

    var $total = 0;
	/**
	 * Constructor.
	 *
	 * @param   array  $config  An optional associative array of configuration settings.
	 *
	 * @see    JController
	 * @since  1.0.2
	 */
	public function __construct($config = array())
	{
		if (empty($config['filter_fields']))
		{
			$config['filter_fields'] = array(
				'id', 'a.id',
				'collectionyear', 'a.collectionyear',
				'unitcode', 'a.unitcode',
				'unitid', 'a.unitid',
				'metricid', 'a.metricid',
				'metrictype', 'a.metrictype',
				'value', 'a.value',
				'institutecode', 'a.institutecode',
				'instituteid', 'a.instituteid',
				'metriccode', 'a.metriccode',
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
	 * @since   1.0.2
	 */
	protected function populateState($ordering = null, $direction = null)
	{
		// List state information.
		parent::populateState('a.id', 'ASC');

		$app = Factory::getApplication();
		$list = $app->getUserState($this->context . '.list');

		$value = $app->getUserState($this->context . '.list.limit', $app->get('list_limit', 25));
		$list['limit'] = $value;
		
		$this->setState('list.limit', $value);

		$value = $app->input->get('limitstart', 0, 'uint');
		$this->setState('list.start', $value);

		$ordering  = $this->getUserStateFromRequest($this->context .'.filter_order', 'filter_order', 'a.id');
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
	 * @since   1.0.2
	 */
	protected function getListQuery()
	{
			// Create a new query object.
            $db = Factory::getContainer()->get('DatabaseDriver');
			$query = $db->getQuery(true);
            $lang = explode("-",Factory::getApplication()->getLanguage()->getTag());
			// Select the required fields from the table.
			$query->select('DISTINCT a.collectionyear,a.unitcode,a.unitid,i.unit_type_id,i.unitTypeCategoryId,
			                i.unitTypeGroupId,i.unit_grouping,i.short_code_'.$lang[0].' as short_code,i.title_'.$lang[0].' as unit_title,
			                i.startupdate,i.ceasedate');
			$query->from('`#__ethaae_qdata_data` AS a');
            $query->join('INNER', '#__ethaae_institutes_structure AS i ON i.id = a.unitid');
//    		$query->join('LEFT', '#__ethaae_qdata_collections AS #__ethaae_qdata_collections_4200256 ON #__ethaae_qdata_collections_4200256.`collection_year` = a.`collectionyear`');
		// Join over the foreign key 'unitid'


		// Filtering collectionyear
		$filter_collectionyear = $this->state->get("filter.collectionyear");

		if ($filter_collectionyear)
		{
			$query->where("a.`collectionyear` = '".$db->escape($filter_collectionyear)."'");
		} else {

            $query->where("a.`collectionyear` = '".$db->escape(date('Y'))."'");
        }

		// Filtering metricid
		$filter_metricid = $this->state->get("filter.metricid");

		if ($filter_metricid)
		{
			$query->where("a.`metricid` = '".$db->escape($filter_metricid)."'");
		}

		// Filtering instituteid
		$filter_instituteid = $this->state->get("filter.instituteid");

		if ($filter_instituteid)
		{
			$query->where("a.`instituteid` = '".$db->escape($filter_instituteid)."'");
		} else {
            $query->where("i.`unit_grouping` = '1'");
        }

        if($filter_instituteid && $filter_collectionyear)
        {
            $query->where("a.`unitid` != '".$db->escape($filter_instituteid)."'");
        }
			
			// Add the list ordering clause.
        $query->order('a.collectionyear DESC, i.unit_grouping,i.exTEI, i.title_'.$lang[0].' ASC');

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

        $db = Factory::getContainer()->get('DatabaseDriver');

		$items = parent::getItems();


		foreach ($items as $item)
		{

			if (isset($item->collectionyear))
			{

                $item->academic_reference_year = $this->getCollectionTitle($item->collectionyear);
			}

            $item->variables = $this->getVariablesValues($item->unitid, $item->collectionyear);
		}
		return $items;
	}


    public function getUnitData(int $unitID, int $collectionyear) : object {
        $lang = explode("-",Factory::getApplication()->getLanguage()->getTag());
        $db = Factory::getContainer()->get('DatabaseDriver');
        $query = $db->getQuery(true);
        $query
            ->select('i.unitTypeCategoryId,i.unitTypeGroupId,i.unit_grouping,i.short_code_'.$lang[0].' as short_code,i.title_'.$lang[0].' as unit_title')
            ->from($db->quoteName('#__ethaae_institutes_structure', 'i'))
            ->where($db->quoteName('i.id') . ' = '. $db->quote($db->escape($unitID)));
        $db->setQuery($query);
        $item = $db->loadObject();
        $item->variables = $this->getVariablesValues($unitID,$collectionyear);
        $item->academic_reference_year = $this->getCollectionTitle($collectionyear);
        return $item;
    }

    private function getCollectionTitle(int $collectionyear) : string {
        $db = Factory::getContainer()->get('DatabaseDriver');
        $query = $db->getQuery(true);
        $query
            ->select('`c`.`academic_reference_year`')
            ->from($db->quoteName('#__ethaae_qdata_collections', 'c'))
            ->where($db->quoteName('c.collection_year') . ' = '. $db->quote($db->escape($collectionyear)));
        $db->setQuery($query);
        $results = $db->loadObject();
        if ($results)
        {
            return $results->academic_reference_year;
        }
        return "";
    }


    /**
     * Retrieves the values of parameters corresponding to a specified unit and, optionally, a collection year.
     *
     * @param   int  $unitID          The ID of the unit whose parameters are being retrieved.
     * @param   int  $collectionyear  (Optional) The target collection year to filter the results by.
     *                                Defaults to 0, which means that the year filter is not applied.
     *
     * @return  array  An associative array containing the metric ID, metric title (localized), and parameter value
     *                 for the specified unit (and year, if provided).
     *
     * Overview of functionality:
     * - This method constructs a database query with optional filtering by `collectionyear`.
     * - It retrieves the following fields:
     *      - `metricid` (ID of the metric),
     *      - Localized metric title (based on the current language tag),
     *      - `value` (parameter value).
     * - The results are fetched as an associative list and returned.
     *
     * Key Operations:
     * - Identifies the current language tag to select the localized metric title.
     * - Constructs and executes a query to join metric details with unit data.
     * - Ensures safety by escaping input values and using quoted names in the query.
     *
     * Example Usage:
     * - Call this function to get the metric values for a specific unit and year:
     *   `$values = $this->getVariablesValues(123, 2025);`
     */

    private function getVariablesValues(int $unitID, int $collectionyear = 0): array {

        $lang = explode("-",Factory::getApplication()->getLanguage()->getTag());

        $db = Factory::getContainer()->get('DatabaseDriver');
        $query = $db->getQuery(true);
        $query
            ->select('d.id,d.metricid,m.title_'.$lang[0].' as title,d.value, d.metrictype,m.description_'.$lang[0].' as description')
            ->from($db->quoteName('#__ethaae_qdata_data', 'd'))
            ->join('INNER', '#__ethaae_qdata_metrics as m On d.metricid = m.id');
        if ($collectionyear > 0) {
            $query->where($db->quoteName('d.collectionyear') . ' = '. $db->quote($db->escape($collectionyear)));
        }
        $query->where($db->quoteName('d.unitid') . ' = '. $db->quote($db->escape($unitID)));
        $query->order('title ASC');
        $db->setQuery($query);
        //Factory::getApplication()->enqueueMessage($db->replacePrefix((string) $query), 'notice');
        (array) $row = $db->loadObjectList();
        return $row;
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
			$app->enqueueMessage(Text::_("COM_ETHAAE_QDATA_SEARCH_FILTER_DATE_FORMAT"), "warning");
			$app->setUserState($this->context . '.filter', $filters);
		}

		return parent::loadFormData();
	}

    public function getTotal()
    {
        // Get a storage key.
        $query = $this->getListQuery();
        $db = Factory::getContainer()->get('DatabaseDriver');
        $db->setQuery($query);
        $items = $db->loadObjectList();
        return count($items);
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
