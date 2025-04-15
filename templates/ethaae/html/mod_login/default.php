<?php
/**
 * @package     Joomla.Site
 * @subpackage  mod_login
 *
 * @copyright   Copyright (C) 2005 - 2015 Open Source Matters, Inc. All rights reserved.
 * @license     GNU General Public License version 2 or later; see LICENSE.txt
 */

defined('_JEXEC') or die;

use Joomla\CMS\Component\ComponentHelper;
use Joomla\CMS\HTML\HTMLHelper;
use Joomla\CMS\Language\Text;
use Joomla\CMS\Plugin\PluginHelper;
use Joomla\CMS\Router\Route;

$app->getDocument()->getWebAssetManager()
    ->useScript('core')
    ->useScript('keepalive')
    ->useScript('field.passwordview');



?>

<?php if ($params->get('pretext')) : ?>
    <div class="pretext">
        <p><?php echo $params->get('pretext'); ?></p>
    </div>
<?php endif; ?>


<div class="userlogin">
    <div class="controls">
        <a href="<?php echo JURI::root().'?hqarequest=ssologin';?> " class="btn btn-primary login-button">
            <span class="icon-lock icon-white"></span> <?php echo JText::_('TPL_ADMIN_MOD_LOGIN_SSO_BTN'); ?>
        </a>
    </div>
</div>
<?php if ($params->get('posttext')) : ?>
    <div class="posttext">
        <p><?php echo $params->get('posttext'); ?></p>
    </div>
<?php endif; ?>

