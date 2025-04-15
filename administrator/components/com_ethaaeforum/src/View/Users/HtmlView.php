<?php
/**
 * @version    CVS: 2.0.0
 * @package    Com_Ethaaeforum
 * @author     Tasos Triantis <tasos.tr@gmail.com>
 * @copyright  2024 Tasos Triantis
 * @license    GNU General Public License version 2 or later; see LICENSE.txt
 */

namespace Ethaaeforum\Component\Ethaaeforum\Administrator\View\Users;
// No direct access
defined('_JEXEC') or die;

use \Joomla\CMS\MVC\View\HtmlView as BaseHtmlView;
use \Ethaaeforum\Component\Ethaaeforum\Administrator\Helper\EthaaeforumHelper;
use \Joomla\CMS\Toolbar\Toolbar;
use \Joomla\CMS\Toolbar\ToolbarHelper;
use \Joomla\CMS\Language\Text;
use \Joomla\Component\Content\Administrator\Extension\ContentComponent;
use \Joomla\CMS\Form\Form;
use \Joomla\CMS\HTML\Helpers\Sidebar;
/**
 * View class for a list of Users.
 *
 * @since  2.0.0
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
	 * @since   2.0.0
	 */
	protected function addToolbar()
	{
		$state = $this->get('State');
		$canDo = EthaaeforumHelper::getActions();

		ToolbarHelper::title(Text::_('COM_ETHAAEFORUM_TITLE_USERS'), "generic");

		$toolbar = Toolbar::getInstance('toolbar');

		// Check if the form exists before showing the add/edit buttons
		$formPath = JPATH_COMPONENT_ADMINISTRATOR . '/src/View/Users';

        $toolbar->standardButton('activateuser')
            ->icon('fa-solid fa-paper-plane')
            ->text('Ενεργοποίηση Χρηστών')
            ->task('users.addforum')
            ->listCheck(true);




        if ($canDo->get('core.admin'))
		{
			$toolbar->preferences('com_ethaaeforum');
		}

		// Set sidebar action
		Sidebar::setAction('index.php?option=com_ethaaeforum&view=users');
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
			'a.`name`' => Text::_('COM_ETHAAEFORUM_USERS_NAME'),
			'a.`username`' => Text::_('COM_ETHAAEFORUM_USERS_USERNAME'),
			'a.`email`' => Text::_('COM_ETHAAEFORUM_USERS_EMAIL'),
			'a.`registerDate`' => Text::_('COM_ETHAAEFORUM_USERS_REGISTERDATE'),
			'a.`lastvisitDate`' => Text::_('COM_ETHAAEFORUM_USERS_LASTVISITDATE'),
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
