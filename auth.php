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
require_once(__DIR__ . '/api/vendor/autoload.php');

require_login();
global $DB, $CFG;
$name = optional_param('name', null, PARAM_TEXT);

$PAGE->set_context(context_system::instance());

$tokensessionkey = 'token-' . "https://www.googleapis.com/auth/youtube";
if ($name) {
    if (!file_exists(__DIR__ . '/api/vendor/autoload.php')) {
        throw new \Exception('Api client not found on '.$CFG->wwwroot.'/mod/jitsi/api/vendor/autoload.php');
    }

    $acountinuse = $DB->get_record('jitsi_record_acount', array('inuse' => 1));
   
    if ($acountinuse) {
        $client = new Google_Client();
        $client->setClientId($CFG->jitsi_oauth_id);
        $client->setClientSecret($CFG->jitsi_oauth_secret);
    
        $tokensessionkey = 'token-' . "https://www.googleapis.com/auth/youtube";
        $client->setAccessToken($acountinuse->clientaccesstoken);
        unset($_SESSION[$tokensessionkey]);
    
        $t = time();
        $timediff = $t - $acountinuse->tokencreated;
    
        if ($timediff > 3599) {
            $newaccesstoken = $client->fetchAccessTokenWithRefreshToken($acount->clientrefreshtoken);
    
            $acountinuse->clientaccesstoken = $newaccesstoken['access_token'];
            $newrefreshaccesstoken = $client->getRefreshToken();
            $acountinuse->refreshtoken = $newrefreshaccesstoken;
            $acountinuse->tokencreated = time();
            $DB->update_record('jitsi_record_acount', $acountinuse);
        }
    
        $client->revokeToken($acountinuse->clientaccesstoken);


        $acountinuse->inuse = 0;
        $DB->update_record('jitsi_record_acount', $acountinuse);
    }

    $_SESSION['name'] = $name;
}

$acounttab = $DB->get_record('jitsi_record_acount', array('name' => $_SESSION['name']));
if (!$acounttab) {
    $_SESSION[$tokensessionkey] = null;
}

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

    $httparray = explode(":", $CFG->wwwroot);
    $principiohttp = $httparray[0].'://';

    $redirect = filter_var($principiohttp . $_SERVER['HTTP_HOST'] . $_SERVER['PHP_SELF'],
              FILTER_SANITIZE_URL);
    $client->setRedirectUri($redirect);

    $tokensessionkey = 'token-' . $client->prepareScopes();

    if (isset($_GET['code'])) {
        $paramstring = base64UrlDecode($_GET['state']);
        $paramarray = explode("&", $paramstring);
        $randstring = $paramarray[1];
        $namestring = $paramarray[0];
        $randarray = explode("=", $randstring);
        $namearray = explode("=", $namestring);
        $rand = $randarray[1];
        $name = $namearray[1];

        if (strval($_SESSION['rand']) !== strval($rand)) {
            die('The session state did not match.');
        }
        $client->authenticate($_GET['code']);
        $_SESSION[$tokensessionkey] = $client->getAccessToken();

        header('Location: ' . $redirect);
    }

    if (isset($_SESSION[$tokensessionkey])) {
        $client->setAccessToken($_SESSION[$tokensessionkey]);
    }

    if ($client->getAccessToken()) {
        try {
            $PAGE->set_url('/mod/jitsi/auth.php');
            $PAGE->set_title(format_string(get_string('acounts', 'jitsi')));
            $PAGE->set_heading(format_string(get_string('acounts', 'jitsi')));
            echo $OUTPUT->header();


            $accesstoken = $client->getAccessToken()["access_token"];
            $clientrefreshtoken = $client->getRefreshToken();
            echo $OUTPUT->box(get_string('acountconnected', 'jitsi'));
            $link = new moodle_url('/mod/jitsi/adminaccounts.php');
            echo '<a href='.$link.'>'.get_string('back').'</a>';

            $acount = $DB->get_record('jitsi_record_acount', array('name' => $_SESSION['name']));

            if ($acount == null) {
                $acount = new stdClass();

                $time = time();

                $acount->name = $_SESSION['name'];
                $acount->clientaccesstoken = $accesstoken;
                $acount->clientrefreshtoken = $clientrefreshtoken;
                $acount->tokencreated = $time;
                $acount->inuse = 1;
                $DB->insert_record('jitsi_record_acount', $acount);
            } else {
                $time = time();
                $acount->clientaccesstoken = $accesstoken;
                $acount->clientrefreshtoken = $clientrefreshtoken;
                $acount->tokencreated = $time;
                $acount->inuse = 1;
                $DB->update_record('jitsi_record_acount', $acount);
            }

        } catch (Google_Service_Exception $e) {
            $htmlbody = sprintf('<p>A service error occurred: <code>%s</code></p>',
                        htmlspecialchars($e->getMessage()));
        } catch (Google_Exception $e) {
            $htmlbody = sprintf('<p>An client error occurred: <code>%s</code></p>',
                        htmlspecialchars($e->getMessage()));
        }
        $_SESSION[$tokensessionkey] = $client->getAccessToken();

        echo $OUTPUT->footer();

    } else if ($oauth2clientid == 'REPLACE_ME') {
        echo "<h3>Client Credentials Required</h3>";
        echo "<p>You need to set <code>\$OAUTH2_CLIENT_ID</code> and";
        echo   "<code>\$OAUTH2_CLIENT_ID</code> before proceeding.";
        echo "<p>";
    } else {
        $PAGE->set_url('/mod/jitsi/auth.php');
        $PAGE->set_title(format_string(get_string('acounts', 'jitsi')));
        $PAGE->set_heading(format_string(get_string('acounts', 'jitsi')));
        echo $OUTPUT->header();


        $rand = mt_rand();
        $stateparameters = 'name='.$name.'&rand='.$rand;
        $state = base64UrlEncode($stateparameters);
        $client->setState($state);
        $_SESSION['rand'] = $rand;

        $authurl = $client->createAuthUrl();
        echo "<h3>Authorization Required</h3>";
        echo "<p>You need to <a href=\"$authurl\">authorize access</a> before proceeding.<p>";

        echo $OUTPUT->footer();
    }

}
