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

$session = JFactory::getSession();
$sid = md5($session->getId());

?>
<ul class="latestreports report-2 <?php echo $moduleclass_sfx; ?> mod-list">
    <?php foreach ($list as $item) :

//        $date = new Date($item->created);
//        $d = $date->format(Text::_('d.m.Y'))
//  ;
        $item->link = "#";
        if (isset($item->files[0])) {
            $item->link = JUri::root() . "index.php?option=com_ethaae_reports&task=report.download&id=" . md5($item->files[0]->id) . "&sid=" . $sid;
        }

        ?>
        <li itemscope itemtype="https://schema.org/Article">
            <div class="clearfix">
                <div class="date">
                    <a href="<?php echo $item->link; ?>" itemprop="url">
                        <span class="date"><?php echo $item->report_year; ?></span>
                    </a>
                </div>
                <div class="title">
                    <a class="html5lightbox" href="<?php echo $item->link; ?>" itemprop="url">
                        <span itemprop="name" class="title"><?php echo $item->{'institute_title_'.$langSEF}." :: ".$item->{'unit_title_'.$langSEF}; ?></span>
                    </a>
                </div>
            </div>
        </li>
    <?php endforeach; ?>
</ul>

<?php
$id = $params->get('show_more_url', '');
if (!empty($id)) :
    ?>
    <div class="latestreports">
        <p class="read_more"><a href="<?php echo JRoute::_("index.php?Itemid={$id}");?>"><?php echo Text::_('JGLOBAL_READ_MORE'); ?></a></p>
    </div>
<?php endif; ?>

