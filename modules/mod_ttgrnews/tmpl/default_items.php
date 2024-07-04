<?php
/**
 * @package     Joomla.Site
 * @subpackage  mod_bienewspage
 *
 * @copyright   (C) 2010 Open Source Matters, Inc. <https://www.joomla.org>
 * @license     GNU General Public License version 2 or later; see LICENSE.txt
 */




?>

<?php foreach ($list as $item) :
    // Get the thumbnail
    //$thumb_img = ModMinifrontpageproHelper::getThumbnail($params, $item->id, $item->images, $item->title,$item->introtext.$item->fulltext,$module->id,$item->modified,$item->attribs);
    //dump($item);
    ?>
    <div class="col-xs-12 col-sm-6 col-md-<?php echo 12/$params->get('num_column', 3);?> mfp_infinity_item">
        <div class="mfp_infinity_item_inner">
            <div class="mfp_image" style="background-image:url('<?php echo $item->imageSrc; ?>');background-size: cover;background-position: center center;">
                <a href="<?php echo $item->link; ?>" class="mfp_thumb_pos_top" itemprop="url"><img src="<?php echo $item->imageSrc; ?>" /></a>
            </div>
            <div class="mfp_body">
            <span class='mfp_date'><?php echo $item->displayDate; ?></span>
            <h2 class="news-title">
                <a href="<?php echo $item->link; ?>" itemprop="url">
                    <?php echo $item->title;  ?>
                </a>
            </h2>
                <div class='news-text'><?php echo $item->displayIntrotext; ?></div>
            </div>
        </div>
    </div>
    <?php
endforeach; ?>
