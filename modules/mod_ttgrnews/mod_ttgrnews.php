<?php
/**
 * @package     ${NAMESPACE}
 * @subpackage
 *
 * @copyright   A copyright
 * @license     A "Slug" license name e.g. GPL2
 */


defined('_JEXEC') or die;

use Joomla\CMS\Helper\ModuleHelper;
use Joomla\Module\TTGRNews\Site\Helper\TTGRNewsHelper;
use Joomla\CMS\Factory;
use Joomla\CMS\HTML\HTMLHelper;
use Joomla\CMS\Form\Form;
use Joomla\Utilities\ArrayHelper;

$app = Factory::getApplication();
$lang = $app->getLanguage()->getTag();

// Get Data from the newsPageForm with the filters
$formData = $app->getInput()->post->get('newsPageForm',array(), 'array');
$selectedYear = ArrayHelper::getValue($formData,'year',0,'INT');
$selectedCatIDs = ArrayHelper::getValue($formData,'category_id',0,'INT');


$form = Form::getInstance("filters", __DIR__ . "/src/Forms/filters.xml", array("control" => "newsPageForm"));
$catids= TTGRNewsHelper::getIncludedCategoriesIDs($params,array($selectedCatIDs));
$form->setFieldAttribute('year', 'catids',implode(",",$catids));
$form->setFieldAttribute('year', 'value',$selectedYear);
$form->setFieldAttribute('year', 'language',$lang);

$form->setFieldAttribute('category_id', 'catids',implode(",",$catids));
$form->setFieldAttribute('category_id', 'value',$selectedCatIDs);
$form->setFieldAttribute('category_id', 'language',$lang);


$list       = TTGRNewsHelper::getArticles($params,$catids,$selectedYear);
$class_sfx  = htmlspecialchars($params->get('class_sfx', ''), ENT_COMPAT, 'UTF-8');


if (!$list) {
    return;
}


HTMLHelper::_('behavior.core');
HTMLHelper::_('jquery.framework');


$wa = $app->getDocument()->getWebAssetManager();
$wa->registerAndUseStyle($module->module.'.style', 'modules/' . $module->module . '/assets/css/card.css',['version' => 'auto']);
$wa->registerAndUseScript($module->module.'.js', 'modules/' . $module->module . '/assets/js/js.cookie.min.js',['version' => 'auto']);
$itemID = $app->getInput()->getInt('Itemid');
$infinity_ajax_trigger = $params->get('infinity_ajax_trigger',0);

require ModuleHelper::getLayoutPath('mod_ttgrnews', $params->get('layout', 'default'));
