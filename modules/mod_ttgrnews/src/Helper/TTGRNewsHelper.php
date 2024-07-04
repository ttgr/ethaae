<?php

/**
 * @package     Joomla.Site
 * @subpackage  mod_bienewspage
 *
 * @copyright   (C) 2010 Open Source Matters, Inc. <https://www.joomla.org>
 * @license     GNU General Public License version 2 or later; see LICENSE.txt
 */

namespace Joomla\Module\TTGRNews\Site\Helper;

use Joomla\CMS\Access\Access;
use Joomla\CMS\Application\SiteApplication;
use Joomla\CMS\Component\ComponentHelper;
use Joomla\CMS\Factory;
use Joomla\CMS\HTML\HTMLHelper;
use Joomla\CMS\Language\Text;
use Joomla\CMS\Router\Route;
use Joomla\CMS\Uri\Uri;
use Joomla\Component\Content\Site\Helper\RouteHelper;
use Joomla\Database\DatabaseAwareInterface;
use Joomla\Database\DatabaseAwareTrait;
use Joomla\Registry\Registry;

// phpcs:disable PSR1.Files.SideEffects
\defined('_JEXEC') or die;
// phpcs:enable PSR1.Files.SideEffects

/**
 * Helper for mod_articles_news
 *
 * @since  1.6
 */
class TTGRNewsHelper implements DatabaseAwareInterface
{
    use DatabaseAwareTrait;

    /**
     * Get a list of the latest articles from the article model.
     *
     * @param   Registry         $params  Object holding the models parameters
     * @param   SiteApplication  $app     The app
     *
     * @return  mixed
     *
     * @since 4.2.0
     */
    public static function getArticles(Registry &$params, array $catids,int $selectedYear = 0, $start = 0)
    {
        $app     = Factory::getApplication();
        $factory = $app->bootComponent('com_content')->getMVCFactory();
        $articles = $factory->createModel('Articles', 'Site', ['ignore_request' => true]);

        // Set application parameters in model
        $input     = $app->getInput();
        $appParams = $app->getParams();
        $articles->setState('params', $appParams);

        $articles->setState('list.start', $start);
        $articles->setState('filter.published', 1);

        // Set the filters based on the module params
        $articles->setState('list.limit', (int) $params->get('count', 3));
        // This module does not use tags data
        $articles->setState('load_tags', false);

        // Access filter
        $access     = !ComponentHelper::getParams('com_content')->get('show_noauth');
        $authorised = Access::getAuthorisedViewLevels($app->getIdentity() ? $app->getIdentity()->id : 0);
        $articles->setState('filter.access', $access);

        // Category filter
        $articles->setState('filter.category_id.include', (bool) $params->get('category_filtering_type', 1));
        if ($catids) {
            $articles->setState('filter.category_id', $catids);
        }

        // Ordering
        $ordering = $params->get('article_ordering', 'a.ordering');

        switch ($ordering) {
            case 'random':
                $articles->setState('list.ordering', Factory::getDbo()->getQuery(true)->rand());
                break;

            case 'rating_count':
            case 'rating':
                $articles->setState('list.ordering', $ordering);
                $articles->setState('list.direction', $params->get('article_ordering_direction', 'ASC'));

                if (!PluginHelper::isEnabled('content', 'vote')) {
                    $articles->setState('list.ordering', 'a.ordering');
                }

                break;

            default:
                $articles->setState('list.ordering', $ordering);
                $articles->setState('list.direction', $params->get('article_ordering_direction', 'ASC'));
                break;
        }

        // Filter by language
        $articles->setState('filter.language', $app->getLanguageFilter());

        // Filter by multiple tags
        $articles->setState('filter.tag', $params->get('filter_tag', []));

        // Featured switch
        $featured = $params->get('show_featured', '');

        $articles->setState('filter.featured', $params->get('show_front', 'show'));
        $excluded_articles = $params->get('excluded_articles', '');


        // Check if we should trigger additional plugin events
        if ($excluded_articles) {
            $excluded_articles = explode("\r\n", $excluded_articles);
            $articles->setState('filter.article_id', $excluded_articles);

            // Exclude
            $articles->setState('filter.article_id.include', false);
        }

        if ($selectedYear > 0) {
            $articles->setState('filter.date_filtering', 'range');
            $articles->setState('filter.date_field', $params->get('date_field', 'a.created'));
            $articles->setState('filter.start_date_range', $selectedYear.'-01-01 00:00:00');
            $articles->setState('filter.end_date_range', $selectedYear.'-12-31 23:59:59');
        }
//        $date_filtering = $params->get('date_filtering', 'off');
//
//        if ($date_filtering !== 'off') {
//            $articles->setState('filter.date_filtering', $date_filtering);
//            $articles->setState('filter.date_field', $params->get('date_field', 'a.created'));
//            $articles->setState('filter.start_date_range', $params->get('start_date_range', '1000-01-01 00:00:00'));
//            $articles->setState('filter.end_date_range', $params->get('end_date_range', '9999-12-31 23:59:59'));
//            $articles->setState('filter.relative_date', $params->get('relative_date', 30));
//        }

        // Retrieve Content
        $items = $articles->getItems();

        $show_date        = $params->get('show_date', 0);
        $show_date_field  = $params->get('show_date_field', 'created');
        $show_date_format = $params->get('show_date_format', 'Y-m-d');
        $show_category    = $params->get('show_category', 0);
        $show_introtext   = $params->get('show_introtext', 0);
        $introtext_limit  = $params->get('introtext_limit', 100);


        foreach ($items as &$item) {
            $item->readmore = \strlen(trim($item->fulltext));
            $item->slug     = $item->id . ':' . $item->alias;

            if ($access || \in_array($item->access, $authorised)) {
                // We know that user has the privilege to view the article
                $item->link     = Route::_(RouteHelper::getArticleRoute($item->slug, $item->catid, $item->language));
                $item->linkText = Text::_('MOD_ARTICLES_NEWS_READMORE');
                $urls = json_decode($item->urls);
                if ($urls && (!empty($urls->urla) || !empty($urls->urlb) || !empty($urls->urlc)))  {
                    $urlarray = [
                        [$urls->urla, $urls->urlatext, $urls->targeta, 'a'],
                        [$urls->urlb, $urls->urlbtext, $urls->targetb, 'b'],
                        [$urls->urlc, $urls->urlctext, $urls->targetc, 'c']
                    ];
                    foreach ($urlarray as $url) {
                        $item->link = $url[0];
                        $item->link_title = (!empty($url[1])) ? $url[1] : $item->title;
                        $item->link_target = (!empty($url[2])) ? $url[2] : "_self";

                        if (!empty($item->link)) break;
                    }
                }


            } else {
                $item->link = new Uri(Route::_('index.php?option=com_users&view=login', false));
                $item->link->setVar('return', base64_encode(RouteHelper::getArticleRoute($item->slug, $item->catid, $item->language)));
                $item->linkText = Text::_('MOD_ARTICLES_NEWS_READMORE_REGISTER');
            }
            if ($show_date) {
                $item->displayDate = HTMLHelper::_('date', $item->$show_date_field, $show_date_format);
            }

            if ($item->catid) {
                $item->displayCategoryLink  = Route::_(RouteHelper::getCategoryRoute($item->catid, $item->category_language));
                $item->displayCategoryTitle = $show_category ? '<a href="' . $item->displayCategoryLink . '">' . $item->category_title . '</a>' : '';
            } else {
                $item->displayCategoryTitle = $show_category ? $item->category_title : '';
            }

            if ($show_introtext) {
                $item->introtext = self::_cleanIntrotext($item->introtext);
            }
            $item->displayIntrotext = $show_introtext ? self::truncate($item->introtext, $introtext_limit) : '';


            // Remove any images belongs to the text
            if (!$params->get('image')) {
                $item->introtext = preg_replace('/<img[^>]*>/', '', $item->introtext);
            }

            // Show the Intro/Full image field of the article
            if ($params->get('img_intro_full') !== 'none') {
                $images = json_decode($item->images);
                $item->imageSrc = '';
                $item->imageAlt = '';
                $item->imageCaption = '';

                if (!empty($images->image_intro)) {
                    $item->imageSrc = htmlspecialchars($images->image_intro, ENT_COMPAT, 'UTF-8');
                    $item->imageAlt = htmlspecialchars($images->image_intro_alt, ENT_COMPAT, 'UTF-8');

                    if ($images->image_intro_caption) {
                        $item->imageCaption = htmlspecialchars($images->image_intro_caption, ENT_COMPAT, 'UTF-8');
                    }
                } elseif (!empty($images->image_fulltext)) {
                    $item->imageSrc = htmlspecialchars($images->image_fulltext, ENT_COMPAT, 'UTF-8');
                    $item->imageAlt = htmlspecialchars($images->image_fulltext_alt, ENT_COMPAT, 'UTF-8');

                    if ($images->image_intro_caption) {
                        $item->imageCaption = htmlspecialchars($images->image_fulltext_caption, ENT_COMPAT, 'UTF-8');
                    }
                }


                //$item->imageSrc = images/announcements/2024/japan-minister-visit/jimi-visit-cover.jpg#joomlaImage://local-images/announcements/2024/japan-minister-visit/
                //Remove the # part before checking if it is a file
                $item->imageSrc = HTMLHelper::cleanImageURL($item->imageSrc)->url;

                if (empty($item->imageSrc) || !is_file($item->imageSrc)) {
                    //file_put_contents(JPATH_SITE.'/tmp/images.txt', print_r($item->title." - ".$item->imageSrc, true).PHP_EOL , FILE_APPEND | LOCK_EX);
                    $item->imageSrc = "images/news/".$item->category_alias.".jpg";
                }
            }
        }

        return $items;
    }

    /**
     * Strips unnecessary tags from the introtext
     *
     * @param   string  $introtext  introtext to sanitize
     *
     * @return mixed|string
     *
     * @since  1.6
     */
    public static function _cleanIntrotext($introtext)
    {
        $introtext = str_replace(['<p>', '</p>'], ' ', $introtext);
        $introtext = strip_tags($introtext);
        $introtext = trim($introtext);

        return $introtext;
    }


    public static function getIncludedCategoriesIDs(Registry &$params, array $selectedCatIDs) {
        $app     = Factory::getApplication();
        $catids = (count($selectedCatIDs)>0 && $selectedCatIDs[0] > 0)? $selectedCatIDs : $params->get('catid');

        if (is_array($catids) && count($catids) > 0) {
            if ($params->get('show_child_category_articles', 0) && (int) $params->get('levels', 0) > 0) {
                // Get an instance of the generic categories model
                $factory = $app->bootComponent('com_content')->getMVCFactory();
                $categories = $factory->createModel('Categories', 'Site', ['ignore_request' => true]);
                $categories->setState('params', $app->getParams());
                $access     = !ComponentHelper::getParams('com_content')->get('show_noauth');


                $levels = $params->get('levels', 1) ?: 9999;
                $categories->setState('filter.get_children', $levels);
                $categories->setState('filter.published', 1);
                $categories->setState('filter.access', $access);
                $additional_catids = [];
                foreach ($catids as $catid) {
                    $categories->setState('filter.parentId', $catid);
                    $recursive = true;
                    $items     = $categories->getItems($recursive);
                    if ($items) {
                        foreach ($items as $category) {
                            $condition = (($category->level - $categories->getParent()->level) <= $levels);
                            if ($condition) {
                                $additional_catids[] = $category->id;
                            }
                        }
                    }
                }
                $catids = array_unique(array_merge($catids, $additional_catids));
            }
        }
        return $catids;
    }
    /**
     * Method to truncate introtext
     *
     * The goal is to get the proper length plain text string with as much of
     * the html intact as possible with all tags properly closed.
     *
     * @param   string   $html       The content of the introtext to be truncated
     * @param   integer  $maxLength  The maximum number of characters to render
     *
     * @return  string  The truncated string
     *
     * @since   1.6
     */
    public static function truncate($html, $maxLength = 0)
    {
        $baseLength = \strlen($html);

        // First get the plain text string. This is the rendered text we want to end up with.
        $ptString = HTMLHelper::_('string.truncate', $html, $maxLength, $noSplit = true, $allowHtml = false);

        for ($maxLength; $maxLength < $baseLength;) {
            // Now get the string if we allow html.
            $htmlString = HTMLHelper::_('string.truncate', $html, $maxLength, $noSplit = true, $allowHtml = true);

            // Now get the plain text from the html string.
            $htmlStringToPtString = HTMLHelper::_('string.truncate', $htmlString, $maxLength, $noSplit = true, $allowHtml = false);

            // If the new plain text string matches the original plain text string we are done.
            if ($ptString === $htmlStringToPtString) {
                return $htmlString;
            }

            // Get the number of html tag characters in the first $maxlength characters
            $diffLength = \strlen($ptString) - \strlen($htmlStringToPtString);

            // Set new $maxlength that adjusts for the html tags
            $maxLength += $diffLength;

            if ($baseLength <= $maxLength || $diffLength <= 0) {
                return $htmlString;
            }
        }

        return $html;
    }


}
