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
 * Library of interface functions and constants for module jitsi
 *
 * All the core Moodle functions, neeeded to allow the module to work
 * integrated in Moodle should be placed here.
 *
 * All the jitsi specific functions, needed to implement all the module
 * logic, should go to locallib.php. This will help to save some memory when
 * Moodle is performing actions across all modules.
 *
 * @package    mod_jitsi
 * @copyright  2021 Sergio Comerón Sánchez-Paniagua <sergiocomeron@icloud.com>
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */
require_once(dirname(dirname(dirname(__FILE__))).'/config.php');
require_once("$CFG->libdir/formslib.php");
require_once(dirname(__FILE__).'/lib.php');
require_once(__DIR__ . '/api/vendor/autoload.php');

global $DB, $CFG;


$dacountid = optional_param('dacountid', 0, PARAM_INT);
$sesskey = optional_param('sesskey', null, PARAM_TEXT);

class acountname_form extends moodleform {
    // Add elements to form.
    public function definition() {
        global $CFG;
        $mform = $this->_form; // Don't forget the underscore!.

        $mform->addElement('text', 'name', 'Name'); // Add elements to your form.
        $mform->setType('name', PARAM_TEXT);        // Set type of element.
        $buttonarray = array();
        $buttonarray[] = $mform->createElement('submit', 'submitbutton', 'Add Acount');
        $mform->addGroup($buttonarray, 'buttonar', '', ' ', false);
    }
    // Custom validation should be added here.
    public function validation($data, $files) {
        return array();
    }
}

$PAGE->set_context(context_system::instance());

$PAGE->set_url('/mod/jitsi/adminaccounts.php');
require_login();

if ($dacountid && confirm_sesskey($sesskey)) {
    $acount = $DB->get_record('jitsi_record_acount', array('id' => $dacountid));

    if ($acount == null) {
        echo "First log in";
    } else {
        if (!file_exists(__DIR__ . '/api/vendor/autoload.php')) {
            throw new \Exception('Api client not found on '.$CFG->wwwroot.'/mod/jitsi/api/vendor/autoload.php');
        }

        $client = new Google_Client();
        $client->setClientId($CFG->jitsi_oauth_id);
        $client->setClientSecret($CFG->jitsi_oauth_secret);

        $tokensessionkey = 'token-' . "https://www.googleapis.com/auth/youtube";
        $client->setAccessToken($acount->clientaccesstoken);
        unset($_SESSION[$tokensessionkey]);

        $t = time();
        $timediff = $t - $acount->tokencreated;

        if ($timediff > 3599) {
            $newaccesstoken = $client->fetchAccessTokenWithRefreshToken($acount->clientrefreshtoken);

            $acount->clientaccesstoken = $newaccesstoken['access_token'];
            $newrefreshaccesstoken = $client->getRefreshToken();
            $acount->refreshtoken = $newrefreshaccesstoken;
            $acount->tokencreated = time();
            $DB->update_record('jitsi_record_acount', $acount);
        }

        $client->revokeToken($acount->clientaccesstoken);

        $acount = $DB->delete_records('jitsi_record_acount', array('id' => $dacountid));

        echo "Log Out OK. You can close this page";
    }
    redirect($PAGE->url, get_string('deleted'));
}

$PAGE->set_title(format_string(get_string('acounts', 'jitsi')));
$PAGE->set_heading(format_string(get_string('acounts', 'jitsi')));

echo $OUTPUT->header();
echo $OUTPUT->heading(get_string('acounts', 'jitsi'));

if (is_siteadmin()) {
    $acounts = $DB->get_records('jitsi_record_acount', array());
    $table = new html_table();
    $table->head = array('Name', 'Actions', 'Records');

    $client = new Google_Client();
    $client->setClientId($CFG->jitsi_oauth_id);
    $client->setClientSecret($CFG->jitsi_oauth_secret);

    $tokensessionkey = 'token-' . "https://www.googleapis.com/auth/youtube";

    foreach ($acounts as $acount) {
        $deleteurl = new moodle_url('/mod/jitsi/adminaccounts.php?&dacountid=' . $acount->id. '&sesskey=' . sesskey());
        $deleteicon = new pix_icon('t/delete', get_string('delete'));
        $deleteaction = $OUTPUT->action_icon($deleteurl, $deleteicon, new confirm_action(get_string('deleteq', 'jitsi')));

        $loginurl = new moodle_url('/mod/jitsi/auth.php?&name=' . $acount->name);
        $loginicon = new pix_icon('i/publish', get_string('login'));
        $loginaction = $OUTPUT->action_icon($loginurl, $loginicon, new confirm_action(get_string('loginq', 'jitsi')));

        $numrecords = $DB->count_records('jitsi_source_record', array('acount' => $acount->id));
        if ($acount->inuse == 1) {
            if ($numrecords == 0) {
                $table->data[] = array($acount->name.get_string('inuse', 'jitsi'), $deleteaction, $numrecords);
            } else {
                $table->data[] = array($acount->name.get_string('inuse', 'jitsi'), null, $numrecords);
            }
        } else {
            if ($numrecords == 0) {
                $table->data[] = array($acount->name, $loginaction.' '.$deleteaction, $numrecords);
            } else {
                $table->data[] = array($acount->name, $loginaction, $numrecords);
            }
        }
    }

    echo html_writer::table($table);

    // Instantiate simplehtml_form.
    $mform = new acountname_form('./auth.php');

    $mform->display();
}
echo $OUTPUT->footer();