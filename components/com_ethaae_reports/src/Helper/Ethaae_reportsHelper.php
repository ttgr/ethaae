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

use \Joomla\Utilities\ArrayHelper;
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
    public static function getFiles($report_id,$state = "",array $filters = array())
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

        $filter_fk_file_id = ArrayHelper::getValue($filters,'fk_file_id',0);
        if(!empty($filter_fk_file_id)) {
            $query->where($db->quoteName('f.fk_file_id') . ' = ' . $db->quote($filter_fk_file_id));
        }

        $query->order('f.`fk_file_id` ASC');

        $db->setQuery($query);
        //Factory::getApplication()->enqueueMessage($db->replacePrefix((string) $query), 'notice');
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

    public static function getUnityReports(int $fk_unity_id) {
        $filters = [
               'fk_unit_id'    =>   $fk_unity_id,
        ];
        $db = Factory::getContainer()->get('DatabaseDriver');
        $query = self::getListQuery($filters);
        $db->setQuery($query);
        $items = $db->loadObjectList();
        foreach ($items as &$item) {
            $filters = [
                'fk_file_id'    =>   1,
            ];

            $item->files = self::getFiles($item->id,1,$filters);
        }
        return $items;
    }


    public static  function getUnitDepts(int $fk_unit_id,string $lang) {
        $db = Factory::getContainer()->get('DatabaseDriver');
        $filters = [
            'fk_institute_id'    =>   $fk_unit_id,
            'fk_reporttype_id'   =>   2,
            'ordering'           =>   'unit_title_'.$lang,
            'direction'           =>   'ASC',
        ];
        $query = self::getListQuery($filters);
        $db->setQuery($query);
        $items = $db->loadObjectList();
        foreach ($items as &$item) {
            $filters = [
                'fk_file_id'    =>   1,
            ];

            $item->files = self::getFiles($item->id,1,$filters);
        }
        return $items;
    }

    protected static function getListQuery(array $filters)
    {
        // Create a new query object.
        $db = Factory::getContainer()->get('DatabaseDriver');
        $query = $db->getQuery(true);

        // Select the required fields from the table.
        $query->select('DISTINCT a.*');

        $query->from('`#__ethaae_reports` AS a');
        $query->join('INNER', '#__ethaae_reports_files AS f ON a.`id` = f.`fk_report_id`');
        // Join over the users for the checked out user.
        // Join over the foreign key 'fk_reporttype_id'
        $query->select('`t`.`name` AS report_type');
        $query->join('LEFT', '#__ethaae_report_types AS t ON t.`id` = a.`fk_reporttype_id`');

        $query->select('`s`.`title_el` AS unit_title_el');
        $query->select('`s`.`title_en` AS unit_title_en');
        $query->select('`s`.`short_code_el` AS unit_short_code_el');
        $query->select('`s`.`short_code_en` AS unit_short_code_en');
        $query->join('LEFT', '#__ethaae_institutes_structure AS s ON s.`id` = a.`fk_unit_id`');

        $query->select('`i`.`title_el` AS institute_title_el');
        $query->select('`i`.`title_en` AS institute_title_en');
        $query->join('LEFT', '#__ethaae_institutes_structure AS i ON i.`id` = a.`fk_institute_id`');

        $query->select('`p`.`title_el` AS parent_title_el');
        $query->select('`p`.`title_en` AS parent_title_en');
        $query->join('LEFT', '#__ethaae_institutes_structure AS p ON p.`id` = a.`fk_parent_id`');


        $query->where('a.state = 1');

        // Filtering fk_reporttype_id
        $filter_fk_reporttype_id = ArrayHelper::getValue($filters,'fk_reporttype_id',0);
        if ($filter_fk_reporttype_id)
        {
            $query->where("a.`fk_reporttype_id` = '".$db->escape($filter_fk_reporttype_id)."'");
        }

        // Filtering fk_unit_id
        $filter_fk_unit_id = ArrayHelper::getValue($filters,'fk_unit_id',0);
        if ($filter_fk_unit_id)
        {
            $query->where("a.`fk_unit_id` = '".$db->escape($filter_fk_unit_id)."'");
        }

        // Filtering fk_unit_id
        $filter_fk_institute_id = ArrayHelper::getValue($filters,'fk_institute_id',0);
        if ($filter_fk_institute_id)
        {
            $query->where("a.`fk_institute_id` = '".$db->escape($filter_fk_institute_id)."'");
        }

        // Filtering fk_parent_id

        $fk_parent_id = ArrayHelper::getValue($filters,'fk_dept_id',0);
        if ($fk_parent_id)
        {
            $query->where("a.`fk_parent_id` = '".$db->escape($fk_parent_id)."'");
        }

        // Filtering report_year
        $filter_report_year = ArrayHelper::getValue($filters,'report_year',0);
        if ($filter_report_year > 0)
        {
            $query->where("a.`report_year` = '".$db->escape($filter_report_year)."'");
        }
        // Add the list ordering clause.
        $orderCol  = ArrayHelper::getValue($filters,'ordering','');
        $orderDirn = ArrayHelper::getValue($filters,'direction','');

        if ($orderCol && $orderDirn)
        {
            $query->order($db->escape($orderCol . ' ' . $orderDirn));
        }
        //Factory::getApplication()->enqueueMessage($db->replacePrefix((string) $query), 'notice');
        return $query;
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

    public static function getUnitInfo(int $id) {
        if (intval($id) > 0) {
            $db = Factory::getContainer()->get('DatabaseDriver');
            $query = $db->getQuery(true);
            $query
                ->select($db->quoteName('i.id'))
                ->select($db->quoteName('i.parentunitid'))
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

    public static function getUnitFullInfo(int $id,string $lang) {
        if (intval($id) > 0) {
            $db = Factory::getContainer()->get('DatabaseDriver');
            $query = $db->getQuery(true);
            $query
                ->select($db->quoteName('i.id'))
                ->select($db->quoteName('i.parentunitid'))
                ->select($db->quoteName('i.unitcode'))
                ->select($db->quoteName('i.unit_type_id'))
                ->select($db->quoteName('i.unit_grouping'))
                ->select($db->quoteName('i.title_'.$lang))
                ->select('CONCAT(u.short_code_'.$lang.',\'::\',i.title_'.$lang.') as full_title_'.$lang)
                ->from($db->quoteName('#__ethaae_institutes_structure','i'))
                ->join('LEFT', '#__ethaae_unit_type AS u ON u.id = i.unit_type_id')
                ->where($db->quoteName('i.id') . ' = '. $db->quote($id));
            $db->setQuery($query);
            $item =  $db->loadObject();

            $extensions = [
                'jpg',
                'jpeg',
                'png',
                'JPG',
                'JPEG',
                'PNG',
            ];
            $item->logo = self::getImage($item->unitcode,'/images/institutes/logos/',$extensions);
            return $item;
        }
        return ;
    }


    public static function getImage(string $img,string $path,array $extensions)
    {
        foreach ($extensions as $ext) {
            if (is_file(JPATH_SITE.$path.$img.'.'.$ext)) {
                return $path.$img.'.'.$ext;
            }
        }
        return false;
    }

}
