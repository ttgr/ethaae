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
use \Joomla\CMS\Language\Text;
use \Joomla\CMS\Layout\LayoutHelper;
use \Joomla\CMS\Router\Route;
use \Ethaaereports\Component\Ethaae_reports\Site\Helper\Ethaae_reportsHelper;

$data = $displayData;
$reports = array();

HTMLHelper::_('bootstrap.tooltip');
HTMLHelper::_('behavior.multiselect');
HTMLHelper::_('formbehavior.chosen', 'select');


$user       = Factory::getApplication()->getIdentity();
$userId     = $user->get('id');

// Import CSS
$document = Factory::getApplication()->getDocument();
$wa = $document->getWebAssetManager();
$wr = $wa->getRegistry();
$wr->addRegistryFile('media/com_ethaae_reports/joomla.asset.json');

$wa->useStyle('com_ethaae_reports.list')
    ->useScript('com_ethaae_reports.list');


$langSEF = Ethaae_reportsHelper::getCurrentLangSef();

if (isset($data['view']))
{
    $reports = $data['view'];
}

$session = Factory::getApplication()->getSession();
$sid = md5($session->getId());

?>

    <div class="table-responsive">
        <h2 itemprop="headline" class="title divider">
            <?php echo Text::_('COM_ETHAAE_UNITS_INFO_INSTITUTESINFOS_REPORTS_3_TITLE'); ?>
        </h2>

        <?php if (count($reports) >0 ) : ?>
            <div class="table-total-rows"> <?php echo Text::sprintf('COM_ETHAAE_UNITS_INFO_INSTITUTESINFOS_REPORT_TOTAL_RECORDS',count($reports)); ?></div>
        <?php endif; ?>

        <table class="table table-striped" id="reportList">
            <thead>
            <tr>
                <th class='' scope="col">
                    <a href="#"><?php echo Text::_('COM_ETHAAE_UNITS_INFO_INSTITUTESINFOS_TABLE_UNIT_COLUMN'); ?></a>
                </th>
                <th class='' scope="col">
                    <a href="#"><?php echo Text::_(  'COM_ETHAAE_UNITS_INFO_INSTITUTESINFOS_TABLE_STUDIES_COLUMN'); ?></a>
                    <div id="tooltip-657649" role="tooltip"><?php echo Text::_('COM_ETHAAE_UNITS_INFO_INSTITUTESINFOS_TABLE_STUDIES_NOTE'); ?></div>
                </th>

                <th class='files' scope="col">
                    <a href="#"><?php echo Text::_('COM_ETHAAE_REPORTS_REPORTS_FK_REPORTTYPE_ID'); ?></a>
                </th>
            </tr>
            </thead>
            <tbody>
            <?php foreach ($reports as $i => $item) : ?>

                <tr class="row<?php echo $i % 2; ?>">
                    <td scope="row" data-label="<?php echo Text::_('COM_ETHAAE_UNITS_INFO_INSTITUTESINFOS_TABLE_UNIT_COLUMN'); ?>">
                        <div class="row-content">
                            <?php echo $item->{'parent_title_'.$langSEF} ?></div>
                        </div>
                    </td>
                    <td scope="row" data-label="<?php echo Text::_('COM_ETHAAE_UNITS_INFO_INSTITUTESINFOS_TABLE_STUDIES_COLUMN'); ?>">
                        <div class="row-content unit-title">
                            <?php echo $item->{'unit_short_code_'.$langSEF}." :: ".$item->{'unit_title_'.$langSEF}; ?>
                        </div>
                    </td>
                    <td scope="row" data-label="<?php echo Text::_('COM_ETHAAE_REPORTS_REPORTS_FK_REPORTTYPE_ID'); ?>">
                        <div class="row-content">
                            <?php if (isset($item->reports) && is_array($item->reports) && count($item->reports) > 0) : ?>
                                <?php echo LayoutHelper::render('default_files', array('view' => $item->reports), dirname(__FILE__)); ?>
                            <?php endif; ?>
                        </div>
                    </td>
                </tr>
            <?php endforeach; ?>
            </tbody>
        </table>
    </div>