<?php

/**
 * Kunena Component
 *
 * @package         Kunena.Template.Aurelia
 * @subpackage      Layout.BBCode
 *
 * @copyright       Copyright (C) 2008 - 2024 Kunena Team. All rights reserved.
 * @license         https://www.gnu.org/copyleft/gpl.html GNU/GPL
 * @link            https://www.kunena.org
 **/

defined('_JEXEC') or die;

use Joomla\CMS\Factory;
use Joomla\CMS\Language\Text;
use Kunena\Forum\Libraries\Date\KunenaDate;

// [tweet]112233445566[/tweet]

// Display individual tweet.
?>

<div id="kunena_twitter_widget"
     style="background: none repeat scroll 0 0 #fff;    border-radius: 5px;    padding: 8px 8px 0;"
     class="root ltr twitter-tweet not-touch var-narrow" lang="en" data-scribe="page:tweet"
     data-iframe-title="Embedded Tweet" data-dt-pm="PM"
     data-dt-am="AM" data-dt-full="%{hours12}:%{minutes} %{amPm} - %{day} %{month} %{year}"
     data-dt-months="Jan|Feb|Mar|Apr|May|Jun|Jul|Aug|Sep|Oct|Nov|Dec" dir="ltr" data-twitter-event-id="4">
    <blockquote class="tweet subject expanded h-entry" data-scribe="section:subject"
                cite="https://twitter.com/<?php echo $this->user_name ?>/status/<?php echo $this->tweetid ?>"
                data-tweet-id="<?php echo $this->tweetid ?>">
        <div class="header">
            <div class="h-card p-author with-verification" data-scribe="component:author">
                <a class="u-url profile" data-scribe="element:user_link"
                   aria-label="<?php echo $this->user_name ?> (screen name: <?php echo $this->user_screen_name ?>)"
                   href="https://twitter.com/<?php echo $this->user_screen_name ?>">
                    <img class="u-photo avatar" data-scribe="element:avatar"
                         data-src-2x="<?php echo $this->user_profile_url_big ?>"
                         src="<?php echo $this->user_profile_url_normal ?>" alt="<?php echo $this->user_name ?>">
                    <span class="full-name">
                    <span class="p-name customisable-highlight"
                          data-scribe="element:name"><?php echo $this->user_name ?></span>
                        <?php if ($this->verified) :
                            ?>
                            <span class="verified" data-scribe="element:verified_badge" aria-label="<?php echo Text::_('COM_KUNENA_WIDGET_X_SOCIAL_VERIFIED_ACCOUNT_TITLE'); ?>"
                                  data-bs-toggle="tooltip" title="<?php echo Text::_('COM_KUNENA_WIDGET_X_SOCIAL_VERIFIED_ACCOUNT_TITLE'); ?>">
                            <b>✔</b>
                        </span>
                        <?php endif; ?>
                </span>
                    <span class="p-nickname" data-scribe="element:screen_name" dir="ltr">
                    @<b><?php echo $this->user_screen_name ?></b>
                </span>
                </a>
            </div>
            <div class="content e-entry-content" data-scribe="component:tweet">
                <p class="e-entry-title">
                    <?php echo $this->tweet_text ?>
                </p>
                <div class="dateline collapsible-container">
                    <a class="u-url customisable-highlight long-permalink" data-scribe="element:full_timestamp"
                       data-datetime="<?php echo Factory::getDate($this->tweet_created_at)->toISO8601(); ?>"
                       href="https://twitter.com/<?php echo $this->user_screen_name ?>/status/<?php echo $this->tweetid ?>">
                        <time class="dt-updated"
                              data-bs-toggle="tooltip" title="<?php echo Text::_('COM_KUNENA_WIDGET_X_SOCIAL_TIME_POSTED_TITLE'); ?>: <?php echo KunenaDate::getInstance($this->tweet_created_at)->toKunena('ago'); ?>"
                              datetime="<?php echo Factory::getDate($this->tweet_created_at)->toISO8601(); ?>">
                            <?php echo KunenaDate::getInstance($this->tweet_created_at)->toKunena('datetime'); ?>
                        </time>
                    </a>
                </div>
            </div>
            <div class="footer customisable-border" data-scribe="component:footer">
            <span class="stats-narrow customisable-border">
                <span class="stats" data-scribe="component:stats">
                    <a data-scribe="element:retweet_count" data-bs-toggle="tooltip" title="View Tweet on Twitter"
                       href="https://twitter.com/<?php echo $this->user_screen_name ?>/status/<?php echo $this->tweetid ?>">
                        <span class="stats-retweets">
                            <strong><?php echo $this->retweet_count; ?></strong>
                            <?php echo Text::_('COM_KUNENA_WIDGET_X_SOCIAL_RETWEETS'); ?>
                        </span>
                    </a>
                    <a data-scribe="element:favorite_count" data-bs-toggle="tooltip" title="View Tweet on Twitter"
                       href="https://twitter.com/<?php echo $this->user_screen_name ?>/status/<?php echo $this->tweetid ?>">
                        <span class="stats-favorites">
                            <strong><?php echo $this->favorite_count; ?></strong>
                            <?php echo Text::_('COM_KUNENA_WIDGET_X_SOCIAL_FAVORITES'); ?>
                        </span>
                    </a>
                </span>
            </span>
                <ul class="tweet-actions" data-scribe="component:actions" aria-label="Tweet actions" role="menu">
                    <li>
                        <a class="reply-action web-intent" data-scribe="element:reply" title="<?php echo Text::_('COM_KUNENA_WIDGET_X_SOCIAL_REPLY'); ?>"
                           href="https://twitter.com/intent/tweet?in_reply_to=<?php echo $this->tweetid ?>">
                            <i class="large-kicon glyphicon glyphicon-undo "></i>
                            <b><?php echo Text::_('COM_KUNENA_WIDGET_X_SOCIAL_REPLY'); ?></b>
                        </a>
                    </li>
                    <li>
                        <a class="retweet-action web-intent" data-scribe="element:retweet" data-bs-toggle="tooltip" title="<?php echo Text::_('COM_KUNENA_WIDGET_X_SOCIAL_RETWEET'); ?>"
                           href="https://twitter.com/intent/retweet?tweet_id=<?php echo $this->tweetid ?>">
                            <i class="large-kicon glyphicon glyphicon-loop"></i>
                            <b><?php echo Text::_('COM_KUNENA_WIDGET_X_SOCIAL_RETWEET'); ?></b>
                        </a>
                    </li>
                    <li>
                        <a class="favorite-action web-intent" data-scribe="element:favorite" data-bs-toggle="tooltip" title="<?php echo Text::_('COM_KUNENA_WIDGET_X_SOCIAL_FAVORITE'); ?>"
                           href="https://twitter.com/intent/favorite?tweet_id=<?php echo $this->tweetid ?>">
                            <i class="large-kicon glyphicon glyphicon-star"></i>
                            <b><?php echo Text::_('COM_KUNENA_WIDGET_X_SOCIAL_FAVORITE'); ?></b>
                        </a>
                    </li>
                </ul>
            </div>
        </div>
    </blockquote>
</div>
