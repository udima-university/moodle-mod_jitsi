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
global $CFG, $DB;
$acount = $DB->get_record('jitsi_record_acount', array('name'=>'Google'));

if ($CFG->jitsi_oauth_id == null || $CFG->jitsi_oauth_secret == null) {
    echo "Empty parameters 'jitsi_oauth_id' & 'jitsi_oauth_secret'";
} else {
    // if (get_config('jitsi_clientaccesstoken', 'mod_jitsi') == null) {
    if ($acount == null) { 
        echo "First log in";
    } else {
        if (!file_exists(__DIR__ . '/api/vendor/autoload.php')) {
            throw new \Exception('Api client not found on '.$CFG->wwwroot.'/mod/jitsi/api/vendor/autoload.php');
        }
        require_once(__DIR__ . '/api/vendor/autoload.php');

        $client = new Google_Client();
        $client->setClientId($CFG->jitsi_oauth_id);
        $client->setClientSecret($CFG->jitsi_oauth_secret);

        $tokensessionkey = 'token-' . "https://www.googleapis.com/auth/youtube";
        $client->setAccessToken($_SESSION[$tokensessionkey]);
        unset($_SESSION[$tokensessionkey]);


        $t = time();
        // $timediff = $t - get_config('mod_jitsi', 'jitsi_tokencreated');
        $timediff = $t - $acount->tokencreated;


        if ($timediff > 3599) {
            // $newaccesstoken = $client->fetchAccessTokenWithRefreshToken(get_config('mod_jitsi', 'jitsi_clientrefreshtoken'));
            $newaccesstoken = $client->fetchAccessTokenWithRefreshToken($acount->clientrefreshtoken);

            // set_config('jitsi_clientaccesstoken', $newaccesstoken['access_token'] , 'mod_jitsi');
            $token -> $accesstoken = $newaccesstoken['access_token'];
            $newrefreshaccesstoken = $client->getRefreshToken();
            // set_config('jitsi_clientrefreshtoken', $newrefreshaccesstoken, 'mod_jitsi');
            $acount -> refreshtoken = $newrefreshaccesstoken;
            // set_config('jitsi_tokencreated', time(), 'mod_jitsi');
            $acount->tokencreated = time();
            $DB->update_record('jitsi_record_acount', $acount);
        }

        // $client->revokeToken(get_config('jitsi_clientaccesstoken', 'mod_jitsi'));
        $client->revokeToken($acount -> clientaccesstoken);

        // set_config('jitsi_clientaccesstoken', '' , 'mod_jitsi');
        // set_config('jitsi_clientrefreshtoken', '' , 'mod_jitsi');
        // set_config('jitsi_tokencreated', time(), 'mod_jitsi');


        /////----->>>>>Esto esta a pelo, habra que cmabiarlo para la gestión de distintas cuentas <-----------
        $DB->delete_records('jitsi_record_acount', array('name'=>'Google'));
        echo "Log Out OK. You can close this page";
    }

}


