<?php

/**
 * @package     Joomla.Site
 * @subpackage  mod_bienewspage
 *
 * @copyright   (C) 2010 Open Source Matters, Inc. <https://www.joomla.org>
 * @license     GNU General Public License version 2 or later; see LICENSE.txt
 */

defined('_JEXEC') or die;

use Joomla\CMS\Helper\ModuleHelper;
use Joomla\CMS\Language\Text;

if (!$list) {
    return;
}

?>

<form action="<?php echo JRoute::_('index.php');?>" method="post" name="newsPageForm" id="newsPageForm" enctype="multipart/form-data">
<div class="bie-controls newpage_filters" >
    <div class="newpage_filtes_item"><?php echo $form->getInput('category_id');  ?></div>
    <div class="newpage_filtes_item"><?php echo $form->getInput('year');  ?></div>
</div>
<div class="mfp_infinity_skin_card mfp_mid_<?php echo $module->id;?>">
    <div class="mfp-grid">
        <?php //require ModuleHelper::getLayoutPath('mod_ttgrnews', $params->get('layout', 'default') . '_items'); ?>
    </div>
        <div id="mfp_load_btn_wrp_<?php echo $module->id; ?>" class="mfp_load_btn_wrp_bottom">
            <input type="hidden" name="count_<?php echo $module->id; ?>" value="<?php echo $params->get('count', 9); ?>"/>
            <button id="mfp_load_btn_<?php echo $module->id; ?>" class="btn"><?php echo Text::_('MOD_TTGRNEWS_INFINITY_LOAD_MORE') ?></button>
        </div>
 </div>
</form>
<script>

    <?php if($infinity_ajax_trigger == 1) { ?>

    jQuery(window).on('scroll',function(e) {
        e.preventDefault();
        var hT = jQuery('#mfp_load_btn_wrp_<?php echo $module->id; ?>').offset().top,
            hH = jQuery('#mfp_load_btn_wrp_<?php echo $module->id; ?>').outerHeight(),
            wH = jQuery(window).height(),
            wS = jQuery(this).scrollTop();

        if (wS > ((hT+hH-wH)-500)){
            jQuery(document).off('scroll');
            jQuery('#mfp_load_btn_<?php echo $module->id; ?>').click().attr("disabled", true);
        }
    });


    <?php } ?>

    function getSelectedOption(obj) {
        var options = jQuery(obj+' option');
        var values = jQuery.map(options ,function(option) {
            return option.value;
        });

        return values[0];
    }

    function getChoiceSelectedValue(obj) {
        field = document.getElementById(obj).closest('joomla-field-fancy-select');
        select = field.choicesInstance;
        return select.getValue().value;
    }

    function setChoiceSelectedValue(obj,val) {
        field = document.getElementById(obj).closest('joomla-field-fancy-select');
        select = field.choicesInstance;
        select.setChoiceByValue(val);
    }

    function getInitialAjax(catID,year,start,loadMore) {
        // console.log(catID);
        // console.log(year);

        let request = {
            'option' : 'com_ajax',
            'module' : 'ttgrnews',
            'format' : 'raw',
            'start': start,
            'year': year,
            'category_id':catID,
            'Itemid': '<?php echo $itemID; ?>',
            'module_title' : '<?php echo $module->title; ?>'
        };

        jQuery('#mfp_load_btn_<?php echo $module->id; ?>').text('').html('<div class="loader"><?php echo Text::_('MOD_TTGRNEWS_INFINITY_LOAD_MORE') ?></div>');
        jQuery.ajax({
            type : 'GET',
            data : request,
            success: function (output) {
                if(jQuery.trim(output).length == 0) {
                    jQuery('#mfp_load_btn_<?php echo $module->id; ?>').text('<?php echo Text::_('MOD_TTGRNEWS_INFINITY_NO_MORE') ?>');
//                    jQuery(".mfp_mid_<?php echo $module->id; ?> > .mfp-grid").html('<?php echo Text::_('MOD_TTGRNEWS_INFINITY_NO_MORE') ?>');
                } else {
                    if (loadMore == 1) {
                        jQuery(output).appendTo(".mfp_mid_<?php echo $module->id; ?> > .mfp-grid");
                    } else {
                        jQuery(".mfp_mid_<?php echo $module->id; ?> > .mfp-grid").html(output);
                    }
                    var count = jQuery(".mfp_mid_<?php echo $module->id; ?> .mfp_infinity_item").length;
                    jQuery('input[name=count_<?php echo $module->id; ?>]').val(count);
                    jQuery('#mfp_load_btn_<?php echo $module->id; ?>').text('<?php echo Text::_('MOD_TTGRNEWS_INFINITY_LOAD_MORE') ?>');
                    jQuery('#mfp_load_btn_<?php echo $module->id; ?>').show();
                }
            },
            error: function(output) {
                jQuery('#mfp_load_btn_<?php echo $module->id; ?>').text('<?php echo Text::_('MOD_TTGRNEWS_INFINITY_ERROR') ?>');
                setTimeout(function() {
                    jQuery('#mfp_load_btn_<?php echo $module->id; ?>').hide();
                }, 2000);
            },
            complete :function(output) {
                jQuery('#mfp_load_btn_<?php echo $module->id; ?>').attr("disabled", false);
            }
        });

    }

    jQuery(document).ready(function() {
        var catID = Cookies.get('categoryid');
        var year = Cookies.get('year');
        if ( typeof catID === 'undefined' || catID === null ) {catID = 0;}
        if ( typeof year === 'undefined' || year === null ) {year = 0;}

        //console.log('Ready');
        getInitialAjax(catID,year,0,0);
        setChoiceSelectedValue('newsPageForm_category_id',catID);
        setChoiceSelectedValue('newsPageForm_year',year);
        return false;
    });

    jQuery(document).on('change', '#newsPageForm_category_id', function(e) {
        e.preventDefault();
        var catID = getChoiceSelectedValue('newsPageForm_category_id');
        var year = getChoiceSelectedValue('newsPageForm_year');

        if (parseInt(catID, 10) > 0) { Cookies.set('categoryid', catID);} else {Cookies.remove('categoryid');}
        if (parseInt(year, 10) > 0) {Cookies.set('year', year);} else {Cookies.remove('year');}

        getInitialAjax(catID,year,0,0);
        return false;
    });

    jQuery(document).on('change', '#newsPageForm_year', function(e) {
        e.preventDefault();
        var catID = getChoiceSelectedValue('newsPageForm_category_id');
        var year = getChoiceSelectedValue('newsPageForm_year');
        if (parseInt(catID, 10) > 0) { Cookies.set('categoryid', catID);} else {Cookies.remove('categoryid');}
        if (parseInt(year, 10) > 0) {Cookies.set('year', year);} else {Cookies.remove('year');}
        getInitialAjax(catID,year,0,0);
        return false;
    });

    jQuery(document).on('click', '#mfp_load_btn_<?php echo $module->id; ?>', function(e) {
        e.preventDefault();

        var start = jQuery('input[name=count_<?php echo $module->id; ?>]').val();
        var catID = getSelectedOption('#newsPageForm_category_id');
        var year = getSelectedOption('#newsPageForm_year');
        getInitialAjax(catID,year,start,1);
        return false;
    });
</script>