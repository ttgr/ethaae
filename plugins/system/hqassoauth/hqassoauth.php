<?php

/**
 * @package     Joomla.System
 * @subpackage  plg_system_miniorangeoauth
 *
 * @copyright   Copyright (C) 2005 - 2015 Open Source Matters, Inc. All rights reserved.
 * @license     GNU General Public License version 2 or later; see LICENSE.txt
 */

// no direct access
defined('_JEXEC') or die('Restricted access');


use Joomla\CMS\Factory;
use Joomla\CMS\Log\Log;
use Joomla\CMS\Plugin\CMSPlugin;
use \Joomla\CMS\Uri\Uri;
use Joomla\CMS\User\UserFactoryInterface;
use Joomla\CMS\Mail\MailerFactoryInterface;
use Joomla\CMS\Component\ComponentHelper;
use Joomla\CMS\User\UserHelper;
use Joomla\CMS\User\User;

class plgSystemHqassoauth extends CMSPlugin
{

    public function onAfterInitialise()
    {
        Log::addLogger(array('text_file' => 'com_ethaae_hqalogin.log.php'), Log::ALL, array('com_ethaae_hqalogin'));

        $params['app_name'] = $this->params->get('app_name');
        $params['client_id'] = $this->params->get('client_id');
        $params['client_secret'] = $this->params->get('client_secret');
        $params['scope'] = $this->params->get('scope');
        $params['authorize_endpoint'] = $this->params->get('authorize_endpoint');
        $params['access_token_endpoint'] = $this->params->get('access_token_endpoint');
        $params['endsession_endpoint'] = $this->params->get('endsession_endpoint');

        try {
            $app = Factory::getApplication();
        } catch (\Exception $e) {
            Log::add('Unable to Initialise Joomla App:: '.$e->getMessage(), Log::ERROR, 'com_ethaae_hqalogin');
            return [];
        }


        $return_url = strtok(Uri::getInstance()->toString(), '?');
        $uri = Uri::getInstance()->toString();
        $get = $app->input->get->getArray();
        $session = $app->getSession();

        if (isset($get['hqarequest']) and $get['hqarequest'] == 'ssologout') {
            $authorizationUrl = $params['endsession_endpoint'] . "?client_id=" . $params['client_id'] . "&post_logout_redirect_uri=" . $return_url;
            $this->logoutCurrentUser();
            //header('Location: ' . $authorizationUrl);
            $app->redirect($authorizationUrl);
        }


        if (isset($get['hqarequest']) and $get['hqarequest'] == 'ssologin') {
            $state = base64_encode($params['app_name']);
            $authorizationUrl = $params['authorize_endpoint'] . "?client_id=" . $params['client_id'] . "&scope=" . $params['scope'] . "&redirect_uri=" . $return_url . "&response_type=code&state=" . $state;
            if (session_id() == '' || !isset($session))
                session_start();
            $session->set('oauth2state', $state);
            $session->set('appname', $params['app_name']);
            header('Location: ' . $authorizationUrl);
            exit;
        } else if (isset($get['code'])) {

            if (session_id() == '' || !isset($session))
                session_start();


            if (!isset($get['code'])) {
                if (isset($get['error_description']))
                    exit($get['error_description']);
                else if (isset($get['error']))
                    exit($get['error']);
                exit('Invalid response');
            } else {
                try {
                    $session_var = $session->get('appname');
                    if (isset($session_var) && !empty($session_var))
                        $currentappname = $session->get('appname');
                    else if (isset($get['state']) && !empty($get['state']))
                        $currentappname = base64_decode($get['state']);

                    if (empty($currentappname)) {
                        exit('No request found for this application.');
                    }

                    $accessToken = $this->getAccessToken($params['access_token_endpoint'], 'authorization_code', $params['client_id'], $params['client_secret'], $get['code'], $return_url);
//                    file_put_contents(JPATH_SITE.'/tmp/token.txt', print_r($accessToken, true));
                    if ($accessToken == false) {
                        //JFactory::getApplication()->enqueueMessage(JText::_('The user does not exist in the current App'), 'error');
                        Log::add('Unable retrieve AccessToken::' . json_encode($accessToken), Log::ERROR, 'com_ethaae_hqalogin');
                        return false;
                    }
                    $email = $accessToken['email'];
                    $name = $accessToken['name'];


                    if (empty($email)) {
                        Log::add('Email address not received. Check your <b>Attribute Mapping</b> configuration', Log::ERROR, 'com_ethaae_hqalogin');
                        $app->enqueueMessage('Empty Mail received by HQA SSO', 'error');
                        return false;
                    }


                    $checkUser = $this->get_user_from_joomla($email);
                    if ($checkUser) {
                        $this->loginCurrentUser($checkUser, $name, $email);
                    } else {
                        $fromFaculty = $this->isFromFaculty($accessToken);
                        $user = $this->createJoomlaUser($name, $email, $fromFaculty);
                        $this->loginCurrentUser($user, $name, $email);
                    }
                } catch (Exception $e) {
                    Log::add('Generic Error on Login User: '.$name. ' '.$email.' Msg:'.$e->getMessage() , Log::ERROR, 'com_ethaae_hqalogin');
                    $app->enqueueMessage($e->getMessage(), 'error');

                }

            }

        }


    }


    function onExtensionBeforeUninstall($id)
    {

    }


    function getAccessToken($tokenendpoint, $grant_type, $clientid, $clientsecret, $code, $redirect_url)
    {
        Log::addLogger(array('text_file' => 'com_ethaae_hqalogin.log.php'), Log::ALL, array('com_ethaae_hqalogin'));
        try {
            $app = Factory::getApplication();
        } catch (\Exception $e) {
            Log::add('Unable to Initialise Joomla App:: '.$e->getMessage(), Log::ERROR, 'com_ethaae_hqalogin');
            return [];
        }

        $ch = curl_init($tokenendpoint);
        curl_setopt($ch, CURLOPT_FOLLOWLOCATION, true);
        curl_setopt($ch, CURLOPT_ENCODING, "UTF-8");
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
        curl_setopt($ch, CURLOPT_AUTOREFERER, true);
        curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, false);
        curl_setopt($ch, CURLOPT_MAXREDIRS, 10);
        curl_setopt($ch, CURLOPT_POST, true);
        curl_setopt($ch, CURLOPT_HTTPHEADER, array(
            'Accept: application/json'
        ));


        curl_setopt($ch, CURLOPT_POSTFIELDS, 'redirect_uri=' . urlencode($redirect_url) . '&grant_type=' . $grant_type . '&client_id=' . $clientid . '&client_secret=' . $clientsecret . '&code=' . $code);
        $content = curl_exec($ch);

        if (curl_error($ch)) {
            exit(curl_error($ch));
        }


        $content = json_decode($content, true);
        //file_put_contents(JPATH_SITE.'/tmp/token.txt', print_r($s, true).PHP_EOL , FILE_APPEND | LOCK_EX);
        if (!isset($content["access_token"])) {
            Log::add('Generic Error form OAuth Provider: No Token' , Log::ERROR, 'com_ethaae_hqalogin');
            return false;
        }
        $s[0] = explode('.', $content["access_token"])[1];
        $s[1] = json_decode(base64_decode(str_replace(array('-', '_'), array('+', '/'), $s[0])), true);
        $content["access_token"] = $s[1];

        if (isset($content["error_description"])) {
            Log::add('Generic Error form OAuth Provider: '.$content["error_description"] , Log::ERROR, 'com_ethaae_hqalogin');
            return false;
        } else if (isset($content["error"])) {
            Log::add('Generic Error form OAuth Provider: '.$content["error"] , Log::ERROR, 'com_ethaae_hqalogin');
            return false;
        } else if (isset($content["access_token"])) {
            return $content["access_token"];
        } else {
            Log::add('Invalid response received from OAuth Provider ' , Log::ERROR, 'com_ethaae_hqalogin');
            return false;
        }
    }


    public function isFromFaculty($access_token)
    {
        if (isset($access_token["eduPersonAffiliation"]) &&
            is_array($access_token["eduPersonAffiliation"]) &&
            in_array('faculty', $access_token["eduPersonAffiliation"])) {
            return true;
        }
        if (isset($access_token["eduPersonPrimaryAffiliation"]) &&
            !empty($access_token["eduPersonPrimaryAffiliation"]) &&
            $access_token["eduPersonPrimaryAffiliation"] == 'faculty') {
            return true;
        }
        return false;
    }

    function get_user_from_joomla($email)
    {
        //Check if email exist in database
        $db = Factory::getContainer()->get('DatabaseDriver');
        $query = $db->getQuery(true)
            ->select('id')
            ->from('#__users')
            ->where('email=' . $db->quote($email))
            ->where('block=0');
        $db->setQuery($query);
        return $db->loadObject();
    }

    function loginCurrentUser(object $checkUser, string $name, string $email): void
    {
        $url = Uri::getInstance()->toString();

        $ui = (strpos($url, 'administrator') !== false) ? 'administrator' : 'site';


        if ($ui == 'administrator') {
            $app = Factory::getApplication('administrator');
        } else {
            $app = Factory::getApplication('site');
        }
        $user   = ($checkUser->id === null)
            ? $app->getIdentity()
            : Factory::getContainer()->get(UserFactoryInterface::class)->loadUserById($checkUser->id);
        $this->updateCurrentUserName($user->id, $name);

        $session = $app->getSession(); #Get current session vars
        // Register the needed session variables
        $session->set('user',$user);

        //$app->checkSession();
        $sessionId = $session->getId();
        $this->updateUsernameToSessionId($user->id, $user->username, $sessionId);

        $user->setLastVisit();

        if ($ui == 'administrator') {
            $app->redirect(URI::root() . 'administrator/index.php?');
        } else {
            //No need to make redirection
            //$app->redirect(JURI::root().'index.php?');
        }
    }

    function logoutCurrentUser(): void
    {
        $url = Uri::getInstance()->toString();
        $ui = (strpos($url, 'administrator') !== false) ? 'administrator' : 'site';

        if ($ui == 'administrator') {
            $app = Factory::getApplication('administrator');
        } else {
            $app = Factory::getApplication('site');
        }
        $user = $app->getIdentity();
        $app->logout($user->id);

//        if ($ui == 'administrator') {
//            $app->redirect(URI::root() . 'administrator/index.php?');
//        }

    }

    function updateCurrentUserName($id, $name): void
    {
        if(empty($name)){
            return;
        }
        $db = Factory::getContainer()->get('DatabaseDriver');
        $query = $db->getQuery(true);
        $fields = array(
            $db->quoteName('name') . ' = ' . $db->quote($name),
        );
        $conditions = array(
            $db->quoteName('id') . ' = ' . $db->quote($id) ,
        );
        $query->update($db->quoteName('#__users'))->set($fields)->where($conditions);
        $db->setQuery($query);
        $db->execute();
    }

    function updateUsernameToSessionId($userID, $username, $sessionId): void
    {
        $db = Factory::getContainer()->get('DatabaseDriver');
        $query = $db->getQuery(true);
        $fields = array(
            $db->quoteName('username') . ' = ' . $db->quote($username),
            $db->quoteName('guest') . ' = ' . $db->quote('0'),
            $db->quoteName('userid') . ' = ' . $db->quote($userID),
        );
        $conditions = array(
            $db->quoteName('session_id') . ' = ' . $db->quote($sessionId),
        );
        $query->update($db->quoteName('#__session'))->set($fields)->where($conditions);
        $db->setQuery($query);
        $db->execute();
    }

    public function createJoomlaUser($name, $mail, $fromFaculty = false)
    {
        Log::addLogger(array('text_file' => 'com_ethaae_users.log.php'), Log::ALL, array('com_ethaae_users'));
        $password = self::getUserPassword();
        $username = self::getUsername($mail);
        //2 = Registered Group  11 = Forum Users
        $usergroupID = $fromFaculty ? 11 : 2;

        $userData = [
            'name'         => $name,
            'username'     => $username,
            'password'     => $password,
            'password2'    => $password,
            'email'        => $mail,
            'block'        => 0,
            'groups'       => [$usergroupID],
            'activation'   => '',
            'requireReset' => 0,
            'sendEmail'    => 1,
            'registerDate' => date('Y-m-d H:i:s'),
        ];


        $joomlaUser = new User();

        if (!$joomlaUser->bind($userData))
        {
            Log::add('Unable to bind: USER: ' . $name . ' ' . $mail . " " . $joomlaUser->getError(), Log::ERROR, 'com_ethaae_users');
            return false;
        }

        $table                  = $joomlaUser->getTable();
        $joomlaUser->params     = '{}';
        $table->bind($joomlaUser->getProperties());
        try
        {
            if (!$table->check())
            {
                Log::add('Unable to bind: USER: ' . $name . ' ' . $mail . " " . $table->getError(), Log::ERROR, 'com_ethaae_users');
                return false;
            }
            $result = $table->store();
            Log::add('Created New User: ' . $name . ' ' . $mail . " ", Log::ERROR, 'com_ethaae_users');
        }
        catch (\Exception $e)
        {
            Log::add('Unable to Save: USER: ' . $name . ' ' . $mail . " " . $e->getMessage(), Log::ERROR, 'com_ethaae_users');

            return false;
        }
        //file_put_contents(JPATH_SITE.'/tmp/joomlaUser.txt', print_r($joomlaUser, true).PHP_EOL , FILE_APPEND | LOCK_EX);

        //Send notification Mail
        $subject = $fromFaculty ? 'ΕΘΑΑΕ :: Νέος Forum User' : 'ΕΘΑΑΕ :: Νέος Χρήστης - προς ενεργοποίηση';
        $body = "Ο Χρήστης: " . $name . " με mail: " . $mail . " εγγράφηκε στο Site";

        self::sendMail(['forum@ethaae.gr'], $subject, $body,['tasos.tr@outlook.com']);
        return $joomlaUser;


    }

    public function getUsername(string $mail) : string
    {

        $t = explode("@", $mail);
        $username = $t[0];
        $username = strtolower($username);
        $username = strtolower($username);
        $username = str_replace(",", "", $username);
        $username = str_replace("-", "", $username);
        $username = str_replace("'", "", $username);
        $username = str_replace(".", "", $username);
        return $username;
    }


    public function sendMail(array $mailTo,string $subject,string $body,array $mailBcc = []) : bool
    {
        $mailer = Factory::getContainer()->get(MailerFactoryInterface::class)->createMailer();
        $sender = [Factory::getApplication()->get('mailfrom'), Factory::getApplication()->get('fromname')];
        $mailer->setSender($sender);

        foreach ($mailTo as $mail) {
            $mailer->addRecipient($mail);
        }

        foreach ($mailBcc as $mail) {
            $mailer->addBcc($mail);
        }


        $mailer->setSubject($subject);
        $mailer->setBody($body);
        $mailer->isHTML(true);
        $mailer->Encoding = 'base64';
        $send             = $mailer->Send();
        if ($send !== true)
        {
            return false;
        } else {
            return true;
        }
    }

    public function getUserPassword()
    {
        $joomlaUsersparams = ComponentHelper::getParams('com_users');
        $minimum_integers  = $joomlaUsersparams->get('minimum_integers');
        $minimum_symbols   = $joomlaUsersparams->get('minimum_symbols');
        $minimum_uppercase = $joomlaUsersparams->get('minimum_uppercase');
        $length            = 8;
        if ($joomlaUsersparams->get('minimum_length') > 8) $length = $joomlaUsersparams->get('minimum_length');
        $tryCount = 0;
        do
        {
            $password = UserHelper::genrandompassword($length);
            $tryCount++;
            if ($tryCount > 50) break;
        } while (self::getConditionPassword($password, $minimum_integers, $minimum_symbols, $minimum_uppercase));

        return $password;
    }

    private function getConditionPassword($password, $minimum_integers, $minimum_symbols, $minimum_uppercase): bool
    {
        $notEnoughInt       = !empty($minimum_integers) && preg_match_all("/[0-9]/", $password, $out) < $minimum_integers;
        $notEnoughSymbols   = !empty($minimum_symbols) && preg_match_all("/[a-z]/", $password, $out) < $minimum_symbols;
        $notEnoughUpperCase = !empty($minimum_uppercase) && preg_match_all("/[A-Z]/", $password, $out) < $minimum_uppercase;

        return $notEnoughInt || $notEnoughSymbols || $notEnoughUpperCase;
    }


}

