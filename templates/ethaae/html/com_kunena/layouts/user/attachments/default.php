<?php

/**
 * Kunena Component
 *
 * @package         Kunena.Template.Aurelia
 * @subpackage      Layout.User
 *
 * @copyright       Copyright (C) 2008 - 2024 Kunena Team. All rights reserved.
 * @license         https://www.gnu.org/copyleft/gpl.html GNU/GPL
 * @link            https://www.kunena.org
 **/

namespace Kunena\Forum\Site;

\defined('_JEXEC') or die();

use Joomla\CMS\HTML\HTMLHelper;
use Joomla\CMS\Language\Text;
use Kunena\Forum\Libraries\Icons\KunenaIcons;
use Kunena\Forum\Libraries\Route\KunenaRoute;
use Kunena\Forum\Libraries\User\KunenaUserHelper;

HTMLHelper::_('behavior.core');

$attachments = $this->attachments;
?>
<h3>
    <?php echo $this->headerText; ?>
</h3>

<div class="kfrontend shadow-lg rounded mt-4 border">
    <form action="<?php echo KunenaRoute::_('index.php?option=com_kunena&view=user'); ?>" method="post" id="adminForm"
          name="adminForm">
        <input type="hidden" name="task" value="delfile"/>
        <input type="hidden" name="boxchecked" value="0"/>
        <?php echo HTMLHelper::_('form.token'); ?>

        <table class="table table-bordered table-striped table-hover">
            <thead>
            <tr>
                <th class="col-md-1 center">
                    #
                </th>
                <?php if ($this->me->userid == $this->profile->userid || KunenaUserHelper::getMyself()->isModerator()) :
                    ?>
                    <th class="col-md-1 center">
                        <label>
                            <input type="checkbox" name="checkall-toggle" value="cid"
                                   data-bs-toggle="tooltip" title="<?php echo Text::_('COM_KUNENA_CHECK_ALL'); ?>" onclick="Joomla.checkAll(this)"/>
                        </label>
                    </th>
                <?php endif; ?>
                <th class="col-md-1 center">
                    <?php echo Text::_('COM_KUNENA_FILETYPE'); ?>
                </th>
                <th class="col-md-2">
                    <?php echo Text::_('COM_KUNENA_FILENAME'); ?>
                </th>
                <th class="col-md-2">
                    <?php echo Text::_('COM_KUNENA_FILESIZE'); ?>
                </th>
                <th class="col-md-2">
                    <?php echo Text::_('COM_KUNENA_ATTACHMENT_MANAGER_TOPIC'); ?>
                </th>
                <th class="col-md-1 center">
                    <?php echo Text::_('COM_KUNENA_PREVIEW'); ?>
                </th>
                <?php if ($this->me->userid == $this->profile->userid || KunenaUserHelper::getMyself()->isModerator()) :
                    ?>
                    <th class="col-md-1 center">
                        <?php echo Text::_('COM_KUNENA_DELETE'); ?>
                    </th>
                <?php endif; ?>
            </tr>
            </thead>
            <tbody>
            <?php if (!$attachments) :
                ?>
                <tr>
                    <td colspan="8">
                        <?php echo Text::_('COM_KUNENA_USER_NO_ATTACHMENTS'); ?>
                    </td>
                </tr>
            <?php else :
                $i = $this->pagination->limitstart;

                foreach ($attachments as $attachment) :
                    $message = $attachment->getMessage();
                    $canDelete = $attachment->isAuthorised('delete');

                    if ($attachment->isAuthorised('read', $this->me)) :
                        ?>
                        <tr>
                            <td class="center"><?php echo ++$i; ?></td>
                            <?php
                            if ($canDelete) :
                                ?>
                                <td class="center">
                                    <?php echo HTMLHelper::_('grid.id', $i, \intval($attachment->id)); ?>
                                </td>
                            <?php endif; ?>
                            <td class="center">
                                <?php echo $attachment->isImage() ? KunenaIcons::picture() : KunenaIcons::file(); ?>
                            </td>
                            <td>
                                <?php echo $attachment->getShortName(10, 5); ?>
                            </td>
                            <td>
                                <?php echo number_format(\intval($attachment->size) / 1024, 0, '', ',') . ' ' . Text::_('COM_KUNENA_USER_ATTACHMENT_FILE_WEIGHT'); ?>
                            </td>
                            <td>
                                <?php
                                if ($message->exists()) {
                                    echo $this->getTopicLink($message->getTopic(), $message, null, null, '', null, false, true);
                                } else {
                                    echo Text::_('COM_KUNENA_USER_ATTACHMENT_MESSAGE_NOT_EXIST');
                                }
                                ?>
                            </td>
                            <td class="center">
                                <?php echo $attachment->getLayout()->render('thumbnail'); ?>
                            </td>
                            <?php if ($canDelete) :
                                ?>
                                <td class="center">
                                    <a href="#modaldelete<?php echo $i ?>" role="button" class="btn center"
                                       data-bs-toggle="modal"><?php echo KunenaIcons::delete(); ?></a>

                                    <div class="modal fade" id="modaldelete<?php echo $i ?>" tabindex="-1" role="dialog"
                                         aria-labelledby="modaldelete<?php echo $i ?>Label">
                                        <div class="modal-dialog" role="document">
                                            <div class="modal-content">
                                                <div class="modal-header">
                                                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                                                    <h4 class="modal-title"
                                                        id="myModalLabel"><?php echo Text::_('COM_KUNENA_FILES_CONFIRMATION_DELETE_MODAL_LABEL ') ?></h4>
                                                </div>
                                                <div class="modal-body">
                                                    <p><?php echo Text::sprintf('COM_KUNENA_FILES_DELETE_MODAL_DESCRIPTION', $attachment->getFilename(), number_format(\intval($attachment->size) / 1024, 0, '', ',') . ' ' . Text::_('COM_KUNENA_USER_ATTACHMENT_FILE_WEIGHT')); ?></p>
                                                </div>
                                                <div class="modal-footer">
                                                    <button type="button" class="btn btn-outline-primary border"
                                                            onclick="return Joomla.listItemTask('cb<?php echo $i; ?>','delfile');"
                                                            data-bs-dismiss="modal"><?php echo Text::_('COM_KUNENA_FILES_CONFIRM_DELETE_MODAL_BUTTON') ?></button>
                                                    <button type="button" data-bs-dismiss="modal"
                                                            class="btn btn-outline-primary"><?php echo Text::_('COM_KUNENA_FILES_CANCEL_DELETE_MODAL_BUTTON') ?></button>
                                                </div>
                                            </div>
                                        </div>
                                    </div>
                                </td>
                            <?php endif; ?>
                        </tr>
                    <?php endif; ?>
                <?php endforeach; ?>
            <?php endif; ?>
            </tbody>
        </table>
    <?php if ($attachments && $this->me->userid == $this->profile->userid || $attachments && KunenaUserHelper::getMyself()->isModerator()) :
        ?>
        <a href="#modaldeleteall" class="btn btn-outline-primary border float-end"
           data-bs-toggle="modal"><?php echo Text::_('COM_KUNENA_FILES_DELETE'); ?></a>

        <div class="modal fade" id="modaldeleteall" tabindex="-1" role="dialog" aria-labelledby="modaldeleteallLabel">
            <div class="modal-dialog" role="document">
                <div class="modal-content">
                    <div class="modal-header">
                        <button type="button" class="close" data-bs-dismiss="modal" aria-label="Close"></button>
                        <h4 class="modal-title"
                            id="myModalLabel"><?php echo Text::_('COM_KUNENA_FILES_CONFIRMATION_DELETE_MODAL_LABEL ') ?></h4>
                    </div>
                    <div class="modal-body">
                        <p><?php echo Text::_('COM_KUNENA_FILES_DELETE_SELECTED_MODAL_DESCRIPTION'); ?></p>
                    </div>
                    <div class="modal-footer">
                        <button type="button" class="btn btn-outline-primary border"
                                id="modaldeteleallsubmit"><?php echo Text::_('COM_KUNENA_FILES_CONFIRM_DELETE_MODAL_BUTTON') ?></button>
                        <button type="button" class="btn btn-outline-primary" data-bs-dismiss="modal"
                        ><?php echo Text::_('COM_KUNENA_FILES_CANCEL_DELETE_MODAL_BUTTON') ?></button>
                    </div>
                </div>
            </div>
        </div>
    <?php endif; ?>
    </form>
</div>

<div class="float-start">
    <?php echo $this->subLayout('Widget/Pagination/List')
        ->set('pagination', $this->pagination->setDisplayedPages(4))
        ->set('display', true); ?>
</div>
<div class="clearfix"></div>
