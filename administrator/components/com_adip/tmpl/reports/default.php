<?php
/**
 * @version    CVS: 2.1.0
 * @package    Com_Adip
 * @author     Tasos Triantis <tasos.tr@gmail.com>
 * @copyright  2024 Tasos Triantis
 * @license    GNU General Public License version 2 or later; see LICENSE.txt
 */

// No direct access
defined('_JEXEC') or die;


use \Joomla\CMS\HTML\HTMLHelper;
use \Joomla\CMS\Factory;
use \Joomla\CMS\Uri\Uri;
use \Joomla\CMS\Router\Route;
use \Joomla\CMS\Layout\LayoutHelper;
use \Joomla\CMS\Language\Text;
use Joomla\CMS\Session\Session;

HTMLHelper::_('bootstrap.tooltip');
HTMLHelper::_('behavior.multiselect');

// Import CSS
$wa =  $this->document->getWebAssetManager();
$wa->useStyle('com_adip.admin')
    ->useScript('com_adip.admin');

$user      = Factory::getApplication()->getIdentity();
$userId    = $user->get('id');
$listOrder = $this->state->get('list.ordering');
$listDirn  = $this->state->get('list.direction');
$canOrder  = $user->authorise('core.edit.state', 'com_adip');

$session = Factory::getApplication()->getSession();
$sid = md5($session->getId());


$saveOrder = $listOrder == 'a.ordering';

if (!empty($saveOrder))
{
	$saveOrderingUrl = 'index.php?option=com_adip&task=reports.saveOrderAjax&tmpl=component&' . Session::getFormToken() . '=1';
	HTMLHelper::_('draggablelist.draggable');
}

?>

<form action="<?php echo Route::_('index.php?option=com_adip&view=reports'); ?>" method="post"
	  name="adminForm" id="adminForm">
	<div class="row">
		<div class="col-md-12">
			<div id="j-main-container" class="j-main-container">
			<?php echo LayoutHelper::render('joomla.searchtools.default', array('view' => $this)); ?>

				<div class="clearfix"></div>
				<table class="table table-striped" id="reportList">
					<thead>
					<tr>
						<th class="w-1 text-center">
							<input type="checkbox" autocomplete="off" class="form-check-input" name="checkall-toggle" value=""
								   title="<?php echo Text::_('JGLOBAL_CHECK_ALL'); ?>" onclick="Joomla.checkAll(this)"/>
						</th>
						
					<?php if (isset($this->items[0]->ordering)): ?>
					<th scope="col" class="w-1 text-center d-none d-md-table-cell">

					<?php echo HTMLHelper::_('searchtools.sort', '', 'a.ordering', $listDirn, $listOrder, null, 'asc', 'JGRID_HEADING_ORDERING', 'icon-menu-2'); ?>

					</th>
					<?php endif; ?>

						
					<th  scope="col" class="w-1 text-center">
						<?php echo HTMLHelper::_('searchtools.sort', 'JSTATUS', 'a.state', $listDirn, $listOrder); ?>
					</th>
                        <th class='left'>
                            <?php echo HTMLHelper::_('searchtools.sort',  'COM_ADIP_REPORTS_FK_REPORTTYPE_ID', 'a.fk_reporttype_id', $listDirn, $listOrder); ?>
                        </th>

                        <th class='left'>
							<?php echo HTMLHelper::_('searchtools.sort',  'COM_ADIP_REPORTS_FK_INSTITUTE_ID', 'a.fk_institute_id', $listDirn, $listOrder); ?>
						</th>
						<th class='left'>
							<?php echo HTMLHelper::_('searchtools.sort',  'COM_ADIP_REPORTS_FK_DEPRTEMENT_ID', 'a.fk_deprtement_id', $listDirn, $listOrder); ?>
						</th>
						<th class='left'>
							<?php echo HTMLHelper::_('searchtools.sort',  'COM_ADIP_REPORTS_FK_PROGRAMME_ID', 'a.fk_programme_id', $listDirn, $listOrder); ?>
						</th>
						<th class='left'>
							<?php echo HTMLHelper::_('searchtools.sort',  'COM_ADIP_REPORTS_ACADEMIC_GREEK', 'a.academic_greek', $listDirn, $listOrder); ?>
						</th>
						<th class='left'>
							<?php echo HTMLHelper::_('searchtools.sort',  'COM_ADIP_REPORTS_SESSION_NUM', 'a.session_num', $listDirn, $listOrder); ?>
						</th>
						<th class='left'>
							<?php echo HTMLHelper::_('searchtools.sort',  'COM_ADIP_REPORTS_REPORT_YEAR', 'a.report_year', $listDirn, $listOrder); ?>
						</th>
						<th class='left'>
							<?php echo HTMLHelper::_('searchtools.sort',  'COM_ADIP_REPORTS_SESSION_DATE', 'a.session_date', $listDirn, $listOrder); ?>
						</th>
						<th class='left'>
							<?php echo HTMLHelper::_('searchtools.sort',  'COM_ADIP_REPORTS_VALID_FROM', 'a.valid_from', $listDirn, $listOrder); ?>
						</th>
						<th class='left'>
							<?php echo HTMLHelper::_('searchtools.sort',  'COM_ADIP_REPORTS_VALID_TO', 'a.valid_to', $listDirn, $listOrder); ?>
						</th>
					<th scope="col" class="w-3 d-none d-lg-table-cell" >

						<?php echo HTMLHelper::_('searchtools.sort',  'JGRID_HEADING_ID', 'a.id', $listDirn, $listOrder); ?>
                    </th>
                        <th class='left'>
                            <?php echo Text::_('COM_ADIP_REPORTTYPES_FILES'); ?>
                        </th>
					</tr>

					</thead>
					<tfoot>
					<tr>
						<td colspan="<?php echo isset($this->items[0]) ? count(get_object_vars($this->items[0])) : 10; ?>">
							<?php echo $this->pagination->getListFooter(); ?>
						</td>
					</tr>
					</tfoot>
					<tbody <?php if (!empty($saveOrder)) :?> class="js-draggable" data-url="<?php echo $saveOrderingUrl; ?>" data-direction="<?php echo strtolower($listDirn); ?>" <?php endif; ?>>
					<?php foreach ($this->items as $i => $item) :
						$ordering   = ($listOrder == 'a.ordering');
						$canCreate  = $user->authorise('core.create', 'com_adip');
						$canEdit    = $user->authorise('core.edit', 'com_adip');
						$canCheckin = $user->authorise('core.manage', 'com_adip');
						$canChange  = $user->authorise('core.edit.state', 'com_adip');

                        $showFiles  = isset($item->files) && count($item->files) > 0;
                        $onClick = ($showFiles) ? 'onclick="toggleMembers('.$item->id.');"':"";

                        ?>
						<tr class="row<?php echo $i % 2; ?>" data-draggable-group='1' data-transition>
							<td class="text-center">
								<?php echo HTMLHelper::_('grid.id', $i, $item->id); ?>
							</td>
							
							<?php if (isset($this->items[0]->ordering)) : ?>

							<td class="text-center d-none d-md-table-cell">

							<?php

							$iconClass = '';

							if (!$canChange)

							{
								$iconClass = ' inactive';

							}
							elseif (!$saveOrder)

							{
								$iconClass = ' inactive" title="' . Text::_('JORDERINGDISABLED');

							}							?>							<span class="sortable-handler<?php echo $iconClass ?>">
							<span class="icon-ellipsis-v" aria-hidden="true"></span>
							</span>
							<?php if ($canChange && $saveOrder) : ?>
							<input type="text" name="order[]" size="5" value="<?php echo $item->ordering; ?>" class="width-20 text-area-order hidden">
								<?php endif; ?>
							</td>
							<?php endif; ?>

							
							<td class="text-center">
								<?php echo HTMLHelper::_('jgrid.published', $item->state, $i, 'reports.', $canChange, 'cb'); ?>
							</td>
                            <td>
                                <?php echo $item->fk_reporttype_id; ?>
                            </td>

							<td>
								<?php if (isset($item->checked_out) && $item->checked_out && ($canEdit || $canChange)) : ?>
									<?php echo HTMLHelper::_('jgrid.checkedout', $i, $item->uEditor, $item->checked_out_time, 'reports.', $canCheckin); ?>
								<?php endif; ?>
								<?php if ($canEdit) : ?>
									<a href="<?php echo Route::_('index.php?option=com_adip&task=report.edit&id='.(int) $item->id); ?>">
									<?php echo $this->escape($item->fk_institute_id); ?>
									</a>
								<?php else : ?>
												<?php echo $this->escape($item->fk_institute_id); ?>
								<?php endif; ?>
							</td>
							<td>
								<?php echo $item->fk_deprtement_id; ?>
							</td>
							<td>
								<?php echo $item->fk_programme_id; ?>
							</td>
							<td>
								<?php echo $item->academic_greek; ?>
							</td>
							<td>
								<?php echo $item->session_num; ?>
							</td>
							<td>
								<?php echo $item->report_year; ?>
							</td>
							<td>
								<?php
									$date = $item->session_date;
									echo $date > 0 ? HTMLHelper::_('date', $date, Text::_('DATE_FORMAT_LC4')) : '-';
								?>
							</td>
							<td>
								<?php
									$date = $item->valid_from;
									echo $date > 0 ? HTMLHelper::_('date', $date, Text::_('DATE_FORMAT_LC4')) : '-';
								?>
							</td>
							<td>
								<?php
									$date = $item->valid_to;
									echo $date > 0 ? HTMLHelper::_('date', $date, Text::_('DATE_FORMAT_LC4')) : '-';
								?>
							</td>
							<td class="d-none d-lg-table-cell">
							<?php echo $item->id; ?>

							</td>
                            <td>
                                <?php if ($showFiles) : ?>
                                    <div <?php echo $onClick; ?> class="btn btn-small button-new btn-success"><?php echo Text::_('COM_ADIP_REPORTTYPES_FILES_SHOW'); ?></div>
                                <?php else: ?>
                                    <?php echo Text::_('COM_ADIP_REPORTTYPES_NO_FILES'); ?>
                                <?php endif; ?>
                            </td>
                        </tr>
                        <?php if ($showFiles) : ?>
                        <tr id="files_<?php echo $item->id; ?>" class="delegates" style="display: none;">
                            <td colspan="20" class="delegates">
                                <?php include 'files.php'; ?>
                            </td>
                        </tr>
                    <?php endif; ?>


						</tr>
					<?php endforeach; ?>
					</tbody>
				</table>

				<input type="hidden" name="task" value=""/>
				<input type="hidden" name="boxchecked" value="0"/>
				<input type="hidden" name="list[fullorder]" value="<?php echo $listOrder; ?> <?php echo $listDirn; ?>"/>
				<?php echo HTMLHelper::_('form.token'); ?>
			</div>
		</div>
	</div>
</form>

<script type="text/javascript">
    js = jQuery.noConflict();

    function toggleMembers(id) {
        el = document.getElementById("files_"+id);
        js(el).slideToggle( "slow", function() {
            // Animation complete.
        });
    }

</script>