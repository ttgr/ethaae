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

use \Joomla\CMS\Table\Table;
use \Joomla\CMS\Factory;
use \Joomla\CMS\Language\Text;
use \Joomla\CMS\Plugin\PluginHelper;
use \Joomla\CMS\MVC\Model\AdminModel;
use \Joomla\CMS\Helper\TagsHelper;
use \Joomla\CMS\Filter\OutputFilter;
use \Joomla\CMS\Event\Model;

/**
 * Report model.
 *
 * @since  1.1.0
 */
class ReportModel extends AdminModel
{
	/**
	 * @var    string  The prefix to use with controller messages.
	 *
	 * @since  1.1.0
	 */
	protected $text_prefix = 'COM_ETHAAE_REPORTS';

	/**
	 * @var    string  Alias to manage history control
	 *
	 * @since  1.1.0
	 */
	public $typeAlias = 'com_ethaae_reports.report';

	/**
	 * @var    null  Item data
	 *
	 * @since  1.1.0
	 */
	protected $item = null;

	
	

	/**
	 * Returns a reference to the a Table object, always creating it.
	 *
	 * @param   string  $type    The table type to instantiate
	 * @param   string  $prefix  A prefix for the table class name. Optional.
	 * @param   array   $config  Configuration array for model. Optional.
	 *
	 * @return  Table    A database object
	 *
	 * @since   1.1.0
	 */
	public function getTable($type = 'Report', $prefix = 'Administrator', $config = array())
	{
		return parent::getTable($type, $prefix, $config);
	}

	/**
	 * Method to get the record form.
	 *
	 * @param   array    $data      An optional array of data for the form to interogate.
	 * @param   boolean  $loadData  True if the form is to load its own data (default case), false if not.
	 *
	 * @return  \JForm|boolean  A \JForm object on success, false on failure
	 *
	 * @since   1.1.0
	 */
	public function getForm($data = array(), $loadData = true)
	{
		// Initialise variables.
		$app = Factory::getApplication();

		// Get the form.
		$form = $this->loadForm(
								'com_ethaae_reports.report', 
								'report',
								array(
									'control' => 'jform',
									'load_data' => $loadData 
								)
							);

		

		if (empty($form))
		{
			return false;
		}

		return $form;
	}

	

	/**
	 * Method to get the data that should be injected in the form.
	 *
	 * @return  mixed  The data for the form.
	 *
	 * @since   1.1.0
	 */
	protected function loadFormData()
	{
		// Check the session for previously entered form data.
		$data = Factory::getApplication()->getUserState('com_ethaae_reports.edit.report.data', array());

		if (empty($data))
		{
			if ($this->item === null)
			{
				$this->item = $this->getItem();
			}

			$data = $this->item;
			

			// Support for multiple or not foreign key field: academic_greek
			$array = array();

			foreach ((array) $data->academic_greek as $value)
			{
				if (!is_array($value))
				{
					$array[] = $value;
				}
			}
			if(!empty($array)){

			$data->academic_greek = $array;
			}
		}

		return $data;
	}

	/**
	 * Method to get a single record.
	 *
	 * @param   integer  $pk  The id of the primary key.
	 *
	 * @return  mixed    Object on success, false on failure.
	 *
	 * @since   1.1.0
	 */
	public function getItem($pk = null)
	{
		
			if ($item = parent::getItem($pk))
			{
				if (isset($item->params))
				{
					$item->params = json_encode($item->params);
				}
				
				// Do any procesing on fields here if needed
			}

			return $item;
		
	}

	/**
	 * Method to duplicate an Report
	 *
	 * @param   array  &$pks  An array of primary key IDs.
	 *
	 * @return  boolean  True if successful.
	 *
	 * @throws  Exception
	 */
	public function duplicate(&$pks)
	{
		$app = Factory::getApplication();
		$user = $app->getIdentity();
        $dispatcher = $this->getDispatcher();

		// Access checks.
		if (!$user->authorise('core.create', 'com_ethaae_reports'))
		{
			throw new \Exception(Text::_('JERROR_CORE_CREATE_NOT_PERMITTED'));
		}

		$context    = $this->option . '.' . $this->name;

		// Include the plugins for the save events.
		PluginHelper::importPlugin($this->events_map['save']);

		$table = $this->getTable();

		foreach ($pks as $pk)
		{
			
				if ($table->load($pk, true))
				{
					// Reset the id to create a new record.
					$table->id = 0;

					if (!$table->check())
					{
						throw new \Exception($table->getError());
					}
					
				if (!empty($table->fk_reporttype_id))
				{
					if (is_array($table->fk_reporttype_id))
					{
						$table->fk_reporttype_id = implode(',', $table->fk_reporttype_id);
					}
				}
				else
				{
					$table->fk_reporttype_id = '';
				}

				if (!empty($table->fk_unit_id))
				{
					if (is_array($table->fk_unit_id))
					{
						$table->fk_unit_id = implode(',', $table->fk_unit_id);
					}
				}
				else
				{
					$table->fk_unit_id = '';
				}


					// Trigger the before save event.
					$beforeSaveEvent = new Model\BeforeSaveEvent($this->event_before_save, [
						'context' => $context,
						'subject' => $table,
						'isNew'   => true,
						'data'    => $table,
					]);
					
						// Trigger the before save event.
						$result = $dispatcher->dispatch($this->event_before_save, $beforeSaveEvent)->getArgument('result', []);
					
					
					if (in_array(false, $result, true) || !$table->store())
					{
						throw new \Exception($table->getError());
					}

					// Trigger the after save event.
					$dispatcher->dispatch($this->event_after_save, new Model\AfterSaveEvent($this->event_after_save, [
						'context' => $context,
						'subject' => $table,
						'isNew'   => true,
						'data'    => $table,
					]));				
				}
				else
				{
					throw new \Exception($table->getError());
				}
			
		}

		// Clean cache
		$this->cleanCache();

		return true;
	}

	/**
	 * Prepare and sanitise the table prior to saving.
	 *
	 * @param   Table  $table  Table Object
	 *
	 * @return  void
	 *
	 * @since   1.1.0
	 */
	protected function prepareTable($table)
	{
		jimport('joomla.filter.output');

		if (empty($table->id))
		{
			// Set ordering to the last item if not set
			if (@$table->ordering === '')
			{
				$db = $this->getDbo();
				$db->setQuery('SELECT MAX(ordering) FROM #__ethaae_reports');
				$max             = $db->loadResult();
				$table->ordering = $max + 1;
			}
		}
	}


    public function getDepartments($institutionID = 0) {
        $db = Factory::getContainer()->get('DatabaseDriver');
        $query = $db->getQuery(true);
        if (intval($institutionID) > 0) {
            $query
                ->select($db->quoteName('i.id'))
                ->select('CONCAT(u.short_code_el,\'::\',i.title_el) as title')
                ->from($db->quoteName('#__ethaae_institutes_structure','i'))
                ->join('LEFT', '#__ethaae_unit_type AS u ON u.id = i.unit_type_id')
                ->where($db->quoteName('i.instituteid') . ' = '. $db->quote($db->escape($institutionID)))
                ->where($db->quoteName('i.parentunitid') . ' > '. $db->quote(0))
                ->where('u.grouping = 2')
                ->order('title ASC');
            $db->setQuery($query);
            $results = $db->loadRowList();
            return $results;
        }

        return array();
    }


    public function getProgrammes($departmentID = 0) {
        $db = Factory::getContainer()->get('DatabaseDriver');
        $query = $db->getQuery(true);
        if (intval($departmentID) > 0) {
            $query
                ->select($db->quoteName('i.id'))
                ->select('CONCAT(u.short_code_el,\'::\',i.title_el) as title')
                ->from($db->quoteName('#__ethaae_institutes_structure','i'))
                ->join('LEFT', '#__ethaae_unit_type AS u ON u.id = i.unit_type_id')
                ->where($db->quoteName('i.parentunitid') . ' = '. $db->quote($departmentID))
                ->where('u.grouping = 3')
                ->order('title ASC');
            $db->setQuery($query);
            $results = $db->loadRowList();
            return $results;
        }

        return array();

    }

}
