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
 * This file keeps track of upgrades to the jitsi module
 *
 * Sometimes, changes between versions involve alterations to database
 * structures and other major things that may break installations. The upgrade
 * function in this file will attempt to perform all the necessary actions to
 * upgrade your older installation to the current version. If there's something
 * it cannot do itself, it will tell you what you need to do.  The commands in
 * here will all be database-neutral, using the functions defined in DLL libraries.
 *
 * @package    mod_jitsi
 * @copyright  2019 Sergio Comerón Sánchez-Paniagua <sergiocomeron@icloud.com>
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

/**
 * Execute jitsi upgrade from the given old version
 *
 * @param int $oldversion
 * @return bool
 */
function xmldb_jitsi_upgrade($oldversion) {
    global $DB;

    $dbman = $DB->get_manager(); // Loads ddl manager and xmldb classes.

    /*
     * And upgrade begins here. For each one, you'll need one
     * block of code similar to the next one. Please, delete
     * this comment lines once this file start handling proper
     * upgrade code.
     *
     * if ($oldversion < YYYYMMDD00) { //New version in version.php
     * }
     *
     * Lines below (this included)  MUST BE DELETED once you get the first version
     * of your module ready to be installed. They are here only
     * for demonstrative purposes and to show how the jitsi
     * iself has been upgraded.
     *
     * For each upgrade block, the file jitsi/version.php
     * needs to be updated . Such change allows Moodle to know
     * that this file has to be processed.
     *
     * To know more about how to write correct DB upgrade scripts it's
     * highly recommended to read information available at:
     *   http://docs.moodle.org/en/Development:XMLDB_Documentation
     * and to play with the XMLDB Editor (in the admin menu) and its
     * PHP generation posibilities.
     *
     * First example, some fields were added to install.xml on 2007/04/01
     */
    if ($oldversion < 2007040100) {

        // Define field course to be added to jitsi.
        $table = new xmldb_table('jitsi');
        $field = new xmldb_field('course', XMLDB_TYPE_INTEGER, '10', XMLDB_UNSIGNED, XMLDB_NOTNULL, null, '0', 'id');

        // Add field course.
        if (!$dbman->field_exists($table, $field)) {
            $dbman->add_field($table, $field);
        }

        // Define field intro to be added to jitsi.
        $table = new xmldb_table('jitsi');
        $field = new xmldb_field('intro', XMLDB_TYPE_TEXT, 'medium', null, null, null, null, 'name');

        // Add field intro.
        if (!$dbman->field_exists($table, $field)) {
            $dbman->add_field($table, $field);
        }

        // Define field introformat to be added to jitsi.
        $table = new xmldb_table('jitsi');
        $field = new xmldb_field('introformat', XMLDB_TYPE_INTEGER, '4', XMLDB_UNSIGNED, XMLDB_NOTNULL, null, '0',
            'intro');

        // Add field introformat.
        if (!$dbman->field_exists($table, $field)) {
            $dbman->add_field($table, $field);
        }

        // Once we reach this point, we can store the new version and consider the module
        // ... upgraded to the version 2007040100 so the next time this block is skipped.
        upgrade_mod_savepoint(true, 2007040100, 'jitsi');
    }

    // Second example, some hours later, the same day 2007/04/01
    // ... two more fields and one index were added to install.xml (note the micro increment
    // ... "01" in the last two digits of the version).
    if ($oldversion < 2007040101) {

        // Define field timecreated to be added to jitsi.
        $table = new xmldb_table('jitsi');
        $field = new xmldb_field('timecreated', XMLDB_TYPE_INTEGER, '10', XMLDB_UNSIGNED, XMLDB_NOTNULL, null, '0',
            'introformat');

        // Add field timecreated.
        if (!$dbman->field_exists($table, $field)) {
            $dbman->add_field($table, $field);
        }

        // Define field timemodified to be added to jitsi.
        $table = new xmldb_table('jitsi');
        $field = new xmldb_field('timemodified', XMLDB_TYPE_INTEGER, '10', XMLDB_UNSIGNED, XMLDB_NOTNULL, null, '0',
            'timecreated');

        // Add field timemodified.
        if (!$dbman->field_exists($table, $field)) {
            $dbman->add_field($table, $field);
        }

        // Define index course (not unique) to be added to jitsi.
        $table = new xmldb_table('jitsi');
        $index = new xmldb_index('courseindex', XMLDB_INDEX_NOTUNIQUE, ['course']);

        // Add index to course field.
        if (!$dbman->index_exists($table, $index)) {
            $dbman->add_index($table, $index);
        }

        // Another save point reached.
        upgrade_mod_savepoint(true, 2007040101, 'jitsi');
    }

    // Third example, the next day, 2007/04/02 (with the trailing 00),
    // some actions were performed to install.php related with the module.
    if ($oldversion < 2007040200) {

        // Insert code here to perform some actions (same as in install.php).

        upgrade_mod_savepoint(true, 2007040200, 'jitsi');
    }

    if ($oldversion < 2021060300) {

        // Define table jitsi_record to be created.
        $table = new xmldb_table('jitsi_record');

        // Adding fields to table jitsi_record.
        $table->add_field('id', XMLDB_TYPE_INTEGER, '10', null, XMLDB_NOTNULL, XMLDB_SEQUENCE, null);
        $table->add_field('jitsi', XMLDB_TYPE_INTEGER, '10', null, XMLDB_NOTNULL, null, null);
        $table->add_field('link', XMLDB_TYPE_CHAR, '255', null, null, null, null);

        // Adding keys to table jitsi_record.
        $table->add_key('primary', XMLDB_KEY_PRIMARY, ['id']);

        // Adding indexes to table jitsi_record.
        $table->add_index('jitsi', XMLDB_INDEX_NOTUNIQUE, ['jitsi']);

        // Conditionally launch create table for jitsi_record.
        if (!$dbman->table_exists($table)) {
            $dbman->create_table($table);
        }

        // Jitsi savepoint reached.
        upgrade_mod_savepoint(true, 2021060300, 'jitsi');
    }

    if ($oldversion < 2021061802) {
        $table = new xmldb_table('jitsi');
        $field = new xmldb_field('timeclose', XMLDB_TYPE_INTEGER, '10', XMLDB_UNSIGNED, XMLDB_NOTNULL, null, '0',
            'timeopen');

        if (!$dbman->field_exists($table, $field)) {
            $dbman->add_field($table, $field);
        }

        // Jitsi savepoint reached.
        upgrade_mod_savepoint(true, 2021061802, 'jitsi');
    }

    if ($oldversion < 2021090100) {
        $table = new xmldb_table('jitsi');
        $field = new xmldb_field('validitytime', XMLDB_TYPE_INTEGER, '10', XMLDB_UNSIGNED, null, null, '0',
            'timeclose');

        if (!$dbman->field_exists($table, $field)) {
            $dbman->add_field($table, $field);
        }

        // Jitsi savepoint reached.
        upgrade_mod_savepoint(true, 2021090100, 'jitsi');
    }

    if ($oldversion < 2021092003) {
        if ($onprivatesessionloggedin = $DB->get_record('config_plugins',
            [
                'name' => 'message_provider_mod_jitsi_onprivatesession_loggedin',
            ])) {
            $onprivatesessionloggedin->value = 'airnotifier,popup';
            $DB->update_record('config_plugins', $onprivatesessionloggedin);
        }
        if ($onprivatesessionloggedoff = $DB->get_record('config_plugins',
            [
                'name' => 'message_provider_mod_jitsi_onprivatesession_loggedoff',
            ])) {
            $onprivatesessionloggedoff->value = 'airnotifier,popup';
            $DB->update_record('config_plugins', $onprivatesessionloggedoff);
        }
        upgrade_mod_savepoint(true, 2021092003, 'jitsi');
    }

    if ($oldversion < 2021101502) {
        $table = new xmldb_table('jitsi');
        $field = new xmldb_field('token', XMLDB_TYPE_TEXT, null, null, null, null, null);

        if (!$dbman->field_exists($table, $field)) {
            $dbman->add_field($table, $field);
        }

        $records = $DB->get_records('jitsi');
        foreach ($records as $record) {
            $record->token = bin2hex(random_bytes(32));
            $DB->update_record('jitsi', $record);
        }
        upgrade_mod_savepoint(true, 2021101502, 'jitsi');
    }

    if ($oldversion < 2021101503) {

        // Define table jitsi_record to be created.
        $table = new xmldb_table('jitsi_record_account');

        // Adding fields to table jitsi_record.
        $table->add_field('id', XMLDB_TYPE_INTEGER, '10', null, XMLDB_NOTNULL, XMLDB_SEQUENCE, null);
        $table->add_field('name', XMLDB_TYPE_CHAR, '255', null, null, null, null);
        $table->add_field('clientaccesstoken', XMLDB_TYPE_TEXT, null, null, null, null, null);
        $table->add_field('clientrefreshtoken', XMLDB_TYPE_TEXT, null, null, null, null, null);
        $table->add_field('tokencreated', XMLDB_TYPE_INTEGER, '10', null, null, null, null);

        // Adding keys to table jitsi_record.
        $table->add_key('primary', XMLDB_KEY_PRIMARY, ['id']);

        // Adding indexes to table jitsi_record.

        // Conditionally launch create table for jitsi_record.
        if (!$dbman->table_exists($table)) {
            $dbman->create_table($table);
        }

        // Jitsi savepoint reached.
        upgrade_mod_savepoint(true, 2021101503, 'jitsi');
    }

    if ($oldversion < 2021101504) {
        $table = new xmldb_table('jitsi_record');
        $field = new xmldb_field('deleted', XMLDB_TYPE_INTEGER, '1', XMLDB_UNSIGNED, XMLDB_NOTNULL, null, '0');

        if (!$dbman->field_exists($table, $field)) {
            $dbman->add_field($table, $field);
        }
        upgrade_mod_savepoint(true, 2021101504, 'jitsi');
    }

    if ($oldversion < 2021101900) {

        // Define table jitsi_record to be created.
        $table = new xmldb_table('jitsi_source_record');

        // Adding fields to table jitsi_record.
        $table->add_field('id', XMLDB_TYPE_INTEGER, '10', null, XMLDB_NOTNULL, XMLDB_SEQUENCE, null);
        $table->add_field('link', XMLDB_TYPE_CHAR, '255', null, null, null, null);
        $table->add_field('account', XMLDB_TYPE_INTEGER, '10', null, null, null, null);

        // Adding keys to table jitsi_record.
        $table->add_key('primary', XMLDB_KEY_PRIMARY, ['id']);

        // Adding indexes to table jitsi_record.

        // Conditionally launch create table for jitsi_record.
        if (!$dbman->table_exists($table)) {
            $dbman->create_table($table);
        }

        $tablerecord = new xmldb_table('jitsi_record');
        $fieldsource = new xmldb_field('source', XMLDB_TYPE_INTEGER, '10', null, null, null, null);

        if (!$dbman->field_exists($tablerecord, $fieldsource)) {
            $dbman->add_field($tablerecord, $fieldsource);
        }

        $records = $DB->get_records('jitsi_record', []);

        foreach ($records as $record) {
            $source = new stdClass();
            $source->link = $record->link;
            $source->account = 1;

            $sourceid = $DB->insert_record('jitsi_source_record', $source);
            $record->source = $sourceid;

            $DB->update_record('jitsi_record', $record);

        }
        $fieldlink = new xmldb_field('link');

        $dbman->drop_field($tablerecord, $fieldlink);

        // Jitsi savepoint reached.
        upgrade_mod_savepoint(true, 2021101900, 'jitsi');
    }

    if ($oldversion < 2021102100) {
        $table = new xmldb_table('jitsi_source_record');
        $field = new xmldb_field('timecreated', XMLDB_TYPE_INTEGER, '10', XMLDB_UNSIGNED, null, null, '0',
            'account');

        if (!$dbman->field_exists($table, $field)) {
            $dbman->add_field($table, $field);
        }

        // Jitsi savepoint reached.
        upgrade_mod_savepoint(true, 2021102100, 'jitsi');
    }

    if ($oldversion < 2021102401) {
        $table = new xmldb_table('jitsi_record');
        $field = new xmldb_field('visible', XMLDB_TYPE_INTEGER, '1', XMLDB_UNSIGNED, null, null, '1');

        if (!$dbman->field_exists($table, $field)) {
            $dbman->add_field($table, $field);
        }
        upgrade_mod_savepoint(true, 2021102401, 'jitsi');
    }

    if ($oldversion < 2021102500) {
        $table = new xmldb_table('jitsi_record');
        $field = new xmldb_field('name', XMLDB_TYPE_TEXT, '50', null, null, null, null);

        if (!$dbman->field_exists($table, $field)) {
            $dbman->add_field($table, $field);
        }
        upgrade_mod_savepoint(true, 2021102500, 'jitsi');
    }

    if ($oldversion < 2021110400) {
        $table = new xmldb_table('jitsi_record_account');
        $field = new xmldb_field('inuse', XMLDB_TYPE_INTEGER, '1', true, '0', null, null);
        $field = new xmldb_field('inuse', XMLDB_TYPE_INTEGER, '1', null, XMLDB_NOTNULL, null, '0', 'tokencreated');

        if (!$dbman->field_exists($table, $field)) {
            $dbman->add_field($table, $field);
        }
        upgrade_mod_savepoint(true, 2021110400, 'jitsi');

        $records = $DB->get_records('jitsi_record', []);
        if ($records) {
            $accesstoken = $DB->get_record('config_plugins', ['name' => 'jitsi_clientaccesstoken']);
            $refreshtoken = $DB->get_record('config_plugins', ['name' => 'jitsi_clientrefreshtoken']);
            $tokencreated = $DB->get_record('config_plugins', ['name' => 'jitsi_tokencreated']);

            $account = new stdClass();
            $account->name = 'Migrated';
            $account->clientaccesstoken = $accesstoken->value;
            $account->clientrefreshtoken = $refreshtoken->value;
            $account->tokencreated = $tokencreated->value;
            $account->inuse = 1;
            $DB->insert_record('jitsi_record_account', $account);
        }
    }

    if ($oldversion < 2021112501) {
        $table = new xmldb_table('jitsi');
        $field = new xmldb_field('completionminutes', XMLDB_TYPE_INTEGER, '3', null, null, null, null);

        if (!$dbman->field_exists($table, $field)) {
            $dbman->add_field($table, $field);
        }
        upgrade_mod_savepoint(true, 2021112501, 'jitsi');
    }

    if ($oldversion < 2021121600) {
        $table = new xmldb_table('jitsi_source_record');
        $field = new xmldb_field('userid', XMLDB_TYPE_INTEGER, '10', null, null, null, null);

        if (!$dbman->field_exists($table, $field)) {
            $dbman->add_field($table, $field);
        }
        upgrade_mod_savepoint(true, 2021121600, 'jitsi');
    }

    if ($oldversion < 2022052800) {
        global $CFG, $DB;
        if ($CFG->jitsi_app_id != null && $CFG->jitsi_secret != null) {
            $config = new stdClass();
            $config->plugin = 'jitsi';
            $config->name = 'tokentype';
            $config->value = 1;

            $DB->insert_record('config_plugins', $config);
        }
        upgrade_mod_savepoint(true, 2022052800, 'jitsi');
    }

    if ($oldversion < 2022070400) {

        // Changing type of field name on table jitsi_record to text.
        $table = new xmldb_table('jitsi_record');
        $field = new xmldb_field('name', XMLDB_TYPE_TEXT, null, null, XMLDB_NOTNULL, null, null, 'visible');

        // Launch change of type for field name.
        $dbman->change_field_type($table, $field);

        // Jitsi savepoint reached.
        upgrade_mod_savepoint(true, 2022070400, 'jitsi');
    }

    if ($oldversion < 2022093000) {

        // Changing type of field name on table jitsi_record to text.
        $table = new xmldb_table('jitsi_source_record');
        $field = new xmldb_field('embed', XMLDB_TYPE_INTEGER, '1', XMLDB_UNSIGNED, XMLDB_NOTNULL, null, '0');
        // Launch change of type for field name.
        $dbman->add_field($table, $field);

        // Jitsi savepoint reached.
        upgrade_mod_savepoint(true, 2022093000, 'jitsi');
    }

    if ($oldversion < 2022120900) {

        // Define field status to be added to jitsi.
        $table = new xmldb_table('jitsi');
        $field = new xmldb_field('status', XMLDB_TYPE_CHAR, '50', null, null, null, null, 'completionminutes');

        // Conditionally launch add field status.
        if (!$dbman->field_exists($table, $field)) {
            $dbman->add_field($table, $field);
        }

        // Jitsi savepoint reached.
        upgrade_mod_savepoint(true, 2022120900, 'jitsi');
    }

    if ($oldversion < 2023011000) {

        // Define field status to be added to jitsi.
        $table = new xmldb_table('jitsi');
        $field = new xmldb_field('numberofparticipants', XMLDB_TYPE_INTEGER, '3', XMLDB_UNSIGNED
            , XMLDB_NOTNULL, null, '0', 'status');

        // Conditionally launch add field status.
        if (!$dbman->field_exists($table, $field)) {
            $dbman->add_field($table, $field);
        }

        // Jitsi savepoint reached.
        upgrade_mod_savepoint(true, 2023011000, 'jitsi');
    }

    if ($oldversion < 2023012601) {

        // Define field status to be dropped from jitsi.
        $table = new xmldb_table('jitsi');
        $field = new xmldb_field('status');

        // Conditionally launch drop field status.
        if ($dbman->field_exists($table, $field)) {
            $dbman->drop_field($table, $field);
        }

        // Jitsi savepoint reached.
        upgrade_mod_savepoint(true, 2023012601, 'jitsi');
    }

    if ($oldversion < 2023021600) {

        // Define field sourcerecord to be added to jitsi.
        $table = new xmldb_table('jitsi');
        $field = new xmldb_field('sourcerecord', XMLDB_TYPE_INTEGER, '10', null, null, null, null, 'numberofparticipants');

        // Conditionally launch add field sourcerecord.
        if (!$dbman->field_exists($table, $field)) {
            $dbman->add_field($table, $field);
        }

        // Jitsi savepoint reached.
        upgrade_mod_savepoint(true, 2023021600, 'jitsi');
    }

    if ($oldversion < 2023030700) {

        // Define field sourcerecord to be added to jitsi.
        $table = new xmldb_table('jitsi_record_account');
        $field = new xmldb_field('embed');

        // Conditionally launch drop field status.
        if ($dbman->field_exists($table, $field)) {
            $dbman->drop_field($table, $field);
        }

        $table = new xmldb_table('jitsi_source_record');
        $field = new xmldb_field('embed', XMLDB_TYPE_INTEGER, '1', XMLDB_UNSIGNED, XMLDB_NOTNULL, null, '0');

        if (!$dbman->field_exists($table, $field)) {
            // Launch change of type for field name.
            $dbman->add_field($table, $field);
        }

        // Jitsi savepoint reached.
        upgrade_mod_savepoint(true, 2023030700, 'jitsi');
    }

    if ($oldversion < 2023051001) {

        // Define field maxparticipants to be added to jitsi_source_record.
        $table = new xmldb_table('jitsi_source_record');
        $field = new xmldb_field('maxparticipants', XMLDB_TYPE_INTEGER, '3', null, null, null, null, 'embed');

        // Conditionally launch add field maxparticipants.
        if (!$dbman->field_exists($table, $field)) {
            $dbman->add_field($table, $field);
        }

        // Jitsi savepoint reached.
        upgrade_mod_savepoint(true, 2023051001, 'jitsi');
    }

    if ($oldversion < 2023052901) {

        // Define field inqueue to be added to jitsi_record_account.
        $table = new xmldb_table('jitsi_record_account');
        $field = new xmldb_field('inqueue', XMLDB_TYPE_INTEGER, '1', null, XMLDB_NOTNULL, null, '0', 'inuse');

        // Conditionally launch add field inqueue.
        if (!$dbman->field_exists($table, $field)) {
            $dbman->add_field($table, $field);
        }

        // Jitsi savepoint reached.
        upgrade_mod_savepoint(true, 2023052901, 'jitsi');
    }

    if ($oldversion < 2023111500) {

        // Define field inqueue to be added to jitsi_record_account.
        $table = new xmldb_table('jitsi');
        $field = new xmldb_field('sessionwithtoken', XMLDB_TYPE_INTEGER, '1', null, XMLDB_NOTNULL, null, '0', 'sourcerecord');

        // Conditionally launch add field inqueue.
        if (!$dbman->field_exists($table, $field)) {
            $dbman->add_field($table, $field);
        }
        $field = new xmldb_field('tokeninvitacion', XMLDB_TYPE_TEXT, null, null, null, null, null);

        // Conditionally launch add field inqueue.
        if (!$dbman->field_exists($table, $field)) {
            $dbman->add_field($table, $field);
        }

        // Jitsi savepoint reached.
        upgrade_mod_savepoint(true, 2023111500, 'jitsi');
    }

    if ($oldversion < 2024011200) {
        $table = new xmldb_table('jitsi');
        $field = new xmldb_field('tokeninterno', XMLDB_TYPE_TEXT, null, null, null, null, null);

        if (!$dbman->field_exists($table, $field)) {
            $dbman->add_field($table, $field);
        }

        $records = $DB->get_records('jitsi');
        foreach ($records as $record) {
            $record->tokeninterno = bin2hex(random_bytes(32));
            $DB->update_record('jitsi', $record);
        }
        upgrade_mod_savepoint(true, 2024011200, 'jitsi');
    }

    if ($oldversion < 2024122700) {
        if ($DB->record_exists('config_plugins', ['plugin' => 'jitsi'])) {
            $DB->execute("UPDATE {config_plugins} SET plugin = 'mod_jitsi' WHERE plugin = 'jitsi'");
            upgrade_mod_savepoint(true, 2024122700, 'jitsi');
        }
    }

    if ($oldversion < 2024122701) {
        $DB->execute("
            INSERT INTO {config_plugins} (plugin, name, value)
            SELECT 'mod_jitsi', REPLACE(name, 'jitsi_', ''), value
              FROM {config}
             WHERE name LIKE 'jitsi_%'
        ");

        $DB->execute("
            DELETE FROM {config}
             WHERE name LIKE 'jitsi_%'
        ");

        upgrade_mod_savepoint(true, 2024122701, 'jitsi');
    }

    if ($oldversion < 2024122703) {
        $table = new xmldb_table('jitsi_servers');

        $table->add_field('id', XMLDB_TYPE_INTEGER, '10', null, XMLDB_NOTNULL, XMLDB_SEQUENCE, null, null);
        $table->add_field('name', XMLDB_TYPE_CHAR, '255', null, null, null, null, null, '');
        $table->add_field('type', XMLDB_TYPE_INTEGER, '4', null, XMLDB_NOTNULL, null, '0', null);
        $table->add_field('domain', XMLDB_TYPE_CHAR, '255', null, null, null, null, null, '');
        $table->add_field('appid', XMLDB_TYPE_CHAR, '255', null, null, null, null, null, '');
        $table->add_field('secret', XMLDB_TYPE_CHAR, '255', null, null, null, null, null, '');
        $table->add_field('eightbyeightappid', XMLDB_TYPE_CHAR, '255', null, null, null, null, null, '');
        $table->add_field('eightbyeightapikeyid', XMLDB_TYPE_CHAR, '255', null, null, null, null, null, '');
        $table->add_field('privatekey', XMLDB_TYPE_TEXT, null, null, null, null, null, null);
        $table->add_field('timecreated', XMLDB_TYPE_INTEGER, '10', null, XMLDB_NOTNULL, null, '0', null);
        $table->add_field('timemodified', XMLDB_TYPE_INTEGER, '10', null, XMLDB_NOTNULL, null, '0', null);

        $table->add_key('primary', XMLDB_KEY_PRIMARY, ['id']);

        if (!$dbman->table_exists($table)) {
            $dbman->create_table($table);
        }

        upgrade_mod_savepoint(true, 2024122703, 'jitsi');
    }

    if ($oldversion < 2024122704) {
        $table = new xmldb_table('jitsi_servers');
        if ($dbman->table_exists($table)) {

            if (!$DB->record_exists('jitsi_servers', ['domain' => 'meet.jit.si'])) {
                $defaultserver = new stdClass();
                $defaultserver->name         = 'Meet JitSi default';
                $defaultserver->type         = 0;
                $defaultserver->domain       = 'meet.jit.si';
                $defaultserver->appid        = '';
                $defaultserver->secret       = '';
                $defaultserver->eightbyeightappid    = '';
                $defaultserver->eightbyeightapikeyid = '';
                $defaultserver->privatekey   = '';
                $defaultserver->timecreated  = time();
                $defaultserver->timemodified = time();
                $DB->insert_record('jitsi_servers', $defaultserver);
            }

            $olddomain = get_config('mod_jitsi', 'domain');
            if (!empty($olddomain) && $olddomain != 'meet.jit.si') {
                $oldtype = get_config('mod_jitsi', 'tokentype');
                $oldappid = get_config('mod_jitsi', 'app_id');
                $oldsecret = get_config('mod_jitsi', 'secret');
                $old8x8appid = get_config('mod_jitsi', '8x8app_id');
                $old8x8apikeyid = get_config('mod_jitsi', '8x8apikey_id');
                $oldprivatekey = get_config('mod_jitsi', 'privatykey');
                if (!$DB->record_exists('jitsi_servers', ['domain' => $olddomain])) {
                    $server = new stdClass();
                    $server->name         = $olddomain;
                    $server->type         = $oldtype;
                    $server->domain       = $olddomain;
                    $server->appid        = $oldappid;
                    $server->secret       = $oldsecret;
                    $server->eightbyeightappid    = $old8x8appid;
                    $server->eightbyeightapikeyid = $old8x8apikeyid;
                    $server->privatekey   = $oldprivatekey;
                    $server->timecreated  = time();
                    $server->timemodified = time();

                    $newserverid = $DB->insert_record('jitsi_servers', $server);
                    set_config('server', $newserverid, 'mod_jitsi');
                } else {
                    $existingsrv = $DB->get_record('jitsi_servers', ['domain' => $olddomain]);
                    set_config('server', $existingsrv->id, 'mod_jitsi');
                }
            }
        }

        upgrade_mod_savepoint(true, 2024122704, 'jitsi');
    }

    if ($oldversion < 2024122706) {
        unset_config('tokentype', 'mod_jitsi');
        unset_config('app_id', 'mod_jitsi');
        unset_config('secret', 'mod_jitsi');
        unset_config('8x8app_id', 'mod_jitsi');
        unset_config('8x8apikey_id', 'mod_jitsi');
        unset_config('privatykey', 'mod_jitsi');
        unset_config('domain', 'mod_jitsi');

        upgrade_mod_savepoint(true, 2024122706, 'jitsi');
    }

    return true;
}
