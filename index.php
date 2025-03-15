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
 * This is a one-line short description of the file
 *
 * You can have a rather longer description of the file as well,
 * if you like, and it can span multiple lines.
 *
 * @package    mod_jitsi
 * @copyright  2019 Sergio Comerón Sánchez-Paniagua <sergiocomeron@icloud.com>
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

require_once(dirname(dirname(dirname(__FILE__))).'/config.php');
require_once(dirname(__FILE__).'/lib.php');

$id = required_param('id', PARAM_INT);   // Course.

$PAGE->set_url('/mod/jitsi/index.php', ['id' => $id]);

if (! $course = $DB->get_record('course', ['id' => $id])) {
    throw new \moodle_exception('invalidcourseid');
}

require_course_login($course);
$PAGE->set_pagelayout('incourse');

$params = [
    'context' => context_course::instance($id),
];
$event = \mod_jitsi\event\course_module_instance_list_viewed::create($params);
$event->add_record_snapshot('course', $course);
$event->trigger();

// Get all required strings.
$strjitsis = get_string('modulenameplural', 'jitsi');
$strjitsi  = get_string('modulename', 'jitsi');

// Print the header.
$PAGE->navbar->add($strjitsis);
$PAGE->set_title($strjitsis);
$PAGE->set_heading($course->fullname);
echo $OUTPUT->header();
echo $OUTPUT->heading($strjitsis, 2);

// Get all the appropriate data.
if (! $jitsis = get_all_instances_in_course('jitsi', $course)) {
    notice(get_string('thereareno', 'moodle', $strjitsis), "../../course/view.php?id=$course->id");
    die();
}

$usesections = course_format_uses_sections($course->format);

// Print the list of instances (your module will probably extend this).

$timenow  = time();
$strname  = get_string('name');

$table = new html_table();

if ($usesections) {
    $strsectionname = get_string('sectionname', 'format_'.$course->format);
    $table->head  = [$strsectionname, $strname];
    $table->align = ['center', 'left'];
} else {
    $table->head  = [$strname];
    $table->align = ['left'];
}

$currentsection = '';
foreach ($jitsis as $jitsi) {
    if (!$jitsi->visible) {
        // Show dimmed if the mod is hidden.
        $link = "<a class=\"dimmed\" href=\"view.php?id=$jitsi->coursemodule\">".format_string($jitsi->name, true)."</a>";
    } else {
        // Show normal if the mod is visible.
        $link = "<a href=\"view.php?id=$jitsi->coursemodule\">".format_string($jitsi->name, true)."</a>";
    }
    $printsection = '';
    if ($jitsi->section !== $currentsection) {
        if ($jitsi->section) {
            $printsection = get_section_name($course, $jitsi->section);
        }
        if ($currentsection !== '') {
            $table->data[] = 'hr';
        }
        $currentsection = $jitsi->section;
    }
    if ($usesections) {
        $table->data[] = [$printsection, $link];
    } else {
        $table->data[] = [$link];
    }
}

echo '<br />';

echo html_writer::table($table);

// Finish the page.

echo $OUTPUT->footer();

