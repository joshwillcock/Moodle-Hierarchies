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

require_once(dirname(__FILE__) . '/../../config.php');
require_once($CFG->libdir.'/adminlib.php');
require_once('locallib.php');
require_login();
global $USER, $DB;

$PAGE->set_url('/blocks/hierarchy/aliasList.php');
$PAGE->set_context(context_system::instance());
$PAGE->set_pagelayout('admin');
$strheading = get_string('aliaslist', 'block_hierarchy');
$PAGE->set_title($strheading);
$PAGE->set_heading($strheading);
if (isset($_GET['filter'])) {
    $filter = $_GET['filter'];
} else {
    $filter = "";
}
$system = context_system::instance();
$isadmin = has_capability('block/hierarchy:bulktool', $system, $USER->id);
if ($isadmin == 1) {
    echo $OUTPUT->header();
    /*Content*/
    global $DB;
    $msg = "";
    if (isset($_GET['action'])) {
        if ($_GET['action'] == "delete") {
            $idtodelete = $_GET['id'];
            $DB->delete_records('hierarchy_aliaslist', array('id' => $idtodelete));
            $msg = get_string('aliasmessage', 'block_hierarchy');
        }
    }
    if (isset($_POST['submit'])) {
        $newalias = $_POST['alias'];
        $newaliastag = $_POST['tag'];
        if ($newaliastag != "") {
            $DB->insert_record('hierarchy_aliaslist', array ('override' => $newalias, 'string' => $newaliastag));
            $msg = get_string('aliasmessage', 'block_hierarchy').': '.$newalias;
        } else {
            $msg = get_string('refertag', 'block_hierarchy');
        }
    }
    $aliasarraytag = $DB->get_records_sql('SELECT al.id AS id, tt.name AS string,
    tc.name AS category, al.override AS override FROM {hierarchy_aliaslist} al
    INNER JOIN {hierarchy_tags} tt ON (tt.id= al.string) LEFT JOIN {hierarchy_category}
    tc ON (tc.id = tt.category) ORDER BY al.override ASC');
    echo '<div class="manageTable" style="width:600px;">'.$msg.'<br><table>';
    echo '<form action="aliasList.php" method="POST">';
    echo '<tr style="text-align:center; font-size:14pt;"><td width="260px">'.get_string('aliasname', 'block_hierarchy');
    echo '</td><td style="text-align:center;" width="30px"></td><td width="260px">';
    echo get_string('refersto', 'block_hierarchy').'</td><td width="30px"></td></tr>';
    echo '<tr><td><input type="text" required style="width:85%;" id="alias" name="alias" placeholder="';
    echo get_string('addnewalias', 'block_hierarchy').'"></td><td>--></td><td>';
    echo '<select required id="tag" name="tag" style="width:100%;"> <option selected disabled>';
    echo get_string('pleaseselect', 'block_hierarchy').'</option>';
    $catarray = $DB->get_records('hierarchy_category', array());
    foreach ($catarray as $cat) {
        echo '<optgroup label="'.$cat->name.'">';
        $tagarray = $DB->get_records('hierarchy_tags', array('category' => $cat->id));
        foreach ($tagarray as $tag) {
            echo   '<option value="'.$tag->id.'">'.$tag->name.'</option>';
        }
    }
    echo '</select></td><td><button id="submit" name="submit" type="submit" class="stealthbutton">';
    echo '<img width="32px" src="pix/add-icon.png"></button></form></td></tr> ';
    foreach ($aliasarraytag as $alias) {
        echo '<tr class="tablerow" style="border-bottom:1px solid #eee;"><td>'.$alias->override.'</td><td> --> </td><td>';
        echo $alias->string.': '.$alias->category.'</td><td><a href="aliasList.php?action=delete&id='.$alias->id.'">';
        echo ' <img width="24px" src="pix/delete-icon.png" alt="Delete"></a></td></tr>';
    }
    echo '</table></div>';
} else {
    echo '<div class="alert alert-error">'.get_string('nopermissions', 'block_hierarchy').'</div>';
}
$error = optional_param('error', '', PARAM_NOTAGS);
if ($error) {
    echo $OUTPUT->notification($error, 'notifyerror');
}
$success = optional_param('success', '', PARAM_NOTAGS);
if ($success) {
    echo $OUTPUT->notification($success, 'notifysuccess');
}
echo $OUTPUT->footer();