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
 * Settings for Jitsi instances
 * @package   mod_jitsi
 * @copyright  2025 Sergio Comerón (jitsi@sergiocomeron.com)
 * @license   http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

require_once(__DIR__ . '/../../config.php');
require_login();
require_capability('moodle/site:config', context_system::instance());

global $DB, $OUTPUT, $PAGE;

$PAGE->set_url('/mod/jitsi/servermanagement.php');

$PAGE->set_context(context_system::instance());
$PAGE->set_url('/mod/jitsi/servermanagement.php');
$PAGE->set_title(get_string('servermanagement', 'mod_jitsi'));

require_once($CFG->dirroot . '/mod/jitsi/servermanagement_form.php');

$action  = optional_param('action', '', PARAM_ALPHA);
$id      = optional_param('id', 0, PARAM_INT);
$confirm = optional_param('confirm', 0, PARAM_BOOL);

if ($action === 'delete' && $id > 0) {
    if (!$server = $DB->get_record('jitsi_servers', ['id' => $id])) {
        throw new moodle_exception('Invalid id');
    }

    if ($confirm) {
        $DB->delete_records('jitsi_servers', ['id' => $server->id]);

        \core\notification::add(
            get_string('serverdeleted', 'mod_jitsi', $server->name),
            \core\output\notification::NOTIFY_SUCCESS
        );
        redirect(new moodle_url('/mod/jitsi/servermanagement.php'));
    } else {
        echo $OUTPUT->header();
        echo $OUTPUT->heading(get_string('delete'));
        $msg = get_string('confirmdelete', 'mod_jitsi', format_string($server->name));
        echo $OUTPUT->confirm(
            $msg,
            new moodle_url('/mod/jitsi/servermanagement.php', ['action' => 'delete', 'id' => $id, 'confirm' => 1]),
            new moodle_url('/mod/jitsi/servermanagement.php')
        );
        echo $OUTPUT->footer();
        exit;
    }
}

$mform = new servermanagement_form();

if ($action === 'edit' && $id > 0) {
    if ($server = $DB->get_record('jitsi_servers', ['id' => $id])) {
        $mform->set_data($server);
    } else {
        throw new moodle_exception('Invalid id');
    }
}

if ($mform->is_cancelled()) {
    redirect(new moodle_url('/mod/jitsi/servermanagement.php'));

} else if ($data = $mform->get_data()) {
    if ($data->id) {
        if (!$server = $DB->get_record('jitsi_servers', ['id' => $data->id])) {
            throw new moodle_exception('Invalid Id');
        }

        $server->name   = $data->name;
        $server->type   = $data->type;
        $server->domain = $data->domain;
        $server->appid                = '';
        $server->secret               = '';
        $server->eightbyeightappid    = '';
        $server->eightbyeightapikeyid = '';
        $server->privatekey           = '';

        if ($data->type == 1) {
            $server->appid  = $data->appid;
            $server->secret = $data->secret;
        } else if ($data->type == 2) {
            $server->eightbyeightappid    = $data->eightbyeightappid;
            $server->eightbyeightapikeyid = $data->eightbyeightapikeyid;
            $server->privatekey           = $data->privatekey;
        }

        $server->timemodified = time();
        $DB->update_record('jitsi_servers', $server);

        \core\notification::add(
            get_string('serverupdated', 'mod_jitsi', $server->name),
            \core\output\notification::NOTIFY_SUCCESS
        );

    } else {
        $server = new stdClass();
        $server->name   = $data->name;
        $server->type   = $data->type;
        $server->domain = $data->domain;
        $server->appid                = '';
        $server->secret               = '';
        $server->eightbyeightappid    = '';
        $server->eightbyeightapikeyid = '';
        $server->privatekey           = '';

        if ($data->type == 1) {
            $server->appid  = $data->appid;
            $server->secret = $data->secret;
        } else if ($data->type == 2) {
            $server->eightbyeightappid    = $data->eightbyeightappid;
            $server->eightbyeightapikeyid = $data->eightbyeightapikeyid;
            $server->privatekey           = $data->privatekey;
        }

        $server->timecreated  = time();
        $server->timemodified = time();

        $DB->insert_record('jitsi_servers', $server);

        \core\notification::add(
            get_string('serveradded', 'mod_jitsi'),
            \core\output\notification::NOTIFY_SUCCESS
        );
    }

    redirect(new moodle_url('/mod/jitsi/servermanagement.php'));
}

echo $OUTPUT->header();
echo $OUTPUT->heading(get_string('servermanagement', 'mod_jitsi'));

$settingsurl = new moodle_url('/admin/settings.php', ['section' => 'modsettingjitsi']);
echo html_writer::link($settingsurl, get_string('backtosettings', 'mod_jitsi'), ['class' => 'btn btn-secondary']);


$servers = $DB->get_records('jitsi_servers', null, 'name ASC');
$table = new html_table();
$table->head = [
    get_string('name'),
    get_string('type', 'mod_jitsi'),
    get_string('domain', 'mod_jitsi'),
    get_string('actions', 'mod_jitsi'),
];

foreach ($servers as $s) {
    switch ($s->type) {
        case 0:
            $typestring = 'Server without token';
            break;
        case 1:
            $typestring = 'Self-hosted (appid & secret)';
            break;
        case 2:
            $typestring = '8x8 server';
            break;
        default:
            $typestring = get_string('unknowntype', 'mod_jitsi');
    }

    $editurl = new moodle_url('/mod/jitsi/servermanagement.php', ['action' => 'edit', 'id' => $s->id]);
    $deleteurl = new moodle_url('/mod/jitsi/servermanagement.php', ['action' => 'delete', 'id' => $s->id]);

    $links = html_writer::link($editurl, get_string('edit')) . ' | '
           . html_writer::link($deleteurl, get_string('delete'));

    $table->data[] = [
        format_string($s->name),
        $typestring,
        format_string($s->domain),
        $links,
    ];
}
echo html_writer::table($table);

if ($action === 'edit' && $id > 0) {
    echo $OUTPUT->heading(get_string('editserver', 'mod_jitsi'));
} else {
    echo $OUTPUT->heading(get_string('addnewserver', 'mod_jitsi'));
}

$mform->display();

echo $OUTPUT->footer();
