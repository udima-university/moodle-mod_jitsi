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

defined('MOODLE_INTERNAL') || die;

/**
 * Define the complete jitsi structure for backup, with file and id annotations
 *
 * @package   mod_jitsi
 * @category  backup
 * @copyright 2016 Your Name <your@email.address>
 * @license   http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */
class backup_jitsi_activity_structure_step extends backup_activity_structure_step {

    /**
     * Defines the backup structure of the module
     *
     * @return backup_nested_element
     */
    protected function define_structure() {

        $jitsi = new backup_nested_element('jitsi', array('id'), array('name', 'intro', 'introformat', 'timeopen'));
        $jitsi->set_source_table('jitsi', array('id' => backup::VAR_ACTIVITYID));
        $jitsi->annotate_files('mod_jitsi', 'intro', null);
        return $this->prepare_activity_structure($jitsi);
    }
}
