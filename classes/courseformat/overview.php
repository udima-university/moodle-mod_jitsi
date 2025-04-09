<?php
namespace mod_jitsi\courseformat;

defined('MOODLE_INTERNAL') || die();

use core_courseformat\activityoverviewbase;
use core_courseformat\local\overview\overviewitem;
use moodle_url;
use html_writer;

/**
 * Overview class to display information about the Jitsi module in the course overview (Moodle 5.0+).
 */
class overview extends activityoverviewbase {

    /**
     * Returns additional items to be displayed in the course overview.
     *
     * @return overviewitem[]
     */
    public function get_extra_overview_items(): array {
        return [
            'sessionstatus' => $this->get_session_timeopen_item(),
        ];
    }

    /**
     * Creates an item that displays the timeopen of the Jitsi session.
     *
     * @return overviewitem|null
     */
    private function get_session_timeopen_item(): ?overviewitem {
        global $DB;
        $jitsi = $DB->get_record('jitsi', ['id' => $this->cm->instance]);
        if ($jitsi->timeopen != 0) {
            return new overviewitem(
                name: get_string('timeopen', 'mod_jitsi'),
                value: $sessionactive ? 1 : 0,
                content: userdate($jitsi->timeopen),
            );
        } else {
            return null;
        }

    }

    /**
     * Return main action.
     *
     * @return overviewitem|null
     */
    public function get_actions_overview(): ?overviewitem {
        $url = new moodle_url('/mod/jitsi/view.php', ['id' => $this->cm->id]);
        $link = html_writer::link($url, get_string('joinmeeting', 'mod_jitsi'));

        return new overviewitem(
            name: get_string('action', 'mod_jitsi'),
            value: 'join',
            content: $link
        );
    }
}
