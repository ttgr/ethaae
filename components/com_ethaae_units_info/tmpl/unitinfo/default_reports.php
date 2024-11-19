<?php

use \Joomla\CMS\Language\Text;
use \Joomla\CMS\Uri\Uri;
use Joomla\CMS\Factory;

$data = $displayData;
$reports = array();

if (isset($data['view']))
{
    $reports = $data['view'];
}

$session = Factory::getApplication()->getSession();
$sid = md5($session->getId());


?>
<?php if (count($reports) > 0) : ?>
<div class="reports">
    <h2 itemprop="headline" class="title divider">
        <?php echo Text::_('COM_ETHAAE_UNITS_INFO_INSTITUTESINFOS_REPORTS_TITLE'); ?>
    </h2>
    <ul>
    <?php foreach ($reports as $report) : ?>
        <li>
            <?php echo Text::_('COM_ETHAAE_REPORTS_TYPE_'.$report->fk_reporttype_id); ?>
            <?php  foreach ($report->files as $f): ?>
                <?php $flink = Uri::root()."index.php?option=com_ethaae_reports&task=report.download&id=".md5($f->id)."&sid=".$sid; ?>
                <?php $f_title = (!empty($f->caption)) ? $f->caption : $f->name; ?>
                <span class="files">
                    <?php
                    ?>
                    <a class="hasTooltip html5lightbox" data-bs-placement="top" href="<?php echo $flink; ?>" data-bs-original-title="<?php echo $f->ftype; ?>">
                        <?php echo $f->langImage; ?>
                    </a>
                </span>
                <?php endforeach; ?>
        </li>
    <?php endforeach; ?>
    </ul>

</div>

<?php endif;  ?>
