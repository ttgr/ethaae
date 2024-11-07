<?php
/**
 * @version    CVS: 1.1.0
 * @package    Com_Ethaae_units_info
 * @author     Tasos Triantis <tasos.tr@gmail.com>
 * @copyright  2024 Tasos Triantis
 * @license    GNU General Public License version 2 or later; see LICENSE.txt
 */

namespace Ethaaunitsinfo\Component\Ethaae_units_info\Administrator\View\Institutesinfo;
// No direct access
defined('_JEXEC') or die;

use Joomla\CMS\MVC\View\HtmlView as BaseHtmlView;
use \Joomla\CMS\Toolbar\ToolbarHelper;
use \Joomla\CMS\Factory;
use \Ethaaunitsinfo\Component\Ethaae_units_info\Administrator\Helper\Ethaae_units_infoHelper;
use \Joomla\CMS\Language\Text;

/**
 * View class for a single Institutesinfo.
 *
 * @since  1.1.0
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
        $isNew = ($this->item->id == 0);
        if(!$isNew) {
            $query ="select
                    `i`.`id` AS `id`,
                     concat(`t`.`short_code_el`, ':: ', `i`.`title_el`) AS `name_el`
                  from
                      `#__ethaae_institutes_structure` `i`
                  join `#__ethaae_unit_type` `t` on(`i`.`unit_type_id` = `t`.`id`)
                      where `i`.`id` = ".$this->item->fk_unit_id;
            $this->form->setFieldAttribute('fk_unit_id', 'query', $query);
        }

		// Check for errors.
		if (count($errors = $this->get('Errors')))
		{
			throw new \Exception(implode("\n", $errors));
		}
				$this->addToolbar();
		
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

		$canDo = Ethaae_units_infoHelper::getActions();

		ToolbarHelper::title(Text::_('COM_ETHAAE_UNITS_INFO_TITLE_INSTITUTESINFO'), "generic");

		// If not checked out, can save the item.
		if (!$checkedOut && ($canDo->get('core.edit') || ($canDo->get('core.create'))))
		{
			ToolbarHelper::apply('institutesinfo.apply', 'JTOOLBAR_APPLY');
			ToolbarHelper::save('institutesinfo.save', 'JTOOLBAR_SAVE');
		}

		if (!$checkedOut && ($canDo->get('core.create')))
		{
			ToolbarHelper::custom('institutesinfo.save2new', 'save-new.png', 'save-new_f2.png', 'JTOOLBAR_SAVE_AND_NEW', false);
		}

		// If an existing item, can save to a copy.
		if (!$isNew && $canDo->get('core.create'))
		{
			ToolbarHelper::custom('institutesinfo.save2copy', 'save-copy.png', 'save-copy_f2.png', 'JTOOLBAR_SAVE_AS_COPY', false);
		}

		

		if (empty($this->item->id))
		{
			ToolbarHelper::cancel('institutesinfo.cancel', 'JTOOLBAR_CANCEL');
		}
		else
		{
			ToolbarHelper::cancel('institutesinfo.cancel', 'JTOOLBAR_CLOSE');
		}
	}
}
