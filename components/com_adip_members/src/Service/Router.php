<?php

/**
 * @version    CVS: 2.0.0
 * @package    Com_Adip_members
 * @author     Tasos Triantis <tasos.tr@gmail.com>
 * @copyright  2024 Tasos Triantis
 * @license    GNU General Public License version 2 ou version ultérieure ; Voir LICENSE.txt
 */

namespace Adipmembers\Component\Adip_members\Site\Service;

// No direct access
defined('_JEXEC') or die;

use Joomla\CMS\Component\Router\RouterViewConfiguration;
use Joomla\CMS\Component\Router\RouterView;
use Joomla\CMS\Component\Router\Rules\StandardRules;
use Joomla\CMS\Component\Router\Rules\NomenuRules;
use Joomla\CMS\Component\Router\Rules\MenuRules;
use Joomla\CMS\Factory;
use Joomla\CMS\Categories\Categories;
use Joomla\CMS\Application\SiteApplication;
use Joomla\CMS\Categories\CategoryFactoryInterface;
use Joomla\CMS\Categories\CategoryInterface;
use Joomla\Database\DatabaseInterface;
use Joomla\CMS\Menu\AbstractMenu;
use Joomla\CMS\Component\ComponentHelper;

/**
 * Class Adip_membersRouter
 *
 */
class Router extends RouterView
{
	private $noIDs;
	/**
	 * The category factory
	 *
	 * @var    CategoryFactoryInterface
	 *
	 * @since  2.0.0
	 */
	private $categoryFactory;

	/**
	 * The category cache
	 *
	 * @var    array
	 *
	 * @since  2.0.0
	 */
	private $categoryCache = [];

	public function __construct(SiteApplication $app, AbstractMenu $menu, CategoryFactoryInterface $categoryFactory, DatabaseInterface $db)
	{
		$params = ComponentHelper::getParams('com_adip_members');
		$this->noIDs = (bool) $params->get('sef_ids');
		$this->categoryFactory = $categoryFactory;
		
		
			$membersboards = new RouterViewConfiguration('membersboards');
            $membersboards->setKey('boardtype');
			$this->registerView($membersboards);
			$ccMembersboard = new RouterViewConfiguration('membersboard');
			$ccMembersboard->setKey('id')->setParent($membersboards);
			$this->registerView($ccMembersboard);
			$membersboardform = new RouterViewConfiguration('membersboardform');
			$membersboardform->setKey('id');
			$this->registerView($membersboardform);
			$memberstypes = new RouterViewConfiguration('memberstypes');
			$this->registerView($memberstypes);

		parent::__construct($app, $menu);

		$this->attachRule(new MenuRules($this));
		$this->attachRule(new StandardRules($this));
		$this->attachRule(new NomenuRules($this));
	}


	
		/**
		 * Method to get the segment(s) for an membersboard
		 *
		 * @param   string  $id     ID of the membersboard to retrieve the segments for
		 * @param   array   $query  The request that is built right now
		 *
		 * @return  array|string  The segments of this item
		 */
		public function getMembersboardSegment($id, $query)
		{
			return array((int) $id => $id);
		}
			/**
			 * Method to get the segment(s) for an membersboardform
			 *
			 * @param   string  $id     ID of the membersboardform to retrieve the segments for
			 * @param   array   $query  The request that is built right now
			 *
			 * @return  array|string  The segments of this item
			 */
			public function getMembersboardformSegment($id, $query)
			{
				return $this->getMembersboardSegment($id, $query);
			}

	
		/**
		 * Method to get the segment(s) for an membersboard
		 *
		 * @param   string  $segment  Segment of the membersboard to retrieve the ID for
		 * @param   array   $query    The request that is parsed right now
		 *
		 * @return  mixed   The id of this item or false
		 */
		public function getMembersboardId($segment, $query)
		{
			return (int) $segment;
		}
			/**
			 * Method to get the segment(s) for an membersboardform
			 *
			 * @param   string  $segment  Segment of the membersboardform to retrieve the ID for
			 * @param   array   $query    The request that is parsed right now
			 *
			 * @return  mixed   The id of this item or false
			 */
			public function getMembersboardformId($segment, $query)
			{
				return $this->getMembersboardId($segment, $query);
			}

	/**
	 * Method to get categories from cache
	 *
	 * @param   array  $options   The options for retrieving categories
	 *
	 * @return  CategoryInterface  The object containing categories
	 *
	 * @since   2.0.0
	 */
	private function getCategories(array $options = []): CategoryInterface
	{
		$key = serialize($options);

		if (!isset($this->categoryCache[$key]))
		{
			$this->categoryCache[$key] = $this->categoryFactory->createCategory($options);
		}

		return $this->categoryCache[$key];
	}
}
