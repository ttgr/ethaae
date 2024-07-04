<?php

/**
 * Kunena Component
 *
 * @package         Kunena.Template.Aurelia
 * @subpackage      BBCode
 *
 * @copyright       Copyright (C) 2008 - 2024 Kunena Team. All rights reserved.
 * @license         https://www.gnu.org/copyleft/gpl.html GNU/GPL
 * @link            https://www.kunena.org
 **/

namespace Kunena\Forum\Site;

\defined('_JEXEC') or die();

use Joomla\CMS\Language\Text;
use Kunena\Forum\Libraries\Attachment\KunenaAttachmentHelper;
use Kunena\Forum\Libraries\Config\KunenaConfig;
use Kunena\Forum\Libraries\Icons\KunenaIcons;

$attachment = $this->attachment;

if (!$attachment->getPath()) {
    return;
}

$config = KunenaConfig::getInstance();

$attributesLink = $attachment->isImage() && $config->lightbox ? ' data-fancybox="none"' : '';
?>

<a class="btn btn-outline-primary border btn-sm" data-bs-toggle="popover" data-bs-placement="bottom" data-bs-trigger="hover"
   target="_blank"
   rel="noopener noreferrer"
   data-bs-content="Filesize: <?php echo number_format($attachment->size / 1024, 0, '', ',') . Text::_('COM_KUNENA_USER_ATTACHMENT_FILE_WEIGHT'); ?>
" data-original-title="<?php echo $attachment->getShortName(); ?>"
   href="<?php echo $attachment->getUrl(false, false, true); ?>"
   title="<?php echo KunenaAttachmentHelper::shortenFileName($attachment->getFilename(), (int) $config->attachStart, (int) $config->attachEnd); ?>" <?php echo $attributesLink; ?>>
    <?php echo KunenaIcons::info(); ?>
</a>
