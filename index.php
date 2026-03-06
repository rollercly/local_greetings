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
// CONTROLAR SI ES INVITADO, EN ESE CASO NO MOSTRAR EL SALUDO PERSONALIZADO, SINO EL DE USUARIO ANÓNIMO. 
if (isguestuser()) {
    throw new moodle_exception('noguest');
}

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

$allowpost = has_capability('local/greetings:postmessages', $context);
$deleteanypost = has_capability('local/greetings:deleteanymessage', $context);

$messageform = new \local_greetings\form\message_form();

$action = optional_param('action', '', PARAM_TEXT);

// SI SE HA SELECCIONADO ELIMINAR UN MENSAJE, LO ELIMINAMOS SI EL USUARIO TIENE PERMISO PARA ELLO.

if ($action == 'del') {
    require_capability('local/greetings:deleteanymessage', $context);
    $id = required_param('id', PARAM_INT);

    // if ($deleteanypost) {
    //     $DB->delete_records('local_greetings_messages', ['id' => $id]);
    // }
    $DB->delete_records('local_greetings_messages', ['id' => $id]);
}

// SI EL USUARIO TIENE PERMISO PARA PUBLICAR MENSAJES, MOSTRAMOS EL FORMULARIO PARA
if ($allowpost) {
    $messageform->display();
}


$templatedata = [
    'messages' => array_values($messages),
    'candeleteany' => $deleteanypost,
];

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