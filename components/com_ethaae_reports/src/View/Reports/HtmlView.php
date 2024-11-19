<?php

/**
 * @version    CVS: 1.1.0
 * @package    Com_Ethaae_reports
 * @author     Tasos Triantis <tasos.tr@gmail.com>
 * @copyright  2024 Tasos Triantis
 * @license    GNU General Public License version 2 or later; see LICENSE.txt
 */

namespace Ethaaereports\Component\Ethaae_reports\Site\View\Reports;
// No direct access
defined('_JEXEC') or die;

use \Joomla\CMS\MVC\View\HtmlView as BaseHtmlView;
use \Joomla\CMS\Factory;
use \Joomla\CMS\Language\Text;
use Joomla\Registry\Registry;
use Joomla\CMS\Form\Form;

/**
 * View class for a list of Ethaae_reports.
 *
 * @since  1.1.0
 */
class HtmlView extends BaseHtmlView
{
	protected $items;

	protected $pagination;

	protected $state;

	protected $params;

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
		$app = Factory::getApplication();

		$this->params = $app->getParams('com_ethaae_reports');
//		$this->filterForm = $this->get('FilterForm');
//        dump($this->filterForm);
//		$this->activeFilters = $this->get('ActiveFilters');

        if ($menu = $app->getMenu()->getActive()) {
            $menu_params = $menu->getParams();
        } else {
            $menu_params = new Registry;
        }


        $this->model = $this->getModel();
        $this->model->resetState = ($app->getInput()->getInt('dontresetstate',0) == 0);
        $this->state = $this->get('State');
        $this->report_id = $app->getInput()->getInt('reporttype',0);

        $option = $app->getInput()->getString('option','');
        if (is_numeric($this->report_id) && $this->report_id > 0) {
            $tpl = $this->report_id;
            $this->filterForm = Form::getInstance("com_ethaae_reports.reports.filter", JPATH_SITE . '/components/' . $option . "/forms/filter_reports_".$this->report_id.".xml", array("control" => ""));
            $this->filterForm->setFieldAttribute('filter_fk_reporttype_id', 'default',$this->report_id);
            $this->state->set('filter.fk_reporttype_id',$this->report_id);
        }
        $this->items = $this->get('Items');
        $this->pagination = $this->get('Pagination');

        // Check for errors.
		if (count($errors = $this->get('Errors')))
		{
			throw new \Exception(implode("\n", $errors));
		}

		$this->_prepareDocument();
		parent::display($tpl);
	}

	/**
	 * Prepares the document
	 *
	 * @return void
	 *
	 * @throws Exception
	 */
	protected function _prepareDocument()
	{
		$app   = Factory::getApplication();
		$menus = $app->getMenu();
		$title = null;

		// Because the application sets a default page title,
		// we need to get it from the menu item itself
		$menu = $menus->getActive();

		if ($menu)
		{
			$this->params->def('page_heading', $this->params->get('page_title', $menu->title));
		}
		else
		{
			$this->params->def('page_heading', Text::_('COM_ETHAAE_REPORTS_DEFAULT_PAGE_TITLE'));
		}

		$title = $this->params->get('page_title', '');

		if (empty($title))
		{
			$title = $app->get('sitename');
		}
		elseif ($app->get('sitename_pagetitles', 0) == 1)
		{
			$title = Text::sprintf('JPAGETITLE', $app->get('sitename'), $title);
		}
		elseif ($app->get('sitename_pagetitles', 0) == 2)
		{
			$title = Text::sprintf('JPAGETITLE', $title, $app->get('sitename'));
		}

		$this->document->setTitle($title);

		if ($this->params->get('menu-meta_description'))
		{
			$this->document->setDescription($this->params->get('menu-meta_description'));
		}

		if ($this->params->get('menu-meta_keywords'))
		{
			$this->document->setMetadata('keywords', $this->params->get('menu-meta_keywords'));
		}

		if ($this->params->get('robots'))
		{
			$this->document->setMetadata('robots', $this->params->get('robots'));
		}

		
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
