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
use \Joomla\CMS\Language\Text;
use Joomla\CMS\Layout\LayoutHelper;
use \Joomla\CMS\Utility\Utility;
use Joomla\CMS\Session\Session;

$app = Factory::getApplication();
$user = $app->getIdentity();

$wa = $this->document->getWebAssetManager();
$wa->useScript('keepalive')
	->useScript('form.validate')
    ->useScript('joomla.dialog-autocreate');
HTMLHelper::_('bootstrap.tooltip');

$wa->useStyle('com_adip.dropzone')
    ->useStyle('com_adip.admin')
    ->useStyle('com_adip.form')
    ->useScript('com_adip.dropzone');


$ajaxDepartmentsUri ='index.php?option=com_adip&task=reports.getDepartmentsAjax&format=json&' . Session::getFormToken() . '=1';
$ajaxProgrammesUri ='index.php?option=com_adip&task=reports.getProgramsAjax&format=json&' . Session::getFormToken() . '=1';

$ajaxFoldersUri = Uri::current().'?option=com_adip&task=reports.uploadFileAjax&format=json&' . Session::getFormToken() . '=1';
$ajaxFoldersListingUri = Uri::current().'?option=com_adip&task=reports.listingFilesAjax&format=json&' . Session::getFormToken() . '=1&docid='.$this->item->id;
$ajaxDeleteFileUri = Uri::current().'?option=com_adip&task=reports.deleteFileAjax&format=json&' . Session::getFormToken() . '=1';
$fileΕditUrl = Uri::current().'?option=com_adip&view=docfile&layout=edit&' . Session::getFormToken() . '=1';

$maxSize = number_format(Utility::getMaxUploadSize()/1048576, 2);



$icons['trash'] = '/images/admin/bin.png';
$icons['view'] = '/images/admin/view.png';
$icons['edit'] = '/images/admin/edit.png';


//dump($this->item);
//https://forum.joomla.org/viewtopic.php?t=1003378
?>


<form
	action="<?php echo Route::_('index.php?option=com_adip&layout=edit&id=' . (int) $this->item->id); ?>"
	method="post" enctype="multipart/form-data" name="adminForm" id="report-form" class="form-validate form-horizontal">

	
	<?php echo HTMLHelper::_('uitab.startTabSet', 'myTab', array('active' => 'StoixeiaEkthesis')); ?>
	<?php echo HTMLHelper::_('uitab.addTab', 'myTab', 'StoixeiaEkthesis', Text::_('COM_ADIP_TAB_STOIXEIAEKTHESIS', true)); ?>
	<div class="row-fluid">
		<div class="col-md-12 form-horizontal" >
			<fieldset class="adminform">
                <h3 class="form-title"><?php echo Text::_('COM_ADIP_REPORTS_TYPE_'.$this->item->fk_reporttype_id); ?></h3>
                <p>&nbsp;</p>
				<?php echo $this->form->renderField('fk_reporttype_id'); ?>
				<?php echo $this->form->renderField('fk_institute_id'); ?>
				<?php echo $this->form->renderField('fk_deprtement_id'); ?>
				<?php echo $this->form->renderField('fk_programme_id'); ?>
                <?php echo $this->form->renderField('report_year'); ?>
                <?php echo $this->form->renderField('academic_greek'); ?>
				<?php echo $this->form->renderField('session_num'); ?>
				<?php echo $this->form->renderField('session_date'); ?>
				<?php echo $this->form->renderField('valid_from'); ?>
				<?php echo $this->form->renderField('valid_to'); ?>
				<?php //echo $this->form->renderField('uploadfiles'); ?>
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
    <?php echo $this->form->renderField('created_by'); ?>
	<?php echo $this->form->renderField('modified_by'); ?>
	<?php echo $this->form->renderField('created'); ?>
	<?php echo $this->form->renderField('modified'); ?>
	<?php echo $this->form->renderField('dep_type_id'); ?>

    <?php echo HTMLHelper::_('uitab.addTab', 'myTab', 'attachements', Text::_('COM_ADIP_TAB_ATTACHEMENTS', true)); ?>
    <div class="row-fluid">
        <div class="span11 form-horizontal">
            <fieldset class="adminform">
                <div id="files-attachments" class="flex-container">
                    <div class="dz-default dz-message flex-item"><span>Drop files here to upload. Max upload file size: <?php echo $maxSize;?> MB</span></div>
                </div>
                <div id="files" class="files-listing">
                    <table id="files-table" class="table table-striped">
                        <thead>
                        <tr>
                            <th><?php echo Text::_('COM_ADIP_TABLE_FILES_TITLE'); ?></th>
                            <th class="center"><?php echo Text::_('COM_ADIP_TABLE_FILES_LANGUAGE'); ?></th>
                            <th><?php echo Text::_('COM_ADIP_TABLE_FILES_TYPE'); ?></th>
                            <th style="width:15%;"><?php echo Text::_('COM_ADIP_TABLE_FILES_ACTIONS'); ?></th>
                        </tr>
                        </thead>
                        <tbody>

                        </tbody>
                    </table>
                </div>

            </fieldset>
        </div>
    </div>
    <?php echo HTMLHelper::_('uitab.endTab'); ?>

	<?php echo HTMLHelper::_('uitab.endTabSet'); ?>

	<input type="hidden" name="task" value=""/>
	<?php echo HTMLHelper::_('form.token'); ?>

</form>

<script type="text/javascript">
    js = jQuery.noConflict();

    var prepareForm = function(val) {
        js("div[id^='fld']").addClass('hide');
        switch(val) {
            case "2":
                js("div[id*='_2_']").removeClass('hide');
                break;
            case "3":
                js("div[id*='_3_']").removeClass('hide');
                break;
            case "4":
                js("div[id*='_4_']").removeClass('hide');
                break;
            default:
            //Do nothing

        }
    }

    var getDepartments = function(id) {
            url = document.getElementById("departements-type-url").value+'&institutionid='+  id,
            Joomla.removeMessages(),
            js.ajax({
                method: "POST",
                url: url,
                dataType: "json"
            }).fail(function(e) {
                console.log(e);
            }).done(function(e) {
                regExp = /\[(.*?)\]/g;
                matches = e.data.match(regExp);

                clearOptionList("jform_fk_deprtement_id");
                el = document.getElementById("jform_fk_deprtement_id");
                const fancySelect = el.closest('joomla-field-fancy-select');
                m =matches[0].substring(1, matches[0].length - 1);
                arr = m.split(",");
                options = '';
                js.each(arr, function(key,value) {
                    t = value.split("|");
                    options += '<option value="' + t[0] + '">' + t[1] + '</option>';
                    const option = {};
                    option.innerText = t[1];
                    option.id = t[0];
                    fancySelect.choicesInstance.setChoices([option], 'id', 'innerText', false);
                });
                fancySelect.choicesInstance.setChoiceByValue('');
                // const newEvent = document.createEvent('HTMLEvents');
                // newEvent.initEvent('change', true, false);
                // document.getElementById('jform_fk_deprtement_id').dispatchEvent(newEvent);
                js(el).html(options);
                js(el).trigger("liszt:updated");
            })
    };

    var clearOptionList = function(obj_name) {
        el = document.getElementById(obj_name);
        js(el).empty();
        js(el).html("");
        js(el).trigger("liszt:updated");
        const fancySelect = el.closest('joomla-field-fancy-select');
        fancySelect.choicesInstance.clearChoices();
    };

    var getProgrammes = function(id) {

        val = id.split('-'),
            id = (val[0] > 0) ? val[0] : 0,
            type_id= (val[1] > 0) ? val[1] : 0,
            document.getElementById("jform_dep_type_id").value = type_id
        url = document.getElementById("programmes-type-url").value+'&department='+  id+'&type='+  type_id,
            Joomla.removeMessages(),

            js.ajax({
                method: "POST",
                url: url,
                dataType: "json"
            }).fail(function(e) {
                console.log(e);
            }).done(function(e) {
                regExp = /\[(.*?)\]/g;
                matches = e.data.match(regExp);
                clearOptionList("jform_fk_programme_id");

                el = document.getElementById("jform_fk_programme_id");
                const fancySelect = el.closest('joomla-field-fancy-select');
                m =matches[0].substring(1, matches[0].length - 1);
                arr = m.split(",");
                options = '';
                js.each(arr, function(key,value) {
                    t = value.split("|");
                    options += '<option value="' + t[0] + '">' + t[1] + '</option>';
                    const option = {};
                    option.innerText = t[1];
                    option.id = t[0];
                    fancySelect.choicesInstance.setChoices([option], 'id', 'innerText', false);
                });
                fancySelect.choicesInstance.setChoiceByValue('');
                js(el).html(options);
                js(el).trigger("liszt:updated");
            })
    };


    var showMessage = function(msg_type,msg) {
        var cls = (msg_type == 1) ? "success" : "error";
        const messages = {error: [msg]};
        console.log(messages);
        Joomla.renderMessages(messages);

        js("#alert").removeClass();
        js("#alert").show();
        js("#alert").addClass('alert '+cls);
        js("div.alert-message" ).html(msg);
    };

    var showFilesListing = function() {
        js('#files-table > tbody').html("");
        js.ajax({
            method: "POST",
            url: "<?php echo $ajaxFoldersListingUri;?>",
            dataType: "json"
        }).fail(function(e) {
            //console.log(e);
            const obj = e.responseJSON;
            const messages = {error: [obj.message,"<?php echo $ajaxFoldersListingUri;?>"]};
            Joomla.renderMessages(messages);

        }).done(function(e) {
            //const messages = {error: ["<?php //echo $ajaxFoldersListingUri;?>//"]};
            //Joomla.renderMessages(messages);
//            console.log(e.data)
            //regExp = /\[(.*?)\]/g;
            //matches = e.data.match(regExp);
             js.each(e.data, function( index, obj ) {
                 addRowFilesTable(obj);
                 //console.log(obj);
             });
        })
    };


    var addRowFilesTable = function (obj) {
        //console.log(obj.downloadlink);
        js('#files-table > tbody:last-child').append('<tr id="item-'+obj.id+'">' +
            '<td id="code-'+obj.id+'" data-value="'+obj.caption+'">'+obj.caption+'</td>' +
            '<td class="center" id="lang-'+obj.id+'">'+obj.langImage+'</td>' +
            '<td id="type-'+obj.id+'">'+obj.ftype+'</td>' +
            '<td class="center">' +
            '<ul class="actions">' +
            '<li>'+obj.downloadlink + '</li>' +
            '<li>'+obj.editlink + '</li>' +
            '<li><img src="<?php echo $icons['trash'];?>" onClick="deleteFile(\''+obj.id+'\');"/></li>' +
            '</ul>' +
            '</td>' +
            '</tr>');

    };

    js(document).ready(function () {

        js("div#files-attachments").dropzone({
            url: "<?php echo $ajaxFoldersUri;?>",
            paramName: "file",
            maxFilesize: <?php echo $maxSize; ?>,
            autoProcessQueue : true,
            thumbnailWidth: 40,
            thumbnailHeight: 40,
            uploadMultiple:false,
            parallelUploads:1,
            error: function(file, err) {
                file.previewElement.classList.add("dz-error");
                msg = file.name + ' :: '+ err;
                this.removeFile(file);
                const messages = {error: [err]};
                Joomla.renderMessages(messages);
                console.log(err);
            },
            success: function(file, responseText) {
                file.previewElement.classList.add("dz-success");
                if(typeof responseText =='object') {
                    if (responseText.success == true) {
                        const obj = responseText.data;
                        const messages = {success: [obj.message]};
                        Joomla.renderMessages(messages);

                    } else {
                        const messages = {success: [responseText.message]};
                        Joomla.renderMessages(messages);
                    }
                } else {
                    const messages = {success: [responseText]};
                    Joomla.renderMessages(messages);
                }

            },
            addRemoveLinks : false,
            acceptedFiles: "application/pdf",
            sending: function(file, xhr, formData) {
                formData.append("id", "<?php echo $this->item->id;?>");
                formData.append("user", "<?php echo $user->id;?>");
            }
        });

        js("div#files-attachments").addClass('dropzone');

        showFilesListing();



    });

    Joomla.submitbutton = function (task) {
        if (task == 'report.cancel') {
            Joomla.submitform(task, document.getElementById('report-form'));
        }
        else {

            if (task != 'report.cancel' && document.formvalidator.isValid(document.getElementById('report-form'))) {
                console.log(task);
                Joomla.submitform(task,document.getElementById('report-form'));
            }
            else {
                //alert('<?php echo $this->escape(Text::_('JGLOBAL_VALIDATION_FORM_FAILED')); ?>');
            }
        }
    }
</script>