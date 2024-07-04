<?php

/**
 * Kunena Component
 *
 * @package         Kunena.Template.Aurelia
 * @subpackage      Layout.Topic
 *
 * @copyright       Copyright (C) 2008 - 2024 Kunena Team. All rights reserved.
 * @license         https://www.gnu.org/copyleft/gpl.html GNU/GPL
 * @link            https://www.kunena.org
 **/

namespace Kunena\Forum\Site;

\defined('_JEXEC') or die();

use Joomla\CMS\HTML\HTMLHelper;
use Joomla\CMS\Language\Text;
use Kunena\Forum\Libraries\Factory\KunenaFactory;
use Kunena\Forum\Libraries\Icons\KunenaIcons;
use Kunena\Forum\Libraries\Icons\KunenaSvgIcons;
use Kunena\Forum\Libraries\Route\KunenaRoute;

HTMLHelper::_('bootstrap.framework');

$this->addScriptDeclaration(
    "// <![CDATA[
kunena_url_ajax= '" . KunenaRoute::_("/index.php?option=com_kunena&view=category&type=raw&format=json&layout=raw") . "';
// ]]>"
);

$this->addScript('assets/js/topic.js');
$this->ktemplate = KunenaFactory::getTemplate();
$topicicontype   = $this->ktemplate->params->get('topicicontype');
$labels          = $this->ktemplate->params->get('labels');
?>
<div class="card">
    <div class="card-header">
        <h3>
            <?php echo $this->title; ?>
        </h3>
    </div>
    <div class="card-body">
        <form action="<?php echo KunenaRoute::_('index.php?option=com_kunena&view=topic') ?>" method="post"
              name="myform" id="myform" class="form-horizontal">
            <input type="hidden" name="task" value="move"/>
            <input type="hidden" name="catid" value="<?php echo $this->category->id; ?>"/>
            <input type="hidden" name="id" value="<?php echo $this->topic->id; ?>"/>
            <?php
            if (isset($this->message)) :
                ?>
                <input type="hidden" name="mesid" value="<?php echo $this->message->id; ?>"/>
            <?php endif; ?>
            <?php echo HTMLHelper::_('form.token'); ?>
            <div>
                <ul class="nav nav-tabs" id="myTab" role="tablist">
                    <li class="nav-item">
                        <a class="nav-link active" id="tab1-tab" data-bs-toggle="tab" href="#tab1" role="tab"
                           aria-controls="tab1"
                           aria-selected="true"><?php echo Text::_('COM_KUNENA_TITLE_MODERATE_TAB_BASIC_INFO'); ?></a>
                    </li>
                    <li class="nav-item">
                        <a class="nav-link" id="tab2-tab" data-bs-toggle="tab" href="#tab2" role="tab"
                           aria-controls="tab2"
                           aria-selected="false"><?php echo Text::_('COM_KUNENA_TITLE_MODERATE_TAB_MOVE_OPTIONS'); ?></a>
                    </li>
                    <?php if (isset($this->message) && $this->message->getAuthor()->id != 0) :
                        ?>
                        <li class="nav-item">
                            <a class="nav-link" id="tab3-tab" data-bs-toggle="tab" href="#tab3" role="tab"
                               aria-controls="tab3"
                               aria-selected="false"><?php echo Text::_('COM_KUNENA_TITLE_MODERATE_TAB_BAN_HISTORY'); ?></a>
                        </li>
                        <!--  <li><a href="#tab4" data-bs-toggle="tab"><?php // Echo Text::_('COM_KUNENA_TITLE_MODERATE_TAB_NEW_BAN');
                        ?></a></li> -->
                    <?php endif; ?>
                </ul>
                <br/>
                <div class="tab-content" id="myTabContent">
                    <div class="tab-pane fade show active" id="tab1" role="tabpanel" aria-labelledby="tab1-tab">
                        <dl class="dl-horizontal">
                            <dt> <?php echo Text::_('COM_KUNENA_MENU_TOPIC'); ?> </dt>
                            <dd> <?php echo $this->topic->displayField('subject'); ?> </dd>
                            <dt> <?php echo Text::_('COM_KUNENA_CATEGORY'); ?> </dt>
                            <dd> <?php echo $this->category->displayField('name') ?> </dd>
                            <?php if (isset($this->userLink)) :
                                ?>
                                <dt> <?php echo Text::_('JGLOBAL_USERNAME'); ?> </dt>
                                <dd><strong> <?php echo $this->userLink; ?></strong></dd>
                            <?php endif; ?>
                        </dl>
                        <?php if ($this->config->topicIcons) :
                            ?>
                            <div><?php echo Text::_('COM_KUNENA_MODERATION_CHANGE_TOPIC_ICON'); ?>:</div>
                            <br/>
                            <div class="kmoderate-topicIcons">
                                <?php foreach ($this->topicIcons as $icon) :
                                    ?>
                                <input type="radio" id="radio<?php echo $icon->id ?>" name="topic_emoticon"
                                       value="<?php echo $icon->id ?>" <?php echo !empty($icon->checked) ? ' checked="checked" ' : '' ?> />
                                    <?php if ($this->config->topicIcons && $topicicontype == 'svg') :
                                        ?>
                                <label class="radio inline" for="radio<?php echo $icon->id; ?>">
                                        <?php
                                        if (!$this->category->iconset) :
                                            $this->category->iconset = 'default';
                                        endif; ?>
                                        <?php echo KunenaSvgIcons::loadsvg($icon->svg, 'usertopicIcons', $this->category->iconset); ?>
                                    <?php elseif ($this->config->topicIcons && $topicicontype == 'fa') :
                                        ?>
                                    <label class="radio inline" for="radio<?php echo $icon->id; ?>"><i
                                                class="fa fa-<?php echo $icon->fa; ?> glyphicon-topic fa-2x"></i>
                                    <?php else :
                                        ?>
                                        <label class="radio inline" for="radio<?php echo $icon->id; ?>"><img
                                                    loading=lazy
                                                    src="<?php echo $icon->relpath; ?>"
                                                    alt="<?php echo $icon->name; ?>"
                                                    style=border:0;/>
                                    <?php endif; ?>
                                        </label>
                                <?php endforeach; ?>
                            </div>
                        <?php elseif ($labels && !$this->config->topicIcons) :
                            ?>
                            <div><strong><?php echo Text::_('COM_KUNENA_MODERATION_CHANGE_LABEL'); ?>:</strong></div>
                            <br/>
                            <div class="kmoderate-topicIcons">
                                <?php foreach ($this->topicIcons as $icon) :
                                    ?>
                                <input type="radio" id="radio<?php echo $icon->id ?>" name="topic_emoticon"
                                       value="<?php echo $icon->id ?>" <?php echo !empty($icon->checked) ? ' checked="checked" ' : '' ?> />
                                    <?php if ($topicicontype == 'svg') :
                                        ?>
                                <label class="radio inline" for="radio<?php echo $icon->id; ?>">
                                        <?php
                                        if (!$this->category->iconset) :
                                            $this->category->iconset = 'default';
                                        endif; ?>
                                        <?php echo KunenaSvgIcons::loadsvg($icon->svg, 'usertopicIcons', $this->category->iconset); ?>
                                    <?php elseif ($topicicontype == 'fa') :
                                        ?>
                                    <label class="radio inline" for="radio<?php echo $icon->id; ?>"><i
                                                class="fa fa-<?php echo $icon->fa; ?> glyphicon-topic fa-2x"></i>
                                    <?php else :
                                        ?>
                                        <label class="radio inline" for="radio<?php echo $icon->id; ?>"><img
                                                    loading=lazy
                                                    src="<?php echo $icon->relpath; ?>"
                                                    alt="<?php echo $icon->name; ?>"
                                                    style="border:0;"/>
                                    <?php endif; ?>
                                        </label>
                                <?php endforeach; ?>
                            </div>
                            <br/>
                        <?php endif; ?>
                        <br/>
                        <?php if (isset($this->message)) :
                            ?>
                            <hr/>
                            <br/>
                            <h3>
                                <div class="float-start">
                                    <?php echo $this->message->getAuthor()->getAvatarImage('img-thumbnail', 'list'); ?>
                                </div>
                                <?php echo $this->message->displayField('subject'); ?>
                                <br/>
                                <small>
                                    <?php echo Text::_('COM_KUNENA_POSTED_AT') ?>
                                    <?php echo $this->message->getTime()->toSpan('config_postDateFormat', 'config_postDateFormatHover'); ?>
                                    <?php echo Text::_('COM_KUNENA_BY') . ' ' . $this->message->getAuthor()->getLink(); ?>
                                </small>
                                <div class="clearfix"></div>
                            </h3>

                            <div class="card card-body khistory">
                                <?php echo $this->message->displayField('message'); ?>
                            </div>

                        <?php endif; ?>
                    </div>

                    <div class="tab-pane fade" id="tab2" role="tabpanel" aria-labelledby="tab2-tab">
                        <h3> <?php echo Text::_('COM_KUNENA_MODERATION_DEST'); ?> </h3>

                        <div class="control-group">
                            <label class="control-label"
                                   for="modcategorieslist"> <?php echo Text::_('COM_KUNENA_MODERATION_DEST_CATEGORY'); ?> </label>

                            <div class="controls" id="modcategorieslist"> <?php echo $this->getCategoryList(); ?> </div>
                        </div>
                        <div class="control-group">
                            <label class="control-label"
                                   for="modtopicslist"> <?php echo Text::_('COM_KUNENA_MODERATION_DEST_TOPIC'); ?> </label>

                            <div class="controls" id="modtopicslist"> <?php echo HTMLHelper::_(
                                'select.genericlist',
                                $this->getTopicOptions(),
                                'targettopic',
                                'class="form-select"',
                                'value',
                                'text',
                                0,
                                'kmod_topics'
                            ); ?> </div>
                        </div>
                        <div class="control-group" id="kmod_targetid" style="display: none;">
                            <label class="control-label"
                                   for="modtopicslist"> <?php echo Text::_('COM_KUNENA_MODERATION_TARGET_TOPIC_ID'); ?> </label>

                            <div class="controls">
                                <input type="text" size="7" class="form-control" name="targetid" value=""/>
                            </div>
                        </div>
                        <div class="control-group" id="kmod_subject">
                            <label class="control-label"
                                   for="kmod_subject"> <?php echo Text::_('COM_KUNENA_MODERATION_TITLE_DEST_SUBJECT'); ?> </label>

                            <div class="controls">
                                <input type="text" class="form-control" name="subject" id="ktitle_moderate_subject"
                                       value="<?php echo !isset($this->message)
                                           ? $this->topic->displayField('subject')
                                           : $this->message->displayField('subject'); ?>"
                                       maxlength="<?php echo $this->escape($this->ktemplate->params->get('SubjectLengthMessage')); ?>"/>
                            </div>
                        </div>
                        <?php if (!empty($this->replies)) :
                            ?>
                            <div class="control-group">
                                <div class="controls">
                                    <label class="checkbox">
                                        <input id="kmoderate-mode-selected" type="radio" name="mode" checked="checked"
                                               value="selected"
                                               style="display: inline-block;"/>
                                        <?php echo Text::_('COM_KUNENA_MODERATION_MOVE_SELECTED'); ?> </label>
                                    <label class="checkbox">
                                        <input id="kmoderate-mode-newer" type="radio" name="mode" value="newer"
                                               style="display: inline-block;"/>
                                        <?php echo Text::sprintf('COM_KUNENA_MODERATION_MOVE_NEWER', $this->escape($this->replies)); ?>
                                    </label>
                                </div>
                            </div>
                        <?php endif; ?>

                        <div class="control-group">
                            <div class="controls">
                                <label class="checkbox">
                                    <input type="checkbox" name="changesubject" value="1"/>
                                    <?php echo Text::_('COM_KUNENA_MODERATION_CHANGE_SUBJECT_ON_REPLIES'); ?> </label>
                            </div>
                        </div>
                        <?php if (!isset($this->message)) :
                            ?>
                            <div class="control-group">
                                <div class="controls">
                                    <label class="checkbox">
                                        <input type="checkbox" <?php if ($this->config->boxGhostMessage) {
                                            echo ' checked="checked"';
                                                               } ?>
                                               name="shadow" value="1"/>
                                        <?php echo Text::_('COM_KUNENA_MODERATION_TOPIC_SHADOW'); ?> </label>
                                </div>
                            </div>
                        <?php endif; ?>
                    </div>
                    <?php if (isset($this->message) && $this->message->getAuthor()->id != 0) :
                        ?>
                        <div class="tab-pane fade" id="tab3" role="tabpanel" aria-labelledby="tab3-tab">
                            <?php echo $this->subLayout('User/Ban/History')->set('profile', $this->message->getAuthor())->set('headerText', Text::_('COM_KUNENA_TITLE_MODERATE_TAB_BAN_HISTORY'))->set('banHistory', $this->banHistory)->set('me', $this->me); ?>
                        </div>

                        <!-- FIXME: This adds a form which cause an issue, because there will be nested forms
                <div class="tab-pane" id="tab4">
                        <?php // Echo $this->subLayout('User/Ban/Form')->set('profile', $this->message->getAuthor())->set('banInfo', $this->banInfo)->set('headerText', Text::_('COM_KUNENA_TITLE_MODERATE_TAB_NEW_BAN'));
                        ?>
                </div>-->
                    <?php endif; ?>
                    <hr/>
                    <br/>
                </div>
            </div>
            <br/>
            <div class="control-group center">
                <button name="submit" type="submit"
                        class="btn btn-outline-success btn-md">
                    <?php echo KunenaIcons::save() . ' ' . Text::_('COM_KUNENA_POST_MODERATION_PROCEED'); ?>
                </button>

                <a href="javascript:window.history.back();"
                   class="btn btn-outline-primary border"> <?php echo KunenaIcons::cancel() . ' ' . Text::_('COM_KUNENA_BACK'); ?> </a>
            </div>
        </form>
    </div>
</div>
