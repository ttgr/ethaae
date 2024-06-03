<?php
/**
 * @version    CVS: 1.1.0
 * @package    Com_Ethaae_reports
 * @author     Tasos Triantis <tasos.tr@gmail.com>
 * @copyright  2024 Tasos Triantis
 * @license    GNU General Public License version 2 or later; see LICENSE.txt
 */

namespace Ethaaereports\Component\Ethaae_reports\Administrator\Helper;
// No direct access
defined('_JEXEC') or die;

use \Joomla\CMS\Factory;
use Joomla\CMS\HTML\HTMLHelper;
use Joomla\CMS\Language\LanguageHelper;
use \Joomla\CMS\Language\Text;
use \Joomla\CMS\Object\CMSObject;

/**
 * Ethaae_reports helper.
 *
 * @since  1.1.0
 */
class Ethaae_reportsHelper
{
	/**
	 * Gets the files attached to an item
	 *
	 * @param   int     $pk     The item's id
	 *
	 * @param   string  $table  The table's name
	 *
	 * @param   string  $field  The field's name
	 *
	 * @return  array  The files
	 */
	public static function getFiles($report_id,$state = "")
	{
        $db = Factory::getContainer()->get('DatabaseDriver');
        $query = $db->getQuery(true);
        $query
            ->select(array('f.*', 't.name as ftype'))
            ->from($db->quoteName('#__ethaae_reports_files', 'f'))
            ->join('LEFT', '#__ethaae_reports_filetype AS t ON t.`id` = f.`fk_file_id`')
            ->where($db->quoteName('f.fk_report_id') . ' = ' . $db->quote($db->escape($report_id)));

            if(!empty($state))
                $query->where($db->quoteName('f.state') . ' = ' . $db->quote($state));

            $query->order('f.`fk_file_id` ASC');

        $db->setQuery($query);
        $items = $db->loadObjectList();

        foreach ($items as &$item) {
            $languages = LanguageHelper::getLanguages('lang_code');
            $sefTag = $languages[$item->language]->sef;
            $item->langImage = HTMLHelper::_('image', 'mod_languages/' . $sefTag . '.gif', $languages[$item->language]->title_native, ['title' => $languages[$item->language]->title_native], true);
        }

        return $items;
	}

	/**
	 * Gets a list of the actions that can be performed.
	 *
	 * @return  CMSObject
	 *
	 * @since   1.1.0
	 */
	public static function getActions()
	{
		$user = Factory::getApplication()->getIdentity();
		$result = new CMSObject;

		$assetName = 'com_ethaae_reports';

		$actions = array(
			'core.admin', 'core.manage', 'core.create', 'core.edit', 'core.edit.own', 'core.edit.state', 'core.delete'
		);

		foreach ($actions as $action)
		{
			$result->set($action, $user->authorise($action, $assetName));
		}

		return $result;
	}


    public static function getDateGRFormat($date) {

        if(self::validateDate($date)) {
            return Factory::getDate($date)->format('DATE_FORMAT_GREEK');
        } else {
            return "";
        }

    }


    public static function getDateISOFormat($date) {

        if(self::validateDate($date,'d/m/Y')) {
            $d = \DateTime::createFromFormat('d/m/Y', $date);
            return $d->format('Y-m-d');
        } else {
            return "";
        }

    }


    private static function validateDate($date, $format = 'Y-m-d H:i:s')
    {
        $d = \DateTime::createFromFormat($format, $date);
        // The Y ( 4 digits year ) returns TRUE for any integer with any number of digits so changing the comparison from == to === fixes the issue.
        return $d && $d->format($format) === $date;
    }


    public static function getUnitInfo(int $id) {
        if (intval($id) > 0) {
            $db = Factory::getContainer()->get('DatabaseDriver');
            $query = $db->getQuery(true);
            $query
                ->select($db->quoteName('i.id'))
                ->select('CONCAT(u.short_code_el,\'::\',i.title_el) as title')
                ->from($db->quoteName('#__ethaae_institutes_structure','i'))
                ->join('LEFT', '#__ethaae_unit_type AS u ON u.id = i.unit_type_id')
                ->where($db->quoteName('i.id') . ' = '. $db->quote($id))
                ->order('title ASC');
            $db->setQuery($query);
            return $db->loadObject();
        }
        return ;
    }


    public static function getReportTypeInfo(int $id) {
        if (intval($id) > 0) {
            $db = $db = Factory::getContainer()->get('DatabaseDriver');
            $query = $db->getQuery(true);
            $query
                ->select('`t`.`name`')
                ->from($db->quoteName('#__ethaae_report_types', 't'))
                ->where($db->quoteName('t.id') . ' = ' . $db->quote($id));

            $db->setQuery($query);
            return $db->loadObject();
        }

        return ;
    }
}

