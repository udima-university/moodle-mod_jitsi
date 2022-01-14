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
        $userinfo = $this->get_setting_value('userinfo');

        $paths[] = new restore_path_element('jitsi', '/activity/jitsi');
        if ($userinfo) {
            $paths[] = new restore_path_element('jitsi_source_record', '/activity/jitsi/records/record/sources/source');
            // $paths[] = new restore_path_element('jitsi_source_record', '/activity/jitsi/sources/source');
            $paths[] = new restore_path_element('jitsi_record', '/activity/jitsi/records/record');

        }

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
        $this->apply_activity_instance($newitemid);
    }

    protected function process_jitsi_source_record($data) {
        global $DB;
        $data = (object)$data;
        $oldid = $data->id;
        
        $source = $DB->get_record('jitsi_source_record', ['link' => $data->link]);
        if (!$source) {
            $data->userid = $this->get_mappingid('user', $data->userid);
    
            $newitemid = $DB->insert_record('jitsi_source_record', $data);
            $data->id = $newitemid;

        } else {
            $data->id = $source->id;
        } 
        $this->get_logger()->process("SOURCE -- el id del source: ".$data->id, backup::LOG_ERROR);
        $this->sources[$data->id] = $data;
    }

    protected function process_jitsi_record($data) {
        global $DB;

        $data = (object)$data;

        $data->jitsi = $this->get_new_parentid('jitsi');

        $data->source = $this->get_mappingid('jitsi_source_record', $data->source);
        $newitemid = $DB->insert_record('jitsi_record', $data);
        $data->id = $newitemid;

        $this->get_logger()->process("RECORD -- el id del record: ".$data->id, backup::LOG_ERROR);

        $this->records[$data->id] = $data;


    }

    /**
     * Post-execution actions
     */
    protected function after_execute() {
        
        global $DB;
        foreach($this->sources as $source) {
            $sourceobj = $DB->get_record('jitsi_source_record', ['link' => $source->link]);
        }

        foreach($this->records as $record) {
            $recordob = $DB->get_record('jitsi_record', ['id' => $record->id]);
            $recordob->source = $sourceobj->id;
            $DB->update_record('jitsi_record', $recordob);
        }
        

        // Add jitsi related files, no need to match by itemname (just internally handled context).
        $this->add_related_files('mod_jitsi', 'intro', null);
    }
}
