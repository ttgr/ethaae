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
$canCreate  = $user->authorise('core.create', 'com_ethaae_units_info') && file_exists(JPATH_COMPONENT . DIRECTORY_SEPARATOR . 'forms' . DIRECTORY_SEPARATOR . 'institutesinfoform.xml');
$canEdit    = $user->authorise('core.edit', 'com_ethaae_units_info') && file_exists(JPATH_COMPONENT .  DIRECTORY_SEPARATOR . 'forms' . DIRECTORY_SEPARATOR . 'institutesinfoform.xml');
$canCheckin = $user->authorise('core.manage', 'com_ethaae_units_info');
$canChange  = $user->authorise('core.edit.state', 'com_ethaae_units_info');
$canDelete  = $user->authorise('core.delete', 'com_ethaae_units_info');

// Import CSS
$wa = $this->document->getWebAssetManager();
$wa->useStyle('com_ethaae_units_info.list');
?>

<form action="<?php echo htmlspecialchars(Uri::getInstance()->toString()); ?>" method="post"
	  name="adminForm" id="adminForm">
	<?php //if(!empty($this->filterForm)) { echo LayoutHelper::render('joomla.searchtools.default', array('view' => $this)); } ?>

    <div class="institutes-listing">
    <?php $preIntiital = ""; ?>
    <?php foreach ($this->items as $key=>$rows) : ?>
        <?php if ($preIntiital !== $key) : ?>
            <div class="divider"><?php echo $key; ?></div>
            <?php $preIntiital = $key; ?>
        <?php endif; ?>
        <ul class="institutes row">
        <?php foreach ($rows as $row) : ?>
          <li class="col-xs-12 col-sm-6 col-md-4 institutes-item">
              <div class="institutes-item-inner">
                  <div class="image-wraper">
                    <img src="<?php echo $row->logo; ?>" width="100" alt="<?php echo $row->name; ?>" />
                  </div>
                  <span class="title"><?php echo $row->name; ?></span>
                  <div class="overlay" onclick="window.location.href='<?php echo Route::_('index.php?option=com_ethaae_units_info&view=institutesinfo&id='.(int) $row->id); ?>';">
                      <div class="text">
                          <a href="<?php echo Route::_('index.php?option=com_ethaae_units_info&view=institutesinfo&id='.(int) $row->id); ?>">
                              <i class="fa-solid fa-link"></i>
                          </a>
                      </div>
                  </div>
              </div>
          </li>
        <?php endforeach;  ?>
        </ul>
    <?php endforeach;  ?>

	</div>
	<input type="hidden" name="task" value=""/>
	<input type="hidden" name="boxchecked" value="0"/>
	<input type="hidden" name="filter_order" value=""/>
	<input type="hidden" name="filter_order_Dir" value=""/>
	<?php echo HTMLHelper::_('form.token'); ?>
</form>

