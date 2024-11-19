<?php
/**
 * @version    CVS: 1.1.0
 * @package    Com_Ethaae_reports
 * @author     Tasos Triantis <tasos.tr@gmail.com>
 * @copyright  2024 Tasos Triantis
 * @license    GNU General Public License version 2 or later; see LICENSE.txt
 */
// No direct access
defined('_JEXEC') or die;

use \Joomla\CMS\HTML\HTMLHelper;
use \Joomla\CMS\Factory;
use \Joomla\CMS\Uri\Uri;
use \Joomla\CMS\Language\Text;
use \Joomla\CMS\Layout\LayoutHelper;
use \Ethaaereports\Component\Ethaae_reports\Site\Helper\Ethaae_reportsHelper;


HTMLHelper::_('bootstrap.tooltip');
HTMLHelper::_('behavior.multiselect');
HTMLHelper::_('formbehavior.chosen', 'select');

$user       = Factory::getApplication()->getIdentity();
$userId     = $user->get('id');
$listOrder  = $this->state->get('list.ordering');
$listDirn   = $this->state->get('list.direction');
$canCreate  = $user->authorise('core.create', 'com_ethaae_reports') && file_exists(JPATH_COMPONENT . DIRECTORY_SEPARATOR . 'forms' . DIRECTORY_SEPARATOR . 'reportform.xml');
$canEdit    = $user->authorise('core.edit', 'com_ethaae_reports') && file_exists(JPATH_COMPONENT .  DIRECTORY_SEPARATOR . 'forms' . DIRECTORY_SEPARATOR . 'reportform.xml');
$canCheckin = $user->authorise('core.manage', 'com_ethaae_reports');
$canChange  = $user->authorise('core.edit.state', 'com_ethaae_reports');
$canDelete  = $user->authorise('core.delete', 'com_ethaae_reports');

// Import CSS
$wa = $this->document->getWebAssetManager();
$wa->useStyle('com_ethaae_reports.list')
    ->useScript('com_ethaae_reports.list');


$this->filterForm->setFieldAttribute('filter_fk_institute_id', 'query',Text::_('COM_ETHAAE_REPORTS_REPORTS_FK_UNIT_ID_QUERY_4'));
$institute = (int) $this->getState('filter.fk_institute_id' ,0);
if ($institute > 0) {
    $this->filterForm->setFieldAttribute('filter_fk_dept_id', 'query',Text::sprintf('COM_ETHAAE_REPORTS_REPORTS_FK_UNIT_ID_QUERY_4_1',$institute));
}
//$db = Factory::getContainer()->get('DatabaseDriver');
//Factory::getApplication()->enqueueMessage($db->replacePrefix((string) Text::_('COM_ETHAAE_REPORTS_REPORTS_FK_UNIT_ID_QUERY_4')), 'notice');

//Get All Filters
$filters = $this->filterForm->getGroup('');

//Get Hidden Filters
$hiddenFilters = array();
foreach ($filters as $fieldName => $field) {
    if ($field->hidden) {
        $hiddenFilters[] =$field;
    }
}

//Assign Default Values
if ($filters !== false) {
    $filterFields = array_keys($filters);
    foreach ($filterFields as $filterField) {
        $val    = $this->getState('filter.' . substr($filterField, 7),0);
        if ($val >0)  $this->filterForm->setFieldAttribute($filterField, 'default',$val);
        $val    = $this->getState('list.' . substr($filterField, 5),0);
        if ($val >0)  $this->filterForm->setFieldAttribute($filterField, 'default',$val);
    }
}

$langSEF = Ethaae_reportsHelper::getCurrentLangSef();

?>

<form action="<?php echo htmlspecialchars(Uri::getInstance()->toString()); ?>" method="post"
      name="adminForm" id="adminForm">
    <div class="page-filter">
        <?php echo LayoutHelper::render('default_filter', array('view' => $this), dirname(__FILE__)); ?>
    </div>
    <?php if ($this->pagination->total >0 ) : ?>
    <div class="table-total-rows"> <?php echo Text::sprintf('COM_ETHAAE_FORM_LBL_REPORT_TOTAL_RECORDS',$this->pagination->total); ?></div>
    <?php endif; ?>
    <div class="table-responsive">
        <table class="table table-striped" id="reportList">
            <thead>
            <tr>


                <th class='' scope="col">
                    <?php echo HTMLHelper::_('grid.sort',  'COM_ETHAAE_REPORTS_REPORTS_INSTITUTE', 'institute_title_'.$langSEF, $listDirn, $listOrder); ?>
                </th>
                <th class='' scope="col">
                    <?php echo HTMLHelper::_('grid.sort',  'COM_ETHAAE_REPORTS_REPORTS_FK_DEPT_ID_LBL', 'parent_title_'.$langSEF, $listDirn, $listOrder); ?>
                </th>
                <th class='' scope="col">
                    <?php echo HTMLHelper::_('grid.sort',  'COM_ETHAAE_REPORTS_REPORTS_FK_UNIT_ID_'.$this->report_id, 'unit_title_'.$langSEF, $listDirn, $listOrder); ?>
                </th>


                <th class='' scope="col">
                    <?php echo HTMLHelper::_('grid.sort',  'COM_ETHAAE_REPORTS_REPORTS_REPORT_YEAR', 'a.report_year', $listDirn, $listOrder); ?>
                </th>

                <th class='files' scope="col">
                    <a href="#"><?php echo Text::_('COM_ETHAAE_REPORTS_REPORTS_FK_REPORTTYPE_ID'); ?></a>
                </th>
            </tr>
            </thead>
            <tfoot>
            <tr>
                <td colspan="<?php echo isset($this->items[0]) ? count(get_object_vars($this->items[0])) : 10; ?>">
                    <div class="pagination">
                        <?php echo $this->pagination->getPagesLinks(); ?>
                    </div>
                </td>
            </tr>
            </tfoot>
            <tbody>
            <?php foreach ($this->items as $i => $item) : ?>

                <tr class="row<?php echo $i % 2; ?>">

                    <td scope="row" data-label="<?php echo Text::_('COM_ETHAAE_REPORTS_REPORTS_FK_UNIT_ID_'.$this->report_id); ?>">
                        <div class="row-content">
                            <?php echo $item->{'institute_title_'.$langSEF}; ?>
                        </div>
                    </td>
                    <td scope="row" data-label="<?php echo Text::_('COM_ETHAAE_REPORTS_REPORTS_FK_DEPT_ID_LBL'); ?>">
                        <div class="row-content">
                            <?php echo $item->{'parent_title_'.$langSEF}; ?>
                        </div>
                    </td>
                    <td scope="row" data-label="<?php echo Text::_('COM_ETHAAE_REPORTS_REPORTS_FK_UNIT_ID_'.$this->report_id); ?>">
                        <div class="row-content">
                            <?php echo $item->{'unit_short_code_'.$langSEF}.": ".$item->{'unit_title_'.$langSEF}; ?>
                        </div>
                    </td>
                    <td scope="row" data-label="<?php echo Text::_('COM_ETHAAE_REPORTS_REPORTS_REPORT_YEAR'); ?>">
                        <div class="row-content">
                            <?php echo $item->report_year; ?>
                        </div>
                    </td>
                    <td scope="row" data-label="<?php echo Text::_('COM_ETHAAE_REPORTS_REPORTS_FK_REPORTTYPE_ID'); ?>">
                        <div class="row-content">
                            <?php if (isset($item->files) && is_array($item->files) && count($item->files) > 0) : ?>
                                <?php echo LayoutHelper::render('default_files', array('view' => $item->files), dirname(__FILE__)); ?>
                            <?php endif; ?>
                        </div>
                    </td>
                </tr>
            <?php endforeach; ?>
            </tbody>
        </table>
    </div>

    <input type="hidden" name="task" value=""/>
    <input type="hidden" name="boxchecked" value="0"/>
    <input type="hidden" name="filter_order" value=""/>
    <input type="hidden" name="filter_order_Dir" value=""/>
    <?php foreach ($hiddenFilters as $field) : ?>
        <input type="hidden" name="<?php echo $field->id;?>" value="<?php echo $field->value;?>"/>
    <?php endforeach; ?>
    <?php echo HTMLHelper::_('form.token'); ?>
</form>
