<?php
/**
 * @version    CVS: 1.0.4
 * @package    Com_Adip
 * @author     Tasos Triantis <tasos.tr@gmail.com>
 * @copyright  2019 Tasos Triantis
 * @license    GNU General Public License version 2 or later; see LICENSE.txt
 */


defined('_JEXEC') or die;

use \Joomla\CMS\HTML\HTMLHelper;
use \Joomla\CMS\Uri\Uri;

?>


<table class="table table-striped" id="fileslist">
    <thead>
    <tr>
        <th class='left' style="width:3%;">
            &nbsp;
        </th>
        <th class='left'>
            <?php echo JText::_('COM_ADIP_FILES_FILENAME'); ?>
        </th>
        <th class='left'>
            <?php echo JText::_('COM_ADIP_FILES_TITLE'); ?>
        </th>
        <th class='left'>
            <?php echo JText::_('COM_ADIP_FILES_TYPE'); ?>
        </th>
        <th class='center'>
            <?php echo JText::_('COM_ADIP_FILES_LANG'); ?>
        </th>
        <th class='center'>
            <?php echo JText::_('COM_ADIP_FILES_STATUS'); ?>
        </th>
    </tr>
    </thead>
    <tbody>

    <?php
    $i = 0;
    foreach ($item->files as $f): ?>
    <?php $flink = Uri::root()."index.php?option=com_cwattachments&task=download&id=".md5($f->id)."&sid=".$sid; ?>

        <tr class="row<?php echo $i % 2; ?>">
            <td class='left' style="width:3%;">
                &nbsp;
            </td>

            <td class="link"  style="width:10%;" >
                <a href="<?php echo $flink; ?>">
                    <?php echo $f->caption; ?>
                </a>
            </td>
            <td class="link"  style="width:20%;" >
                <?php echo $f->path; ?>
            </td>
            <td class="link" style="width:5%;">
                <?php echo $f->ftype; ?>
            </td>
            <td class="link center"  style="width:2%;">
                <?php echo $f->langImage; ?>
            </td>
            <td class="link center" style="width:2%;">
                <?php echo HTMLHelper::_('jgrid.published', $f->publish, $i, 'reports.', false, 'cb');  ?>
            </td>
        </tr>
        <?php
        $i++;
    endforeach;
    ?>
    </tbody>
</table>
