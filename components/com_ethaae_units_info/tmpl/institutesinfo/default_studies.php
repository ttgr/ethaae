<?php


/**
 * @version    CVS: 1.0.4
 * @package    Com_Adip
 * @author     Tasos Triantis <tasos.tr@gmail.com>
 * @copyright  2019 Tasos Triantis
 * @license    GNU General Public License version 2 or later; see LICENSE.txt
 */

defined('_JEXEC') or die;

use Joomla\CMS\Factory;
use \Joomla\CMS\Uri\Uri;
use \Joomla\CMS\Language\Text;
use \Ethaaereports\Component\Ethaae_reports\Site\Helper\Ethaae_reportsHelper;

$session = Factory::getApplication()->getSession();
$sid = md5($session->getId());


$data = $displayData;
$studies = array();

if (isset($data['view']))
{
    $studies = $data['view'];
}

$langSEF = Ethaae_reportsHelper::getCurrentLangSef();
$withReports = $studies[1];
$id = $studies[2]
?>

<ul class="studies-list">
    <?php
    $i = 0;
        foreach ($studies[0] as $key => $item): ?>
        <li class="studies-list-item">
            <div class="studies-title">
                <?php echo $item->{'short_code_'.$langSEF}; ?>
            </div>
            <div id="tooltip-<?php echo $id.'-'.$key?>" role="tooltip"><?php echo Text::_('COM_ETHAAE_UNIT_TYPE_'.$key); ?></div>
            <div class="studies-num">
                <?php echo $item->totals; ?>
                <?php if (isset($withReports[$key])) {
                    echo "/<span class=\"certified\">".$withReports[$key]->totals."</span>";
                } ?>
            </div>
        </li>
        <?php
        $i++;
    endforeach;
    ?>

</ul>