<?php
/**
 * @package     Joomla.Site
 * @subpackage  mod_minifrontpagepro
 *
 * @copyright   Copyright (C) 2005 - 2020 Open Source Matters, Inc. All rights reserved.
 * @license     GNU General Public License version 2 or later; see LICENSE.txt
 * 
 * @subpackage	mod_minifrontpagepro
 * @author     	TemplatePlazza
 * @link 		http://www.templateplazza.com
 */

defined('_JEXEC') or die;
// Added for com_ajax support in J 3.x
use Joomla\CMS\Helper\ModuleHelper;
use Joomla\Module\TTGRNews\Site\Helper\TTGRNewsHelper;
use Joomla\CMS\Factory;
use Joomla\Utilities\ArrayHelper;



abstract class ModTTGRNewsHelper {

    public static function getAjax() {
        $input = Factory::getApplication()->getInput();
		$module = ModuleHelper::getModule('mod_ttgrnews', $input->getString('module_title',''));
		$params = new JRegistry();
		$params->loadString($module->params);
        $catids= TTGRNewsHelper::getIncludedCategoriesIDs($params,array($input->getInt('category_id',0)));
        $list  = TTGRNewsHelper::getArticles($params, $catids,$input->getInt('year',0),$input->getInt('start',0));
		ob_start();
            require ModuleHelper::getLayoutPath('mod_ttgrnews', $params->get('layout', 'default') . '_items');
		$output = ob_get_contents();
		ob_end_clean();
		return $output;

    }
}