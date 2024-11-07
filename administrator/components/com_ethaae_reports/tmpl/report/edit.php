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
use \Joomla\CMS\Language\Text;
use Joomla\CMS\Session\Session;
use \Joomla\CMS\Utility\Utility;

$app = Factory::getApplication();
$user = $app->getIdentity();

$wa = $this->document->getWebAssetManager();
$wa->useScript('keepalive')
    ->useScript('form.validate');
HTMLHelper::_('bootstrap.tooltip');

$wa->useStyle('com_ethaae_reports.dropzone')
    ->useStyle('com_ethaae_reports.admin')
    ->useStyle('com_ethaae_reports.form')
    ->useScript('com_ethaae_reports.admin')
    ->useScript('com_ethaae_reports.dialog-autocreate')
    ->useScript('com_ethaae_reports.dropzone');

$ajaxDepartmentsUri ='index.php?option=com_ethaae_reports&task=reports.getDepartmentsAjax&format=json&' . Session::getFormToken() . '=1';
$ajaxOtherUnitsUri ='index.php?option=com_ethaae_reports&task=reports.getOtherUnitsAjax&format=json&' . Session::getFormToken() . '=1';
$ajaxProgrammesUri ='index.php?option=com_ethaae_reports&task=reports.getProgramsAjax&format=json&' . Session::getFormToken() . '=1';
$UnitInfoUri = 'index.php?option=com_ethaae_units&view=institutesstructure&layout=view&tmpl=component&' . Session::getFormToken() . '=1&id='.$this->item->fk_unit_id;
$ajaxFoldersListingUri = 'index.php?option=com_ethaae_reports&task=reports.listingFilesAjax&format=json&' . Session::getFormToken() . '=1&id='.$this->item->id;
$ajaxFoldersUri = 'index.php?option=com_ethaae_reports&task=reports.uploadFileAjax&format=json&' . Session::getFormToken() . '=1';
$deleteFileUri = 'index.php?option=com_ethaae_reports&task=reports.deleteFile&' . Session::getFormToken() . '=1&cid='.$this->item->id.'&fid=';

$maxSize = number_format(Utility::getMaxUploadSize()/1048576, 2);

$class = ((int) $this->item->fk_unit_id > 0) ? 'show' : 'hide';
$popupOptions = [
    'popupType'  => 'iframe',
    'src'        => $UnitInfoUri,
];
$link = HTMLHelper::_(
    'link',
    '#',
    'Κάντε κλικ για να δείτε τα στοιχεία της επιλγμένης Δομής',
    [
        'id'                 => 'show-info',
        'class'                 => $class,
        'data-joomla-dialog'    => htmlspecialchars(json_encode($popupOptions, JSON_UNESCAPED_SLASHES), ENT_COMPAT, 'UTF-8'),
    ],
);

?>

<form
	action="<?php echo Route::_('index.php?option=com_ethaae_reports&layout=edit&id=' . (int) $this->item->id); ?>"
	method="post" enctype="multipart/form-data" name="adminForm" id="report-form" class="form-validate form-horizontal">

	
	<?php echo HTMLHelper::_('uitab.startTabSet', 'myTab', array('active' => 'StoixeiaEkthesis')); ?>
	<?php echo HTMLHelper::_('uitab.addTab', 'myTab', 'StoixeiaEkthesis', Text::_('COM_ETHAAE_REPORTS_TAB_STOIXEIAEKTHESIS', true)); ?>
	<div class="row-fluid">
		<div class="col-md-12 form-horizontal">
			<fieldset class="adminform">
				<legend><?php echo Text::_('COM_ETHAAE_REPORTS_FIELDSET_'); ?></legend>
				<?php echo $this->form->renderField('fk_reporttype_id'); ?>
                <?php echo $this->form->renderField('fk_institute_id'); ?>
                <?php echo $this->form->renderField('fk_deprtement_id'); ?>
                <?php echo $this->form->renderField('fk_other_unit_id'); ?>
                <?php echo $this->form->renderField('fk_programme_id'); ?>
				<?php echo $this->form->renderField('academic_greek'); ?>
				<?php echo $this->form->renderField('session_num'); ?>
				<?php echo $this->form->renderField('report_year'); ?>
				<?php echo $this->form->renderField('session_date'); ?>
				<?php echo $this->form->renderField('valid_from'); ?>
				<?php echo $this->form->renderField('valid_to'); ?>
				<?php echo $this->form->renderField('fk_unit_id'); ?>
                <div id="unit-more-info"><?php echo $link; ?></div>
            </fieldset>
		</div>
	</div>
    <?php echo HTMLHelper::_('uitab.endTab'); ?>
	<input type="hidden" name="jform[id]" value="<?php echo $this->item->id; ?>" />
	<input type="hidden" name="jform[ordering]" value="<?php echo $this->item->ordering; ?>" />
	<input type="hidden" name="jform[state]" value="<?php echo $this->item->state; ?>" />
	<input type="hidden" name="jform[checked_out]" value="<?php echo $this->item->checked_out; ?>" />
	<input type="hidden" name="jform[checked_out_time]" value="<?php echo $this->item->checked_out_time; ?>" />
    <input type="hidden" id="departements-type-url" data-ajaxuri="<?php echo $ajaxDepartmentsUri ;?> " value="<?php echo $ajaxDepartmentsUri ;?>" />
    <input type="hidden" id="programmes-type-url" data-ajaxuri="<?php echo $ajaxProgrammesUri ;?> " value="<?php echo $ajaxProgrammesUri ;?>" />
    <input type="hidden" id="otherunites-type-url" data-ajaxuri="<?php echo $ajaxOtherUnitsUri ;?> " value="<?php echo $ajaxOtherUnitsUri ;?>" />

    <?php echo $this->form->renderField('created_by'); ?>
	<?php echo $this->form->renderField('modified_by'); ?>
	<?php echo $this->form->renderField('created'); ?>
	<?php echo $this->form->renderField('modified'); ?>
	<input type="hidden" name="jform[params]" value="<?php echo $this->item->params; ?>" />


    <?php echo HTMLHelper::_('uitab.addTab', 'myTab', 'attachements', Text::_('COM_ETHAAE_REPORTS_TAB_ATTACHEMENTS', true)); ?>
    <div class="row-fluid">
        <div class="span11 form-horizontal">
            <fieldset class="adminform">
                <?php if($this->item->id) : ?>
                <div id="files-attachments" class="flex-container">
                    <div class="dz-default dz-message flex-item">
                        <span><?php echo Text::sprintf('COM_ETHAAE_REPORTS_FORM_DROPZONE_MSG',$maxSize);?></span>
                    </div>
                </div>
                <div id="files" class="files-listing">
                    <table id="files-table" class="table table-striped">
                        <thead>
                        <tr>
                            <th><?php echo Text::_('COM_ETHAAE_REPORTS_TABLE_FILES_TITLE'); ?></th>
                            <th class="center"><?php echo Text::_('COM_ETHAAE_REPORTS_TABLE_FILES_LANGUAGE'); ?></th>
                            <th><?php echo Text::_('COM_ETHAAE_REPORTS_TABLE_FILES_TYPE'); ?></th>
                            <th><?php echo Text::_('COM_ETHAAE_REPORTS_FILES_STATUS'); ?></th>
                            <th style="width:15%;"><?php echo Text::_('COM_ETHAAE_REPORTS_TABLE_FILES_ACTIONS'); ?></th>
                        </tr>
                        </thead>
                        <tbody>
                    </table>
                </div>
                <?php else : ?>
                    <div class="create-report-msg flex-container">
                        <div class="flex-item">
                            <span><?php echo Text::_('COM_ETHAAE_REPORTS_FORM_DROPZONE_SAVE_REPORT_FIRST');?></span>
                        </div>
                    </div>
                <?php endif; ?>


            </fieldset>
        </div>
    </div>


    <?php echo HTMLHelper::_('uitab.endTabSet'); ?>

	<input type="hidden" name="task" value=""/>
	<?php echo HTMLHelper::_('form.token'); ?>

</form>


<script type="text/javascript">
    js = jQuery.noConflict();

    var setSelectedUnit  = function (val) {
        document.getElementById("jform_fk_unit_id").value = val;
        const link = document.getElementById("show-info");
        const options = {};
        options.popupType = "iframe";
        options.src = '<?php echo $UnitInfoUri;?>'+val;
        link.setAttribute("data-joomla-dialog",JSON.stringify(options));
        if (parseInt(val) > 0) {
            link.classList.remove('hide');
            link.classList.add('show');
        } else {
            link.classList.remove('show');
            link.classList.add('hide');
        }
    }


    var deleteReportFile = function (id) {
        if(confirm('<?php echo Text::_('COM_ETHAAE_REPORTS_FILES_DELETE_FILE');?>')) {
            let url = '<?php echo $deleteFileUri; ?>' + id;
            window.location.href = url;
        }

    }

    const params = {
        id      :   "<?php echo $this->item->id;?>",
        user    :   "<?php echo $user->id;?>",
    };

    const args = {
        obj             : 'div#files-attachments',
        paramName       : 'file',
        ajaxFoldersUri  : '<?php echo $ajaxFoldersUri ?>',
        maxSize         : '<?php echo $maxSize;?>',
        userID          : '<?php echo $user->id;?>',
        itemID          : '<?php echo $this->item->id;?>',
        callbackFunc    : 'refreshFileTables',
        params          :  params,
    };

    function refreshFileTables(obj) {
        showFilesListing('<?php echo $ajaxFoldersListingUri;?>');
    }


    js(document).ready(function () {
        addDropZone(args);
        js("div#files-attachments").addClass('dropzone');
        showFilesListing('<?php echo $ajaxFoldersListingUri;?>');

    });

    Joomla.submitbutton = function (task) {
        if (task == 'report.cancel') {
            Joomla.submitform(task, document.getElementById('report-form'));
        }
        else {

            if (task != 'report.cancel' && validateForm()) {
                console.log(task);
                Joomla.submitform(task,document.getElementById('report-form'));
            }
            else {
                //alert('<?php echo $this->escape(Text::_('JGLOBAL_VALIDATION_FORM_FAILED')); ?>');
            }
        }
    }
</script>