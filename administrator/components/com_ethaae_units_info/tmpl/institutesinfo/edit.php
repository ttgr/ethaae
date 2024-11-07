<?php
/**
 * @version    CVS: 1.1.0
 * @package    Com_Ethaae_units_info
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

$wa = $this->document->getWebAssetManager();
$wa->useScript('keepalive')
	->useScript('form.validate');
HTMLHelper::_('bootstrap.tooltip');
?>

<form
	action="<?php echo Route::_('index.php?option=com_ethaae_units_info&layout=edit&id=' . (int) $this->item->id); ?>"
	method="post" enctype="multipart/form-data" name="adminForm" id="institutesinfo-form" class="form-validate form-horizontal">

	
	<?php echo HTMLHelper::_('uitab.startTabSet', 'myTab', array('active' => 'institutesinfo')); ?>
	<?php echo HTMLHelper::_('uitab.addTab', 'myTab', 'institutesinfo', Text::_('COM_ETHAAE_UNITS_INFO_TAB_INSTITUTESINFO', true)); ?>
	<div class="row-fluid">
		<div class="col-md-12 form-horizontal">
			<fieldset class="adminform">
				<legend><?php echo Text::_('COM_ETHAAE_UNITS_INFO_FIELDSET_INSTITUTESINFO'); ?></legend>
				<?php echo $this->form->renderField('fk_unit_id'); ?>
                <?php echo $this->form->renderField('url_el'); ?>
                <?php echo $this->form->renderField('url_en'); ?>
                <?php echo $this->form->renderField('description_el'); ?>
				<?php echo $this->form->renderField('description_en'); ?>
			</fieldset>
		</div>
	</div>
	<?php echo HTMLHelper::_('uitab.endTab'); ?>
	<input type="hidden" name="jform[id]" value="<?php echo isset($this->item->id) ? $this->item->id : ''; ?>" />

	<input type="hidden" name="jform[state]" value="<?php echo isset($this->item->state) ? $this->item->state : ''; ?>" />

	<?php echo $this->form->renderField('created_by'); ?>
	<?php echo $this->form->renderField('modified_by'); ?>
	<?php echo $this->form->renderField('created'); ?>
	<?php echo $this->form->renderField('modified'); ?>

	
	<?php echo HTMLHelper::_('uitab.endTabSet'); ?>

	<input type="hidden" name="task" value=""/>
	<?php echo HTMLHelper::_('form.token'); ?>

</form>
