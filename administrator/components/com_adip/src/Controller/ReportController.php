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

use Joomla\CMS\MVC\Controller\FormController;
use Joomla\CMS\Router\Route;


/**
 * Report controller class.
 *
 * @since  2.1.0
 */
class ReportController extends FormController
{
	protected $view_list = 'reports';

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



    public function savefile() {
        $model   = $this->getModel();
        $data    = $this->input->post->get('editFileForm', [], 'array');
        $task    = $this->getTask();

        $model->updateFileMetadata($data);

        $recordId = $this->input->getInt('id');
        $this->setRedirect(
            Route::_(
                'index.php?option=' . $this->option . '&view=' . $this->view_item
                . $this->getRedirectToItemAppend($recordId, 'id'),
                false
            )
        );

    }
}
