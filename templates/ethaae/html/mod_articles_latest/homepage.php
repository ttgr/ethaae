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

?>
<ul class="latestnews<?php echo $moduleclass_sfx; ?> mod-list">
    <?php foreach ($list as $item) : ?>
        <?php
        $date = new Date($item->created);
        $d = $date->format(Text::_('d.m.Y'));
        ?>
        <li itemscope itemtype="https://schema.org/Article">
            <div class="clearfix">
                <div class="date">
                    <a href="<?php echo $item->link; ?>" itemprop="url">
                        <span class="date"><?php echo $d; ?></span>
                    </a>
                </div>
                <div class="title">
                    <a href="<?php echo $item->link; ?>" itemprop="url">
                        <span itemprop="name" class="title"><?php echo $item->title; ?></span>
                    </a>
                </div>
            </div>
        </li>
    <?php endforeach; ?>
</ul>
