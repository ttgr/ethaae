<?php
/**
 * @package     Joomla.Site
 * @subpackage  mod_articles_latest
 *
 * @copyright   Copyright (C) 2005 - 2019 Open Source Matters, Inc. All rights reserved.
 * @license     GNU General Public License version 2 or later; see LICENSE.txt
 */

defined('_JEXEC') or die;


use Joomla\CMS\Language\LanguageHelper;
use Joomla\Utilities\ArrayHelper;
use Joomla\CMS\Factory;

/**
 * Helper for mod_articles_latest
 *
 * @since  1.6
 */
abstract class ModAdipReportsHelper
{
	/**
	 * Retrieve a list of article
	 *
	 * @param   \Joomla\Registry\Registry  &$params  module parameters
	 *
	 * @return  mixed
	 *
	 * @since   1.6
	 */
	public static function getList(&$params)
	{
		// Get the dbo
        $db = Factory::getContainer()->get('DatabaseDriver');
        $app       = Factory::getApplication();

		// Get an instance of the generic articles model
        $model = $app->bootComponent('com_ethaae_reports')->getMVCFactory()->createModel('Reports', 'Site',['ignore_request' => true]);

		// Set application parameters in model

		$appParams = $app->getParams();
		$model->setState('params', $appParams);

		$model->setState('list.start', 0);
		$model->setState('filter.state', 1);

		// Set the filters based on the module params
		$model->setState('list.limit', (int) $params->get('count', 5));

		// Category filter
		$model->setState('filter.fk_reporttype_id', $params->get('reporttype', '1'));
        // Set ordering
        $order_map = array(
            'id_dsc' => 'a.id',
            'c_dsc' => 'a.report_year',
            'sess_dsc' => 'a.session_date',
            'random' => $db->getQuery(true)->Rand(),
        );

        $ordering = ArrayHelper::getValue($order_map, $params->get('ordering'), 'a.id');
        $dir      = 'DESC';

        $model->setState('list.ordering', $ordering);
        $model->setState('list.direction', $dir);

        $items = $model->getItems();

		return $items;
	}

    public static function getCurrentLangSef() {
        $languages = LanguageHelper::getLanguages('lang_code');
        $lang = Factory::getApplication()->getLanguage();
        return $languages[$lang->getTag()]->sef;
    }

}
