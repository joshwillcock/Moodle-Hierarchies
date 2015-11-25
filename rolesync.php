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
require_once($CFG->libdir.'/tablelib.php');
$PAGE->set_url('/blocks/hierarchy/rolesync.php');
$PAGE->set_context(context_system::instance());
$PAGE->set_pagelayout('admin');
$strheading = get_string('rolesync', 'block_hierarchy');
$PAGE->set_title($strheading);
$PAGE->set_heading($strheading);
require_login();
global $USER;
global $DB;
$system = context_system::instance();
$isadmin = has_capability('block/hierarchy:managetags', $system, $USER->id);
if ($isadmin == 1) {
//Add New Category submit
  if (isset($_POST['profilefield'])) {
    $profileid = $_POST['profilefield'];
    $roleid = $_POST['moodleId'];
    $alreadySet = $DB->get_records('hierarchy_rolesync', array('profileid' => $profileid));
    if (empty($alreadySet)) {
    $deleted = $DB->delete_records('hierarchy_rolesync', array('roleid' => $roleid));
    if ($profileid != "donotsync") {
      $added = $DB->insert_record('hierarchy_rolesync', array('roleid' => $roleid, 'profileid' => $profileid),  $returnid=true, $bulk=false);
    }
  } else {
    $msg = "You can only link to a profile field once. Please remove any conflicting relationships first.";
  }
  }
  echo $OUTPUT->header();
  echo '<div class="manageTable" width="600px">
  <h1>Moodle Role Sync</h1>
  <a>From here you can setup how Hierarchies synchronizes your Moodle Roles. You will have the option to link Moodle Roles to profile fields within \'Permissions Category\'</a><br><br>';
  if (isset($msg)) {
    echo '<strong>'.$msg.'</strong><br><br>';
  }
  echo '<table>
    <tr style="font-weight:bold;">
      <td width="40%">Moodle Role</td><td width="20%">&#8594;</td>
      <td width="40%">Profile Field</td>
    </tr>';
    $availableRoles = $DB->get_records_sql('SELECT ro.id AS `id`, ro.shortname AS `shortname`, rc.roleid AS `roleid`, rc.profileid AS `profileid` FROM {role} ro LEFT JOIN {hierarchy_rolesync} rc ON (ro.id = rc.roleid)');
    $permissionCategory = $DB->get_record('user_info_category', array('name' => 'Permissions'));
    if (empty($permissionCategory)) {
      echo 'Please ensure you have a Profile Field Category Called permissions';
    } else {
      $profileFieldOptions = $DB->get_records('user_info_field', array('categoryid' => $permissionCategory->id));
      foreach ($availableRoles as $availableRole) {
        echo '<form action="rolesync.php" method="POST"><tr><td>';
        echo $availableRole->shortname;
        echo '<input type="hidden" name="moodleId" value="'.$availableRole->id.'"></td><td>&#8594;</td>';
        echo '<td><select onchange="this.form.submit()" name="profilefield">
        <option value="donotsync">Do Not Sync</option>';
        foreach($profileFieldOptions as $profileFieldOption) {
          if ($availableRole->profileid == $profileFieldOption->id) {
            echo '<option selected value="'.$profileFieldOption->id.'">'.$profileFieldOption->name.'</option>';
          } else {
            echo '<option value="'.$profileFieldOption->id.'">'.$profileFieldOption->name.'</option>';
          }
        }
        echo'</select></td></form></tr>';
      }
    }
    echo'</table>
  </div>';
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