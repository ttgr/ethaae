<?php

use \Joomla\CMS\Language\Text;
use Joomla\CMS\Factory;
use \Joomla\CMS\Layout\LayoutHelper;


$data = $displayData;
$depts = array();

if (isset($data['view']))
{
    $rows = $data['view'];
}

?>

<?php if (count($rows) > 0) : ?>

<div class="reports">
    <h2 itemprop="headline" class="title divider">
        <?php echo Text::_('COM_ETHAAE_UNITS_INFO_INSTITUTESINFOS_REPORTS_2_TITLE'); ?>
    </h2>
    <ul class="institutes row">
        <?php foreach ($rows as $row) : ?>
            <?php echo LayoutHelper::render('default_dept_card', array('view' => $row), dirname(__FILE__)); ?>
        <?php endforeach;  ?>
    </ul>
</div>


<?php endif;  ?>
