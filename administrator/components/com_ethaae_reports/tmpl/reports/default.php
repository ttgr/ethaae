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
use \Joomla\CMS\Router\Route;
use \Joomla\CMS\Layout\LayoutHelper;
use \Joomla\CMS\Language\Text;
use Joomla\CMS\Session\Session;
use \Ethaaereports\Component\Ethaae_reports\Administrator\Helper\Ethaae_reportsHelper;

HTMLHelper::_('bootstrap.tooltip');
HTMLHelper::_('behavior.multiselect');

// Import CSS
$wa =  $this->document->getWebAssetManager();
$wa->useStyle('com_ethaae_reports.admin')
    ->useScript('com_ethaae_reports.admin');

$wa->useScript('keepalive')
    ->useScript('joomla.dialog-autocreate')
    ->useScript('joomla.dialog');


$user      = Factory::getApplication()->getIdentity();
$userId    = $user->get('id');
$listOrder = $this->state->get('list.ordering');
$listDirn  = $this->state->get('list.direction');
$canOrder  = $user->authorise('core.edit.state', 'com_ethaae_reports');

$saveOrder = $listOrder == 'a.ordering';

if (!empty($saveOrder))
{
	$saveOrderingUrl = 'index.php?option=com_ethaae_reports&task=reports.saveOrderAjax&tmpl=component&' . Session::getFormToken() . '=1';
	HTMLHelper::_('draggablelist.draggable');
}

?>

<form action="<?php echo Route::_('index.php?option=com_ethaae_reports&view=reports'); ?>" method="post"
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

						
					<th  scope="col" class="w-1 text-center">
						<?php echo Text::_( 'JSTATUS'); ?>
					</th>
						
						<th class='left'>
							<?php echo Text::_( 'COM_ETHAAE_REPORTS_REPORTS_TITLE'); ?>
						</th>
						<th class='left'>
							<?php echo Text::_( 'COM_ETHAAE_REPORTS_FORM_LBL_REPORT_FK_INSTITUTE_ID');  ?>
						</th>
						<th class='left'>
							<?php echo Text::_( 'COM_ETHAAE_REPORTS_REPORTS_ACADEMIC_GREEK');?>
						</th>
						<th class='left'>
							<?php echo Text::_( 'COM_ETHAAE_REPORTS_REPORTS_SESSION_NUM');?>
						</th>
						<th class='left'>
							<?php echo Text::_( 'COM_ETHAAE_REPORTS_REPORTS_REPORT_YEAR');?>
						</th>
						<th class='left'>
							<?php echo Text::_( 'COM_ETHAAE_REPORTS_REPORTS_SESSION_DATE');?>
						</th>
						<th class='left'>
							<?php echo Text::_( 'COM_ETHAAE_REPORTS_REPORTS_VALID_FROM');?>
						</th>
						<th class='left'>
							<?php echo Text::_( 'COM_ETHAAE_REPORTS_REPORTS_VALID_TO');?>
						</th>
						<th class='left'>
							<?php echo Text::_( 'COM_ETHAAE_REPORTS_REPORTS_FK_REPORTTYPE_ID');?>
						</th>
                        <th class='left'>
                            <?php echo Text::_('COM_ETHAAE_REPORTS_REPORTTYPES_FILES'); ?>
                        </th>


                        <th scope="col" class="w-3 d-none d-lg-table-cell" >

						<?php echo HTMLHelper::_('searchtools.sort',  'JGRID_HEADING_ID', 'a.id', $listDirn, $listOrder); ?>					</th>
					</tr>
					</thead>
					<tfoot>
					<tr>
						<td colspan="<?php echo isset($this->items[0]) ? count(get_object_vars($this->items[0])) : 10; ?>">
							<?php echo $this->pagination->getListFooter(); ?>
						</td>
					</tr>
					</tfoot>
					<tbody>
					<?php foreach ($this->items as $i => $item) :
						$ordering   = ($listOrder == 'a.ordering');
						$canCreate  = $user->authorise('core.create', 'com_ethaae_reports');
						$canEdit    = $user->authorise('core.edit', 'com_ethaae_reports');
						$canCheckin = $user->authorise('core.manage', 'com_ethaae_reports');
						$canChange  = $user->authorise('core.edit.state', 'com_ethaae_reports');

                        $showFiles  = isset($item->files) && count($item->files) > 0;
                        $onClick = ($showFiles) ? 'onclick="toggleMembers('.$item->id.');"':"";

                        ?>
						<tr class="row<?php echo $i % 2; ?>">
							<td class="text-center">
								<?php echo HTMLHelper::_('grid.id', $i, $item->id); ?>
							</td>

							<td class="text-center">
								<?php echo HTMLHelper::_('jgrid.published', $item->state, $i, 'reports.', $canChange, 'cb'); ?>
							</td>
							
							<td>
                                <?php if ($canEdit) : ?>
                                    <a href="<?php echo Route::_('index.php?option=com_ethaae_reports&task=report.edit&id='.(int) $item->id); ?>">
                                        <?php echo $this->escape($item->title); ?>
                                    </a>
                                <?php else : ?>
                                    <?php echo $this->escape($item->title); ?>
                                <?php endif; ?>

                                <?php
                                $popupOptions = [
                                    'popupType'  => 'iframe',
                                    'src'        => 'index.php?option=com_ethaae_units&view=institutesstructure&layout=view&' . Session::getFormToken() . '=1&id='.$item->fk_unit_id.'&tmpl=component',
                                ];
                                $link = HTMLHelper::_(
                                    'link',
                                    '#',
                                    HTMLHelper::_('image', 'com_adip/view.png', null, null, true),
                                    [
                                        'class'                 => 'alert-link',
                                        'data-joomla-dialog'    => htmlspecialchars(json_encode($popupOptions, JSON_UNESCAPED_SLASHES), ENT_COMPAT, 'UTF-8'),
                                    ],
                                );
                                    echo $link;
                                ?>
							</td>
                            <td>
                                <?php echo $item->fk_institute; ?>
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
                                    echo Ethaae_reportsHelper::getDateGRFormat($item->session_date);
								?>
							</td>
							<td>
								<?php
                                    echo Ethaae_reportsHelper::getDateGRFormat($item->valid_from);
								?>
							</td>
							<td>
								<?php
									//$date = $item->valid_to;
									//echo $date > 0 ? HTMLHelper::_('date', $date, Text::_('DATE_FORMAT_GREEK')) : '-';
                                    echo Ethaae_reportsHelper::getDateGRFormat($item->valid_to);
								?>
							</td>
							<td>
								<?php echo $item->fk_reporttype; ?>
							</td>
                            <td>
                                <?php if ($showFiles) : ?>
                                    <div <?php echo $onClick; ?> class="btn btn-small button-new btn-success"><?php echo Text::_('COM_ETHAAE_REPORTS_REPORTTYPES_FILES_SHOW'); ?></div>
                                <?php else: ?>
                                    <?php echo Text::_('COM_ETHAAE_REPORTS_REPORTTYPES_NO_FILES'); ?>
                                <?php endif; ?>
                            </td>

                            <td class="d-none d-lg-table-cell">
							    <?php echo $item->id; ?>
							</td>
						</tr>
                        <?php if ($showFiles) : ?>
                        <tr id="files_<?php echo $item->id; ?>" class="files">
                            <td colspan="20" >
                                <?php include 'files.php'; ?>
                            </td>
                        </tr>
                    <?php endif; ?>


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
        js(el).toggleClass( "open" );
        // js(el).slideToggle( "slow", function() {
        //     js(el).toggleClass( "open" );
        // });
    }

</script>