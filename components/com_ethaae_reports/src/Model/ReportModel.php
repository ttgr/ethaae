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
use \Joomla\Utilities\ArrayHelper;
use \Joomla\CMS\Language\Text;
use \Joomla\CMS\Table\Table;
use \Joomla\CMS\MVC\Model\ItemModel;
use \Joomla\CMS\Helper\TagsHelper;
use \Joomla\CMS\Object\CMSObject;
use \Joomla\CMS\User\UserFactoryInterface;
use Joomla\Utilities\IpHelper;
use \Ethaaereports\Component\Ethaae_reports\Site\Helper\Ethaae_reportsHelper;
use \Joomla\CMS\Uri\Uri;

/**
 * Ethaae_reports model.
 *
 * @since  1.1.0
 */
class ReportModel extends ItemModel
{
	public $_item;

	

	

	/**
	 * Method to auto-populate the model state.
	 *
	 * Note. Calling getState in this method will result in recursion.
	 *
	 * @return  void
	 *
	 * @since   1.1.0
	 *
	 * @throws Exception
	 */
	protected function populateState()
	{
		$app  = Factory::getApplication('com_ethaae_reports');
		$user = $app->getIdentity();

		// Check published state
		if ((!$user->authorise('core.edit.state', 'com_ethaae_reports')) && (!$user->authorise('core.edit', 'com_ethaae_reports')))
		{
			$this->setState('filter.published', 1);
			$this->setState('filter.archived', 2);
		}

		// Load state from the request userState on edit or from the passed variable on default
		if (Factory::getApplication()->input->get('layout') == 'edit')
		{
			$id = Factory::getApplication()->getUserState('com_ethaae_reports.edit.report.id');
		}
		else
		{
			$id = Factory::getApplication()->input->get('id');
			Factory::getApplication()->setUserState('com_ethaae_reports.edit.report.id', $id);
		}

		$this->setState('report.id', $id);

		// Load the parameters.
		$params       = $app->getParams();
		$params_array = $params->toArray();

		if (isset($params_array['item_id']))
		{
			$this->setState('report.id', $params_array['item_id']);
		}

		$this->setState('params', $params);
	}

	/**
	 * Method to get an object.
	 *
	 * @param   integer $id The id of the object to get.
	 *
	 * @return  mixed    Object on success, false on failure.
	 *
	 * @throws Exception
	 */
	public function getItem($id = null)
	{
		if ($this->_item === null)
		{
			$this->_item = false;

			if (empty($id))
			{
				$id = $this->getState('report.id');
			}

			// Get a level row instance.
			$table = $this->getTable();

			// Attempt to load the row.
			if ($table && $table->load($id))
			{
				

				// Check published state.
				if ($published = $this->getState('filter.published'))
				{
					if (isset($table->state) && $table->state != $published)
					{
						throw new \Exception(Text::_('COM_ETHAAE_REPORTS_ITEM_NOT_LOADED'), 403);
					}
				}

				// Convert the Table to a clean CMSObject.
				$properties  = $table->getProperties(1);
				$this->_item = ArrayHelper::toObject($properties, CMSObject::class);

				
			}

			if (empty($this->_item))
			{
				throw new \Exception(Text::_('COM_ETHAAE_REPORTS_ITEM_NOT_LOADED'), 404);
			}
		}

		

		 $container = \Joomla\CMS\Factory::getContainer();

		 $userFactory = $container->get(UserFactoryInterface::class);

		if (isset($this->_item->created_by))
		{
			$user = $userFactory->loadUserById($this->_item->created_by);
			$this->_item->created_by_name = $user->name;
		}

		 $container = \Joomla\CMS\Factory::getContainer();

		 $userFactory = $container->get(UserFactoryInterface::class);

		if (isset($this->_item->modified_by))
		{
			$user = $userFactory->loadUserById($this->_item->modified_by);
			$this->_item->modified_by_name = $user->name;
		}

		if (isset($this->_item->fk_reporttype_id) && $this->_item->fk_reporttype_id != '')
		{
			if (is_object($this->_item->fk_reporttype_id))
			{
				$this->_item->fk_reporttype_id = ArrayHelper::fromObject($this->_item->fk_reporttype_id);
			}

			$values = (is_array($this->_item->fk_reporttype_id)) ? $this->_item->fk_reporttype_id : explode(',',$this->_item->fk_reporttype_id);

			$textValue = array();

			foreach ($values as $value)
			{
				$db    = $this->getDbo();
				$query = $db->getQuery(true);

				$query
					->select('`#__ethaae_report_types_4052118`.`name`')
					->from($db->quoteName('#__ethaae_report_types', '#__ethaae_report_types_4052118'))
					->where($db->quoteName('id') . ' = ' . $db->quote($value));

				$db->setQuery($query);
				$results = $db->loadObject();

				if ($results)
				{
					$textValue[] = $results->name;
				}
			}

			$this->_item->fk_reporttype_id = !empty($textValue) ? implode(', ', $textValue) : $this->_item->fk_reporttype_id;

		}

		if (isset($this->_item->fk_unit_id) && $this->_item->fk_unit_id != '')
		{
			if (is_object($this->_item->fk_unit_id))
			{
				$this->_item->fk_unit_id = ArrayHelper::fromObject($this->_item->fk_unit_id);
			}

			$values = (is_array($this->_item->fk_unit_id)) ? $this->_item->fk_unit_id : explode(',',$this->_item->fk_unit_id);

			$textValue = array();

			foreach ($values as $value)
			{
				$db    = $this->getDbo();
				$query = $db->getQuery(true);

				$query
					->select('`#__ethaae_institutes_structure_4052221`.`title_el`')
					->from($db->quoteName('#__ethaae_institutes_structure', '#__ethaae_institutes_structure_4052221'))
					->where($db->quoteName('id') . ' = ' . $db->quote($value));

				$db->setQuery($query);
				$results = $db->loadObject();

				if ($results)
				{
					$textValue[] = $results->title_el;
				}
			}

			$this->_item->fk_unit_id = !empty($textValue) ? implode(', ', $textValue) : $this->_item->fk_unit_id;

		}

		return $this->_item;
	}
	


	/**
	 * Get an instance of Table class
	 *
	 * @param   string $type   Name of the Table class to get an instance of.
	 * @param   string $prefix Prefix for the table class name. Optional.
	 * @param   array  $config Array of configuration values for the Table object. Optional.
	 *
	 * @return  Table|bool Table if success, false on failure.
	 */
	public function getTable($type = 'Report', $prefix = 'Administrator', $config = array())
	{
		return parent::getTable($type, $prefix, $config);
	}

	/**
	 * Get the id of an item by alias
	 * @param   string $alias Item alias
	 *
	 * @return  mixed
	 * 
	 * @deprecated  No replacement
	 */
	public function getItemIdByAlias($alias)
	{
		$table      = $this->getTable();
		$properties = $table->getProperties();
		$result     = null;
		$aliasKey   = null;
		if (method_exists($this, 'getAliasFieldNameByView'))
		{
			$aliasKey   = $this->getAliasFieldNameByView('report');
		}
		

		if (key_exists('alias', $properties))
		{
			$table->load(array('alias' => $alias));
			$result = $table->id;
		}
		elseif (isset($aliasKey) && key_exists($aliasKey, $properties))
		{
			$table->load(array($aliasKey => $alias));
			$result = $table->id;
		}
		
			return $result;
		
	}

	/**
	 * Method to check in an item.
	 *
	 * @param   integer $id The id of the row to check out.
	 *
	 * @return  boolean True on success, false on failure.
	 *
	 * @since   1.1.0
	 */
	public function checkin($id = null)
	{
		// Get the id.
		$id = (!empty($id)) ? $id : (int) $this->getState('report.id');
				
		if ($id)
		{
			// Initialise the table
			$table = $this->getTable();

			// Attempt to check the row in.
			if (method_exists($table, 'checkin'))
			{
				if (!$table->checkin($id))
				{
					return false;
				}
			}
		}

		return true;
		
	}

	/**
	 * Method to check out an item for editing.
	 *
	 * @param   integer $id The id of the row to check out.
	 *
	 * @return  boolean True on success, false on failure.
	 *
	 * @since   1.1.0
	 */
	public function checkout($id = null)
	{
		// Get the user id.
		$id = (!empty($id)) ? $id : (int) $this->getState('report.id');

				
		if ($id)
		{
			// Initialise the table
			$table = $this->getTable();

			// Get the current user object.
			$user = Factory::getApplication()->getIdentity();

			// Attempt to check the row out.
			if (method_exists($table, 'checkout'))
			{
				if (!$table->checkout($user->get('id'), $id))
				{
					return false;
				}
			}
		}

		return true;
				
	}

	/**
	 * Publish the element
	 *
	 * @param   int $id    Item id
	 * @param   int $state Publish state
	 *
	 * @return  boolean
	 */
	public function publish($id, $state)
	{
		$table = $this->getTable();
				
		$table->load($id);
		$table->state = $state;

		return $table->store();
				
	}

	/**
	 * Method to delete an item
	 *
	 * @param   int $id Element id
	 *
	 * @return  bool
	 */
	public function delete($id)
	{
		$table = $this->getTable();

		
			return $table->delete($id);
		
	}

    /**
     * @param   int $sid Element id
     *
     * @return  bool
     */
    public function downloadFile($id,$sid) {

        $app = Factory::getApplication();
        $db = Factory::getContainer()->get('DatabaseDriver');
        $query = "SELECT * FROM #__session WHERE MD5(session_id) = ".$db->quote($sid);
        $db->setQuery($query);
        $session = $db->loadObject();

        $file = $this->getFile($id);
        if ($session && is_object($file) && (int) $file->id > 0 ) {

            set_time_limit(0);
            //Add a HIT
              $query = "UPDATE #__ethaae_reports_files SET hits = hits + 1"
                  . " WHERE id = ".$db->quote($file->id);
              $db->setQuery($query);
              $db->execute();

            $user = Factory::getUser($session->userid);
            $object = new \stdClass();
            $object->userid = $user->id;
            $object->file_id = $file->id;
            $object->date = date("Y-m-d H:i:s");
            $object->ip = IpHelper::getIp();

            $db->insertObject("#__ethaae_reports_files_user_download",$object);

            //Tasos Normally this is the one should be correct
            //$this->output_file( JPATH_SITE.$file->path, $file->caption, $file->type);

            //Tasos This is to allow Html5Lightbox to allow popup viewing of PDFs
            $link = Uri::root().$file->path;
            $app->redirect($link);
            die();
        } else {
            $app->enqueueMessage(Text::_('JGLOBAL_AUTH_ACCESS_DENIED'), 'error');
        }


        return true;
    }

    private function output_file($file, $name, $mime_type='') {
        $app = Factory::getApplication();
        //Check the file premission
        if(!is_readable($file)) {
            $msg = [
                "message"=>"File is not accessible",
                "file"=>$file,
            ];
            file_put_contents(JPATH_SITE.'/logs/files.download.error.log', print_r($msg, true));
            $app->enqueueMessage(Text::_('JGLOBAL_AUTH_ACCESS_DENIED'), 'error');
            return false;
        }

        if($mime_type==''){
            $mime_type="application/force-download";
        }

        $size = filesize($file);
        $name = rawurldecode($name);

        //turn off output buffering to decrease cpu usage
        @ob_end_clean();

        // required for IE, otherwise Content-Disposition may be ignored
        if(ini_get('zlib.output_compression'))
            ini_set('zlib.output_compression', 'Off');

        header('Content-Type: ' . $mime_type);
        header('Content-Disposition: attachment; filename="'.$name.'"');
        header("Content-Transfer-Encoding: binary");
        header('Accept-Ranges: bytes');
        header("Cache-control: private");
        header('Pragma: private');
        header("Expires: Mon, 26 Jul 1997 05:00:00 GMT");

        // multipart-download and download resuming support
        if(isset($_SERVER['HTTP_RANGE']))
        {
            list($a, $range) = explode("=",$_SERVER['HTTP_RANGE'],2);
            list($range) = explode(",",$range,2);
            list($range, $range_end) = explode("-", $range);
            $range=intval($range);
            if(!$range_end) {
                $range_end=$size-1;
            } else {
                $range_end=intval($range_end);
            }

            $new_length = $range_end-$range+1;
            header("HTTP/1.1 206 Partial Content");
            header("Content-Length: $new_length");
            header("Content-Range: bytes $range-$range_end/$size");
        } else {
            $new_length=$size;
            header("Content-Length: ".$size);
        }

        /* Will output the file itself */
        $chunksize = 1*(1024*1024); //changeable
        $bytes_send = 0;
        if ($file = fopen($file, 'r'))
        {
            if(isset($_SERVER['HTTP_RANGE']))
                fseek($file, $range);

            while(!feof($file) &&
                (!connection_aborted()) &&
                ($bytes_send<$new_length)
            )
            {
                $buffer = fread($file, $chunksize);
                echo($buffer);
                flush();
                $bytes_send += strlen($buffer);
            }
            fclose($file);
        } else
            //If no permissiion
            die('Error - can not open file.');
        //die
        die();

    }

    private function getFile($id) {
        $db = Factory::getContainer()->get('DatabaseDriver');
        $sql = "SELECT * FROM `#__ethaae_reports_files` as f WHERE MD5(f.id) = ".$db->quote($id);
        $db->setQuery($sql);
        return $db->loadObject();
    }

	
}
