<?php
/**
 * @version    CVS: 1.1.0
 * @package    Com_Ethaae_reports
 * @author     Tasos Triantis <tasos.tr@gmail.com>
 * @copyright  2024 Tasos Triantis
 * @license    GNU General Public License version 2 or later; see LICENSE.txt
 */

namespace Ethaaereports\Component\Ethaae_reports\Administrator\Controller;

\defined('_JEXEC') or die;

use Joomla\CMS\Application\SiteApplication;
use Joomla\CMS\Factory;
use Joomla\CMS\Language\Multilanguage;
use Joomla\CMS\Language\Text;
use Joomla\CMS\MVC\Controller\AdminController;
use Joomla\CMS\Response\JsonResponse;
use Joomla\CMS\Router\Route;
use Joomla\CMS\Uri\Uri;
use Joomla\Utilities\ArrayHelper;

/**
 * Reports list controller class.
 *
 * @since  1.1.0
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
				throw new \Exception(Text::_('COM_ETHAAE_REPORTS_NO_ELEMENT_SELECTED'));
			}

			ArrayHelper::toInteger($pks);
			$model = $this->getModel();
			$model->duplicate($pks);
			$this->setMessage(Text::_('COM_ETHAAE_REPORTS_ITEMS_SUCCESS_DUPLICATED'));
		}
		catch (\Exception $e)
		{
			Factory::getApplication()->enqueueMessage($e->getMessage(), 'warning');
		}

		$this->setRedirect('index.php?option=com_ethaae_reports&view=reports');
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
	 * @since   1.1.0
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
	 * @since   1.1.0
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
            $results ="[0|".Text::_('COM_ETHAAE_REPORTS_FK_DEPRTEMENT_ID_FILTER');
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

            // Check if user token is valid.
            if (!$this->checkToken('get'))
            {
                $app->enqueueMessage(Text::_('JINVALID_TOKEN'), 'error');
                echo new JsonResponse;
            }

            $model = $this->getModel();
            $r = $model->getProgrammes($id);
            $results ="[0|".Text::_('COM_ETHAAE_REPORTS_FK_PROGRAMME_ID_FILTER');
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

}
