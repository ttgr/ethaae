<?php

/**
 * Kunena Component
 *
 * @package         Kunena.Template.Aurelia
 * @subpackage      Layout.Widget
 *
 * @copyright       Copyright (C) 2008 - 2024 Kunena Team. All rights reserved.
 * @license         https://www.gnu.org/copyleft/gpl.html GNU/GPL
 * @link            https://www.kunena.org
 **/

defined('_JEXEC') or die;

use Joomla\CMS\Filter\OutputFilter;
use Joomla\CMS\Helper\ModuleHelper;
use Joomla\CMS\HTML\HTMLHelper;

// Basic logic has been taken from Joomla! 2.5 (mod_menu)
// Note. It is important to remove spaces between elements.
?>

<ul class="navbar-nav mr-auto">
    <?php

    $columninx  = 0;
    $lastlevel  = 4;
    $extrastyle = '';

    foreach ($this->list as $i => $item) {
        // Exclude item with menu item option set to exclude from menu modules
        if ($item->getParams()->get('menu_show', 1) == 0) {
            continue;
        }

        // The next item is deeper.
        if (($item->level == 2) && ($lastlevel == 1)) {
            $columninx++;
        }

        $class = 'nav-item item-' . $item->id;

        if (in_array($item->id, $this->path)) {
            $class .= ' active';
        } elseif ($item->type == 'alias') {
            $aliasToId = $item->params->get('aliasoptions');

            if (count($this->path) > 0 && $aliasToId == $this->path[count($this->path) - 1]) {
                $class .= ' active';
            } elseif (in_array($aliasToId, $this->path)) {
                $class .= ' alias-parent-active';
            }
        }

        if ($item->parent) {
            $class .= ' parent';
        }

        if (!empty($class)) {
            $class = ' class="' . trim($class) . '"';
        }

        echo '<li' . $class . ' >';

        $item->menu_image = $item->getParams()->get('menu_image');

        // Render the menu item.
        if ($item->type == 'separator') {
            if ($item->level == 2) {
                echo '</ul><ul class="unstyled">';
            } else {
                echo '<li class="dropdown-divider"></li>';
            }
            /*} elseif ($item->deeper) {
            if ($item->level > 1) {
                require ModuleHelper::getLayoutPath('mod_menu', 'default_url');
            } else {
                echo '<a href="#" class="dropdown-toggle" data-bs-toggle="dropdown">';
                require ModuleHelper::getLayoutPath('mod_menu', 'default_url');
                echo ' <b class="caret"></b></a>';
            }*/
        } else {
            switch ($item->type) {
                case 'separator':
                case 'url':
                case 'component':
                    $attributes = [];

                    if ($item->deeper) {
                        $attributes['class'] = 'nav-link dropdown-toggle';
                        $attributes['role']  =   'button';
                        $attributes['data-bs-toggle']  = 'dropdown';
                    } elseif ($item->level > 2) {
                        $attributes['class'] = 'dropdown-item';
                    } else {
                        $attributes['class'] = 'nav-link';
                    }

                    if ($item->id == $this->active_id) {
                        $attributes['class'] .= ' active';
                    }

                    $linktype = $item->title;

                    if ($item->menu_image) {
                        if ($item->menu_image_css) {
                            $image_attributes['class'] = $item->menu_image_css;
                            $linktype                  = HTMLHelper::_('image', $item->menu_image, $item->title, $image_attributes);
                        } else {
                            $linktype = HTMLHelper::_('image', $item->menu_image, $item->title);
                        }

                        if ($item->getParams()->get('menu_text')) {
                            $linktype .= '<span class="image-title">' . $item->title . '</span>';
                        }
                    }

                    if ($item->browserNav == 1) {
                        $attributes['target'] = '_blank';
                        $attributes['rel']    = 'noopener noreferrer';

                        if ($item->anchor_rel == 'nofollow') {
                            $attributes['rel'] .= ' nofollow';
                        }
                    } elseif ($item->browserNav == 2) {
                        $options = 'toolbar=no,location=no,status=no,menubar=no,scrollbars=yes,resizable=yes,';

                        $attributes['onclick'] = "window.open(this.href, 'targetWindow', '" . $options . "'); return false;";
                    }

                    echo HTMLHelper::_('link', OutputFilter::ampReplace(htmlspecialchars($item->flink, ENT_COMPAT, 'UTF-8', false)), $linktype, $attributes);

                    break;

                default:
                    require ModuleHelper::getLayoutPath('mod_menu', 'default_url');
                    break;
            }
        }

        // The next item is deeper.
        if ($item->deeper) {
            if ($item->level < 3) {
                echo '<ul class="dropdown-menu" style="left:0;top:40px;">';
            } elseif ($item->level == 3) {
                echo '<ul class="dropdown-menu">';
            } else {
                echo '<ul class="dropdown-menu" style="left:0;top:40px;">';
            }
        }
        // The next item is shallower.
        elseif ($item->shallower) {
            echo '</li>';
            $nlevel = $item->level;

            for ($x = 0; $x < $item->level_diff; $x++) {
                $nlevel--;

                if ($nlevel == 1) {
                    echo '</ul></div></li>';
                }

                echo '</ul></li>';
                $extrastyle = '';
            }
        }
        // The next item is on the same level.
        else {
            echo '</li>';
        }

        $lastlevel = $item->level;
    }
    ?>
</ul>