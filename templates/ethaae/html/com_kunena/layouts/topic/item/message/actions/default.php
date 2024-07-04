<?php

/**
 * Kunena Component
 *
 * @package         Kunena.Template.Aurelia
 * @subpackage      Layout.Topic
 *
 * @copyright       Copyright (C) 2008 - 2024 Kunena Team. All rights reserved.
 * @license         https://www.gnu.org/copyleft/gpl.html GNU/GPL
 * @link            https://www.kunena.org
 **/

namespace Kunena\Forum\Site;

\defined('_JEXEC') or die();

use Joomla\CMS\Language\Text;

$message = $this->message;

$dateText = '';

if ($this->config->editMarkup && $this->message->modified_time) {
    $dateText  = $message->getModifiedTime()->toKunena('config_postDateFormat') . ' ';
    $dateTitle = 'data-bs-toggle="tooltip" title="' . $message->getModifiedTime()->toKunena('config_postDateFormatHover') . '"';
}
?>
    <div>

        <?php if ($this->config->editMarkup && $this->message->modified_by) :
            ?>
            <span class="alert" <?php echo $dateTitle; ?>>
            <?php echo Text::sprintf('COM_KUNENA_EDITING_LASTEDIT_ON_BY', $dateText, $this->message->getModifier()->getLink()); ?>
            <?php
            if ($this->message->modified_reason) {
                echo Text::_('COM_KUNENA_REASON')
                . ': ' . $this->escape($this->message->modified_reason);
            } ?>
    </span>
            <br/>
        <?php endif ?>

        <?php if (!empty($this->thankyou)) :
            ?>
            <div>
                <?php
                echo Text::_('COM_KUNENA_THANKYOU') . ': ' . implode(', ', $this->thankyou) . ' ';

                if ($this->more_thankyou) {
                    echo Text::sprintf('COM_KUNENA_THANKYOU_MORE_USERS', $this->more_thankyou);
                }
                ?>
            </div>
        <?php endif; ?>

        <?php echo $this->subRequest('Message/Item/Actions')->set('mesid', $this->message->id); ?>
    </div>


<?php echo $this->subLayout('Message/Edit')->set('message', $this->message)->setLayout('quickReply');
