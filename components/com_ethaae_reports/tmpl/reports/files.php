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

?>

<ul>
    <?php
    $i = 0;
    foreach ($item->files as $f): ?>
        <?php $flink = Uri::root()."index.php?option=com_ethaae_reports&task=report.download&id=".md5($f->id)."&sid=".$sid; ?>
        <?php $f_title = (!empty($f->caption)) ? $f->caption : $f->name; ?>
        <li class="files"  >
            <?php
            ?>
                    <a class="hasTooltip html5lightbox" data-bs-placement="top" href="<?php echo $flink; ?>" data-bs-original-title="<?php echo $f->ftype; ?>">
                        <?php echo $f->langImage; ?>
                    </a>
        </li>
        <?php
        $i++;
    endforeach;
    ?>

</ul>