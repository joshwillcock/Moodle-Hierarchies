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
if (isset($_GET['filter'])) {
$filter = $_GET['filter'];
}
require_login();
global $USER;
$system = context_system::instance();
$isadmin = has_capability('block/hierarchy:managetags', $system, $USER->id);
if ($isadmin == 1) {


//Add New Tag submit
  if (isset($_POST['addbtn'])) {
    $newTags = $_POST['newTag'];
    $newTags = explode(',',$newTags);
    $recordstoinsert = array();
    foreach ($newTags as $newTag) {
      $tag = new stdClass();
     $newTag = preg_replace('/\G\s|\s(?=\s*$)/', '', $newTag);
      $tag->name = $newTag;
      $tag->category = $_GET['filter'];
      $recordstoinsert[] = $tag;
  //2.6+  $DB->insert_record('hierarchy_tags', $tag);
    }
    $DB->insert_records('hierarchy_tags', $recordstoinsert);
 //2.7 insert statment
  }elseif (isset($_POST['editbtn'])) {
    $DB->update_record('hierarchy_tags',array('id'  => $_POST['id'],'category' => $_POST['cat'],'name'  => $_POST['tagName']));
  }




  echo $OUTPUT->header();
  /*Content*/
  if (isset($_GET['action'])) {
  if ($_GET['action'] == "confirm") {
    $DB->delete_records('hierarchy_tags', array('id'  => $_GET['id']));
  }
}
if (isset($_GET['filter'])) {
  $filter = $_GET['filter'];
} else {
  $filter = "";
}
  if ($filter == "") {
    echo '<div class="manageTable">
    <table >
      <tr class="header">
        <td width="240px">';
          echo new lang_string('filterby','block_hierarchy');
          echo '</td></tr>'; 

          $result = $DB->get_records('hierarchy_category');
          foreach ($result as $category) {
            echo '<tr class="tableEntry"><td><a href="manageTags.php?action=view&filter='.$category->id.'">'.$category->name.'</a></td></tr>';
          }
          echo '<tr class="tableEntry"><td><a href="manageTags.php?action=view&filter=none">'.new lang_string('viewall','block_hierarchy').'</a></td></tr>';
          echo '  </table></div>';
        } else {
         echo '<div class="manageTable" style="width:400px;">
         <table>
          <tr class="header">
            <td width="180px">';
              echo new lang_string('tag','block_hierarchy');
              echo '</td><td width="140px">';
              echo new lang_string('category','block_hierarchy');
              echo '</td><td>';
              echo '</td><td >';
              if ($_GET['filter'] != "none") {
               echo '<a href="manageTags.php?action=add&filter='.$_GET['filter'].'"><img width="30px" src="pix/add-icon.png" alt="Add"></a>';
             }
             echo '</td></tr>'; 
             if ($_GET['filter'] == "none") {
              $tagresult = $DB->get_records_sql('SELECT tt.id AS `id`, tt.name AS `name`, tc.id AS `categoryid`, tc.name AS `categoryname` FROM {hierarchy_tags} tt JOIN {hierarchy_category} tc ON (tc.id = tt.category) ORDER BY tc.name ASC');
            } else {
             
             $tagresult = $DB->get_records_sql('SELECT tt.id AS `id`, tt.name AS `name`, tc.id AS `categoryid`, tc.name AS `categoryname` FROM {hierarchy_tags} tt JOIN {hierarchy_category} tc ON (tc.id = tt.category) WHERE tc.id='.$filter.' ORDER BY tc.name ASC');
           }
           foreach ($tagresult as $tag) {
             $getTags = '';
             echo '<tr class="tableEntry"><td>'.$tag->name.'</td>
             <td>'.$tag->categoryname.'</td>
             <td><a href="manageTags.php?action=editTag&tag='.$tag->id.'&filter='.$filter.'"><img width="30px" src="pix/edit-icon.png" alt="Edit"></a></td>
             <td><a href="manageTags.php?action=deleteTAG&id='.$tag->id.'&filter='.$filter.'"><img width="30px" src="pix/delete-icon.png" alt="Delete"></a></td></tr>'.PHP_EOL;
             
           }
           echo '  </table> <div class="editClose"><a href="manageTags.php">'.new lang_string('clearfilters','block_hierarchy').'</a></div></div>';
         }
         
         if (isset($_GET['action'])) {
          if ($_GET['action'] == "editTag") {
            $tag = $_GET['tag'];
            $filter = $_GET['filter'];
            $currenttag = $DB->get_record('hierarchy_tags',array('id' => $tag));
            echo '<div class="manageTable"><h2 class="header">'.new lang_string('edittag','block_hierarchy').':</h2>
            <form action="manageTags.php" method="post"><input type="hidden" value="'.$_GET['tag'].'" name="id"><input type="text" value="'.$currenttag->name.'" name="tagName"><br>
             <select name="cat">';

               $result = $DB->get_records('hierarchy_category');
               foreach ($result as $cat) {
                if ($cat->id== $filter) {
                 echo '<option selected value="'.$cat->id.'">'.$cat->name.'</option>';
               } else {
                echo '<option value="'.$cat->id.'">'.$cat->name.'</option>';                   
              }
              
            }
            echo '  </select>
            <br><button name="editbtn" type="submit">'.new lang_string('update', 'block_hierarchy').'</button></form>
            <div class="editClose"> <a href="manageTags.php">'.new lang_string('cancel','block_hierarchy').'</a></div>
          </div>';
          
        }elseif ($_GET['action'] == "deleteTAG") {
          echo '<div class="manageTable"><h2 class="header">'.new lang_string('deletetag','block_hierarchy').'</h2>'.new lang_string('selectedtag','block_hierarchy').':<b>';
          $tagDelete = $DB->get_record('hierarchy_tags', array('id' => $_GET['id']));
          echo $tagDelete->name;
          echo '</b><br><br>
          '.new lang_string('deletewarning','block_hierarchy').' 
          <div class="editClose"> <a href="manageTags.php?action=confirm&id='.$_GET['id'].'&filter='.$filter.'">'.new lang_string('confirm','block_hierarchy').'</a>&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;<a href="managetags.php">'.new lang_string('cancel','block_hierarchy').'</a></div>
        </div>';
      }elseif ($_GET['action'] == "confirm") {
        $DB->delete_records('hierarchy_tags', array('id'  => $_GET['id']));
        echo '<div class="manageTable"><h2 class="header">'.new lang_string('tagdeleted','block_hierarchy').'</h2>
      '.get_string('removetagsuccess', 'block_hierarchy').'.
        <div class="editClose"><a href="manageTags.php">'.new lang_string('close','block_hierarchy').'</a></div>
      </div>';
    }elseif ($_GET['action'] == "add") {
      echo '<div class="manageTable"><h2 class="header">'.new lang_string('add','block_hierarchy').' '.new lang_string('tag','block_hierarchy').'</h2>
      <form method="POST" action="manageTags.php?filter='.$filter.'">
        <input name="newTag" placeholder="Tag Name, Tag Name"><br><br><button name="addbtn"> '.get_string('add', 'block_hierarchy').'</button>   <div class="editClose"> <a href="manageTags.php">'.new lang_string('cancel','block_hierarchy').'</a></div></form>';
        echo '</div>';
      }
    }

    echo '<div style="clear:both;"></div>';

  } else {
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



