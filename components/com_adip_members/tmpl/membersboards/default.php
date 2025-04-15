<?php
/**
 * @version    CVS: 2.0.0
 * @package    Com_Adip_members
 * @author     Tasos Triantis <tasos.tr@gmail.com>
 * @copyright  2024 Tasos Triantis
 * @license    GNU General Public License version 2 ou version ultérieure ; Voir LICENSE.txt
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
$canCreate  = $user->authorise('core.create', 'com_adip_members') && file_exists(JPATH_COMPONENT . DIRECTORY_SEPARATOR . 'forms' . DIRECTORY_SEPARATOR . 'membersboardform.xml');
$canEdit    = $user->authorise('core.edit', 'com_adip_members') && file_exists(JPATH_COMPONENT .  DIRECTORY_SEPARATOR . 'forms' . DIRECTORY_SEPARATOR . 'membersboardform.xml');
$canCheckin = $user->authorise('core.manage', 'com_adip_members');
$canChange  = $user->authorise('core.edit.state', 'com_adip_members');
$canDelete  = $user->authorise('core.delete', 'com_adip_members');

// Import CSS
$wa = $this->document->getWebAssetManager();
$wa->useStyle('com_adip_members.list');

$empty = URI::root()."images/adip_board_members/empty.png";

?>

<div class="board-members mod-list">
    <div class="g-grid">
        <?php foreach ($this->items as $item) : ?>
            <div class="g-block size-33 equal-height">
                <div class="g-content">
                    <div class="member-image" style="background-image:url('<?php echo $item->image; ?>');">
                        <a href="#<?php echo $item->link; ?>" class="html5lightbox" aria-label="<?php echo $item->fullname; ?>">
                            <img src="<?php echo $empty; ?>" alt="<?php echo strip_tags($item->fullname); ?>" class="sprocket-mosaic-image" />
                        </a>
                    </div>
                    <a alt="<?php echo strip_tags($item->fullname); ?>" href="#<?php echo $item->link; ?>" class="html5lightbox" aria-label="<?php echo $item->fullname; ?>"><h1 class="member-fullname"><?php echo $item->fullname; ?></h1></a>
                    <a alt="<?php echo strip_tags($item->member_type); ?>" href="#<?php echo $item->link; ?>" class="html5lightbox" aria-label="<?php echo $item->fullname; ?>" ><h2 class="member-position"><?php echo $item->member_type; ?></h2></a>
                    <div class="clearfix footer">&nbsp;</div>

                </div>
            </div>
        <?php endforeach; ?>
    </div>

    <?php foreach ($this->items as $item) : ?>

        <div id="<?php echo $item->link?>" class="modal-wrapper">
            <div class="borad-member-cv">
                <div class="moduletabletitle-border clearfix">
                    <div class="title">
                        <h1 class="fullname"><?php echo $item->fullname; ?></h1>
                    </div>
                </div>
                <div class="subtitle clearfix">
                    <h2><?php echo $item->full_member_type; ?></h2>
                </div>

                <div class="content clearfix">
                    <img src="<?php echo $item->image; ?>" alt="<?php echo strip_tags($item->fullname); ?>"  />
                    <?php echo $item->cv; ?>
                </div>
            </div>
        </div>
    <?php endforeach; ?>


</div>
