<?php
/**
 * @version    CVS: 1.1.0
 * @package    Com_Ethaae_reports
 * @author     Tasos Triantis <tasos.tr@gmail.com>
 * @copyright  2024 Tasos Triantis
 * @license    GNU General Public License version 2 or later; see LICENSE.txt
 */

namespace Ethaaereports\Component\Ethaae_reports\Site\Helper;

defined('_JEXEC') or die;

use \Joomla\CMS\Factory;
use Joomla\CMS\HTML\HTMLHelper;
use Joomla\CMS\Language\LanguageHelper;
use \Joomla\CMS\MVC\Model\BaseDatabaseModel;
use \Joomla\CMS\Language\Text;

/**
 * Class Ethaae_reportsFrontendHelper
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
            $f_title = (!empty($f->caption)) ? $item->caption : $item->name;
            $title = Text::sprintf('COM_ETHAAE_DOWNLOAD_LINK_TIP',$f_title);
            $item->langImage = HTMLHelper::_('image', 'com_ethaae_reports/' . $sefTag . '.png', $languages[$item->language]->title_native, ['title' => $title], true);
            $item->langTitle = $languages[$item->language]->title_native;
        }

        return $items;
    }

    public static function getCurrentLangSef() {
        $languages = LanguageHelper::getLanguages('lang_code');
        $lang = Factory::getApplication()->getLanguage();
        return $languages[$lang->getTag()]->sef;
    }

	/**
	 * Gets the edit permission for an user
	 *
	 * @param   mixed  $item  The item
	 *
	 * @return  bool
	 */
	public static function canUserEdit($item)
	{
		$permission = false;
		$user       = Factory::getApplication()->getIdentity();

		if ($user->authorise('core.edit', 'com_ethaae_reports') || (isset($item->created_by) && $user->authorise('core.edit.own', 'com_ethaae_reports') && $item->created_by == $user->id) || $user->authorise('core.create', 'com_ethaae_reports'))
		{
			$permission = true;
		}

		return $permission;
	}
}
