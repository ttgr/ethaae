<?php
/**
 * @version    CVS: 1.0.2
 * @package    Com_Ethaae_qdata
 * @author     Tasos Triantis <tasos.tr@gmail.com>
 * @copyright  2025 Tasos Triantis
 * @license    GNU General Public License version 2 or later; see LICENSE.txt
 */
// No direct access
defined('_JEXEC') or die;

use \Joomla\CMS\HTML\HTMLHelper;
use \Joomla\CMS\Factory;
use \Joomla\CMS\Uri\Uri;
use \Joomla\CMS\Router\Route;
use \Joomla\CMS\Language\Text;
use \Joomla\CMS\Layout\LayoutHelper;
use \Joomla\CMS\Session\Session;
use \Joomla\CMS\User\UserFactoryInterface;

HTMLHelper::_('bootstrap.tooltip');
HTMLHelper::_('behavior.multiselect');
HTMLHelper::_('formbehavior.chosen', 'select');

$user       = Factory::getApplication()->getIdentity();
$userId     = $user->get('id');
$listOrder  = $this->state->get('list.ordering');
$listDirn   = $this->state->get('list.direction');


// Import CSS
$wa = $this->document->getWebAssetManager();
$wa->useStyle('com_ethaae_qdata.list')
    ->useScript('com_ethaae_qdata.list');

$annotations = array();
$hasZeroValue = false;
?>

<?php if ($this->params->get('show_page_heading')) : ?>
    <div class="page-header">
        <h1> <?php echo $this->escape($this->params->get('page_heading')); ?> </h1>
    </div>
<?php endif;?>
<form action="<?php echo htmlspecialchars(Uri::getInstance()->toString()); ?>" method="post"
	  name="adminForm" id="adminForm">
    <div class="page-filter">
        <?php echo LayoutHelper::render('default_filter', array('view' => $this), dirname(__FILE__)); ?>
    </div>

    <?php if (isset($this->topItem)) : ?>
    <div class="page-top-item">
        <?php echo LayoutHelper::render('default_topitem', array('view' => $this->topItem), dirname(__FILE__)); ?>
    </div>

    <?php endif; ?>

    <?php if ($this->pagination->total >0 ) : ?>
    <?php $totalsTxt = (isset($this->topItem)) ? 'COM_ETHAAE_FORM_LBL_REPORT_TOTAL_RECORDS_2': 'COM_ETHAAE_FORM_LBL_REPORT_TOTAL_RECORDS_1'; ?>
        <div class="table-total-rows"> <?php echo Text::sprintf($totalsTxt,$this->pagination->total); ?></div>
    <?php endif; ?>
    <div id="annotation-top" class="annotation"></div>
    <div class="rowstats">
			<?php foreach ($this->items as $i => $item) : ?>
            <div class="stats-item row" data-id="<?php echo $item->unitid; ?>">
                <div class="col-md-5">
                    <div class="stats-item-year">
                        <?php echo $item->academic_reference_year; ?>
                    </div>
                    <div class="stats-item-title">
                        <?php echo $item->short_code.' :: '.$item->unit_title ; ?>
                    </div>
                    <?php if (!empty($item->startupdate)) : ?>
                        <span class="stats-item-date">
                            <?php echo Text::sprintf('COM_ETHAAE_QDATA_UNIT_START_DATE',HTMLHelper::_('date', $item->startupdate, 'd/m/Y')); ?>
                        </span>
                    <?php endif; ?>
                    <?php if (!empty($item->ceasedate)) : ?>
                        <span class="stats-item-date">
                            <?php echo Text::sprintf('COM_ETHAAE_QDATA_UNIT_CEASE_DATE',HTMLHelper::_('date', $item->ceasedate, 'd/m/Y')); ?>
                        </span>
                    <?php endif; ?>
                    <div class="clearfix space-bottom-2x"></div>
                </div>
                <div class="col-md-7">
                    <div class="stats-variables row">
                    <?php foreach ($item->variables as $variable) : ?>
                    <?php $annotations[$variable->metricid] = array('title'=>$variable->title,'description'=>$variable->description); ?>
                    <?php if ($variable->value == 0) $hasZeroValue = true; ?>

                        <div class="stats-item-variable col-md-6">
                            <div class="stats-item-variable-title"><?php echo $variable->title ?></div>
                            <div class="stats-item-variable-value"><?php echo ($variable->value == 0)? " -- " : number_format($variable->value, 0, ',', '.'); ?></div>
                            <div id="variable-id-<?php echo $variable->id;?>" role="tooltip"><?php echo $variable->description; ?></div>
                        </div>
                    <?php endforeach; ?>
                    </div>
                </div>
            </div>
			<?php endforeach; ?>
        <div class="pagination">
            <?php echo $this->pagination->getPagesLinks(); ?>
        </div>

    </div>
	<input type="hidden" name="task" value=""/>
	<input type="hidden" name="boxchecked" value="0"/>
	<input type="hidden" name="filter_order" value=""/>
	<input type="hidden" name="filter_order_Dir" value=""/>
	<input type="hidden" name="limitstart" value=""/>
	<?php echo HTMLHelper::_('form.token'); ?>
</form>

<div id="annotation-bottom" class="annotation">
    <ul>
    <?php foreach ($annotations as $annotation) : ?>
     <li><b><?php echo $annotation['title'] ;?></b> : <?php echo $annotation['description'] ;?></li>
    <?php endforeach; ?>
        <?php if ($hasZeroValue) : ?>
            <li><?php echo Text::_('COM_ETHAAE_QDATA_VARIABLE_ZERO_DESC');?></li>
        <?php endif; ?>
        <li><?php echo Text::_('COM_ETHAAE_QDATA_VARIABLE_OLD_ATEI');?></li>
    </ul>
</div>


<script>
    js = jQuery.noConflict();
    js(document).ready(function () {
        js('#annotation-top').html(js('#annotation-bottom').html());

        // Set the selected value to current Year
        <?php if(isset($this->defaultCollectionyear)) : ?>
            js('#filter_collectionyear').val('<?php echo $this->defaultCollectionyear;?>').trigger("chosen:updated").trigger("liszt:updated.chosen");
        <?php endif; ?>
    });
</script>

