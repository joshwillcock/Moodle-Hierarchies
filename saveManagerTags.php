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
 *
 * @package   block_hierarchy
 * @copyright 2014 Josh Willcock (http://joshwillcock.co.uk)
 * @license   http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

require_once(dirname(__FILE__) . '/../../config.php');
require_once($CFG->libdir.'/adminlib.php');
$PAGE->set_url('/blocks/hierarchy/saveManagerTags.php');
$PAGE->set_context(context_system::instance());
require_login();
global $USER;
global $DB;
global $CFG;
$system = context_system::instance();
$isadmin = has_capability('block/hierarchy:managetags', $system, $USER->id);
if ($isadmin == 1) {
    $userid = $_POST['userid'];
    $catsql = 'SELECT * FROM {hierarchy_category}';
    $getcategories = $DB->get_records_sql($catsql);
    $DB->delete_records('hierarchy_managertags', array('userid' => $userid));
    $recordstoinsert = array();
    foreach ($getcategories as $category) {
        if ($_POST[$category->id] != "") {
            foreach ($_POST[$category->id] as $selectedoption) {
                $entry = new stdClass();
                $entry->userid = $userid;
                $entry->tagid = $selectedoption;
                $recordstoinsert[] = $entry;
            }
        }
    }
     $DB->insert_records('hierarchy_managertags', $recordstoinsert);
     header('Location: '.$CFG->wwwroot.'/user/profile.php?id='.$userid);
} else {
    echo $OUTPUT->header();
    echo '<div class="alert alert-error">'.get_string('nopermissions', 'block_hierarchy').'</div>';
}