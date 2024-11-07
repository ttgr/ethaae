<?php
/**
 * @version    CVS: 2.1.0
 * @package    Com_Adip
 * @author     Tasos Triantis <tasos.tr@gmail.com>
 * @copyright  2024 Tasos Triantis
 * @license    GNU General Public License version 2 or later; see LICENSE.txt
 */

namespace Adip\Component\Adip\Administrator\View\Report;
// No direct access
defined('_JEXEC') or die;

use Joomla\CMS\MVC\View\HtmlView as BaseHtmlView;
use Joomla\CMS\Toolbar\Toolbar;
use \Joomla\CMS\Toolbar\ToolbarHelper;
use \Joomla\CMS\Factory;
use \Adip\Component\Adip\Administrator\Helper\AdipHelper;
use \Joomla\CMS\Language\Text;
use Joomla\CMS\Helper\ContentHelper;
use Joomla\CMS\Form\Form;

/**
 * View class for a single Report.
 *
 * @since  2.1.0
 */
class HtmlView extends BaseHtmlView
{
	protected $state;

	protected $item;

	protected $form;

	/**
	 * Display the view
	 *
	 * @param   string  $tpl  Template name
	 *
	 * @return void
	 *
	 * @throws Exception
	 */
	public function display($tpl = null)
	{

        $app  = Factory::getApplication();

        $option = $app->input->getCmd('option', '');

        if ($this->getLayout() === 'editfile') {

            /** @var Adip\Component\Adip\Administrator\Model $model */
            $model = $this->getModel();
            $this->form = Form::getInstance("editFileForm", JPATH_ADMINISTRATOR . '/components/' . $option . "/forms/editfile.xml", array("control" => "editFileForm"));
            $this->item = $model->getFile($app->input->get('id',0,'INT'));
            $this->form->setFieldAttribute('caption', 'default',$this->item->caption);
            $this->form->setFieldAttribute('language', 'default',$this->item->language);
            $this->form->setFieldAttribute('image', 'default',$this->item->image);
            $this->form->setFieldAttribute('publish', 'default',$this->item->publish);
            $this->addModalToolbar();
            parent::display($tpl);
            return;
        }

		$this->state = $this->get('State');
		$this->item  = $this->get('Item');
		$this->form  = $this->get('Form');
        if ($this->item->fk_institute_id) {
            $this->form->setFieldAttribute('fk_deprtement_id','condition','inst_id = '. $this->item->fk_institute_id);
            if ($this->item->fk_deprtement_id) {
                $this->form->setFieldAttribute('fk_programme_id','condition','dep_id = '. $this->item->fk_deprtement_id.' AND parent_type_id='.$this->item->dep_type_id);
                //$this->item->fk_deprtement_id = $this->item->fk_deprtement_id."-".$this->item->dep_type_id;
            }
        }
        $this->item->fk_reporttype_id = (intval($this->item->fk_reporttype_id) > 0) ? $this->item->fk_reporttype_id : 1;
		// Check for errors.
		if (count($errors = $this->get('Errors')))
		{
			throw new \Exception(implode("\n", $errors));
		}

        if ($this->getLayout() !== 'editfile') {
            $this->addToolbar();
        } else {
            $this->addModalToolbar();
        }
		parent::display($tpl);
	}

	/**
	 * Add the page title and toolbar.
	 *
	 * @return void
	 *
	 * @throws Exception
	 */
	protected function addToolbar()
	{
		Factory::getApplication()->input->set('hidemainmenu', true);

		$user  = Factory::getApplication()->getIdentity();
		$isNew = ($this->item->id == 0);

		if (isset($this->item->checked_out))
		{
			$checkedOut = !($this->item->checked_out == 0 || $this->item->checked_out == $user->get('id'));
		}
		else
		{
			$checkedOut = false;
		}

		$canDo = AdipHelper::getActions();

		ToolbarHelper::title(Text::_('COM_ADIP_TITLE_REPORT'), "generic");

		// If not checked out, can save the item.
		if (!$checkedOut && ($canDo->get('core.edit') || ($canDo->get('core.create'))))
		{
			ToolbarHelper::apply('report.apply', 'JTOOLBAR_APPLY');
			ToolbarHelper::save('report.save', 'JTOOLBAR_SAVE');
		}

		if (!$checkedOut && ($canDo->get('core.create')))
		{
			ToolbarHelper::custom('report.save2new', 'save-new.png', 'save-new_f2.png', 'JTOOLBAR_SAVE_AND_NEW', false);
		}

		// If an existing item, can save to a copy.
		if (!$isNew && $canDo->get('core.create'))
		{
			ToolbarHelper::custom('report.save2copy', 'save-copy.png', 'save-copy_f2.png', 'JTOOLBAR_SAVE_AS_COPY', false);
		}

		

		if (empty($this->item->id))
		{
			ToolbarHelper::cancel('report.cancel', 'JTOOLBAR_CANCEL');
		}
		else
		{
			ToolbarHelper::cancel('report.cancel', 'JTOOLBAR_CLOSE');
		}
	}

    protected function addModalToolbar()
    {
        $toolbar    = Toolbar::getInstance();
        $toolbar->apply('report.savefile');
    }
}
