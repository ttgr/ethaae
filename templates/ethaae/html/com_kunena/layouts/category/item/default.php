<?php

/**
 * Kunena Component
 *
 * @package         Kunena.Template.Aurelia
 * @subpackage      Layout.Category
 *
 * @copyright       Copyright (C) 2008 - 2024 Kunena Team. All rights reserved.
 * @license         https://www.gnu.org/copyleft/gpl.html GNU/GPL
 * @link            https://www.kunena.org
 **/

namespace Kunena\Forum\Site;

\defined('_JEXEC') or die();

use Joomla\CMS\HTML\HTMLHelper;
use Joomla\CMS\Language\Text;
use Kunena\Forum\Libraries\Forum\Topic\KunenaTopic;
use Kunena\Forum\Libraries\Icons\KunenaIcons;
use Kunena\Forum\Libraries\Route\KunenaRoute;
use Kunena\Forum\Libraries\Template\KunenaTemplate;

$categoryActions = $this->getCategoryActions();
$this->addStyleSheet('rating.css');
?>

<?php if ($this->category->headerdesc) : ?>
    <div class="clearfix"></div>
    <br>
    <h1 class="alert alert-info shadow-lg rounded alert-dismissible fade show">
        <?php echo $this->category->displayField('headerdesc'); ?>
        <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
    </h1>
<?php endif; ?>

<?php if (!$this->category->isSection()) : ?>
    <?php if (!empty($this->topics)) : ?>
        <div class="row">
            <div class="col-md-12">
                <h2 class="float-end">
                    <?php echo $this->subLayout('Widget/Search')
                        ->set('catid', $this->category->id)
                        ->setLayout('topic'); ?>
                </h2>

                <div class="float-start">
                    <?php echo $this->subLayout('Widget/Pagination/List')
                        ->set('pagination', $this->pagination)
                        ->set('display', true); ?>
                </div>
            </div>
        </div>
    <?php endif; ?>

    <form action="<?php echo KunenaRoute::_('index.php?option=com_kunena'); ?>" method="post" id="categoryactions">
        <input type="hidden" name="view" value="topics"/>
        <?php echo HTMLHelper::_('form.token'); ?>
        <div>
            <ul class="inline">
                <?php if ($categoryActions) : ?>
                    <li>
                        <?php echo implode($categoryActions); ?>
                    </li>
                <?php endif; ?>
            </ul>
        </div>
        <?php if ($this->topics) : ?>
        <table class="table<?php echo KunenaTemplate::getInstance()->borderless(); ?> table-topics">
            <thead>
            <tr>
                <th scope="col" class="center d-none d-md-table-cell">
                    <a id="forumtop"> </a>
                    <a href="#forumbottom" rel="nofollow">
                        <?php echo KunenaIcons::arrowdown(); ?>
                    </a>
                </th>
                <th scope="col" class="d-none d-md-table-cell"><?php echo Text::_('COM_KUNENA_GEN_SUBJECT'); ?></th>
                <th scope="col" class="d-none d-md-table-cell"><?php echo Text::_('COM_KUNENA_GEN_REPLIES'); ?>
                    / <?php echo Text::_('COM_KUNENA_GEN_HITS'); ?></th>
                <th scope="col" class="d-none d-md-table-cell"><?php echo Text::_('COM_KUNENA_GEN_LAST_POST'); ?></th>

                <?php if (!empty($this->topicActions)) : ?>
                    <th scope="col" class="center"><input class="kcheckall" type="checkbox" name="toggle" value=""/>
                    </th>
                <?php endif; ?>
            </tr>
            </thead>
            <tbody class="category-item">
            <?php
            /** @var KunenaTopic $previous */
            $previous = null;

            foreach ($this->topics as $position => $topic) {
                echo $this->subLayout('Topic/Row')
                    ->set('topic', $topic)
                    ->set('spacing', $previous && $previous->ordering != $topic->ordering)
                    ->set('position', 'kunena_topic_' . $position)
                    ->set('checkbox', !empty($this->topicActions))
                    ->setLayout('category');
                $previous = $topic;
            }
            ?>
            </tbody>
            <tfoot>
            <?php if ($this->topics) : ?>
                <tr>
                    <th scope="col" class="center d-none d-md-table-cell">
                        <a id="forumbottom"> </a>
                        <a href="#forumtop" rel="nofollow">
                            <span class="dropdown-divider"></span>
                            <?php echo KunenaIcons::arrowup(); ?>
                        </a>
                    </th>
                    <th scope="col" class="d-none d-md-table-cell">
                        <div class="form-group">
                            <div class="input-group" role="group">
                                <?php if (!empty($this->moreUri)) {
                                    echo HTMLHelper::_(
                                        'kunenaforum.link',
                                        $this->moreUri,
                                        Text::_('COM_KUNENA_MORE'),
                                        null,
                                        null,
                                        'follow'
                                    );
                                } ?>

                                <?php if (!empty($this->topicActions)) : ?>
                                    <?php echo HTMLHelper::_(
                                        'select.genericlist',
                                        $this->topicActions,
                                        'task',
                                        'class="form-select kchecktask"',
                                        'value',
                                        'text',
                                        0,
                                        'kchecktask'
                                    ); ?>

                                    <?php if ($this->actionMove) : ?>
                                        <?php
                                        $options = [HTMLHelper::_('select.option', '0', Text::_('COM_KUNENA_BULK_CHOOSE_DESTINATION'))];
                                        echo HTMLHelper::_(
                                            'kunenaforum.categorylist',
                                            'target',
                                            0,
                                            $options,
                                            [],
                                            'class="form-select fbs" disabled="disabled"',
                                            'value',
                                            'text',
                                            0,
                                            'kchecktarget'
                                        );
                                        ?>
                                        <button class="btn btn-outline-primary border" name="kcheckgo"
                                                type="submit"><?php echo Text::_('COM_KUNENA_GO') ?></button>
                                    <?php endif; ?>
                                <?php endif; ?>
                            </div>
                        </div>
                    </th>
                </tr>
            <?php endif; ?>
            </tfoot>
        <?php endif; ?>
        </table>
    </form>

    <?php if ($this->topics) : ?>
        <div class="float-start">
            <?php echo $this->subLayout('Widget/Pagination/List')
                ->set('pagination', $this->pagination)
                ->set('display', true); ?>
        </div>
    <?php endif; ?>

    <div class="clearfix"></div>

    <?php if (!empty($this->moderators)) {
        echo $this->subLayout('Category/Moderators')
            ->set('moderators', $this->moderators);
    }
    ?>

    <?php if ($this->ktemplate->params->get('writeaccess')) : ?>
        <div><?php echo $this->subLayout('Widget/Writeaccess')->set('id', $this->category->id); ?></div>
    <?php endif; ?>

<?php endif; ?>
