<?php

/**
 * @package     Joomla.Site
 * @subpackage  Layout
 *
 * @copyright   (C) 2017 Open Source Matters, Inc. <https://www.joomla.org>
 * @license     GNU General Public License version 2 or later; see LICENSE.txt
 */

defined('_JEXEC') or die;

use Joomla\CMS\Language\Text;
use Joomla\CMS\Uri\Uri;

$blockPosition = $displayData['params']->get('info_block_position', 0);
$articleId = $displayData['item']->id;
$title= $displayData['item']->title;
$description = $displayData['item']->introtext;
$fb_Share = "https://www.facebook.com/sharer/sharer.php?u=".urlencode(URI::current())."&amp;title=".urlencode($title);
$twitter_Share = "https://twitter.com/share?url=".urlencode(URI::current())."&amp;text=".urlencode($title);
$linkedin_Share = "https://www.linkedin.com/sharing/share-offsite/?url=".urlencode(URI::current());

?>
<dl class="news-article-info text-muted">

    <?php
    if (
        $displayData['position'] === 'above' && ($blockPosition == 0 || $blockPosition == 2)
        || $displayData['position'] === 'below' && ($blockPosition == 1)
    ) : ?>
        <?php if ($displayData['params']->get('show_author') && !empty($displayData['item']->author)) : ?>
            <?php echo $this->sublayout('author', $displayData); ?>
        <?php endif; ?>

        <?php if ($displayData['params']->get('show_parent_category') && !empty($displayData['item']->parent_id)) : ?>
            <?php echo $this->sublayout('parent_category', $displayData); ?>
        <?php endif; ?>

        <?php if ($displayData['params']->get('show_category')) : ?>
            <?php echo $this->sublayout('category', $displayData); ?>
        <?php endif; ?>

        <?php if ($displayData['params']->get('show_associations')) : ?>
            <?php echo $this->sublayout('associations', $displayData); ?>
        <?php endif; ?>

        <?php if ($displayData['params']->get('show_publish_date')) : ?>
            <?php echo $this->sublayout('publish_date', $displayData); ?>
        <?php endif; ?>
    <?php endif; ?>

    <?php
    if (
        $displayData['position'] === 'above' && ($blockPosition == 0)
        || $displayData['position'] === 'below' && ($blockPosition == 1 || $blockPosition == 2)
    ) : ?>
        <?php if ($displayData['params']->get('show_create_date')) : ?>
            <?php echo $this->sublayout('create_date', $displayData); ?>
        <?php endif; ?>

        <?php if ($displayData['params']->get('show_modify_date')) : ?>
            <?php echo $this->sublayout('modify_date', $displayData); ?>
        <?php endif; ?>

        <?php if ($displayData['params']->get('show_hits')) : ?>
            <?php echo $this->sublayout('hits', $displayData); ?>
        <?php endif; ?>
        <dd  class="social-media">
            <dl  class="flex-container" aria-labelledby="dropdownMenuButton-<?php echo $articleId; ?>">
                <dd class="flex-item social-media-item facebook-icon">
                    <a href="<?php echo $fb_Share;?>" class="popup" onclick="javascript:window.open(this.href, '', 'menubar=no,toolbar=no,resizable=yes,scrollbars=yes,height=300,width=600');return false;" target="_blank" alt="Share on Facebook">
                        <span class="fa-brands fa-square-facebook" aria-hidden="true"></span>
                    </a>
                </dd>
                <dd class="flex-item social-media-item twitter-icon">
                    <a href="<?php echo $twitter_Share;?>" class="popup" onclick="javascript:window.open(this.href, '', 'menubar=no,toolbar=no,resizable=yes,scrollbars=yes,height=300,width=600');return false;" target="_blank" alt="Share on Facebook">
                        <span class="fa-brands fa-square-x-twitter" aria-hidden="true"></span>
                    </a>
                </dd>
                <dd class="flex-item social-media-item linkedin-icon">
                    <a href="<?php echo $linkedin_Share;?>" class="popup" onclick="javascript:window.open(this.href, '', 'menubar=no,toolbar=no,resizable=yes,scrollbars=yes,height=300,width=600');return false;" target="_blank" alt="Share on Facebook">
                        <span class="fa fa-linkedin-square" aria-hidden="true"></span>
                    </a>
                </dd>
            </dl>

        </dd>

    <?php endif; ?>
</dl>
