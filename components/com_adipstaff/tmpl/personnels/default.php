<?php
/**
 * @version    CVS: 2.0.7
 * @package    Com_Adipstaff
 * @author     Tasos Triantis <tasos.tr@gmail.com>
 * @copyright  2024 Tasos Triantis
 * @license    GNU General Public License version 2 or later; see LICENSE.txt
 */
// No direct access
defined('_JEXEC') or die;

use \Joomla\CMS\HTML\HTMLHelper;
use \Joomla\CMS\Factory;
use \Joomla\CMS\Uri\Uri;
use \Joomla\CMS\Router\Route;
use \Joomla\CMS\Language\Text;
use \Joomla\CMS\Layout\LayoutHelper;
use \Joomla\CMS\Session\Session;
use \Joomla\CMS\User\UserFactoryInterface;

HTMLHelper::_('bootstrap.tooltip');
HTMLHelper::_('behavior.multiselect');
HTMLHelper::_('formbehavior.chosen', 'select');

$user       = Factory::getApplication()->getIdentity();
$userId     = $user->get('id');
$listOrder  = $this->state->get('list.ordering');
$listDirn   = $this->state->get('list.direction');
$canCreate  = $user->authorise('core.create', 'com_adipstaff') && file_exists(JPATH_COMPONENT . DIRECTORY_SEPARATOR . 'forms' . DIRECTORY_SEPARATOR . 'personnelform.xml');
$canEdit    = $user->authorise('core.edit', 'com_adipstaff') && file_exists(JPATH_COMPONENT .  DIRECTORY_SEPARATOR . 'forms' . DIRECTORY_SEPARATOR . 'personnelform.xml');
$canCheckin = $user->authorise('core.manage', 'com_adipstaff');
$canChange  = $user->authorise('core.edit.state', 'com_adipstaff');
$canDelete  = $user->authorise('core.delete', 'com_adipstaff');

// Import CSS
$wa = $this->document->getWebAssetManager();
$wa->useStyle('com_adipstaff.list');

$showMails = $this->params->get('display_mail',0);
$showPhones = $this->params->get('display_phones',0);
?>



<table class="personnel">
    <tbody>
    <?php foreach ($this->items as $i => $persons) : ?>

        <tr>
            <td class="header" colspan="4"><?php echo $persons[0]->direction ?></td>
        </tr>
        <tr class="row0">
            <td class="subheader"><?php echo $persons[0]->dep ?></td>
            <td><?php echo $persons[0]->fullname ?></td>
            <?php if ($showMails): ?>
                <td><?php echo $persons[0]->mail ?></td>
            <?php endif; ?>
            <?php if ($showPhones): ?>
                <td><?php echo $persons[0]->phones ?></td>
            <?php endif; ?>

        </tr>
        <?php
        $pre_dep = '';
        foreach(array_slice($persons,1) as $p) : ?>
            <tr class="row1">
                <?php
                if ($pre_dep !== $p->dep) {
                    $pre_dep =  $p->dep;
                    ?>
                    <td class="subheader"><?php echo $p->dep ?></td>
                <?php } else { ?>
                    <td>&nbsp;</td>
                <?php } ?>


                <td><?php echo $p->fullname ?></td>
                <?php if ($showMails): ?>
                    <td><?php echo $p->mail ?></td>
                <?php endif; ?>
                <?php if ($showPhones): ?>
                    <td><?php echo $p->phones ?></td>
                <?php endif; ?>
            </tr>
        <?php endforeach; ?>
    <?php endforeach; ?>
    </tbody>
</table>
