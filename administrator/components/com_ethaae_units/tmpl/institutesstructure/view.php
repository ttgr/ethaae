<?php
/**
 * @version    CVS: 1.0.0
 * @package    Com_Anand
 * @author     Super User <dev@component-creator.com>
 * @copyright  2023 Super User
 * @license    GNU General Public License version 2 or later; see LICENSE.txt
 */

// No direct access
defined('_JEXEC') or die;

use \Joomla\CMS\HTML\HTMLHelper;
use \Joomla\CMS\Factory;
use \Joomla\CMS\Uri\Uri;
use \Joomla\CMS\Router\Route;
use \Joomla\CMS\Language\Text;
use \Joomla\CMS\Session\Session;
use Joomla\Utilities\ArrayHelper;


$db = Factory::getContainer()->get('DatabaseDriver');
$query = $db->getQuery(true);
$query
    ->select('u.short_code_el,i.title_el')
    ->from($db->quoteName('#__ethaae_institutes_structure','i'))
    ->join('LEFT', '#__ethaae_unit_type AS u ON u.id = i.unit_type_id')
    ->where($db->quoteName('i.id') . ' = '. $db->quote($db->escape($this->item->parentunitid)));
$db->setQuery($query);
$result = $db->loadObject();
//Factory::getApplication()->enqueueMessage($db->replacePrefix((string) $query), 'notice');
$this->item->parentunitid = $result->short_code_el." :: ".$result->title_el;

$query = $db->getQuery(true);
$query
    ->select('u.name_el')
    ->from($db->quoteName('#__ethaae_unit_type','u'))
    ->where($db->quoteName('u.id') . ' = '. $db->quote($db->escape($this->item->unit_type_id)));
$db->setQuery($query);
$result = $db->loadObject();
$this->item->unit_type_id = $result->name_el;


?>

<div class="item_fields">

    <table class="table">
    <tr>
        <td>ID</td>
        <td><?php echo $this->item->id; ?></td>
    </tr>
    <tr>
        <td><?php echo $this->form->getLabel('uuid'); ?></td>
        <td><?php echo $this->item->uuid; ?></td>
    </tr>
    <tr>
        <td><?php echo $this->form->getLabel('title_el'); ?></td>
        <td><?php echo $this->item->title_el; ?></td>
    </tr>
    <tr>
        <td><?php echo $this->form->getLabel('title_en'); ?></td>
        <td><?php echo $this->item->title_en; ?></td>
    </tr>
    <tr>
        <td><?php echo $this->form->getLabel('ministrycode'); ?></td>
        <td><?php echo $this->item->ministrycode; ?></td>
    </tr>
    <tr>
        <td><?php echo $this->form->getLabel('unittypecode'); ?></td>
        <td><?php echo $this->item->unittypecode; ?></td>
    </tr>
    <tr>
        <td><?php echo $this->form->getLabel('unitcode'); ?></td>
        <td><?php echo $this->item->unitcode; ?></td>
    </tr>
    <tr>
        <td><?php echo $this->form->getLabel('parentunitid'); ?></td>
        <td><?php echo $this->item->parentunitid; ?></td>
    </tr>
    <tr>
        <td><?php echo $this->form->getLabel('startupdate'); ?></td>
        <td><?php echo Factory::getDate($this->item->startupdate)->format('d/m/Y'); ?></td>
    </tr>
    <tr>
        <td><?php echo $this->form->getLabel('ceasedate'); ?></td>
        <td><?php echo ((!empty($this->item->ceasedate)) ?  Factory::getDate($this->item->ceasedate)->format('d/m/Y') : "&nbsp;");  ?></td>
    </tr>
    <tr>
        <td><?php echo $this->form->getLabel('greek'); ?></td>
        <td><?php echo ($this->item->greek) ? Text::_('JYES') : Text::_('NO'); ?></td>
    </tr>
    <tr>
        <td><?php echo $this->form->getLabel('notestablished'); ?></td>
        <td><?php echo ($this->item->notestablished) ? Text::_('JYES') : Text::_('NO'); ?></td>
    </tr>
    <tr>
        <td><?php echo $this->form->getLabel('unit_type_id'); ?></td>
        <td><?php echo $this->item->unit_type_id; ?></td>
    </tr>

    </table>

</div>

