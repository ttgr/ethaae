<?php

/**
 * @package     Joomla.Site
 * @subpackage  mod_login
 *
 * @copyright   (C) 2006 Open Source Matters, Inc. <https://www.joomla.org>
 * @license     GNU General Public License version 2 or later; see LICENSE.txt
 */

defined('_JEXEC') or die;

use Joomla\CMS\HTML\HTMLHelper;
use Joomla\CMS\Language\Text;
use Joomla\CMS\Router\Route;

/** @var Joomla\CMS\WebAsset\WebAssetManager $wa */
$wa = \Joomla\CMS\Factory::getApplication()->getDocument()->getWebAssetManager();
$wa->useScript('keepalive');

?>
<form class="mod-login-logout form-vertical" action="<?php echo Route::_('index.php', true); ?>" method="post" id="login-form-<?php echo $module->id; ?>">
<?php if ($params->get('greeting', 1)) : ?>
    <div class="mod-login-logout__login-greeting login-greeting">
    <?php if (!$params->get('name', 0)) : ?>
        <?php echo Text::sprintf('MOD_LOGIN_HINAME', htmlspecialchars($user->name, ENT_COMPAT, 'UTF-8')); ?>
    <?php else : ?>
        <?php echo Text::sprintf('MOD_LOGIN_HINAME', htmlspecialchars($user->username, ENT_COMPAT, 'UTF-8')); ?>
    <?php endif; ?>
    </div>
<?php endif; ?>
    <div class="mod-login-logout__button logout-button">
        <div class="controls">
            <a href="<?php echo JURI::root().'?hqarequest=ssologout';?> " class="btn btn-primary login-button">
                <span class="icon-lock icon-white"></span> <?php echo JText::_('TPL_ADMIN_MOD_LOGOUT_SSO_BTN'); ?>
            </a>
        </div>
        <input type="hidden" name="return" value="<?php echo $return; ?>">
        <?php echo HTMLHelper::_('form.token'); ?>
    </div>
</form>
