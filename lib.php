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
 * @copyright  2016 Sergio Comerón Sánchez-Paniagua <sergiocomeron@icloud.com>
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

defined('MOODLE_INTERNAL') || die();

/* Moodle core API */

/**
 * Returns the information on whether the module supports a feature
 *
 * See {@link plugin_supports()} for more info.
 *
 * @param string $feature FEATURE_xx constant for requested feature
 * @return mixed true if the feature is supported, null if unknown
 */
function jitsi_supports($feature) {

    switch($feature) {
        case FEATURE_MOD_INTRO:
            return true;
        case FEATURE_SHOW_DESCRIPTION:
            return true;
        case FEATURE_GRADE_HAS_GRADE:
            return true;
        case FEATURE_BACKUP_MOODLE2:
            return true;
        default:
            return null;
    }
}

/**
 * Saves a new instance of the jitsi into the database
 *
 * Given an object containing all the necessary data,
 * (defined by the form in mod_form.php) this function
 * will create a new instance and return the id number
 * of the new instance.
 *
 * @param stdClass $jitsi Submitted data from the form in mod_form.php
 * @param mod_jitsi_mod_form $mform The form instance itself (if needed)
 * @return int The id of the newly inserted jitsi record
 */
function jitsi_add_instance($jitsi,  $mform = null) {
    global $CFG, $DB;
    require_once($CFG->dirroot.'/mod/jitsi/locallib.php');


    $jitsi->timecreated = time();
    $cmid       = $jitsi->coursemodule;

    $jitsi->id = $DB->insert_record('jitsi', $jitsi);
    jitsi_update_calendar($jitsi, $cmid);

    return $jitsi->id;
}

/**
 * Updates an instance of the jitsi in the database
 *
 * Given an object containing all the necessary data,
 * (defined by the form in mod_form.php) this function
 * will update an existing instance with new data.
 *
 * @param stdClass $jitsi An object from the form in mod_form.php
 * @param mod_jitsi_mod_form $mform The form instance itself (if needed)
 * @return boolean Success/Fail
 */
function jitsi_update_instance($jitsi,  $mform = null) {
    global $CFG, $DB;
    require_once($CFG->dirroot.'/mod/jitsi/locallib.php');

    $jitsi->timemodified = time();
    $jitsi->id = $jitsi->instance;
    $cmid       = $jitsi->coursemodule;

    $result = $DB->update_record('jitsi', $jitsi);
    jitsi_update_calendar($jitsi, $cmid);

    return $result;
}

/**
 * This standard function will check all instances of this module
 * and make sure there are up-to-date events created for each of them.
 * If courseid = 0, then every jitsi event in the site is checked, else
 * only jitsi events belonging to the course specified are checked.
 * This is only required if the module is generating calendar events.
 *
 * @param int $courseid Course ID
 * @return bool
 */
function jitsi_refresh_events($courseid = 0, $instance = null, $cm = null) {
    global $CFG, $DB;
    require_once($CFG->dirroot . '/mod/jitsi/locallib.php');

    if (isset($instance)) {
        if (!is_object($instance)) {
            $instance = $DB->get_record('jitsi', array('id' => $instance), '*', MUST_EXIST);
        }
        if (isset($cm)) {
            if (!is_object($cm)) {
                $cm = (object)array('id' => $cm);
            }
        } else {
            $cm = get_coursemodule_from_instance('jitsi', $instance->id);
        }
        scorm_update_calendar($instance, $cm->id);
        return true;
    }

    if ($courseid) {
      if (!is_numeric($courseid)) {
          return false;
      }
      if (!$scorms = $DB->get_records('jitsi', array('jitsi' => $courseid))) {
          return true;
      }
    } else {
        if (!$jitsis = $DB->get_records('jitsi', array('course' => $courseid))) {
            return true;
        }
    }

    foreach ($jitsis as $jitsi) {
        // Create a function such as the one below to deal with updating calendar events.
        $cm = get_coursemodule_from_instance('jitsi', $jitsi->id);
        jitsi_update_calendar($jitsi, $cm->id);
        // jitsi_update_events($jitsi);
    }

    return true;
}

/**
 * Removes an instance of the jitsi from the database
 *
 * Given an ID of an instance of this module,
 * this function will permanently delete the instance
 * and any data that depends on it.
 *
 * @param int $id Id of the module instance
 * @return boolean Success/Failure
 */
function jitsi_delete_instance($id) {
    global $CFG, $DB;

    if (! $jitsi = $DB->get_record('jitsi', array('id' => $id))) {
        return false;
    }

    $result = true;

    if (! $DB->delete_records('jitsi', array('id' => $jitsi->id))) {
        $result = false;
    }

    return $result;
}

/**
 * Returns a small object with summary information about what a
 * user has done with a given particular instance of this module
 * Used for user activity reports.
 *
 * $return->time = the time they did it
 * $return->info = a short text description
 *
 * @param stdClass $course The course record
 * @param stdClass $user The user record
 * @param cm_info|stdClass $mod The course module info object or record
 * @param stdClass $jitsi The jitsi instance record
 * @return stdClass|null
 */
function jitsi_user_outline($course, $user, $mod, $jitsi) {

    $return = new stdClass();
    $return->time = 0;
    $return->info = '';
    return $return;
}

/**
 * Prints a detailed representation of what a user has done with
 * a given particular instance of this module, for user activity reports.
 *
 * It is supposed to echo directly without returning a value.
 *
 * @param stdClass $course the current course record
 * @param stdClass $user the record of the user we are generating report for
 * @param cm_info $mod course module info
 * @param stdClass $jitsi the module instance record
 */
function jitsi_user_complete($course, $user, $mod, $jitsi) {
}

/**
 * Given a course and a time, this module should find recent activity
 * that has occurred in jitsi activities and print it out.
 *
 * @param stdClass $course The course record
 * @param bool $viewfullnames Should we display full names
 * @param int $timestart Print activity since this timestamp
 * @return boolean True if anything was printed, otherwise false
 */
function jitsi_print_recent_activity($course, $viewfullnames, $timestart) {
    return false;
}

/**
 * Prepares the recent activity data
 *
 * This callback function is supposed to populate the passed array with
 * custom activity records. These records are then rendered into HTML via
 * {@link jitsi_print_recent_mod_activity()}.
 *
 * Returns void, it adds items into $activities and increases $index.
 *
 * @param array $activities sequentially indexed array of objects with added 'cmid' property
 * @param int $index the index in the $activities to use for the next record
 * @param int $timestart append activity since this time
 * @param int $courseid the id of the course we produce the report for
 * @param int $cmid course module id
 * @param int $userid check for a particular user's activity only, defaults to 0 (all users)
 * @param int $groupid check for a particular group's activity only, defaults to 0 (all groups)
 */
function jitsi_get_recent_mod_activity(&$activities, &$index, $timestart, $courseid, $cmid, $userid=0, $groupid=0) {
}

/**
 * Prints single activity item prepared by {@link jitsi_get_recent_mod_activity()}
 *
 * @param stdClass $activity activity record with added 'cmid' property
 * @param int $courseid the id of the course we produce the report for
 * @param bool $detail print detailed report
 * @param array $modnames as returned by {@link get_module_types_names()}
 * @param bool $viewfullnames display users' full names
 */
function jitsi_print_recent_mod_activity($activity, $courseid, $detail, $modnames, $viewfullnames) {
}

/**
 * Function to be run periodically according to the moodle cron
 *
 * This function searches for things that need to be done, such
 * as sending out mail, toggling flags etc ...
 *
 * Note that this has been deprecated in favour of scheduled task API.
 *
 * @return boolean
 */
function jitsi_cron () {
    return true;
}

/**
 * Returns all other caps used in the module
 *
 * For example, this could be array('moodle/site:accessallgroups') if the
 * module uses that capability.
 *
 * @return array
 */
function jitsi_get_extra_capabilities() {
    return array();
}

/* Gradebook API */

/**
 * Is a given scale used by the instance of jitsi?
 *
 * This function returns if a scale is being used by one jitsi
 * if it has support for grading and scales.
 *
 * @param int $jitsiid ID of an instance of this module
 * @param int $scaleid ID of the scale
 * @return bool true if the scale is used by the given jitsi instance
 */
function jitsi_scale_used($jitsiid, $scaleid) {
    global $DB;

    if ($scaleid and $DB->record_exists('jitsi', array('id' => $jitsiid, 'grade' => -$scaleid))) {
        return true;
    } else {
        return false;
    }
}

/**
 * Checks if scale is being used by any instance of jitsi.
 *
 * This is used to find out if scale used anywhere.
 *
 * @param int $scaleid ID of the scale
 * @return boolean true if the scale is used by any jitsi instance
 */
function jitsi_scale_used_anywhere($scaleid) {
    global $DB;

    if ($scaleid and $DB->record_exists('jitsi', array('grade' => -$scaleid))) {
        return true;
    } else {
        return false;
    }
}

/**
 * Creates or updates grade item for the given jitsi instance
 *
 * Needed by {@link grade_update_mod_grades()}.
 *
 * @param stdClass $jitsi instance object with extra cmidnumber and modname property
 * @param bool $reset reset grades in the gradebook
 * @return void
 */
function jitsi_grade_item_update(stdClass $jitsi, $reset=false) {
    global $CFG;
    require_once($CFG->libdir.'/gradelib.php');

    $item = array();
    $item['itemname'] = clean_param($jitsi->name, PARAM_NOTAGS);
    $item['gradetype'] = GRADE_TYPE_VALUE;

    if ($jitsi->grade > 0) {
        $item['gradetype'] = GRADE_TYPE_VALUE;
        $item['grademax']  = $jitsi->grade;
        $item['grademin']  = 0;
    } else if ($jitsi->grade < 0) {
        $item['gradetype'] = GRADE_TYPE_SCALE;
        $item['scaleid']   = -$jitsi->grade;
    } else {
        $item['gradetype'] = GRADE_TYPE_NONE;
    }

    if ($reset) {
        $item['reset'] = true;
    }

    grade_update('mod/jitsi', $jitsi->course, 'mod', 'jitsi',
            $jitsi->id, 0, null, $item);
}

/**
 * Delete grade item for given jitsi instance
 *
 * @param stdClass $jitsi instance object
 * @return grade_item
 */
function jitsi_grade_item_delete($jitsi) {
    global $CFG;
    require_once($CFG->libdir.'/gradelib.php');

    return grade_update('mod/jitsi', $jitsi->course, 'mod', 'jitsi',
            $jitsi->id, 0, null, array('deleted' => 1));
}

/**
 * Update jitsi grades in the gradebook
 *
 * Needed by {@link grade_update_mod_grades()}.
 *
 * @param stdClass $jitsi instance object with extra cmidnumber and modname property
 * @param int $userid update grade of specific user only, 0 means all participants
 */
function jitsi_update_grades(stdClass $jitsi, $userid = 0) {
    global $CFG, $DB;
    require_once($CFG->libdir.'/gradelib.php');

    // Populate array of grade objects indexed by userid.
    $grades = array();

    grade_update('mod/jitsi', $jitsi->course, 'mod', 'jitsi', $jitsi->id, 0, $grades);
}

/* File API */

/**
 * Returns the lists of all browsable file areas within the given module context
 *
 * The file area 'intro' for the activity introduction field is added automatically
 * by {@link file_browser::get_file_info_context_module()}
 *
 * @param stdClass $course
 * @param stdClass $cm
 * @param stdClass $context
 * @return array of [(string)filearea] => (string)description
 */
function jitsi_get_file_areas($course, $cm, $context) {
    return array();
}

/**
 * File browsing support for jitsi file areas
 *
 * @package mod_jitsi
 * @category files
 *
 * @param file_browser $browser
 * @param array $areas
 * @param stdClass $course
 * @param stdClass $cm
 * @param stdClass $context
 * @param string $filearea
 * @param int $itemid
 * @param string $filepath
 * @param string $filename
 * @return file_info instance or null if not found
 */
function jitsi_get_file_info($browser, $areas, $course, $cm, $context, $filearea, $itemid, $filepath, $filename) {
    return null;
}

/**
 * Serves the files from the jitsi file areas
 *
 * @package mod_jitsi
 * @category files
 *
 * @param stdClass $course the course object
 * @param stdClass $cm the course module object
 * @param stdClass $context the jitsi's context
 * @param string $filearea the name of the file area
 * @param array $args extra arguments (itemid, path)
 * @param bool $forcedownload whether or not force download
 * @param array $options additional options affecting the file serving
 */
function jitsi_pluginfile($course, $cm, $context, $filearea, array $args, $forcedownload, array $options=array()) {
    global $DB, $CFG;

    if ($context->contextlevel != CONTEXT_MODULE) {
        send_file_not_found();
    }

    require_login($course, true, $cm);

    send_file_not_found();
}

/* Navigation API */

/**
 * Extends the global navigation tree by adding jitsi nodes if there is a relevant content
 *
 * This can be called by an AJAX request so do not rely on $PAGE as it might not be set up properly.
 *
 * @param navigation_node $navref An object representing the navigation tree node of the jitsi module instance
 * @param stdClass $course current course record
 * @param stdClass $module current jitsi instance record
 * @param cm_info $cm course module information
 */
function jitsi_extend_navigation(navigation_node $navref, stdClass $course, stdClass $module, cm_info $cm) {
    // TODO Delete this function and its docblock, or implement it.
}

/**
 * Extends the settings navigation with the jitsi settings
 *
 * This function is called when the context for the page is a jitsi module. This is not called by AJAX
 * so it is safe to rely on the $PAGE.
 *
 * @param settings_navigation $settingsnav complete settings navigation tree
 * @param navigation_node $jitsinode jitsi administration node
 */
function jitsi_extend_settings_navigation(settings_navigation $settingsnav, navigation_node $jitsinode=null) {
    // TODO Delete this function and its docblock, or implement it.
}
