<?php
/**
 * @version    CVS: 1.4.2
 * @package    Com_Ethaae_units
 * @author     Tasos Triantis <tasos.tr@gmail.com>
 * @copyright  2024 Tasos Triantis
 * @license    GNU General Public License version 2 or later; see LICENSE.txt
 */

namespace Ethaaeunits\Component\Ethaae_units\Administrator\View\Institutesstructure;
// No direct access
defined('_JEXEC') or die;

use Joomla\CMS\MVC\View\HtmlView as BaseHtmlView;
use \Joomla\CMS\Toolbar\ToolbarHelper;
use \Joomla\CMS\Factory;
use \Ethaaeunits\Component\Ethaae_units\Administrator\Helper\Ethaae_unitsHelper;
use \Joomla\CMS\Language\Text;
use Joomla\CMS\Toolbar\Toolbar;

/**
 * View class for a single Institutesstructure.
 *
 * @since  1.4.2
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
		$this->state = $this->get('State');
		$this->item  = $this->get('Item');
		$this->form  = $this->get('Form');

		// Check for errors.
		if (count($errors = $this->get('Errors')))
		{
			throw new \Exception(implode("\n", $errors));
		}
        if ($this->getLayout() == 'view') {

            $this->addModalToolbar();
        } else {
            $this->addToolbar();
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

		$canDo = Ethaae_unitsHelper::getActions();

		ToolbarHelper::title(Text::_('COM_ETHAAE_UNITS_TITLE_INSTITUTESSTRUCTURE'), "generic");

		// If not checked out, can save the item.
		if (!$checkedOut && ($canDo->get('core.edit') || ($canDo->get('core.create'))))
		{
			ToolbarHelper::apply('institutesstructure.apply', 'JTOOLBAR_APPLY');
			ToolbarHelper::save('institutesstructure.save', 'JTOOLBAR_SAVE');
		}

		if (!$checkedOut && ($canDo->get('core.create')))
		{
			ToolbarHelper::custom('institutesstructure.save2new', 'save-new.png', 'save-new_f2.png', 'JTOOLBAR_SAVE_AND_NEW', false);
		}

		// If an existing item, can save to a copy.
		if (!$isNew && $canDo->get('core.create'))
		{
			ToolbarHelper::custom('institutesstructure.save2copy', 'save-copy.png', 'save-copy_f2.png', 'JTOOLBAR_SAVE_AS_COPY', false);
		}



		if (empty($this->item->id))
		{
			ToolbarHelper::cancel('institutesstructure.cancel', 'JTOOLBAR_CANCEL');
		}
		else
		{
			ToolbarHelper::cancel('institutesstructure.cancel', 'JTOOLBAR_CLOSE');
		}
	}

    protected function addModalToolbar()
    {
        Factory::getApplication()->input->set('hidemainmenu', true);
        ToolbarHelper::title(Text::_('COM_ETHAAE_UNITS_TITLE_INSTITUTESSTRUCTURE'), "generic");

    }

}
