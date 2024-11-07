<?php
/**
 * @package     ${NAMESPACE}
 * @subpackage
 *
 * @copyright   A copyright
 * @license     A "Slug" license name e.g. GPL2
 */

use Joomla\CMS\Factory;
use Joomla\CMS\HTML\HTMLHelper;
use Joomla\CMS\Language\Multilanguage;
use Joomla\CMS\Language\Text;
use Joomla\CMS\Layout\LayoutHelper;
use Joomla\CMS\Router\Route;
use Joomla\CMS\Session\Session;
use Joomla\Component\Content\Site\Helper\RouteHelper;

/** @var \Joomla\Component\Content\Administrator\View\Articles\HtmlView $this */

$app = Factory::getApplication();
$wa = $this->document->getWebAssetManager();
$wa->useScript('keepalive')
    ->useScript('form.validate');

if ($app->isClient('site')) {
    Session::checkToken('get') or die(Text::_('JINVALID_TOKEN'));
}
?>
<div class="subhead noshadow mb-3">
    <?php echo $this->document->getToolbar('toolbar')->render(); ?>
</div>
<div class="container-popup">
    <form
            action="<?php echo Route::_('index.php?option=com_adip&view=report&layout=editfile&tmpl=component&id=' . (int) $this->item->id); ?>"
            method="post" enctype="multipart/form-data" name="adminForm" id="report-file-form" class="form-validate form-horizontal">

        <div class="row-fluid">
            <div class="col-md-12 form-horizontal" >
                <fieldset class="adminform">
                    <?php echo $this->form->renderField('caption'); ?>
                    <?php echo $this->form->renderField('language'); ?>
                    <?php echo $this->form->renderField('image'); ?>
                    <?php echo $this->form->renderField('publish'); ?>
                </fieldset>
            </div>
        </div>
        <input type="hidden" name="editFileForm[id]" value="<?php echo $this->item->id; ?>" />

        <input type="hidden" name="task" value=""/>
        <?php echo HTMLHelper::_('form.token'); ?>
    </form>

</div>

<script type="text/javascript">
    js = jQuery.noConflict();

    Joomla.submitbutton = function (task) {
        if (task == 'report.cancel') {
            Joomla.submitform(task, document.getElementById('report-file-form'));
        }
        else {

            if (task != 'report.cancel' && document.formvalidator.isValid(document.getElementById('report-file-form'))) {
                console.log(task);
                Joomla.submitform(task,document.getElementById('report-file-form'));
            }
            else {
                //alert('<?php echo $this->escape(Text::_('JGLOBAL_VALIDATION_FORM_FAILED')); ?>');
            }
        }
    }
</script>