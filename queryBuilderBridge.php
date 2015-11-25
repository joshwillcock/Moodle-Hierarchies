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

// Created By Josh Willcock Copyright 2014.
// Created For CLC Hierarchies System Intergration.

// This is the class you create to run the build query function.
class queryBuilder
{
    public function getManagerTags($userid) {
        global $DB;
        $managertagarray = $DB->get_records_sql('SELECT tm.tagid as tagid, tt.category as category FROM {hierarchy_managertags} tm JOIN {hierarchy_tags} tt ON (tm.tagid = tt.id) WHERE userid = "'.$userid.'"');
        $managertags = array();
        foreach ($managertagarray as $managertagfromarray) {
            $managertag = new stdClass();
            $managertag->tagid = $managertagfromarray->tagid;
            $managertag->category = $managertagfromarray->category;
            $managertags[] = $managertag;
        }
        return $managertags;
    }
    // This function requires the manager's userid to create a restrictive where clause.
    public function build($userids, $useridcolumn, $pre = null, $post = null, $forcedtags = null, $deleted = null) {
        // Builtforcount = userid = base.userid / false  auser.id.
        global $DB;
        // Get Manager Tags.
        $managertagresult = $DB->get_records_sql('SELECT mt.tagid as tagid, tt.category as categoryid FROM {hierarchy_managertags} mt JOIN {hierarchy_tags} tt ON (tt.id = mt.tagid) WHERE mt.userid = '.$userids);
        $managertags = array();
        $managerCatagories = array();
        foreach($managertagresult as $managertag) {
            $managertags[] = $managertag->tagid;
             if (!in_array($managertag->categoryid, $managerCatagories)) {
                     $managerCatagories[] = $managertag->categoryid;
              }
        }
        $numberoftags = count($managertags);
        $categorycount = count($managerCatagories);
        if ($forcedtags != null) {
            $categorycount = $categorycount+count(explode(',',$forcedtags));
        }
        $listoftags = implode(",", $managertags);
        if (!empty($forcedtags)) {
           $listoftags = $listoftags.','.$forcedtags;
        }
        if ($listoftags == "") {
            $sql = 'SELECT * FROM {hierarchy_usertags} WHERE tagid = 0';
        } else {
            if ($deleted == true) {
                $sql = 'SELECT tu.userid as userid FROM {hierarchy_usertags} tu JOIN {user} u ON (tu.userid = u.id) WHERE tu.tagid IN ('.$listoftags.') AND u.deleted<>1 AND u.suspended<>1 GROUP BY tu.userid HAVING COUNT(tu.userid) >='.$categorycount;
            } else {
                $sql = 'SELECT * FROM {hierarchy_usertags} WHERE tagid IN ('.$listoftags.') GROUP BY userid HAVING COUNT(userid) >='.$categorycount;
            }
        }

         // If admin .
        $admins = get_admins();
        $isadmin = false;
        foreach ($admins as $admin) {
            if ($userids  ==  $admin->id) {
                $isadmin = true; break;
            }
        } 
        if ($isadmin) {
             $sql = 'SELECT id as userid FROM {user} ORDER BY firstname';
        }

        $usersOfManagers = $DB->get_records_sql($sql);
        //Loop Through Available Requested Tags
        if (!empty($usersOfManagers)) {
            //Create SQL Query
            //$query = 'AND ';
            $query = $pre;
            $query = $query.'(';
            $and = 0;
            foreach($usersOfManagers as $userOfManager) {
                if ( $and  ==  0 ) { $and = 1; } else { $query = $query.' OR '; }
                if (!empty($userOfManager)) {
                   $query = $query.$useridcolumn.' = '.$userOfManager->userid;
                }
                
            }

            $query = $query.')'.$post;
//echo '<pre> Total users: '.count($usersOfManagers).'</pre>';
        } else {
            $query = $pre.'(0 = 1)'.$post;
        }

        return $query;
    }


 public function getUserArray($userids, $forcedtags = null, $deleted = null)
    {
        //builtforcount = userid = base.userid / false  auser.id
        global $DB;
        //Get Manager Tags
        $managertagresult = $DB->get_records_sql('SELECT mt.tagid as tagid, tt.category as categoryid FROM {hierarchy_managertags} mt JOIN {hierarchy_tags} tt ON (tt.id = mt.tagid) WHERE mt.userid = '.$userids);
        
        $managertags = array();
        $managerCatagories = array();
        foreach($managertagresult as $managertag) {
            $managertags[] = $managertag->tagid;
             if (!in_array($managertag->categoryid, $managerCatagories)) {
                     $managerCatagories[] = $managertag->categoryid;
              }
        }
        $numberoftags = count($managertags);
        $categorycount = count($managerCatagories);
         if ($forcedtags != null) {
            
        }
        $listoftags = implode(",",$managertags);
        if (!empty($forcedtags)) {
           $listoftags = $listoftags.','.$forcedtags;
           $countForcedTags = count(explode(',',$forcedtags));
                   $categorycount = $categorycount+count(explode(',',$forcedtags));
        }
        if (empty($listoftags)) {
            $listoftags = '0';
        }
       
        // $sql = 'SELECT * FROM {hierarchy_usertags} WHERE tagid IN ('.$listoftags.') GROUP BY userid HAVING COUNT(userid) > = '.$numberoftags;


        // if admin 
        $admins = get_admins(); $isadmin = false; foreach ($admins as $admin) { if ($userids  ==  $admin->id) { $isadmin = true; break; } } 
        if ($isadmin) {
            if ($deleted) {
             $sql = 'SELECT id as userid FROM {user}  WHERE deleted<>1 and suspended<>1 ORDER BY firstname ';
         } else {
             $sql = 'SELECT id as userid FROM {user} ORDER BY firstname';
            }
        } else {
            if ($deleted) {
 $sql = 'SELECT * FROM {hierarchy_usertags} tu JOIN {user} mu ON (mu.id = tu.userid) WHERE tu.tagid IN ('.$listoftags.') AND mu.deleted<>1 and mu.suspended<>1 GROUP BY tu.userid HAVING COUNT(tu.userid) >='.$categorycount.' ORDER BY mu.firstname';
            } else {
 $sql = 'SELECT * FROM {hierarchy_usertags} tu JOIN {user} mu ON (mu.id = tu.userid) WHERE tu.tagid IN ('.$listoftags.') GROUP BY tu.userid HAVING COUNT(tu.userid) >='.$categorycount.' ORDER BY mu.firstname';
        }
    }
                $usersOfManagers = $DB->get_records_sql($sql);
        //Loop Through Available Requested Tags
       return $usersOfManagers;
    }
}
