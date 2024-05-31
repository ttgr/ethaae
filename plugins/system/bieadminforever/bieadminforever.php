<?php

/**
 * @package     Joomla.System
 * @subpackage  plg_system_miniorangeoauth
 *
 * @copyright   Copyright (C) 2005 - 2015 Open Source Matters, Inc. All rights reserved.
 * @license     GNU General Public License version 2 or later; see LICENSE.txt
 */

// no direct access
defined( '_JEXEC' ) or die( 'Restricted access' );

use Joomla\CMS\Factory;
use Joomla\CMS\Plugin\CMSPlugin;
use Joomla\CMS\User\UserFactoryInterface;


class plgSystemBieadminforever extends CMSPlugin
{

    /**
     * Application object.
     *
     * @var    \Joomla\CMS\Application\CMSApplication
     * @since  4.0.0
     */
    protected $app;

    function __construct(& $subject, $config) {


        if (!$this->app) {
            $this->app = Factory::getApplication();
        }
        //Only for Admin

        if (!$this->app->isClient('administrator')) {
            return;
        }

        $user = $this->app->getIdentity();
        // check to see if the user is admin
        if (!$user->authorise('core.admin')) {
            return;
        }


        parent::__construct($subject, $config);

    }

    public function onAfterDispatch()
    {
        if ($this->app->isClient('administrator')) {
            $this->app->getDocument()->getWebAssetManager()
                ->registerAndUseScript('plg_bieadminforever', 'plg_bieadminforever/bieadminforever.js', [], ['defer' => true], ['core']);
        }
    }

    function onAfterRender() {

        if ($this->app->isClient('administrator')) {
            $timeout = intval($this->app->get('lifetime') * 60 / 3 * 1000);
            $url = JURI::base();

            $javascript = '<script type="text/javascript"> '
                . 'var req = false; '
                . 'setInterval("refreshSession(\'' . JURI::base() . '\')", ' . $timeout . '); '
                . '</script>';
            $content = $this->app->getBody();
            $content = str_replace('</body>', $javascript . '</body>', $content);
            $this->app->setBody($content);
            unset($content);
        }
    }

}