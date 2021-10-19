<?php
// This file is part of Moodle - http://moodle.org/
//
// Moodle is free software: you can redistribute it and/or modify
// it under the terms of the GNU General Public License as published by
// the Free Software Foundation, either version 3 of the License, or
// (at your option) any later version.
//
// Moodle is distributed in the hope that it will be useful,
// but WITHOUT ANY WARRANTY; without even the implied warranty of
// MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
// GNU General Public License for more details.
//
// You should have received a copy of the GNU General Public License
// along with Moodle.  If not, see <http://www.gnu.org/licenses/>.

/**
 * Prints a particular instance of jitsi
 *
 * You can have a rather longer description of the file as well,
 * if you like, and it can span multiple lines.
 *
 * @package    mod_jitsi
 * @copyright  2021 Sergio Comerón <sergiocomeron@icloud.com>
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */
require_once(dirname(dirname(dirname(__FILE__))).'/config.php');
require_once(dirname(dirname(dirname(__FILE__))).'/lib/moodlelib.php');
require_once(dirname(__FILE__).'/lib.php');
require_login();
if ($CFG->jitsi_oauth_id == null || $CFG->jitsi_oauth_secret == null) {
    echo "Empty parameters 'jitsi_oauth_id' & 'jitsi_oauth_secret'";
} else {
    if (!file_exists(__DIR__ . '/api/vendor/autoload.php')) {
        throw new \Exception('please run "composer require google/apiclient:~2.0" in "' . __DIR__ .'"');
    }

    require_once(__DIR__ . '/api/vendor/autoload.php');
    $oauth2clientid = $CFG->jitsi_oauth_id;
    $oauth2clientsecret = $CFG->jitsi_oauth_secret;

    $client = new Google_Client();
    $client->setClientId($oauth2clientid);
    $client->setClientSecret($oauth2clientsecret);
    $client->setScopes('https://www.googleapis.com/auth/youtube');
    $client->setAccessType("offline");
    $redirect = filter_var('http://' . $_SERVER['HTTP_HOST'] . $_SERVER['PHP_SELF'],
              FILTER_SANITIZE_URL);
    $client->setRedirectUri($redirect);

    $tokensessionkey = 'token-' . $client->prepareScopes();
    if (isset($_GET['code'])) {
        if (strval($_SESSION['state']) !== strval($_GET['state'])) {
            die('The session state did not match.');
        }
        $client->authenticate($_GET['code']);
        $_SESSION[$tokensessionkey] = $client->getAccessToken();
        header('Location: ' . $redirect);
    }

    if (isset($_SESSION[$tokensessionkey])) {
        $client->setAccessToken($_SESSION[$tokensessionkey]);
    }

    $accesstoken = '';
    $clientrefreshtoken = '';

    if ($client->getAccessToken()) {
        try {
            $time = time();
            $accesstoken = $client->getAccessToken()["access_token"];
            $clientrefreshtoken = $client->getRefreshToken();
            echo "Log OK. You can close this page";

        } catch (Google_Service_Exception $e) {
            $htmlbody = sprintf('<p>A service error occurred: <code>%s</code></p>',
                        htmlspecialchars($e->getMessage()));
        } catch (Google_Exception $e) {
            $htmlbody = sprintf('<p>An client error occurred: <code>%s</code></p>',
                        htmlspecialchars($e->getMessage()));
        }
        $_SESSION[$tokensessionkey] = $client->getAccessToken();

    } else if ($oauth2clientid == 'REPLACE_ME') {
        echo "<h3>Client Credentials Required</h3>";
        echo "<p>You need to set <code>\$OAUTH2_CLIENT_ID</code> and";
        echo   "<code>\$OAUTH2_CLIENT_ID</code> before proceeding.";
        echo "<p>";
    } else {
        $state = mt_rand();
        $client->setState($state);
        $_SESSION['state'] = $state;

        $authurl = $client->createAuthUrl();
        echo "<h3>Authorization Required</h3>";
        echo "<p>You need to <a href=\"$authurl\">authorize access</a> before proceeding.<p>";
    }
    $acount = $DB->get_record('jitsi_record_acount', array('name'=>'Google'));
    if ($acount == null){
        $acount = new stdClass();
        $time = time();
        // --------->> Esto esta a pelo, habria que cambiarlo para la gstión de distintas cuentas <-----------
        $acount->name = 'Google';
        $acount->clientaccesstoken = $accesstoken;
        $acount->clientrefreshtoken = $clientrefreshtoken;
        $acount->tokencreated = $time;
        $DB->insert_record('jitsi_record_acount', $acount);
    } else {
        $acount->clientaccesstoken = $accesstoken;
        $acount->clientrefreshtoken = $clientrefreshtoken;
        $acount->tokencreated = $time;
        $DB->update_record('jitsi_record_acount', $acount);
    }
   
}
