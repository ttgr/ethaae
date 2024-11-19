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
use \Joomla\CMS\Language\Text;
use \Joomla\CMS\Layout\LayoutHelper;

//HTMLHelper::_('bootstrap.tooltip');
HTMLHelper::_('behavior.multiselect');
HTMLHelper::_('formbehavior.chosen', 'select');

$wa = $this->document->getWebAssetManager();
$wa->useStyle('com_ethaae_units_info.list');


?>


<?php  if(isset($this->item->reports) && count($this->item->reports) >0 ) : ?>

    <?php //echo LayoutHelper::render('default_reports', array('view' => $this->item->reports), dirname(__FILE__)); ?>

<?php endif;?>

<?php  if(isset($this->item->studies) && count($this->item->studies) >0 ) : ?>

    <?php echo LayoutHelper::render('default_studies', array('view' => $this->item->studies), dirname(__FILE__)); ?>

<?php endif;?>

