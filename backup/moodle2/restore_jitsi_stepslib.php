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
 * Structure step to restore one jitsi activity
 *
 * @package   mod_jitsi
 * @category  backup
 * @copyright 2019 Sergio Comerón Sánchez-Paniagua <sergiocomeron@icloud.com>
 * @license   http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */
class restore_jitsi_activity_structure_step extends restore_activity_structure_step {

    /**
     * Defines structure of path elements to be processed during the restore
     *
     * @return array of {@link restore_path_element}
     */
    protected function define_structure() {

        $paths = array();
        $paths[] = new restore_path_element('jitsi', '/activity/jitsi');
        $paths[] = new restore_path_element('jitsi_record', '/activity/jitsi/records/record');

        // Return the paths wrapped into standard activity structure.
        return $this->prepare_activity_structure($paths);
    }

    /**
     * Process the given restore path element data
     *
     * @param array $data parsed element data
     */
    protected function process_jitsi($data) {
        global $DB;

        $data = (object)$data;
        $oldid = $data->id;
        $data->course = $this->get_courseid();

        if (empty($data->timecreated)) {
            $data->timecreated = time();
        }

        if (empty($data->timemodified)) {
            $data->timemodified = time();
        }

        $data->token = bin2hex(random_bytes(32));
        

        

        // Create the jitsi instance.
        $newitemid = $DB->insert_record('jitsi', $data);
        
        $records = $DB->get_records('jitsi_record', array('jitsi'=>$data->id));
        foreach ($records as $record) {
            if ($record->deleted == 1) {
                $record->deleted = 1;
            } else {
                $record->deleted = 0;
            }
            $record->jitsi = $newitemid;
            // if ($DB->record_exists('jitsi_record', $conditions_array) {
                
            // }
            // $DB->update_record('jitsi_record', $record);
            $DB->insert_record('jitsi_record', $record);
        }
        
        
        $this->apply_activity_instance($newitemid);



    }

    protected function process_jitsi_record($data) {
        global $DB;

        $data = (object)$data;
        // $data->course = $this->get_courseid();

        $data->jitsi = $this->get_new_parentid('record');
        if ($data->deleted=0) {
            $newitemid = $DB->insert_record('jitsi_record', $data);
        }
        // No need to save this mapping as far as nothing depend on it
        // (child paths, file areas nor links decoder)
    }

    /**
     * Post-execution actions
     */
    protected function after_execute() {
        // Add jitsi related files, no need to match by itemname (just internally handled context).
        $this->add_related_files('mod_jitsi', 'intro', null);
    }
}
