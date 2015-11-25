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
 * @copyright 2010 Petr Skoda (http://skodak.org)
 * @license   http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 * @package   block_hierarchy
 * @category  files
 * @param stdClass $course course object
 * @param stdClass $birecord_or_cm block instance record
 * @param stdClass $context context object
 * @param string $filearea file area
 * @param array $args extra arguments
 * @param bool $forcedownload whether or not force download
 * @param array $options additional options affecting the file serving
 * @return bool
 * @todo MDL-36050 improve capability check on stick blocks, so we can check user capability before sending images.
 */
namespace block_hierarchy;
function block_hierarchy_pluginfile($course, $birecord_or_cm, $context, $filearea, $args, $forcedownload, array $options=array()) {
    global $DB, $CFG, $USER;

    if ($context->contextlevel != CONTEXT_BLOCK) {
        send_file_not_found();
    }

    // If block is in course context, then check if user has capability to access course.
    if ($context->get_course_context(false)) {
        require_course_login($course);
    } else if ($CFG->forcelogin) {
        require_login();
    } else {
        // Get parent context and see if user have proper permission.
        $parentcontext = $context->get_parent_context();
        if ($parentcontext->contextlevel  == CONTEXT_COURSECAT) {
            // Check if category is visible and user can view this category.
            $category = $DB->get_record('course_categories', array('id' => $parentcontext->instanceid), '*', MUST_EXIST);
            if (!$category->visible) {
                require_capability('moodle/category:viewhiddencategories', $parentcontext);
            }
        } else if ($parentcontext->contextlevel  == CONTEXT_USER && $parentcontext->instanceid != $USER->id) {
            // The block is in the context of a user, it is only visible to the user who it belongs to.
            send_file_not_found();
        }
        // At this point there is no way to check SYSTEM context, so ignoring it.
    }

    if ($filearea !==  'content') {
        send_file_not_found();
    }

    $fs = get_file_storage();

    $filename = array_pop($args);
    $filepath = $args ? '/'.implode('/', $args).'/' : '/';

    if (!$file = $fs->get_file($context->id, 'block_hierarchy', 'content', 0, $filepath, $filename) or $file->is_directory()) {
        send_file_not_found();
    }

    if ($parentcontext = context::instance_by_id($birecord_or_cm->parentcontextid, IGNORE_MISSING)) {
        if ($parentcontext->contextlevel  ==  CONTEXT_USER) {
            // force download on all personal pages including /my/
            //because we do not have reliable way to find out from where this is used
            $forcedownload = true;
        }
    } else {
        // weird, there should be parent context, better force dowload then
        $forcedownload = true;
    }

    // NOTE: it woudl be nice to have file revisions here, for now rely on standard file lifetime,
    //       do not lower it because the files are dispalyed very often.
    \core\session\manager::write_close();
    send_stored_file($file, null, 0, $forcedownload, $options);
}

class trackTime{
    private $start;
    public function start(){
        $this->start = microtime(true);
    }
    public function timetaken(){
        $executiontime = (microtime(true) - $this->start);
        return $executiontime;
    }
}
class hierarchysync
{
    public $createdtag=0;
    public $tagfound=0;
    public $aliasfound=0;
    public function getTagCreated(){
        return $this->createdtag;
    }
    public function getTagFound(){
        return $this->tagfound;
    }  
    public function getAliasFound(){
        return $this->aliasfound;
    }
    public function sync() {
        global $DB;
        $settingDBRaw = $DB->get_record('config', array('name'=>'manager_hierarchy_sync'));
        $settingDB= $settingDBRaw->value;
        $this->rolesync();
        $DB->execute('DELETE FROM {hierarchy_usertags}');
        $DB->execute('DELETE FROM {hierarchy_managertags}');
        $userInfoFields = $this->getCategories('user');
        if($userInfoFields==0){
        // Echo 'Unable to find any User Profile Field Items under "Hierarchy"';
        }else{
            foreach ($userInfoFields as $userInfoField){
                $bridge = $this->bridgeCategory($userInfoField->name);
                if($bridge == false){
                    $tagid = $DB->insert_record('hierarchy_category', array('name'=>$userInfoField->name), $returnid=true, $bulk=false);
                    //createcategory
                    //continue
                }else{
                    //we have a match=
                    $userInfoData = $DB->get_records_sql('SELECT * FROM {user_info_data} WHERE fieldid="'.$userInfoField->id.'"');
                    foreach ($userInfoData as $userData){
                    //Clean up the data;
                        $usersplit = array();
                        $usersplit = explode(',', $userData->data);
                        foreach($usersplit as $singleData){
                              $singleData = $this->standardize($singleData);
                            if($singleData=="" or $singleData=="please select..."){
                                //ignore
                            }else{
                                $tagid = $this->lookupTag($singleData, $bridge->id);
                                if($settingDB!=2){
                                $this->addToUserTags($tagid,$userData->userid,'both');
                                }else{
                                 $this->addToUserTags($tagid,$userData->userid,'user');                                
                                }
                            }                           
                        }
  
                    }
                }
            }
        }
        
        if($settingDB==2){
        $userInfoFields = $this->getCategories('manager');
        if($userInfoFields==0){
        // Echo 'Unable to find any User Profile Field Items under "Hierarchy"';
        }else{
            foreach ($userInfoFields as $userInfoField){
                $bridge = $this->bridgeCategory($userInfoField->name);
                if($bridge == false){
                    $tagid = $DB->insert_record('hierarchy_category', array('name'=>$userInfoField->name), $returnid=true, $bulk=false);
                    //createcategory
                    //continue
                }else{
                    //we have a match=
                    $userInfoData = $DB->get_records_sql('SELECT * FROM {user_info_data} WHERE fieldid="'.$userInfoField->id.'"');
                    foreach ($userInfoData as $userData){
                    //Clean up the data;
                          $usersplit = array();
                        $usersplit = explode(',', $userData->data);
                        foreach($usersplit as $singleData){
                           $singleData = $this->standardize($singleData);
                        if($singleData=="" or $singleData=="please select..."){
                            //ignore
                        }else{
                            $tagid = $this->lookupTag($singleData, $bridge->id);
                            $this->addToUserTags($tagid,$userData->userid,'manager');
                        } 
                        }
  
                    }
                }
            }
        }
      }  
    }
    private function standardize($data){
            $data=strtolower($data);
            $data = preg_replace('/\G\s|\s(?=\s*$)/', '', $data);
            $data = preg_replace(array('/\s{2,}/', '/[\t\n]/'), ' ', $data);
            $data = trim($data);
            return $data;
    }
    private function rolesync(){
        global $DB;
        //Find relationships.
        $relationships = $DB->get_records('hierarchy_rolesync');
        foreach ($relationships as $relationship){
            $DB->execute('DELETE FROM {role_assignments} WHERE roleid='.$relationship->roleid);
        $recordsToAdd = $DB->get_records_sql('SELECT * FROM {user_info_data} WHERE fieldid='.$relationship->profileid.' and data=1');
            foreach($recordsToAdd as $recordToAdd){
                $DB->insert_record('role_assignments', array('roleid'=>$relationship->roleid, 'userid'=>$recordToAdd->userid, 'contextid'=>'1'));
            }
        }
    }
    private function bridgeCategory($categoryName){
        global $DB;
        $bridge = $DB->get_record_sql('SELECT * FROM {hierarchy_category} WHERE name="'.$categoryName.'" limit 0,1');
        return $bridge;
    }
    private function addToUserTags($tagid, $userid, $who){
        global $DB;
        
        if($who=="manager"){
           $result = $DB->insert_record('hierarchy_managertags',array('userid'=>$userid, 'tagid'=>$tagid), $returnid=true, $bulk=false);
        }
        if($who=="user"){
            $result = $DB->insert_record('hierarchy_usertags',array('userid'=>$userid, 'tagid'=>$tagid), $returnid=true, $bulk=false);
        }
        if($who=="both"){
            $result = $DB->insert_record('hierarchy_usertags',array('userid'=>$userid, 'tagid'=>$tagid), $returnid=true, $bulk=false);
           $result = $DB->insert_record('hierarchy_managertags',array('userid'=>$userid, 'tagid'=>$tagid), $returnid=true, $bulk=false);         
        }
        return $result;
    }
    private function getCategories($type){
        global $DB;
        if($type=="user"){
        $hierarchyCategoryResult = $DB->get_record_sql('SELECT * FROM {user_info_category} WHERE name="Hierarchy" LIMIT 0,1');
        if(isset($hierarchyCategoryResult->id)){
            $moodleCategory = $hierarchyCategoryResult->id;
            $moodleFieldResults = $DB->get_records_sql('Select * FROM {user_info_field} WHERE categoryid='.$moodleCategory);
            return $moodleFieldResults;
        }else{
            return false;
        }            
        }else if($type=="manager"){
        $hierarchyCategoryResult = $DB->get_record_sql('SELECT * FROM {user_info_category} WHERE name="ManagerTags" LIMIT 0,1');
        if(isset($hierarchyCategoryResult->id)){
            $moodleCategory = $hierarchyCategoryResult->id;
            $moodleFieldResults = $DB->get_records_sql('Select * FROM {user_info_field} WHERE categoryid='.$moodleCategory);
            return $moodleFieldResults;
        }else{
            return false;
        }
        }

    }
    private function lookupTag($tag, $category){
        global $DB;
        $foundtag = $DB->get_record_sql('SELECT * FROM {hierarchy_tags} where name="'.$tag.'" AND category="'.$category.'" LIMIT 1 ');
        if(!empty($foundtag->id)){
            // found a tag
            $tagid = $foundtag->id;
            $this->tagfound++;
        }else{
        // tag not found - is there an alias?
            $foundalias = $DB->get_record_sql('SELECT tal.`id` AS id, tal.`string` AS tagid, tt.`category` AS category FROM {hierarchy_aliaslist} tal INNER JOIN {hierarchy_tags} tt ON (tt.`id` = tal.`string`) WHERE tal.override="'.$tag.'" AND tt.category='.$category.' LIMIT 1');
            if(!empty($foundalias->id)){
                // found a alias
                $tagid = $foundalias->tagid;
                $this->aliasfound++;
            }else{
            //no alias? Make a tag
                $tagid = $DB->insert_record('hierarchy_tags', array('category'=>$category, 'name'=>$tag), $returnid=true, $bulk=false);
                $this->createdtag++;
            }
        }
        return $tagid;
    } 
}