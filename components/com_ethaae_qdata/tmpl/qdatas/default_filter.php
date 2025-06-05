<?php

/**
 * @version    CVS: 1.0.4
 * @package    Com_Adip
 * @author     Tasos Triantis <tasos.tr@gmail.com>
 * @copyright  2019 Tasos Triantis
 * @license    GNU General Public License version 2 or later; see LICENSE.txt
 */

defined('JPATH_BASE') or die;

use \Joomla\CMS\HTML\HTMLHelper;
use \Joomla\CMS\Factory;
use \Joomla\CMS\Language\Text;

$data = $displayData;


// Receive overridable options
$data['options'] = !empty($data['options']) ? $data['options'] : array();

// Check if any filter field has been filled
$filters       = false;
$filtered      = false;
$search_filter = false;

if (isset($data['view']->filterForm))
{
	$fields = $data['view']->filterForm->getGroup('');
}


$filters = array();
$lists = array();

foreach ($fields as $fieldName => $field) {
    if (strrpos($fieldName, "filter_",0) !== false && $field->hidden == false) {
        $filters[] = $field;
    }
    if (strrpos($fieldName, "list_") !== false && $field->hidden == false) {
        $lists[] = $field;
    }
}


    $options = $data['options'];
$options['filtersHidden'] = false;

// Set some basic options
$customOptions = array(
	'filtersHidden'       => isset($options['filtersHidden']) ? $options['filtersHidden'] : empty($data['view']->activeFilters) && !$filtered,
	'defaultLimit'        => isset($options['defaultLimit']) ? $options['defaultLimit'] : Factory::getApplication()->get('list_limit', 20),
	'searchFieldSelector' => '#filter_search',
	'orderFieldSelector'  => '#list_fullordering'
);

$data['options'] = array_unique(array_merge($customOptions, $data['options']));

$formSelector = !empty($data['options']['formSelector']) ? $data['options']['formSelector'] : '#adminForm';

// Load search tools
HTMLHelper::_('searchtools.form', $formSelector, $data['options']);
?>

<div class="js-stools clearfix">

	<!-- Filters div -->
	<div class="container-filters">
		<?php // Load the form filters ?>
		<?php if ($filters) : ?>
            <div class="filter-group filters-item">
			<?php foreach ($filters as $fieldName => $field) : ?>
					<div class="filter-item">
                        <?php echo $field->label; ?>
						<?php echo $field->renderField(array('hiddenLabel' => true,'class'=>'width-35')); ?>
					</div>
			<?php endforeach; ?>
            </div>
		<?php endif; ?>

		<?php if ($lists) : ?>
            <div class="filter-group filters-item">
			<?php foreach ($lists as $fieldName => $field) : ?>
					<div class="filter-item">
                        <?php echo $field->label; ?>
						<?php echo $field->renderField(array('hiddenLabel' => true,'class'=>'width-35')); ?>
					</div>
			<?php endforeach; ?>
            </div>
		<?php endif; ?>
	</div>
    <div class="submit-btn clearfix js-stools-container-bar frontpage">
        <div class="btn-wrapper">
            <button type="submit" class="btn submit hasTooltip" title=""
                    data-original-title="<?php echo Text::_('COM_ETHAAE_SEARCH_FILTER_SUBMIT'); ?>">
                <?php echo Text::_('COM_ETHAAE_SEARCH_FILTER_SUBMIT'); ?>
            </button>
        </div>

        <div class="btn-wrapper">
            <button type="button" class="btn hasTooltip" title=""
                    data-original-title="<?php echo Text::_('COM_ETHAAE_FILTER_CLEAR'); ?>"
                    onclick="clearOptions(this);">
                <?php echo Text::_('COM_ETHAAE_FILTER_CLEAR'); ?>
            </button>
        </div>
    </div>

</div>