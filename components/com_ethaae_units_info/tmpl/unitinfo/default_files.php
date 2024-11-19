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

$session = Factory::getApplication()->getSession();
$sid = md5($session->getId());

$data = $displayData;
$reports = array();

if (isset($data['view']))
{
    $reports = $data['view'];
}


?>

<ul>
    <?php
    $i = 0;
    foreach ($reports[0]->files as $f): ?>
        <?php $flink = Uri::root()."index.php?option=com_ethaae_reports&task=report.download&id=".md5($f->id)."&sid=".$sid; ?>
        <?php $f_title = (!empty($f->caption)) ? $f->caption : $f->name; ?>
        <li class="files"  >
            <?php
            ?>
            <a class="hasTooltip" data-bs-placement="top" href="<?php echo $flink; ?>" data-bs-original-title="<?php echo $f->ftype; ?>">
                <?php echo $f->langImage; ?>
            </a>
            <div id="download-file-<?php echo $f->id;?>" role="tooltip"><?php echo $f->tooltip; ?></div>
        </li>
        <?php
        $i++;
    endforeach;
    ?>

</ul>