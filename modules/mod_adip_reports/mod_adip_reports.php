<?php
/**
 * @package     Joomla.Site
 * @subpackage  mod_articles_latest
 *
 * @copyright   Copyright (C) 2005 - 2019 Open Source Matters, Inc. All rights reserved.
 * @license     GNU General Public License version 2 or later; see LICENSE.txt
 */

defined('_JEXEC') or die;

// Include the latest functions only once
JLoader::register('ModAdipReportsHelper', __DIR__ . '/helper.php');

$list            = ModAdipReportsHelper::getList($params);
$langSEF         = ModAdipReportsHelper::getCurrentLangSef();
$moduleclass_sfx = htmlspecialchars($params->get('moduleclass_sfx'), ENT_COMPAT, 'UTF-8');

$layout = "report_".$params->get('reporttype', '1');

require JModuleHelper::getLayoutPath('mod_adip_reports', $layout);
