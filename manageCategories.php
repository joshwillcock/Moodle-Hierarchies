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
$PAGE->set_url('/blocks/hierarchy/manageCategories.php');
$PAGE->set_context(context_system::instance());
$PAGE->set_pagelayout('admin');
$strheading = get_string('menucategories', 'block_hierarchy');
$PAGE->set_title($strheading);
$PAGE->set_heading($strheading);

require_login();
global $USER;
global $DB;
      $system = context_system::instance();
       $isadmin = has_capability('block/hierarchy:managetags', $system, $USER->id);
       if ($isadmin == 1) {





//Add New Category submit
if (!empty($_POST)) {
if (isset($_POST['addbtn'])) {
  $newCategories = $_POST['newCat'];
  $newCategories = explode(',',$newCategories);
  $recordstoinsert = array();
  foreach ($newCategories as $newCategory) {
    $category = new stdClass();
        $newCategory = preg_replace('/\G\s|\s(?=\s*$)/', '', $newCategory);
    $category->name = $newCategory;
//2.7+ only $recordstoinsert[] = $category;
  $DB->insert_record('hierarchy_category', $category, $returnid=false, $bulk=false);
} 

 //2.7+ only | $DB->insert_record('hierarchy_category', $recordstoinsert, $returnid=false, $bulk=true);
}elseif (isset($_POST['editbtn'])) {
$DB->update_record('hierarchy_category',array('id'  => $_POST['id'],'name'  => $_POST['catName']));

}
}
    echo $OUTPUT->header();
    /*Content*/
    if (isset($_GET['action'])) {
      $action = $_GET['action'];
    } else {
      $action = "";
    }

     if ($action == "confirm") {
     		$DB->delete_records('hierarchy_tags', array('category'  => $_GET['id']));
            $DB->delete_records('hierarchy_category', array('id'  => $_GET['id']));
        }
	echo '<div class="manageTable">
	<table >
<tr class="header">
        <td width="200px">';
        echo new lang_string('category','block_hierarchy');
        echo '</td><td >';
        echo '</td><td ><a href="manageCategories.php?action=add"><img width="30px" src="pix/add-icon.png" alt="Add"></a>';
        echo '</td></tr>'; 
			$result = $DB->get_records('hierarchy_category');
			foreach ($result as $category) {
				echo '<tr class="tableEntry"><td>'.$category->name.' </td><td> <a href="manageCategories.php?action=editCategory&cat='.$category->id.'"><img width="30px" src="pix/edit-icon.png" alt="Edit"></a> </td><td> <a href="manageCategories.php?action=deleteCategory&id='.$category->id.'"><img width="30px" src="pix/delete-icon.png" alt="Delete"></a></td></tr>';
				}
	echo '	</table></div>';
   if (isset($_GET['action'])) {
        if ($_GET['action'] == "editCategory") {
          echo '<div class="manageTable"><h2 class="header">'.new lang_string('editcategory','block_hierarchy').':</h2>
         ';
          $catEdit = $DB->get_record('hierarchy_category', array('id'  => $_GET['cat']));
                   echo '<form action="manageCategories.php" method="POST"><input type="hidden" value="'.$catEdit->id.'" name="id"><input type=text value="'.$catEdit->name.'" name="catName"><br>
   ';
                 echo '
         <br><button name="editbtn" id="editbtn" type="submit">Update</button></form>
        <div class="editClose"> <a href="manageCategories.php">'.new lang_string('cancel','block_hierarchy').'</a></div>
          </div>';
        } elseif ($_GET['action'] == "deleteCategory") {
          echo '<div class="manageTable"><h2 class="header">'.new lang_string('deletecategory','block_hierarchy').'</h2>
          '.new lang_string('selectedtag','block_hierarchy').': <b>';
          $catDelete = $DB->get_record('hierarchy_category', array('id' => $_GET['id']));
          echo $catDelete->name;
          echo '</b><br><br>
          '.new lang_string('deletecatwarning','block_hierarchy').'
        <div class="editClose"> <a href="manageCategories.php?action=confirm&id='.$_GET['id'].'">'.new lang_string('confirm','block_hierarchy').'</a>&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;<a href="manageCategories.php">'.new lang_string('cancel','block_hierarchy').'</a></div>
          </div>';
        }elseif ($_GET['action'] == "confirm") {
            $DB->delete_records('hierarchy_tags', array('id'  => $_GET['id']));
          echo '<div class="manageTable"><h2 class="header">'.get_string('catdeleted', 'block_hierarchy').'</h2>
         '.get_string('removedcategory', 'block_hierarchy').'
        <div class="editClose"><a href="manageCategories.php">'.get_string('close', 'block_hierarchy').'</a></div>
          </div>';
        }elseif ($_GET['action'] == "add") {
          echo '<div class="manageTable"><h2 class="header">'.new lang_string('add','block_hierarchy').' '.new lang_string('category','block_hierarchy').'</h2>
<form method="POST" action="manageCategories.php">
          <input name="newCat" placeholder="Category Name, Category Name, Category Name"><br><br><button name="addbtn">'.get_string('add', 'block_hierarchy').'</button>   <div class="editClose"> <a href="manageCategories.php">'.new lang_string('cancel','block_hierarchy').'</a></div></form>';
          echo '</div>';
        }
    }
        echo '<div style="clear:both;"></div>';

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



