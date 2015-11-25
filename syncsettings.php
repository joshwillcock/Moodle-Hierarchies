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
$PAGE->set_url('/blocks/hierarchy/syncsettings.php');
$PAGE->set_context(context_system::instance());
$PAGE->set_pagelayout('admin');
$strheading = get_string('syncsettings', 'block_hierarchy');
$PAGE->set_title($strheading);
$PAGE->set_heading($strheading);

require_login();
global $USER;
global $DB;
$msg = array();
$system = context_system::instance();
$isadmin = has_capability('block/hierarchy:managetags', $system, $USER->id);
        echo $OUTPUT->header();
        if ($isadmin == 1) {
      echo '<div class="block" style="padding:20px;"><h1>'.get_string('syncsettings', 'block_hierarchy').'</h1>';
      echo 'To change the syncing method for your hierarchy system please click below. Please note that changing this will cause significant changes to your data structure:<br>';

// Check if They've Asked To Update
    if(isset($_GET['syncstatus'])){
        $syncstatus = $_GET['syncstatus'];
        if($syncstatus==1 or $syncstatus==2){

            // Check that profile fields are configured correctly
            $categories = $DB->get_records('user_info_category');
            $hierarchycategoryexist=false;
            $permissionscategoryexist=false;
            $managertagscategoryexist=false;
            if($syncstatus==1){
                foreach($categories as $category){
                    if($category->name=="Hierarchy"){
                        $hierarchycategoryexist=true;
                    }
                    if($category->name=="Permissions"){
                        $permissionscategoryexist=true;
                    }
                }
                if($permissionscategoryexist==false){
                    $msg[] = 'Permissions Category does not exist.';
                    $error=true;
                }
                if($hierarchycategoryexist==false){
                    $msg[] = 'Hierarchy User Tags Category does not exist.';
                    $error=true;
                }
                if($error){
                }else{
                    $currentsetting = $DB->get_record('config', array('name'=>'manager_hierarchy_sync'));
                    if(!empty($currentsetting)){
                        $updatedRecord = new stdClass();
                        $updatedRecord->id=$currentsetting->id;
                        $updatedRecord->name='manager_hierarchy_sync';
                        $updatedRecord->value=1;
                        $DB->update_record('config', $updatedRecord);
                        $success=true;
                        //Updated
                    }else{
                        $newRecord = new stdClass();
                        $newRecord->name='manager_hierarchy_sync';
                        $newRecord->value=1;
                        $DB->insert_record('config',$newRecord);
                        $success=true;
                        // Created
                    }
                }
            }else if($syncstatus==2){
                foreach($categories as $category){
                    if($category->name=="Hierarchy"){
                        $hierarchycategoryexist=true;
                    }
                    if($category->name=="Permissions"){
                        $permissionscategoryexist=true;
                    }
                    if($category->name=="ManagerTags"){
                        $managertagscategoryexist=true;
                    }
                }
                if($permissionscategoryexist==false){
                    $msg[] = 'Permissions Category does not exist.';
                    $error=true;
                }
                if($hierarchycategoryexist==false){
                    $msg[] = 'Hierarchy User Tags Category does not exist.';
                    $error=true;
                }
                if($managertagscategoryexist==false){
                    $msg[] = 'Manager Tags Category does not exist.';
                    $error=true;
                }
                if($error){
                }else{
                    $currentsetting = $DB->get_record('config', array('name'=>'manager_hierarchy_sync'));
                    if(!empty($currentsetting)){
                        $updatedRecord = new stdClass();
                        $updatedRecord->id=$currentsetting->id;
                        $updatedRecord->name='manager_hierarchy_sync';
                        $updatedRecord->value=2;
                        $DB->update_record('config', $updatedRecord);
                        //Updated
                        $success=true;
                    }else{
                        $newRecord = new stdClass();
                        $newRecord->name='manager_hierarchy_sync';
                        $newRecord->value=2;
                        $DB->insert_record('config',$newRecord);
                        // Created
                        $success=true;
                    }
                
}            }
            if($success){
                //update
                echo 'all done';
            }else{
                echo '<div class="alert alert-error">';
                echo '<strong>We were unable to activate this method. Please see issues below:</strong><br><br>';
               foreach($msg as $issue){
                echo '&bull; '.$issue.'<br>';
               }
               echo '</div>';
            }
        }else{
            // Not a valid status
            $msg = 'Your status selection is invalid. Please pick a valid option';
        }
    }
      // DISPLAY NOW WHATEVER HAPPENED

     $currentsetting = $DB->get_record('config', array('name'=>'manager_hierarchy_sync'));

     echo '<form action="syncsettings.php" method="GET"><br>';
        if($currentsetting->value==1){
            echo '
                <input type="radio" name="syncstatus" checked value="1">For managers tags duplicate their user tags.<br>
                <input type="radio" name="syncstatus" value="2">For managers tags use the profile field "ManagerTags".<br>
            ';
        }else if($currentsetting->value==2){
             echo '
                <input type="radio" name="syncstatus" value="1">For managers tags duplicate their user tags.<br>
                <input type="radio" name="syncstatus" checked value="2">For managers tags use the profile field "ManagerTags".<br>
            ';
        }else{
            echo '
                <input type="radio" name="syncstatus" value="1">For managers tags duplicate their user tags.<br>
                <input type="radio" name="syncstatus" value="2">For managers tags use the profile field "ManagerTags".<br>
            ';
        }

     echo'
                <br><button type="submit" name="submit">Update</button>
          </form>';
        if(count($msg)!=0){
            echo '<div class="alert alert-error"> Please ensure all errors are rectified before continuing. </div>';
        }


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