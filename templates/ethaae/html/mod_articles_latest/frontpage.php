<?php
/**
 * @package     Joomla.Site
 * @subpackage  mod_articles_latest
 *
 * @copyright   Copyright (C) 2005 - 2019 Open Source Matters, Inc. All rights reserved.
 * @license     GNU General Public License version 2 or later; see LICENSE.txt
 */

defined('_JEXEC') or die;

use Joomla\CMS\Language\Text;
use Joomla\CMS\Date\Date;
use Joomla\CMS\Factory;


if(!class_exists('SimpleImageGalleryProHelper')) require_once (JPATH_PLUGINS.'/content/jw_sigpro/jw_sigpro/includes/helper.php');

?>
<div class="latestnews<?php echo $moduleclass_sfx; ?> mod-list">
    <div class="g-grid">
        <?php foreach ($list as $item) : ?>
            <div class="g-block size-33 equal-height">
                <div class="g-content">
                 <?php
                    $date = new Date($item->created);
                    $d = $date->format(Text::_('d.m.Y'));
                 ?>
                    <p class="date"><?php echo $d; ?></p>
                    <h4 class="g-features-particle-title"><a href="<?php echo $item->link;?>"><?php echo $item->title; ?></a></h4>
                    <!-- <p class="read_more"><a href="<?php echo $item->link;?>"><?php echo JText::_('MOD_ARTICLES_LATEST_MORE');?></a></p> -->
            </div>
            </div>
        <?php endforeach; ?>
    </div>
</div>

