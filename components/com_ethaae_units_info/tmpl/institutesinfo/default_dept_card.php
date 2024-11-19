<?php
/**
 * @package     ${NAMESPACE}
 * @subpackage
 *
 * @copyright   A copyright
 * @license     A "Slug" license name e.g. GPL2
 */


use \Joomla\CMS\Language\Text;
use Joomla\CMS\Factory;
use \Joomla\CMS\Layout\LayoutHelper;


$data = $displayData;
$row = array();

if (isset($data['view']))
{
    $row = $data['view'];
}

$langTag = Factory::getApplication()->getLanguage()->getTag();
$lang = explode("-",$langTag)[0];

?>
<li class="col-xs-12 col-sm-6 col-md-4 institutes-item">
    <div class="institutes-item-inner">
        <div class="unit-title"><?php echo $row->{'unit_title_'.$lang}; ?></div>
        <div class="parent-title"><?php echo $row->{'parent_title_'.$lang}; ?></div>
        <?php if (isset($row->reports) && is_array($row->reports) && count($row->reports) > 0) : ?>
            <?php echo LayoutHelper::render('default_files', array('view' => $row->reports), dirname(__FILE__)); ?>
        <?php endif; ?>
    </div>
</li>



