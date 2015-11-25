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
require_once(dirname(__file__) . '/queryBuilderBridge.php');
global $USER;
global $DB;

class block_hierarchy extends block_base {

    function init() {
        $this->title = get_string('blocktitle', 'block_hierarchy');
    }
    function has_config() {
        return true;
    }
    function applicable_formats() {
        return array('all' => true);
    }
    function specialization() {
        $this->title = get_string('blocktitle', 'block_hierarchy');
    }
    function instance_allow_multiple() {
        return false;
    }
    function get_content() {
        global $CFG;
        global $USER;
        $taglist = "";
        $managertaglist = "";
        global $DB;
        require_once($CFG->libdir . '/filelib.php');

        if ($this->content !== null) {
            return $this->content;
        }
        $this->content = new stdClass;
        $userid = $_GET['id'];
        $ismanager = has_capability('block/hierarchy:ismanager', context_system::instance(), $userid);
        if ( $ismanager == 1 ) {
            $ismanager = "1";
        }
        $sql = 'SELECT tt.name as tag, tc.name as category FROM {hierarchy_tags} tt LEFT JOIN {hierarchy_usertags} ut ON (ut.tagid = tt.id) INNER JOIN {hierarchy_category} tc ON (tc.id = tt.category) WHERE ut.userid="'.$userid.'"';
        $tagstodisplay = $DB->get_records_sql($sql);
        foreach ($tagstodisplay as $tagtodisplay) {
            $taglist .= '<b>'.$tagtodisplay->category.'</b>: '.$tagtodisplay->tag.'<br>';
        }
        if ($taglist == "") {
            $taglist = "None Currently Assigned";
        }
        $this->content->text = "";
        $this->content->text = $this->content->text.$taglist;
        $taglist = "";
        if ( !empty( $ismanager ) ) {
            $managertaglist = "";
            $managersql = 'SELECT tt.name as tag, tc.name as category FROM {hierarchy_tags} tt INNER JOIN {hierarchy_managertags} mt ON (mt.tagid = tt.id) INNER JOIN {hierarchy_category} tc ON (tc.id= tt.category) WHERE mt.userid="'.$userid.'"';
            $managertagstodisplay = $DB->get_records_sql($managersql);
            foreach ($managertagstodisplay as $managertagtodisplay) {
                $managertaglist .= '<b>'.$managertagtodisplay->category.'</b>: '.$managertagtodisplay->tag.'<br>';
            }
            $this->content->text .= '<hr><div class="header" ><div class="title" ><h2 style="padding-left:0;">';
            $this->content->text .= 'Mananger Permissions:</h2></div></div>';
            if ($managertaglist == "") {
                $managertaglist = "None Currently Assigned";
            }
            $this->content->text = $this->content->text.$managertaglist;
        }
        $system = context_system::instance();
        $isadmin = has_capability('block/hierarchy:managetags', $system, $USER->id);
        if ($isadmin == 1) {
            $this->content->text = $this->content->text.'<hr>';
         //   $this->content->footer .= 'Manage: <a href="'.$CFG->wwwroot.'/blocks/hierarchy/editUserTags.php?id='.$userid;
           // $this->content->footer .= '">User Tags</a> - ';
            if ( !empty( $ismanager ) ) {
                $this->content->footer .= '<a href="'.$CFG->wwwroot.'/blocks/hierarchy/editManagerTags.php?id='.$userid;
                $this->content->footer .= '">Temporary Manager Permissions</a>';
            }
            return $this->content;
        }
    }
}
