<?php

/**
 * Kunena Component
 *
 * @package         Kunena.Template.Aurelia
 * @subpackage      Layout.Announcement
 *
 * @copyright       Copyright (C) 2008 - 2024 Kunena Team. All rights reserved.
 * @license         https://www.gnu.org/copyleft/gpl.html GNU/GPL
 * @link            https://www.kunena.org
 **/

namespace Kunena\Forum\Site;

\defined('_JEXEC') or die();

use Joomla\CMS\HTML\HTMLHelper;
use Joomla\CMS\Language\Text;

$row          = $this->row;
$announcement = $this->announcement;
?>

<tr>
    <td class="nowrap d-none d-md-table-cell">
        <?php echo $announcement->displayField('created', 'date_today'); ?>
    </td>

    <td class="nowrap">
        <div class="overflow">
            <?php echo HTMLHelper::_(
                'kunenaforum.link',
                $announcement->getUri(),
                $announcement->displayField('title'),
                null,
                'follow'
            ); ?>
        </div>
    </td>

    <?php if ($this->checkbox) :
    ?>
        <td class="center">
            <?php if ($this->canPublish()) {
                echo HTMLHelper::_('kunenagrid.published', $row, $announcement->published, '', true);
            } ?>
        </td>
        <td class="center">
            <?php if ($this->canEdit()) {
                echo HTMLHelper::_(
                    'kunenagrid.task',
                    $row,
                    'tick.png',
                    Text::_('COM_KUNENA_ANN_EDIT'),
                    'edit',
                    '',
                    true
                );
            } ?>
        </td>
        <td class="center">
            <?php if ($this->canDelete()) {
                echo HTMLHelper::_(
                    'kunenagrid.task',
                    $row,
                    'publish_x.png',
                    Text::_('COM_KUNENA_ANN_DELETE'),
                    'delete',
                    '',
                    true
                );
            } ?>
        </td>
        <td>
        <?php if ($this->config->username) :
            ?>
                <?php echo $announcement->getAuthor()->username; ?>
            <?php else :
            ?>
                <?php echo $announcement->getAuthor()->name; ?>
            <?php endif; ?>
        </td>
    <?php endif; ?>

    <td class="center d-none d-md-table-cell">
        <?php echo $announcement->displayField('id'); ?>
    </td>

    <?php if ($this->checkbox) :
    ?>
        <td class="center">
            <?php echo HTMLHelper::_('kunenagrid.id', $row, $announcement->id); ?>
        </td>
    <?php endif; ?>

</tr>