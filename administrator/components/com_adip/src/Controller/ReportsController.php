<?php
/**
 * @version    CVS: 2.1.0
 * @package    Com_Adip
 * @author     Tasos Triantis <tasos.tr@gmail.com>
 * @copyright  2024 Tasos Triantis
 * @license    GNU General Public License version 2 or later; see LICENSE.txt
 */

namespace Adip\Component\Adip\Administrator\Controller;

\defined('_JEXEC') or die;

use Joomla\CMS\Application\SiteApplication;
use Joomla\CMS\Factory;
use Joomla\CMS\Language\Multilanguage;
use Joomla\CMS\Language\Text;
use Joomla\CMS\MVC\Controller\AdminController;
use Joomla\CMS\Router\Route;
use Joomla\CMS\Uri\Uri;
use Joomla\Utilities\ArrayHelper;
use Joomla\Session\Session;
use Joomla\CMS\Response\JsonResponse;
use \Joomla\CMS\Utility\Utility;

/**
 * Reports list controller class.
 *
 * @since  2.1.0
 */
class ReportsController extends AdminController
{
	/**
	 * Method to clone existing Reports
	 *
	 * @return  void
	 *
	 * @throws  Exception
	 */
	public function duplicate()
	{
		// Check for request forgeries
		$this->checkToken();

		// Get id(s)
		$pks = $this->input->post->get('cid', array(), 'array');

		try
		{
			if (empty($pks))
			{
				throw new \Exception(Text::_('COM_ADIP_NO_ELEMENT_SELECTED'));
			}

			ArrayHelper::toInteger($pks);
			$model = $this->getModel();
			$model->duplicate($pks);
			$this->setMessage(Text::_('COM_ADIP_ITEMS_SUCCESS_DUPLICATED'));
		}
		catch (\Exception $e)
		{
			Factory::getApplication()->enqueueMessage($e->getMessage(), 'warning');
		}

		$this->setRedirect('index.php?option=com_adip&view=reports');
	}

	/**
	 * Proxy for getModel.
	 *
	 * @param   string  $name    Optional. Model name
	 * @param   string  $prefix  Optional. Class prefix
	 * @param   array   $config  Optional. Configuration array for model
	 *
	 * @return  object	The Model
	 *
	 * @since   2.1.0
	 */
	public function getModel($name = 'Report', $prefix = 'Administrator', $config = array())
	{
		return parent::getModel($name, $prefix, array('ignore_request' => true));
	}

	

	/**
	 * Method to save the submitted ordering values for records via AJAX.
	 *
	 * @return  void
	 *
	 * @since   2.1.0
	 *
	 * @throws  Exception
	 */
	public function saveOrderAjax()
	{
		// Get the input
		$pks   = $this->input->post->get('cid', array(), 'array');
		$order = $this->input->post->get('order', array(), 'array');

		// Sanitize the input
		ArrayHelper::toInteger($pks);
		ArrayHelper::toInteger($order);

		// Get the model
		$model = $this->getModel();

		// Save the ordering
		$return = $model->saveorder($pks, $order);

		if ($return)
		{
			echo "1";
		}

		// Close the application
		Factory::getApplication()->close();
	}


    public function getDepartmentsAjax()
    {
        try
        {

            $app = Factory::getApplication();
            $institutionid = $app->input->get('institutionid',0);

            // Check if user token is valid.
            if (!$this->checkToken('get'))
            {
                $app->enqueueMessage(Text::_('JINVALID_TOKEN'), 'error');
                echo new JsonResponse;
            }

            $model = $this->getModel();
            $r = $model->getDepartments($institutionid);
            $results ="[|".Text::_('COM_ADIP_REPORTS_FK_DEPRTEMENT_ID_FILTER');
            foreach ($r as $value) {
//                $results .= ",".$value[0].'-'.$value[2].'|'.str_replace(","," ",$value[1]);
                $results .= ",".$value[0].'|'.str_replace(","," ",$value[1]);
            }
            $results .="]";


            echo new JsonResponse($results);
        }
        catch(Exception $e)
        {
            echo new JsonResponse($e);
        }

    }

    public function getProgramsAjax()
    {
        try
        {

            $app = Factory::getApplication();
            $id = $app->input->get('department',0);
            $type_id = $app->input->get('type',0);

            // Check if user token is valid.
            if (!$this->checkToken('get'))
            {
                $app->enqueueMessage(Text::_('JINVALID_TOKEN'), 'error');
                echo new JsonResponse;
            }

            $model = $this->getModel();
            $r = $model->getProgrammes($id,$type_id);
            $results ="[|".Text::_('COM_ADIP_REPORTS_FK_PROGRAMME_ID_FILTER');
            foreach ($r as $value) {
                $results .= ",".$value[0].'|'.str_replace(","," ",$value[1]);
            }
            $results .="]";


            echo new JsonResponse($results);
        }
        catch(Exception $e)
        {
            echo new JsonResponse($e);
        }

    }

    public function uploadFileAjax() {
        $app = Factory::getApplication();

        // Check if user token is valid.
        if (!$this->checkToken('get'))
        {
            $app->enqueueMessage(JText::_('JINVALID_TOKEN'), 'error');
            echo new JsonResponse;
        }

        try
        {
            $model = $this->getModel();


            $data['doc_id'] = $app->input->get('id',0,'INT');
            $data['user'] = $app->input->get('user',0,'INT');
            $data['file'] = $app->input->files->get('file');

            //file_put_contents(JPATH_SITE.'/tmp/files.txt', print_r($data, true));

            if (!$data['file']) {
                $maxSize = number_format(Utility::getMaxUploadSize()/1048576, 2);
                echo "Unable to upload file. Please make sure that the file does not overpass the Max Allowed Filesize of ". $maxSize. " MB";
                exit();
            }


            $result = array();

            echo new JsonResponse($result);

        }
        catch(Exception $e)
        {
            echo new JsonResponse($e);
        }
    }

    public function listingFilesAjax() {
        try
        {

            $app = Factory::getApplication();
            $doc_id = $app->input->get('docid',0,'INT');

            // Check if user token is valid.
            if (!$this->checkToken('get'))
            {
                $app->enqueueMessage(JText::_('JINVALID_TOKEN'), 'error');
                echo new JsonResponse;
            }

            $model = $this->getModel();
            //Creation of the Folder
            $result = $model->getFilesLIsting($doc_id);
            echo new JsonResponse($result);

        }
        catch(Exception $e)
        {
            echo new JsonResponse($e);
        }

    }

}
