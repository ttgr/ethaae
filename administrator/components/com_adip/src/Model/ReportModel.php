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

use Joomla\CMS\Response\JsonResponse;
use \Joomla\CMS\Table\Table;
use \Joomla\CMS\Factory;
use \Joomla\CMS\Language\Text;
use \Joomla\CMS\Plugin\PluginHelper;
use \Joomla\CMS\MVC\Model\AdminModel;
use \Joomla\CMS\Event\Model;
use \Joomla\CMS\Router\Route;
use \Joomla\CMS\HTML\HTMLHelper;
use Joomla\CMS\Language\LanguageHelper;
use \Joomla\CMS\Uri\Uri;
use \Joomla\Filesystem\File;
use Adip\Component\Adip\Administrator\Helper\CWAttachmentsHelper;
use Adip\Component\Adip\Administrator\Helper\AdipHelper;

/**
 * Report model.
 *
 * @since  2.1.0
 */
class ReportModel extends AdminModel
{
	/**
	 * @var    string  The prefix to use with controller messages.
	 *
	 * @since  2.1.0
	 */
	protected $text_prefix = 'COM_ADIP';

	/**
	 * @var    string  Alias to manage history control
	 *
	 * @since  2.1.0
	 */
	public $typeAlias = 'com_adip.report';

	/**
	 * @var    null  Item data
	 *
	 * @since  2.1.0
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
	 * @since   2.1.0
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
	 * @since   2.1.0
	 */
	public function getForm($data = array(), $loadData = true)
	{
		// Initialise variables.
		$app = Factory::getApplication();

		// Get the form.
		$form = $this->loadForm(
								'com_adip.report', 
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
	 * @since   2.1.0
	 */
	protected function loadFormData()
	{
		// Check the session for previously entered form data.
		$data = Factory::getApplication()->getUserState('com_adip.edit.report.data', array());

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
	 * @since   2.1.0
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
		if (!$user->authorise('core.create', 'com_adip'))
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

				if (!empty($table->fk_institute_id))
				{
					if (is_array($table->fk_institute_id))
					{
						$table->fk_institute_id = implode(',', $table->fk_institute_id);
					}
				}
				else
				{
					$table->fk_institute_id = '';
				}

				if (!empty($table->fk_deprtement_id))
				{
					if (is_array($table->fk_deprtement_id))
					{
						$table->fk_deprtement_id = implode(',', $table->fk_deprtement_id);
					}
				}
				else
				{
					$table->fk_deprtement_id = '';
				}

				if (!empty($table->fk_programme_id))
				{
					if (is_array($table->fk_programme_id))
					{
						$table->fk_programme_id = implode(',', $table->fk_programme_id);
					}
				}
				else
				{
					$table->fk_programme_id = '';
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
	 * @since   2.1.0
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
				$db->setQuery('SELECT MAX(ordering) FROM #__adip_reports');
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
                ->select($db->quoteName('dep_id'))
                ->select($db->quoteName('dep_title'))
                ->select($db->quoteName('parent_type_id'))
                ->from($db->quoteName('#__adip_all_departments'))
                ->where($db->quoteName('inst_id') . ' = '. $db->quote($db->escape($institutionID)))
                ->order('dep_title ASC');
            $db->setQuery($query);
            $results = $db->loadRowList();
            return $results;
        }

        return array();
    }


    public function getProgrammes($departmentID = 0,$type_id = 0) {
        $db = Factory::getContainer()->get('DatabaseDriver');
        $query = $db->getQuery(true);
        if (intval($departmentID) > 0) {
            $query
                ->select($db->quoteName('prog_id'))
                ->select("CONCAT(prog_type,' :: ',prog_name)")
                ->from($db->quoteName('#__adip_programmes_list'))
                ->where($db->quoteName('dep_id') . ' = '. $db->quote($db->escape($departmentID)))
                ->where($db->quoteName('parent_type_id') . ' = '. $db->quote($db->escape($type_id)))
                ->order('prog_type, prog_name ASC');
            $db->setQuery($query);
            $results = $db->loadRowList();
            return $results;
        }

        return array();

    }


    public function getFilesLIsting(int $report_id) {

        $db = Factory::getContainer()->get('DatabaseDriver');
        $session = Factory::getApplication()->getSession();
        $sid = md5($session->getId());

        $query = $db->getQuery(true);
        $query
            ->select(array('f.*','t.name as ftype'))
            ->from($db->quoteName('#__adip_attachments_files','f'))
            ->join('LEFT', '#__adip_attachements_filetype AS t ON t.`id` = f.`image`')
            ->where($db->quoteName('item_id') . ' = '. $db->quote($db->escape($report_id)));
        $db->setQuery($query);
        $rows = $db->loadAssocList();
        foreach ($rows as &$row) {
            $popupOptions = [
                'popupType'  => 'iframe',
                'textHeader' => TEXT::sprintf('COM_ADIP_EDIT_FILE_LABEL',$row['caption']),
                'src'        => Route::_('?option=com_adip&view=report&layout=editfile&tmpl=component&id=' . (int) $row['id']),
            ];
            $link = HTMLHelper::_(
                'link',
                '#',
                HTMLHelper::_('image', 'com_adip/edit.png', null, null, true),
                [
                    'class'                 => 'alert-link',
                    'data-joomla-dialog'    => htmlspecialchars(json_encode($popupOptions, JSON_UNESCAPED_SLASHES), ENT_COMPAT, 'UTF-8'),
                    'data-checkin-url'      => '',
                    'data-close-on-message' => '',
                    'data-reload-on-close'  => '',
                ],
            );
            $row['editlink'] = $link;


            $link = HTMLHelper::_(
                'link',
                Uri::root()."index.php?option=com_cwattachments&task=download&id=".md5($row['id'])."&sid=".$sid,
                HTMLHelper::_('image', 'com_adip/view.png', null, null, true),
                [
                    'class'                 => 'alert-link',
                ],
            );

            $row['downloadlink'] = $link;
            $row['editurl'] = Uri::current().$popupOptions['src'];
            $languages = LanguageHelper::getLanguages('lang_code');
            $sefTag = $languages[$row['language']]->sef;
            $row['langImage'] = HTMLHelper::_('image', 'mod_languages/' . $sefTag . '.gif', $languages[$row['language']]->title_native, ['title' => $languages[$row['language']]->title_native], true);
        }

        return $rows;
    }

    public function getFile($fileID) {
        $db = Factory::getContainer()->get('DatabaseDriver');
        $query = $db->getQuery(true);
        $query
            ->select(array('f.*'))
            ->from($db->quoteName('#__adip_attachments_files','f'))
            ->where($db->quoteName('id') . ' = '. $db->quote($db->escape($fileID)));
        $db->setQuery($query);

        return $db->loadObject();
    }

    public function updateFileMetadata($data) {
        $db = Factory::getContainer()->get('DatabaseDriver');
        $query = $db->getQuery(true);

        $fields = array(
            $db->quoteName('caption') . ' = ' .  $db->quote($data['caption']),
            $db->quoteName('language') . ' = ' .  $db->quote($data['language']),
            $db->quoteName('image') . ' = ' . $data['image'],
            $db->quoteName('publish') . ' = ' . $data['publish'],
        );

// Conditions for which records should be updated.
        $conditions = array(
            $db->quoteName('id') . ' = '.$data['id'],
        );

        $query->update($db->quoteName('#__adip_attachments_files'))->set($fields)->where($conditions);
        $db->setQuery($query);
        try {
            $db->execute();
            Factory::getApplication()->enqueueMessage(
                Text::sprintf('COM_ADIP_TABLE_FILES_EDIT_SUCCESS_MSG',$data['caption']), 'success');

        } catch (\Exception $e) {
            Factory::getApplication()->enqueueMessage(
                Text::sprintf('JLIB_DATABASE_ERROR_FUNCTION_FAILED', $e->getCode(), $e->getMessage()),
                'warning'
            );
        }
    }


    public function saveFile($data) {

        try {
            $galleryID = CWAttachmentsHelper::getGalleryId($row->id);
            if ($galleryID == false) {
                $galleryID = CWAttachmentsHelper::createGallery($row->id, $item->univ_name . ' - ' . $item->dep_name);
            }

        } catch (Exception $e) {
            echo 'CWAttachmentsHelper::createGallery exception: ',  $e->getMessage(), "\n";die;
        }
    }


    public function save($data)
    {

        dump($data);
//        $s_date = $data['session_date'];
//        $v_from = $data['valid_from'];
//        $v_to = $data['valid_to'];


//        $data['session_date'] = AdipHelper::getDateISOFormat($data['session_date']);
//        $data['valid_from'] = AdipHelper::getDateISOFormat($data['valid_from']);
//        $data['valid_to'] = AdipHelper::getDateISOFormat($data['valid_to']);

//        $t = explode("-",$data['fk_deprtement_id']);
//        if(is_array($t) && count($t) >0) {
//            $data['fk_deprtement_id'] = $t[0];
//        } else {
//            throw new Exception('The Department ID:'.$data['fk_deprtement_id'].' is invalid. Contact the Admin');
//        }

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
        }
//        $data['session_date'] = $s_date;
//        $data['valid_from'] = $v_from;
//        $data['valid_to'] = $v_to;

        return $result;
    }

}
