<?php
/**
 * @version    CVS: 1.1.0
 * @package    Com_Ethaae_units_info
 * @author     Tasos Triantis <tasos.tr@gmail.com>
 * @copyright  2024 Tasos Triantis
 * @license    GNU General Public License version 2 or later; see LICENSE.txt
 */

namespace Ethaaunitsinfo\Component\Ethaae_units_info\Site\Helper;

defined('_JEXEC') or die;

use \Joomla\CMS\Factory;
use \Joomla\CMS\MVC\Model\BaseDatabaseModel;
use \Joomla\CMS\Helper\ModuleHelper;

/**
 * Class Ethaae_units_infoFrontendHelper
 *
 * @since  1.1.0
 */
class Ethaae_units_infoHelper
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
	public static function getFiles($pk, $table, $field)
	{
		$db = Factory::getContainer()->get('DatabaseDriver');
		$query = $db->getQuery(true);

		$query
			->select($field)
			->from($table)
			->where('id = ' . (int) $pk);

		$db->setQuery($query);

		return explode(',', $db->loadResult());
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

		if ($user->authorise('core.edit', 'com_ethaae_units_info') || (isset($item->created_by) && $user->authorise('core.edit.own', 'com_ethaae_units_info') && $item->created_by == $user->id) || $user->authorise('core.create', 'com_ethaae_units_info'))
		{
			$permission = true;
		}

		return $permission;
	}


    public static function getImageGallery(array $mod_params) {
        $app = Factory::getApplication();
        $renderer = $app->getDocument()->loadRenderer('module');
        $module = ModuleHelper::getModule( 'amazingslider');
        $options  = array('style' => 'raw');

        $mod_params = array('params' => json_encode($mod_params));
        return $renderer->render($module, $mod_params, $options);
    }

}
