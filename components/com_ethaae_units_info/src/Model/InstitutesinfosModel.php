<?php
/**
 * @version    CVS: 1.1.0
 * @package    Com_Ethaae_units_info
 * @author     Tasos Triantis <tasos.tr@gmail.com>
 * @copyright  2024 Tasos Triantis
 * @license    GNU General Public License version 2 or later; see LICENSE.txt
 */

namespace Ethaaunitsinfo\Component\Ethaae_units_info\Site\Model;
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
use \Ethaaunitsinfo\Component\Ethaae_units_info\Site\Helper\Ethaae_units_infoHelper;


/**
 * Methods supporting a list of Ethaae_units_info records.
 *
 * @since  1.1.0
 */
class InstitutesinfosModel extends ListModel
{
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
				'id', 'a.id',
				'state', 'a.state',
				'ordering', 'a.ordering',
				'created_by', 'a.created_by',
				'modified_by', 'a.modified_by',
				'created', 'a.created',
				'modified', 'a.modified',
				'fk_unit_id', 'a.fk_unit_id',
				'description_el', 'a.description_el',
				'url_en', 'a.url_en',
				'url_el', 'a.url_el',
				'description_en', 'a.description_en',
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
		parent::populateState('i.title_el', 'ASC');

		$app = Factory::getApplication();
		$list = $app->getUserState($this->context . '.list');

		$value = $app->getUserState($this->context . '.list.limit', $app->get('list_limit', 25));
		$list['limit'] = $value;
		
		$this->setState('list.limit', $value);

		$value = $app->input->get('limitstart', 0, 'uint');
		$this->setState('list.start', $value);

		$ordering  = $this->getUserStateFromRequest($this->context .'.filter_order', 'filter_order', 'i.title_el');
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
	 * @since   1.1.0
	 */
	protected function getListQuery()
	{
            $app = Factory::getApplication();
            $lang = substr($app->getLanguage()->getTag(),0,2);
			// Create a new query object.
			$db    = $this->getDatabase();
			$query = $db->getQuery(true);

			// Select the required fields from the table.
			$query->select(
						$this->getState(
								'list.select', 'DISTINCT a.*'
						)
				);

			$query->from('`#__ethaae_institutes_info` AS a');
			
		// Join over the foreign key 'fk_unit_id'
		$query->select(array('i.title_'.$lang.' as name','SUBSTRING(i.title_'.$lang.',1,1) as initial','i.unitcode'));
		$query->join('LEFT', '#__ethaae_institutes_structure AS i ON i.`id` = a.`fk_unit_id`');

		if (!Factory::getApplication()->getIdentity()->authorise('core.edit', 'com_ethaae_units_info'))
		{
			$query->where('a.state = 1');
		}
		else
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
				}
			}
			

		// Filtering fk_unit_id
		$filter_fk_unit_id = $this->state->get("filter.fk_unit_id");

		if ($filter_fk_unit_id)
		{
			$query->where("a.`fk_unit_id` = '".$db->escape($filter_fk_unit_id)."'");
		}

			
			
			// Add the list ordering clause.
			$orderCol  = $this->state->get('list.ordering', 'i.title_el');
			$orderDirn = $this->state->get('list.direction', 'ASC');

			if ($orderCol && $orderDirn)
			{
				$query->order($db->escape('i.title_'.$lang . ' ASC'));
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
        $extensions = [
                        'jpg',
                        'jpeg',
                        'png',
                        'JPG',
                        'JPEG',
                        'PNG',
                      ];
        $db = $this->getDatabase();
        $db->setQuery($this->getListQuery());
        $rows = $db->loadObjectList();
        $items = [];
        $preLetter = "";
        foreach ($rows as $row) {
            $curLetter = $row->initial;
            if($preLetter !== $curLetter) {
                $preLetter = $curLetter;
            }
            $row->logo = $this->getImage(strtolower($row->unitcode),'/images/institutes/logos/',$extensions);
            $items[$preLetter][] = $row;
        }

		return $items;
	}

    private function getImage(string $img,string $path,array $extensions)
    {
//        $paths = [];
        foreach ($extensions as $ext) {
//            $paths[] = JPATH_SITE.$path.$img.'.'.$ext;
            if (is_file(JPATH_SITE.$path.$img.'.'.$ext)) {
                return $path.$img.'.'.$ext;
            }
//            file_put_contents(JPATH_SITE.'/tmp/paths.txt', print_r($paths, true).PHP_EOL , FILE_APPEND | LOCK_EX);
        }

         return false;
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
			$app->enqueueMessage(Text::_("COM_ETHAAE_UNITS_INFO_SEARCH_FILTER_DATE_FORMAT"), "warning");
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
