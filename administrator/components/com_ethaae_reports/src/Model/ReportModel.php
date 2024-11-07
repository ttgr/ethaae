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

use Joomla\CMS\HTML\HTMLHelper;
use Joomla\CMS\Language\LanguageHelper;
use Joomla\CMS\Router\Route;
use \Joomla\CMS\Table\Table;
use \Joomla\CMS\Factory;
use \Joomla\CMS\Language\Text;
use \Joomla\CMS\Plugin\PluginHelper;
use \Joomla\CMS\MVC\Model\AdminModel;
use \Joomla\CMS\Helper\TagsHelper;
use \Joomla\CMS\Filter\OutputFilter;
use \Joomla\CMS\Event\Model;
use Ethaaereports\Component\Ethaae_reports\Administrator\Helper\Ethaae_reportsHelper;
use Joomla\CMS\Component\ComponentHelper;
use Joomla\CMS\Uri\Uri;
use \Joomla\Filesystem\File;
use Joomla\Utilities\ArrayHelper;

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
                $item->fk_deprtement_id = $item->params['fk_deprtement_id'];
                $item->fk_programme_id = $item->params['fk_programme_id'];
                $item->fk_other_unit_id = $item->params['fk_other_unit_id'];

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


    public function getDepartments(int $institutionID = 0) {
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


    public function getProgrammes(int $departmentID = 0) {
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

    public function getOtherUnits(int $departmentID = 0) {
        $db = Factory::getContainer()->get('DatabaseDriver');
        $query = $db->getQuery(true);
        if (intval($departmentID) > 0) {
            $query
                ->select($db->quoteName('i.id'))
                ->select('CONCAT(u.short_code_el,\'::\',i.title_el) as title')
                ->from($db->quoteName('#__ethaae_institutes_structure','i'))
                ->join('LEFT', '#__ethaae_unit_type AS u ON u.id = i.unit_type_id')
                ->where($db->quoteName('i.parentunitid') . ' = '. $db->quote($departmentID))
                ->where('u.grouping = 4')
                ->order('title ASC');
            $db->setQuery($query);
            $results = $db->loadRowList();
            return $results;
        }

        return array();

    }



    //To-Do: Delete Physical Files too
    public function deleteFile(int $id): bool
    {
        $canDo = Ethaae_reportsHelper::getActions();

        if ($id > 0 && $canDo->get('core.delete')) {

            $db = Factory::getContainer()->get('DatabaseDriver');
            $query = $db->getQuery(true);

            $conditions = array(
                $db->quoteName('id') . ' = '.$db->quote($id),
            );
            $query->delete($db->quoteName('#__ethaae_reports_files'));
            $query->where($conditions);
            $db->setQuery($query);

            if($result = $db->execute()) {
                return true;
            } else {
                return false;
            }

        }
        return true;
    }


    public function save($data)
    {
//        $s_date = $data['session_date'];
//        $v_from = $data['valid_from'];
//        $v_to = $data['valid_to'];
//
//
//        $data['session_date'] = Ethaae_reportsHelper::getDateISOFormat($data['session_date']);
//        $data['valid_from'] = Ethaae_reportsHelper::getDateISOFormat($data['valid_from']);
//        $data['valid_to'] = Ethaae_reportsHelper::getDateISOFormat($data['valid_to']);
        $data['params'] = [
            "fk_reporttype_id" => $data['fk_reporttype_id'],
            "fk_institute_id" => $data['fk_institute_id'],
            "fk_deprtement_id" => $data['fk_deprtement_id'],
            "fk_programme_id" => $data['fk_programme_id'],
            "fk_other_unit_id" => $data['fk_other_unit_id'],
        ];
        $data['title'] = Ethaae_reportsHelper::getUnitInfo($data['fk_unit_id'])->title;
        $data['fk_parent_id'] = Ethaae_reportsHelper::getUnitInfo($data['fk_unit_id'])->parentunitid;




        try {
            $result = parent::save($data);
            if ($result) {
                $id = $this->getState($this->getName().'.id');
                $item = $this->getItem($id);
                return true;
            }
        } catch (\Exception $e) {
            Factory::getApplication()->enqueueMessage(
                Text::sprintf('JLIB_DATABASE_ERROR_FUNCTION_FAILED', $e->getCode(), $e->getMessage()),
                'warning'
            );
            return false;
        }



//        $data['session_date'] = $s_date;
//        $data['valid_from'] = $v_from;
//        $data['valid_to'] = $v_to;

        return true;
    }



    public function registerFile(array $data) {

//        file_put_contents(JPATH_SITE.'/tmp/files.txt', print_r($data, true).PHP_EOL , FILE_APPEND | LOCK_EX);

        $params = ComponentHelper::getParams('com_ethaae_reports');

        $prefix = $data['report'].'_';
        $file = $data['file'];
        $row = new \stdClass();

        $row->path = $params->get('upload_dir','/images/reports/').'/';

        $row->fk_report_id = $data['report'];
        $row->rid = md5(time().rand());
        $row->type = 'application/pdf';
        $row->name = $file['name'];
        $ext = pathinfo($file['name'], PATHINFO_EXTENSION);
        $filename = $prefix.md5($file['name'].rand()).'.'.$ext;
        $row->path = $row->path.$filename;
        $row->size = $file['size'];
        $row->caption = $file['name'];
        $row->state = 1;
        $row->fk_file_id = 1;
        $row->language = 'el-GR';
        $row->created = date("Y-m-d H:i:s");
        $row->modified = '0000-00-00 00:00:00';
        $row->created_by = $data['user'];
        $row->modified_by = 0;
        $row->hits = 0;
//        file_put_contents(JPATH_SITE.'/tmp/files.txt', print_r($row, true).PHP_EOL , FILE_APPEND | LOCK_EX);
        try {
            File::copy($file['tmp_name'],JPATH_SITE.$row->path);
        } catch (\Exception $e) {
            return array('message'=>'Unable to move tmp file '.  $e->getMessage().' '.JPATH_SITE.$row->path);
        }
        try {
            Factory::getContainer()->get('DatabaseDriver')->insertObject('#__ethaae_reports_files', $row, 'id');
            return array('message'=>Text::sprintf('COM_ETHAAE_REPORTS_FORM_UPLOAD_FILE_SUCCESS',$row->name));
        } catch (\Exception $e) {
            return array('message'=>'Register File exception '.  $e->getMessage());
        }
    }

    public function getFilesLIsting(int $report_id) {

        $db = Factory::getContainer()->get('DatabaseDriver');
        $session = Factory::getApplication()->getSession();
        $sid = md5($session->getId());

        $query = $db->getQuery(true);
        $query
            ->select(array('f.*','t.name as ftype'))
            ->from($db->quoteName('#__ethaae_reports_files','f'))
            ->join('LEFT', '#__ethaae_reports_filetype AS t ON t.`id` = f.`fk_file_id`')
            ->where($db->quoteName('fk_report_id') . ' = '. $db->quote($db->escape($report_id)));
        $db->setQuery($query);
        $rows = $db->loadAssocList();
        foreach ($rows as &$row) {
            $popupOptions = [
                'popupType'  => 'iframe',
                'textHeader' => TEXT::sprintf('COM_ADIP_EDIT_FILE_LABEL',$row['caption']),
                'src'        => 'index.php?option=com_ethaae_reports&view=reportsfile&layout=edit&id=' . (int) $row['id'],
            ];
            $link = HTMLHelper::_(
                'link',
                '#',
                HTMLHelper::_('image', 'com_ethaae_reports/edit.png', null, null, true),
                [
                    'class'                 => 'alert-link',
                    'data-joomla-dialog'    => htmlspecialchars(json_encode($popupOptions, JSON_UNESCAPED_SLASHES), ENT_COMPAT, 'UTF-8'),
                    'data-callback-func'  => 'refreshFileTables',
                ],
            );
            $row['editlink'] = $link;


            $popupOptions = [
                'popupType'  => 'iframe',
                'textHeader' => $row['caption'],
                'src'        => Uri::root()."index.php?option=com_ethaae_reports&task=report.download&id=".md5($row['id'])."&sid=".$sid
            ];
            $link = HTMLHelper::_(
                'link',
                "#",
                HTMLHelper::_('image', 'com_ethaae_reports/view.png', null, null, true),
                [
                    'class'                 => 'alert-link',
                    'data-joomla-dialog'    => htmlspecialchars(json_encode($popupOptions, JSON_UNESCAPED_SLASHES), ENT_COMPAT, 'UTF-8'),
                ],
            );

            $row['downloadlink'] = $link;




            $row['editurl'] = Uri::current().$popupOptions['src'];
            $languages = LanguageHelper::getLanguages('lang_code');
            $sefTag = $languages[$row['language']]->sef;
            $row['langImage'] = HTMLHelper::_('image', 'mod_languages/' . $sefTag . '.gif', $languages[$row['language']]->title_native, ['title' => $languages[$row['language']]->title_native], true);
            $row['state_link'] = HTMLHelper::_('jgrid.published', $row['state'], 0, 'reports.', false, 'cb');

            $link = HTMLHelper::_(
                'link',
                "javascript:deleteReportFile('". (int) $row['id']."');",
                HTMLHelper::_('image', 'com_ethaae_reports/bin.png', null, null, true),
                [
                    'class'                 => 'alert-link',
                ],
            );
            $row['delete_link'] = $link;
        }

        return $rows;
    }


//To-DO: create a delete function that deletes files too
//    public function delete(&$pks) {
//
//        $pks  = ArrayHelper::toInteger((array) $pks);
//
//
//    }

}
