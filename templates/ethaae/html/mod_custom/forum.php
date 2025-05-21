<?php
/**
 * @package     Joomla.Site
 * @subpackage  mod_custom
 *
 * @copyright   Copyright (C) 2005 - 2020 Open Source Matters, Inc. All rights reserved.
 * @license     GNU General Public License version 2 or later; see LICENSE.txt
 */

defined('_JEXEC') or die;

use Joomla\CMS\Factory;
use \Joomla\CMS\Uri\Uri;
$app = Factory::getApplication();
$user = $app->getIdentity();


$usergroups = $user->getAuthorisedGroups();
$userAccessLevels = $user->getAuthorisedViewLevels();


$menu = $app->getMenu()->getActive();
//  echo $menu->id;

?>


<div class="custom<?php echo $moduleclass_sfx; ?>" <?php if ($params->get('backgroundimage')) : ?> style="background-image:url(<?php echo $params->get('backgroundimage'); ?>)"<?php endif; ?> >
    <?php if ($user->id > 0 && (in_array(8,$usergroups) || in_array(11,$usergroups))): ?>
        <div class="align-right">
            <p class="align-right">Xρήστης: <b><?php echo "<span class=\"customuserdisplay\">$user->name</span>"; ?></b>
            <a href="<?php echo URI::root();?>el/forum/?hqarequest=ssologout" class="logout">
                <span class="icon-lock icon-white"></span>Αποσύνδεση</a>
            </p>
        </div>
    <?php endif; ?>



<?php
if ($user->id == 0) :
?>
    <?php if ($menu->id == 644 && !empty($module->content)): ?>
       <!-- <h3 class="center">Καλώς ήλθατε στο Forum της ΕΘΑΑΕ</h3> -->
    <?php endif;?>
	<!--<h3 class="center">Εναλλακτικοί Τρόποι Αξιολόγησης των Φοιτητών/τριών για την Εξεταστική του Ιουνίου</h3>-->
    <?php if ($menu->id == 644 && !empty($module->content)) {
        echo $module->content;
        }
    ?>

    <div class="pretext">
        <!-- <p>Για να κάνετε login θα πρέπει να έχει ήδη κωδικό στο SSO της ΕΘΑΑΕ. Για περισσότερες πληροφορίες επικοινωνήστε με την τεχνική υποστήριξη της ΕΘΑΑΕ.</p> -->
    </div>
    <div class="userlogin">
        <div class="controls">
            <a href="<?php echo URI::root();?>el/forum/?hqarequest=ssologin" class="btn btn-primary login-button">
                <span class="icon-lock icon-white"></span> Είσοδος</a>
        </div>
    </div>
<?php elseif (in_array(8,$usergroups) || in_array(11,$usergroups) || in_array(8,$userAccessLevels)): ?>
    <?php if ($menu->id == 644 && !empty($module->content)): ?>
       <!-- <h3 class="center">Καλώς ήλθατε στο Forum της ΕΘΑΑΕ</h3> -->
    <?php endif;?>

	<!--<h3 class="center">Εναλλακτικοί Τρόποι Αξιολόγησης των Φοιτητών/τριών για την Εξεταστική του Ιουνίου</h3> -->
    <?php if ($menu->id == 644 && !empty($module->content)) {
        echo $module->content;
        }
    ?>

    <?php else: ?>

    <?php if ($menu->id == 644 && !empty($module->content)): ?>
       <!-- <h3 class="center">Καλώς ήλθατε στο Forum της ΕΘΑΑΕ</h3> -->
    <?php endif;?>

    <!--<h3 class="center">Εναλλακτικοί Τρόποι Αξιολόγησης των Φοιτητών/τριών για την Εξεταστική του Ιουνίου</h3> -->
    <?php if ($menu->id == 644 && !empty($module->content)) {
        echo $module->content;
    }
    ?>

    <div class="pretext customuserinfo">
        <p class="loggedintext">Έχετε συνδεθεί ως χρήστης: <b><?php echo "<span class=\"customuserdisplay\">$user->name</span>"; ?></b>. Δεν έχετε ακόμα πρόσβαση στο Forum. Ο λογαριασμός σας είναι υπό έγκριση από τον Διαχειριστή του Forum.</p>
        <div class="controls center">
            <a href="<?php echo URI::root();?>el/forum/?hqarequest=ssologout" class="btn btn-primary login-button">
                <span class="icon-lock icon-white"></span> Αποσύνδεση</a>
        </div>

    </div>

<?php endif;?>

</div>
