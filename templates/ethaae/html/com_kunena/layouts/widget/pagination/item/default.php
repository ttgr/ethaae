<?php

/**
 * Kunena Component
 *
 * @package         Kunena.Template.Aurelia
 * @subpackage      Layout.Pagination
 *
 * @copyright       Copyright (C) 2008 - 2024 Kunena Team. All rights reserved.
 * @license         https://www.gnu.org/copyleft/gpl.html GNU/GPL
 * @link            https://www.kunena.org
 **/

defined('_JEXEC') or die();

use Joomla\CMS\Language\Text;
use Kunena\Forum\Libraries\Template\KunenaTemplate;

$item = $this->item;

if ($item->base !== null) {
    // Check if the item can be clicked.
    $limit = 'limitstart.value=' . (int) $item->base;
    echo '<li class="page-item">
			<a class="page-link" ' . KunenaTemplate::getInstance()->tooltips(true) . ' href="' . $item->link . '" data-bs-toggle="tooltip" title="' . Text::_('COM_KUNENA_PAGE') . $item->text . '">' . $item->text . '</a>
		  </li>';
} elseif (!empty($item->active)) {
    // Check if the item is the active (or current) page.
    echo '<li class="page-item active"><a class="page-link">' . $item->text . '</a></li>';
} else {
    // Doesn't match any other condition, render disabled item.
    echo '<li class="page-item disabled"><a class="page-link">' . $item->text . '</a></li>';
}
