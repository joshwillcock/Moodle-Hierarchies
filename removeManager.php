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


$PAGE->set_url('/blocks/hierarchy/removeManager.php');
$PAGE->set_context(context_system::instance());
$PAGE->set_pagelayout('admin');
$strheading = get_string('managetags', 'block_hierarchies');
$PAGE->set_title($strheading);
$PAGE->set_heading($strheading);
$filter = $_GET['filter'];
require_login();
global $USER;
global $DB;
      $system = context_system::instance();
       $isadmin = has_capability('block/hierarchy:managetags', $system, $USER->id);
       if ($isadmin == 1) {

$userid= $_GET['id'];
$DB->delete_records('hierarchy_ismanager', array('userid' => $userid));
$DB->insert_record('hierarchy_managerlog', array('userid' => $userid,'adminid' => $USER->id,'action' => 'Manager Status Disabled'));


    echo $OUTPUT->header();
 
    echo '<div class="manageTable" style="max-width:400px; width:100%;">
  <h2 style="font-size:1.1em; color:#999; line-height:20px; text-transform:uppercase; word-wrap:break-word;">'.get_string('disabledmanager', 'block_hierarchy').'</h2>
    '.get_string('managerdisabledsuccess', 'block_hierarchy').'<br>
    <div class="editClose"> <a href="'.$CFG->wwwroot.'/user/profile.php?id='.$userid.'">'.get_string('continue', 'block_hierarchy').'</a>
  </div></div>';
      echo '<div class="manageTable" style="width:400px; ">
  <h2 style="font-size:1.1em; color:#999; line-height:20px; text-transform:uppercase; word-wrap:break-word;">Log:</h2>';
$logDataSQL = 'SELECT ml.`id`, mu.`firstname`, mu.`lastname`, ml.`action`, ml.`date` FROM {hierarchy_managerlog} ml INNER JOIN {user} mu ON (mu.`id`=ml.`adminid`) WHERE ml.`userid`='.$userid.' ORDER BY ml.`date` DESC LIMIT 15';
$logData = $DB->get_records_sql($logDataSQL);
echo '<table width="100%"><tr><td>'.get_string('admin', 'block_hierarchy').'</td><td>'.get_string('action', 'block_hierarchy').'</td><td>'.get_string('date', 'block_hierarchy').'</td></tr>';
foreach($logData as $logDatas) {
  echo '<tr><td>'.$logDatas->firstname.' '.$logDatas->lastname.'</td>';
      echo '<td>'.$logDatas->action.'</td>';
        echo '<td>'.$logDatas->date.'</td>';
        echo '</tr>';
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



