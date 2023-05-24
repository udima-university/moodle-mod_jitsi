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
 * Test table class to be put in test_table.php of root of Moodle installation.
 *  for defining some custom column names and proccessing
 * Username and Password feilds using custom and other column methods.
 */

defined('MOODLE_INTERNAL') || die();
require_once($CFG->libdir . '/tablelib.php');
require_once($CFG->libdir.'/adminlib.php');

class mod_search_table extends table_sql {
    /**
     * Constructor
     * @param int $uniqueid all tables have to have a unique id, this is used
     *      as a key when storing table properties like sort order in the session.
     */
    public function __construct($uniqueid) {
        parent::__construct($uniqueid);
        // Define the list of columns to show.
        $columns = array('id', 'link', 'jitsi', 'account', 'userid', 'timecreated', 'deleted');
        $this->define_columns($columns);

        // Define the titles of columns to show in header.
        $headers = array('Id', 'Link', 'Jitsi', 'Account', 'User', 'Date', 'Deleted');
        $this->define_headers($headers);
    }

    protected function col_userid($values) {
        global $DB;
        $user = $DB->get_record('user', array('id' => $values->userid));
        return $user->username;
    }

    /**
     * This function is called for each data row to allow processing of the
     * username value.
     *
     * @param object $values Contains object with all the values of record.
     * @return $string Return username with link to profile or username only
     *     when downloading.
     */
    protected function col_timecreated($values) {
        // If the data is being downloaded than we don't want to show HTML.
        return userdate($values->timecreated);
    }

    /**
     * This function is called for each data row to allow processing of the
     * account value.
     *
     * @param object $values Contains object with all the values of record.
     * @return $string Return username with link to profile or username only
     *     when downloading.
     */
    protected function col_account($values) {
        global $DB;
        $acount = $DB->get_record('jitsi_record_account', array('id' => $values->account));
        return $acount->name;
    }

    /**
     * This function is called for each data row to allow processing of the
     * link value.
     *
     * @param object $values Contains object with all the values of record.
     * @return $string Return username with link to profile or username only
     *     when downloading.
     */
    protected function col_link($values) {
        return '<a href="https://youtu.be/'.$values->link.'" target="_blank">'.$values->link.'</a>';
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
        $jitsi = $DB->get_record('jitsi', array('id' => $values->jitsi));
        $coursemodule = get_coursemodule_from_instance('jitsi', $values->jitsi);
        $urljitsiparams = array('id' => $coursemodule->id);
        $urljitsi = new moodle_url('/mod/jitsi/view.php', $urljitsiparams);
        return "<a href=".$urljitsi." >".$jitsi->name."</a>";
    }

    /**
     * This function is called for each data row to allow processing of the
     * account value.
     *
     * @param object $values Contains object with all the values of record.
     * @return $string Return username with link to profile or username only
     *     when downloading.
     */
    protected function col_deleted($values) {
        if ($values->deleted == 1) {
            return get_string('yes');
        } else {
            return get_string('no');
        }
        return $acount->name;
    }

}
