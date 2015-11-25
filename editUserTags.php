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


$PAGE->set_url('/blocks/hierarchy/editUserTags.php');
$PAGE->set_context(context_system::instance());
$PAGE->set_pagelayout('admin');
$strheading = get_string('tags', 'block_hierarchy');
$PAGE->set_title($strheading);
$PAGE->set_heading($strheading);
if (isset($_GET['filter'])) {
$filter = $_GET['filter'];
}
require_login();
global $USER;
global $DB;
      $system = context_system::instance();
       $isadmin = has_capability('block/hierarchy:managetags', $system, $USER->id);
       if ($isadmin == 1) {



echo '<style>.select{
  float:left;
  -webkit-touch-callout: none;
  -webkit-user-select: none;
  -khtml-user-select: none;
  -moz-user-select: none;
  -ms-user-select: none;
  user-select: none;

}</style>';

global $USER;
global $DB;
$userid= $_GET['id'];echo $OUTPUT->header();

echo '<div class="manageTable" style=" width:90%;">
<h2 style="font-size:1.1em; color:#999; line-height:20px; text-transform:uppercase; word-wrap:break-word;">'.get_string('tagoptions', 'block_hierarchy').': </h2>
<div class="editClose"> <a href="manageCategories.php">Continue</a></div></div>';

echo'<form action="saveUserTags.php" method="POST"><input type="hidden" name="userid" value="'.$userid.'"></input>';

$getcategory = "SELECT * FROM {hierarchy_category} ORDER BY name";
$catResults = $DB->get_records_sql($getcategory);
$currentTags = 'SELECT `tagid` as id FROM {hierarchy_usertags} WHERE `userid`='.$userid;
$currentResults = $DB->get_records_sql($currentTags);

foreach ($catResults as $catResult) {
  echo '<div class="select"><a class="title">'.$catResult->name.'</a><br><select style="width:200px; height:40px;" name="'.$catResult->id.'"><option value = "">None</option>';

  $getTags = 'SELECT tt.`id` AS id, tt.`name` AS `name`, tu.`userid` AS `user`  FROM {hierarchy_tags} tt LEFT JOIN {hierarchy_usertags} tu ON tu.`tagid`=tt.`id` AND tu.`userid`='.$userid.'
WHERE category='.$catResult->id.' ORDER BY tt.`name`';
  $tagResults = $DB->get_records_sql($getTags);
  foreach ($tagResults as $tagResult) {
    echo '<option value="'.$tagResult->id.'" ';
    if ($tagResult->user  != "") {echo 'selected';}
      echo '>'.$tagResult->name.'</option>'.PHP_EOL;
  }
  echo '</select></div>';

}
echo '<div class="select" style="padding-top:20px;"> <button name="submit" style="height:40px; width:196px;" type="submit">'.get_string('savetags', 'block_hierarchy').'</button></form></div><div style="clear:both;"></div>';

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



