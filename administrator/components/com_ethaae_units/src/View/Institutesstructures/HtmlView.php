<?php
/**
 * @version    CVS: 1.4.3
 * @package    Com_Ethaae_units
 * @author     Tasos Triantis <tasos.tr@gmail.com>
 * @copyright  2024 Tasos Triantis
 * @license    GNU General Public License version 2 or later; see LICENSE.txt
 */

namespace Ethaaeunits\Component\Ethaae_units\Administrator\View\Institutesstructures;
// No direct access
defined('_JEXEC') or die;

use \Joomla\CMS\MVC\View\HtmlView as BaseHtmlView;
use \Ethaaeunits\Component\Ethaae_units\Administrator\Helper\Ethaae_unitsHelper;
use \Joomla\CMS\Toolbar\Toolbar;
use \Joomla\CMS\Toolbar\ToolbarHelper;
use \Joomla\CMS\Language\Text;
use \Joomla\Component\Content\Administrator\Extension\ContentComponent;
use \Joomla\CMS\Form\Form;
use \Joomla\CMS\HTML\Helpers\Sidebar;
/**
 * View class for a list of Institutesstructures.
 *
 * @since  1.4.3
 */
class HtmlView extends BaseHtmlView
{
	protected $items;

	protected $pagination;

	protected $state;

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
		$this->items = $this->get('Items');
		$this->pagination = $this->get('Pagination');
		$this->filterForm = $this->get('FilterForm');
		$this->activeFilters = $this->get('ActiveFilters');

		// Check for errors.
		if (count($errors = $this->get('Errors')))
		{
			throw new \Exception(implode("\n", $errors));
		}

		$this->addToolbar();

		$this->sidebar = Sidebar::render();
		parent::display($tpl);
	}

	/**
	 * Add the page title and toolbar.
	 *
	 * @return  void
	 *
	 * @since   1.4.3
	 */
	protected function addToolbar()
	{
		$state = $this->get('State');
		$canDo = Ethaae_unitsHelper::getActions();

		ToolbarHelper::title(Text::_('COM_ETHAAE_UNITS_TITLE_INSTITUTESSTRUCTURES'), "generic");

		$toolbar = Toolbar::getInstance('toolbar');

		// Check if the form exists before showing the add/edit buttons
		$formPath = JPATH_COMPONENT_ADMINISTRATOR . '/src/View/Institutesstructures';

		if (file_exists($formPath))
		{
			if ($canDo->get('core.create'))
			{
				$toolbar->addNew('institutesstructure.add');
			}
		}

		if ($canDo->get('core.edit.state'))
		{
			$dropdown = $toolbar->dropdownButton('status-group')
				->text('JTOOLBAR_CHANGE_STATUS')
				->toggleSplit(false)
				->icon('fas fa-ellipsis-h')
				->buttonClass('btn btn-action')
				->listCheck(true);

			$childBar = $dropdown->getChildToolbar();

			if (isset($this->items[0]->state))
			{
				$childBar->publish('institutesstructures.publish')->listCheck(true);
				$childBar->unpublish('institutesstructures.unpublish')->listCheck(true);
				$childBar->archive('institutesstructures.archive')->listCheck(true);
			}
			elseif (isset($this->items[0]))
			{
				// If this component does not use state then show a direct delete button as we can not trash
				$toolbar->delete('institutesstructures.delete')
				->text('JTOOLBAR_EMPTY_TRASH')
				->message('JGLOBAL_CONFIRM_DELETE')
				->listCheck(true);
			}

			$childBar->standardButton('duplicate')
				->text('JTOOLBAR_DUPLICATE')
				->icon('fas fa-copy')
				->task('institutesstructures.duplicate')
				->listCheck(true);

			if (isset($this->items[0]->checked_out))
			{
				$childBar->checkin('institutesstructures.checkin')->listCheck(true);
			}

			if (isset($this->items[0]->state))
			{
				$childBar->trash('institutesstructures.trash')->listCheck(true);
			}
		}

		

		// Show trash and delete for components that uses the state field
		if (isset($this->items[0]->state))
		{

			if ($this->state->get('filter.state') == ContentComponent::CONDITION_TRASHED && $canDo->get('core.delete'))
			{
				$toolbar->delete('institutesstructures.delete')
					->text('JTOOLBAR_EMPTY_TRASH')
					->message('JGLOBAL_CONFIRM_DELETE')
					->listCheck(true);
			}
		}
        $nRecords = $this->pagination->total;
        $toolbar->standardButton('nrecords')
            ->icon('fa fa-info-circle')
            ->text($nRecords . ' Εγγραφές')
            ->task('')
            ->onclick('return false')
            ->listCheck(false);
        // Set sidebar action
        Sidebar::setAction('index.php?option=com_ethaae_reports&view=reports');


        if ($canDo->get('core.admin'))
		{
			$toolbar->preferences('com_ethaae_units');
		}

		// Set sidebar action
		Sidebar::setAction('index.php?option=com_ethaae_units&view=institutesstructures');
	}
	
	/**
	 * Method to order fields 
	 *
	 * @return void 
	 */
	protected function getSortFields()
	{
		return array(
			'a.`id`' => Text::_('JGRID_HEADING_ID'),
			'a.`state`' => Text::_('JSTATUS'),
			'a.`ordering`' => Text::_('JGRID_HEADING_ORDERING'),
			'a.`title_el`' => Text::_('COM_ETHAAE_UNITS_INSTITUTESSTRUCTURES_TITLE_EL'),
			'a.`title_en`' => Text::_('COM_ETHAAE_UNITS_INSTITUTESSTRUCTURES_TITLE_EN'),
			'a.`unittypecode`' => Text::_('COM_ETHAAE_UNITS_INSTITUTESSTRUCTURES_UNITTYPECODE'),
			'a.`unitcode`' => Text::_('COM_ETHAAE_UNITS_INSTITUTESSTRUCTURES_UNITCODE'),
			'a.`parentunitid`' => Text::_('COM_ETHAAE_UNITS_INSTITUTESSTRUCTURES_PARENTUNITID'),
			'a.`startupdate`' => Text::_('COM_ETHAAE_UNITS_INSTITUTESSTRUCTURES_STARTUPDATE'),
			'a.`ceasedate`' => Text::_('COM_ETHAAE_UNITS_INSTITUTESSTRUCTURES_CEASEDATE'),
			'a.`greek`' => Text::_('COM_ETHAAE_UNITS_INSTITUTESSTRUCTURES_GREEK'),
			'a.`notestablished`' => Text::_('COM_ETHAAE_UNITS_INSTITUTESSTRUCTURES_NOTESTABLISHED'),
			'a.`unit_type_id`' => Text::_('COM_ETHAAE_UNITS_INSTITUTESSTRUCTURES_UNIT_TYPE_ID'),
		);
	}

	/**
	 * Check if state is set
	 *
	 * @param   mixed  $state  State
	 *
	 * @return bool
	 */
	public function getState($state)
	{
		return isset($this->state->{$state}) ? $this->state->{$state} : false;
	}
}
