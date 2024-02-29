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
 * Defines the accept event.
 *
 * @package    mod_jitsi
 * @copyright  2023 Sergio Comerón Sánchez-Paniagua <sergiocomeron@icloud.com>
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

defined('MOODLE_INTERNAL') || die();
require_once($CFG->libdir . '/tablelib.php');
require_once($CFG->libdir.'/adminlib.php');
require_once(dirname(__FILE__).'/lib.php');

/**
 * Extend the standard table class for jitsi.
 */
class mod_attendee_table extends table_sql {
    /**
     * Constructor
     * @param int $uniqueid all tables have to have a unique id, this is used
     *      as a key when storing table properties like sort order in the session.
     */
    public function __construct($uniqueid) {
        parent::__construct($uniqueid);
        // Define the list of columns to show.
        $columns = ['user', 'minutestoday', 'totalminutes'];
        $this->define_columns($columns);

        // Define the titles of columns to show in header.
        $headers = [get_string('name'), get_string('minutestoday', 'jitsi').': '
        .date('d/m', strtotime('today midnight')), get_string('totalminutes', 'jitsi')];
        $this->define_headers($headers);
    }

    /**
     * This function is called for each data row to allow processing of the
     * username value.
     *
     * @param object $values Contains object with all the values of record.
     * @return $string Return username with link to profile or username only
     *     when downloading.
     */
    protected function col_user($values) {
        global $DB;
        $user = $DB->get_record('user', ['id' => $values->userid]);
        $urluser = new moodle_url('/user/profile.php', ['id' => $user->id]);
        return "<a href=".$urluser." data-toggle=\"tooltip\" data-placement=\"top\" title=\""
            .$user->username."\">" . $user->firstname . " ".$user->lastname.'</a>';
    }

    /**
     * This function is called for each data row to allow processing of the
     * username value.
     *
     * @param object $values Contains object with all the values of record.
     * @return $string Return username with link to profile or username only
     *     when downloading.
     */
    protected function col_minutestoday($values) {
        return getminutesdates($values->contextinstanceid, $values->userid,
         strtotime('today midnight'), strtotime('today midnight +1 day'));
    }

    /**
     * This function is called for each data row to allow processing of the
     * account value.
     *
     * @param object $values Contains object with all the values of record.
     * @return $string Return username with link to profile or username only
     *     when downloading.
     */
    protected function col_totalminutes($values) {
        global $DB;
        return getminutes($values->contextinstanceid, $values->userid);
    }

    /**
     * This function is called for each data row to allow processing of the
     * delete value.
     *
     * @param object $values Contains object with all the values of record.
     * @return $string Return username with link to profile or username only
     *     when downloading.
     */
    protected function col_jitsi($values) {
        global $DB, $OUTPUT;
        $jitsi = $DB->get_record('jitsi', ['id' => $values->jitsi]);
        $coursemodule = get_coursemodule_from_instance('jitsi', $values->jitsi);
        $urljitsiparams = ['id' => $coursemodule->id];
        $urljitsi = new moodle_url('/mod/jitsi/view.php', $urljitsiparams);
        return "<a href=".$urljitsi." >".$jitsi->name."</a>";
    }
}
