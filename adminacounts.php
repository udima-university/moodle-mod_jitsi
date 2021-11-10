<?php
require_once(dirname(dirname(dirname(__FILE__))).'/config.php');
require_once("$CFG->libdir/formslib.php");
require_once(dirname(__FILE__).'/lib.php');
require_once(__DIR__ . '/api/vendor/autoload.php');

global $DB, $CFG;


$dacountid = optional_param('dacountid', 0, PARAM_INT);
$sesskey = optional_param('sesskey', null, PARAM_TEXT);

class acountname_form extends moodleform {
    //Add elements to form
    public function definition() {
        global $CFG;
       
        $mform = $this->_form; // Don't forget the underscore! 

        $mform->addElement('text', 'name', 'Name'); // Add elements to your form
        $mform->setType('name', PARAM_TEXT);                   //Set type of element
        $buttonarray=array();
        $buttonarray[] = $mform->createElement('submit', 'submitbutton', 'Add Acount');
        $mform->addGroup($buttonarray, 'buttonar', '', ' ', false);
    }
    //Custom validation should be added here
    function validation($data, $files) {
        return array();
    }
}

$PAGE->set_context(context_system::instance());

$PAGE->set_url('/mod/jitsi/adminacounts.php');
require_login();

if ($dacountid && confirm_sesskey($sesskey)) {
    $acount = $DB->get_record('jitsi_record_acount', array('id'=>$dacountid));

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

            $acount -> clientaccesstoken = $newaccesstoken['access_token'];
            $newrefreshaccesstoken = $client->getRefreshToken();
            $acount -> refreshtoken = $newrefreshaccesstoken;
            $acount->tokencreated = time();
            $DB->update_record('jitsi_record_acount', $acount);
        }

        $client->revokeToken($acount -> clientaccesstoken);

        $acount = $DB->delete_records('jitsi_record_acount', array('id'=>$dacountid));

        echo "Log Out OK. You can close this page";
    }
    redirect($PAGE->url, get_string('deleted'));
}

$PAGE->set_title(format_string(get_string('acounts', 'jitsi')));
$PAGE->set_heading(format_string(get_string('acounts', 'jitsi')));

echo $OUTPUT->header();
echo $OUTPUT->heading(get_string('acounts', 'jitsi'));

if  (is_siteadmin()){
    $acounts = $DB->get_records('jitsi_record_acount', array());
    $table = new html_table();
    $table->head = array('Name', 'Actions', 'Records');

    $client = new Google_Client();
    $client->setClientId($CFG->jitsi_oauth_id);
    $client->setClientSecret($CFG->jitsi_oauth_secret);

    $tokensessionkey = 'token-' . "https://www.googleapis.com/auth/youtube";

    foreach ($acounts as $acount) {
        $deleteurl = new moodle_url('/mod/jitsi/adminacounts.php?&dacountid=' . $acount->id. '&sesskey=' . sesskey());
        $deleteicon = new pix_icon('t/delete', get_string('delete'));
        $deleteaction = $OUTPUT->action_icon($deleteurl, $deleteicon, new confirm_action(get_string('delete?', 'jitsi')));

        $loginurl = new moodle_url('/mod/jitsi/auth.php?&name=' . $acount->name);
        $loginicon = new pix_icon('i/publish', get_string('login'));
        $loginaction = $OUTPUT->action_icon($loginurl, $loginicon, new confirm_action(get_string('login?', 'jitsi')));
        $numrecords = $DB->count_records('jitsi_source_record', array('acount'=>$acount->id));
            if ($acount->inuse == 1) {
                if ($numrecords == 0){
                    $table->data[] = array($acount->name.get_string('(inuse)', 'jitsi'), $deleteaction, $numrecords);
                } else {
                    $table->data[] = array($acount->name.get_string('(inuse)', 'jitsi'), null, $numrecords);
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

    //Instantiate simplehtml_form 
    $mform = new acountname_form('./auth.php');

    //Form processing and displaying is done here
    if ($mform->is_cancelled()) {
        //Handle form cancel operation, if cancel button is present on form
    } else if ($fromform = $mform->get_data()) {
        //In this case you process validated data. $mform->get_data() returns data posted in form.
    } else {
        // this branch is executed if the form is submitted but the data doesn't validate and the form should be redisplayed
        // or on the first display of the form.

        //Set default data (if any)
        //displays the form
        $mform->display(); 
    }

}
echo $OUTPUT->footer();
