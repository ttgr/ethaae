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

$app = Factory::getApplication();
$user = $app->getIdentity();

$wa = $this->document->getWebAssetManager();
$wa->useScript('keepalive')
    ->useScript('form.validate')
    ->useScript('joomla.dialog-autocreate');
HTMLHelper::_('bootstrap.tooltip');

$wa->useStyle('com_ethaae_reports.dropzone')
    ->useStyle('com_ethaae_reports.admin')
    ->useStyle('com_ethaae_reports.form')
    ->useScript('com_ethaae_reports.admin')
    ->useScript('com_ethaae_reports.dropzone');

$ajaxDepartmentsUri ='index.php?option=com_ethaae_reports&task=reports.getDepartmentsAjax&format=json&' . Session::getFormToken() . '=1';
$ajaxProgrammesUri ='index.php?option=com_ethaae_reports&task=reports.getProgramsAjax&format=json&' . Session::getFormToken() . '=1';



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
                <?php echo $this->form->renderField('fk_programme_id'); ?>
				<?php echo $this->form->renderField('academic_greek'); ?>
				<?php echo $this->form->renderField('session_num'); ?>
				<?php echo $this->form->renderField('report_year'); ?>
				<?php echo $this->form->renderField('session_date'); ?>
				<?php echo $this->form->renderField('valid_from'); ?>
				<?php echo $this->form->renderField('valid_to'); ?>
				<?php echo $this->form->renderField('title'); ?>
				<?php echo $this->form->renderField('fk_unit_id'); ?>
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
	<input type="hidden" name="jform[params]" value="<?php echo $this->item->params; ?>" />

	
	<?php echo HTMLHelper::_('uitab.endTabSet'); ?>

	<input type="hidden" name="task" value=""/>
	<?php echo HTMLHelper::_('form.token'); ?>

</form>

<script type="text/javascript">
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