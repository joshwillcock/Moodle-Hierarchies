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
global $DB;

$PAGE->set_url('/blocks/hierarchy/manageTags.php');
$PAGE->set_context(context_system::instance());
$PAGE->set_pagelayout('admin');
$strheading = get_string('managetags', 'block_hierarchy');
$PAGE->set_title($strheading);
$PAGE->set_heading($strheading);
if(isset($_GET['filter'])){
$filter=$_GET['filter'];
}
require_login();
global $USER;
$system = context_system::instance();
$isadmin = has_capability('block/hierarchy:managetags', $system, $USER->id);
echo $OUTPUT->header();
if($isadmin==1){

  //if submit
  //Update All Usertag
  //Update All Manager Tag
  //Delete A TAG
  // Create Alias

  // Display Done Message


  /*Content*/
    echo '<div class="manageTable">';
  if(isset($_POST['tagToMerge'])&&isset($_POST['mergeInto'])){
    $beingDestoyed = $_POST['tagToMerge'];
    $becomingMorePowerful = $_POST['mergeInto'];
     //Update All Usertag
    $DB->execute('UPDATE {tag_usertags} SET tagid='.$becomingMorePowerful.' WHERE tagid='.$beingDestoyed);
    $DB->execute('UPDATE {tag_managertags} SET tagid='.$becomingMorePowerful.' WHERE tagid='.$beingDestoyed);
    $deadTag = $DB->get_record('tag_tags', array('id'=>$beingDestoyed));
    $recordToInsert = new stdClass();
    $recordToInsert->string=$becomingMorePowerful;
    $recordToInsert->override=$deadTag->name;
    $DB->insert_record('tag_aliaslist', $recordToInsert);
    $DB->delete_records('tag_tags', array('id'=>$beingDestoyed));

echo '<a>All users and managers have been updated. The tag has been removed and an alias rule created.</a><br><a href="manageTags.php">Return to Manage Tags</a>';
  }else{
    echo '<br><a> I am afraid there has been an issue, please return to the previous page.</a>';
  }


    echo '</div>';
  }else{
    echo '<div class="alert alert-error">I am afraid you do not have the correct permissions to access this page. If you feel this is in error please contact your administrator</div>';
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


