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
 * TODO describe file index
 *
 * @package    local_greetings
 * @copyright  2026 Jose Lorenzo <jose.lorenzo@rdt.com>
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

require('../../config.php');
require_once($CFG->dirroot . '/local/greetings/lib.php');


require_login();

$url = new moodle_url('/local/greetings/index.php', []);
$PAGE->set_url($url);

$context = context_system::instance();
$PAGE->set_context($context);

$PAGE->set_pagelayout('standard');
$PAGE->set_title(get_string('pluginname', 'local_greetings'));

$PAGE->set_heading(get_string('pluginname', 'local_greetings'));

echo $OUTPUT->header();

if (isloggedin()) {
    // $usergreeting = 'Greetings, ' . fullname($USER);.
    // $usergreeting = get_string('greetingloggedinuser', 'local_greetings', fullname($USER));
    $usergreeting = local_greetings_get_greeting($USER);
} else {
    // $usergreeting = 'Greetings, user';
    $usergreeting = get_string('greetinguser', 'local_greetings');
}

$templatedata = ['usergreeting' => $usergreeting];

echo $OUTPUT->render_from_template('local_greetings/greeting_message', $templatedata);


// $messages = $DB->get_records('local_greetings_messages');

$userfields = \core_user\fields::for_name()->with_identity($context);
$userfieldssql = $userfields->get_sql('u');

$sql = "SELECT m.id, m.message, m.timecreated, m.userid {$userfieldssql->selects}
          FROM {local_greetings_messages} m
     LEFT JOIN {user} u ON u.id = m.userid
      ORDER BY timecreated DESC";

$messages = $DB->get_records_sql($sql);

$messageform = new \local_greetings\form\message_form();
$messageform->display();

// foreach ($messages as $m) {
//     echo '<p>' . $m->message . ', ' . $m->timecreated . '</p>';
// }
$templatedata = ['messages' => array_values($messages)];
echo $OUTPUT->render_from_template('local_greetings/messages', $templatedata);

if ($data = $messageform->get_data()) {
    $message = required_param('message', PARAM_TEXT);

    // AÑADIMOS EL MENSAJE PERSONALIZADO AL SALUDO POR DEFECTO.
   if (!empty($message)) {
        $record = new stdClass;
        $record->message = $message;
        $record->timecreated = time();
        $record->userid = $USER->id;

        $DB->insert_record('local_greetings_messages', $record);
    }
}

echo $OUTPUT->footer();