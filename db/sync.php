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
 * Scheduled task admin pages.
 *
 * @package    block_hierarchy
 * @copyright  2014 Josh Willcock
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

require_once(dirname(__FILE__) . '/../../../config.php');
require_once($CFG->libdir.'/adminlib.php');
require_once($CFG->libdir.'/tablelib.php');
$PAGE->set_url('/blocks/hierarchy/db/sync.php');
$PAGE->set_context(context_system::instance());
$PAGE->set_pagelayout('admin');
$strheading = get_string('menucategories', 'block_hierarchy');
$PAGE->set_title($strheading);
$PAGE->set_heading($strheading);

require_login();
global $DB;
require_once('../lib.php');
echo $OUTPUT->header();
$createdtag = 0;
$tagfound = 0;
$aliasfound = 0;
echo '<div class="manageTable">
<h1>Manual SSO/Bulk Upload Sync</h1>
<a>Manually check for changes from your last SSO sync or Bulk Upload. This will ensure all your users have all the tags correct after you have fixed any missing tags/categories.</a><br><br><br><a href="sync.php?execute=true">Sync Now</a><br><br>';


if (isset($_GET['execute'])) {
    if ($_GET['execute'] == "true") {
        $usertagtime = new \block_hierarchy\trackTime;
        $usertagtime->start(); 
        $sync = new \block_hierarchy\hierarchysync();
        $sync->sync();
        echo 'Time Taken: '.$usertagtime->timetaken().'secs<br>
        Tags Created: '.$sync->getTagCreated().'<br>'.'Tags Found: '.$sync->getTagFound().'<br>'.
        'Alias Found: '.$sync->getAliasFound().'<br>';
    }
}
echo '</div>';
$error = optional_param('error', '', PARAM_NOTAGS);
if ($error) {
    echo $OUTPUT->notification($error, 'notifyerror');
}
$success = optional_param('success', '', PARAM_NOTAGS);
if ($success) {
    echo $OUTPUT->notification($success, 'notifysuccess');
}
echo $OUTPUT->footer();