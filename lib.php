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
 * Callback implementations for Greetings
 *
 * @package    local_greetings
 * @copyright  2026 Jose Lorenzo <jose.lorenzo@rdt.com>
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

function local_greetings_get_greeting($user) {
    if ($user == null) {
        return get_string('greetinguser', 'local_greetings');
    }

    $country = $user->country;
    switch ($country) {
        case 'ES':
            $langstr = 'greetinguseres';
            break;
        default:
            $langstr = 'greetingloggedinuser';
            break;
    }

    return get_string($langstr, 'local_greetings', fullname($user));
}

/**
* Add link to index.php into navigation block.
*
* @param global_navigation $root Node representing the global navigation tree.
*/
function local_greetings_extend_navigation(global_navigation $root) {
    if (! isguestuser()) {
        
        $node = navigation_node::create(
            get_string('greetings', 'local_greetings'),
            new moodle_url('/local/greetings/index.php'),
            navigation_node::TYPE_CUSTOM,
            null,
            null,
            new pix_icon('t/message', '')
            );
            
            $root->add_node($node);
    }
}

// function local_greetings_extend_navigation_frontpage(global_navigation $root) {
//     $node = navigation_node::create(
//         get_string('greetings', 'local_greetings'),
//         new moodle_url('/local/greetings/index.php'),
//         navigation_node::TYPE_CUSTOM,
//         null,
//         null,
//         new pix_icon('t/message', '')
//     );

//     $root->add_node($node);
// }