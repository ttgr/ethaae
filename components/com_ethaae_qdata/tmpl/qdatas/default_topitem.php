<?php
/**
 * @version    CVS: 1.0.2
 * @package    Com_Ethaae_qdata
 * @author     Tasos Triantis <tasos.tr@gmail.com>
 * @copyright  2025 Tasos Triantis
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

$data = $displayData;
$item = $data['view'];
?>
<div class="top-item page-header">
    <h1> <?php echo $item->short_code.' :: '.$item->unit_title ; ?> </h1>
    <h4 class="lead"><?php echo TEXT::_(  'COM_ETHAAE_QDATA_QDATAS_COLLECTIONYEAR')." :: ". $item->academic_reference_year; ?></h4>
</div>

<div class="header-stats-variables row">
    <?php foreach ($item->variables as $variable) : ?>
        <div class="stats-item-variable-wrapper col-md-3">
            <div class="stats-item-variable" data-toggle="tooltip" data-placement="top" title="<?php echo $variable->title; ?>">
                <div class="stats-item-variable-title"><?php echo $variable->title ?></div>
                <div class="stats-item-variable-value"><?php echo number_format($variable->value, 0, ',', '.'); ?></div>
            </div>
        </div>
    <?php endforeach; ?>
</div>

