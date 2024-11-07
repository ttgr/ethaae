<?php

/**
 * @version    CVS: 1.1.0
 * @package    Com_Ethaae_units_info
 * @author     Tasos Triantis <tasos.tr@gmail.com>
 * @copyright  2024 Tasos Triantis
 * @license    GNU General Public License version 2 or later; see LICENSE.txt
 */

namespace Ethaaunitsinfo\Component\Ethaae_units_info\Site\View\Institutesinfo;
// No direct access
defined('_JEXEC') or die;



use Joomla\CMS\MVC\View\HtmlView as BaseHtmlView;
use \Joomla\CMS\Factory;
use \Joomla\CMS\Language\Text;
use Joomla\CMS\Plugin\PluginHelper;
use Joomla\Registry\Registry;
use Ethaaunitsinfo\Component\Ethaae_units_info\Site\Helper\Ethaae_units_infoHelper;
/**
 * View class for a list of Ethaae_units_info.
 *
 * @since  1.1.0
 */
class HtmlView extends BaseHtmlView
{
	protected $state;

	protected $item;

	protected $form;

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
		$app  = Factory::getApplication();
		$user = $app->getIdentity();
        $app->getLanguage()->load('com_ethaae_reports', JPATH_SITE, $app->getLanguage()->getTag(), true);

		$this->state  = $this->get('State');
		$this->item   = $this->get('Item');
		$this->params = $app->getParams('com_ethaae_units_info');
        $this->item->lang = $app->getLanguage()->getTag();
        $this->item->langTag = explode("-",$this->item->lang);



        if (!empty($this->item)) {

            $plugin = PluginHelper::getPlugin('content', 'expoimagegallery');
            $plg_params = new Registry($plugin->params);
            $mod_params = array(
                "module_id" => 1,
                "width" => $plg_params->get('width', '0'),
                "height" => $plg_params->get('height', '0'),
                "mfolder" => $this->item->info->unitcode,
                "folder_path" => "images/institutes/slides",
                "is_full" => $plg_params->get('is_full', '1'),
                "from_url" => 0,
                "module_tag" => "div",
                "bootstrap_size" => 0,
                "header_tag" => "h3",
                "header_class" => "",
                "style" => 0,
                "navstyle" => 'none',
                "arrowstyle" => 'mouseover',
                "autoplay" => 1,
                "is_second" => 0,
                "read_more" => 0,
            );


            $this->item->imggallery = Ethaae_units_infoHelper::getImageGallery($mod_params);
        }
		if (!empty($this->item))
		{

		}

		// Check for errors.
		if (count($errors = $this->get('Errors')))
		{
			throw new \Exception(implode("\n", $errors));
		}

		

		if ($this->_layout == 'edit')
		{
			$authorised = $user->authorise('core.create', 'com_ethaae_units_info');

			if ($authorised !== true)
			{
				throw new \Exception(Text::_('JERROR_ALERTNOAUTHOR'));
			}
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
		// We need to get it from the menu item itself
		$menu = $menus->getActive();

		if ($menu)
		{
			$this->params->def('page_heading', $this->params->get('page_title', $menu->title));
		}
		else
		{
			$this->params->def('page_heading', Text::_('COM_ETHAAE_UNITS_INFO_DEFAULT_PAGE_TITLE'));
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
}
