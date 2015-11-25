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
 * This file keeps track of upgrades to the html block
 *
 * @since Moodle 2.0
 * @package block_hierarchy
 * @copyright 2014 Josh Willcock
 * @license http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

/**
 * Upgrade code for the HTML block.
 *
 * @param int $oldversion
 * @param object $block
 */
function xmldb_block_hierarchy_upgrade($oldversion) {
    global $CFG, $DB;
    $result = true;
    $dbman = $DB->get_manager();
    // Change Database Naming - tags_ to hierarchy_.
    if ($result && $oldversion < 2015060401) {
        $rolesyncexist =  $dbman->table_exists('tag_rolesync');
        if(empty($rolesyncexist)){
            $DB->execute('CREATE TABLE {tag_rolesync}(id int, profileid int, roleid int)');
        }
        $tagrolesync = new xmldb_table('tag_rolesync');
        $tagaliaslist = new xmldb_table('tag_aliaslist');
        $tagcategory = new xmldb_table('tag_category');
        $tagismanager = new xmldb_table('tag_ismanager');
        $tagmanagerlog = new xmldb_table('tag_managerlog');
        $tagmanagertags = new xmldb_table('tag_managertags');
        $tagtags = new xmldb_table('tag_tags');
        $tagusertags = new xmldb_table('tag_usertags');
        $tagusertagsbackup = new xmldb_table('tag_usertags_backup');
        $dbman->rename_table($tagrolesync, 'hierarchy_rolesync', $continue = true, $feedback = true);
        $dbman->rename_table($tagaliaslist, 'hierarchy_aliaslist', $continue = true, $feedback = true);
        $dbman->rename_table($tagcategory, 'hierarchy_category', $continue = true, $feedback = true);
        $dbman->rename_table($tagismanager, 'hierarchy_ismanager', $continue = true, $feedback = true);
        $dbman->rename_table($tagmanagerlog, 'hierarchy_managerlog', $continue = true, $feedback = true);
        $dbman->rename_table($tagmanagertags, 'hierarchy_managertags', $continue = true, $feedback = true);
        $dbman->rename_table($tagtags, 'hierarchy_tags', $continue = true, $feedback = true);
        $dbman->rename_table($tagusertags, 'hierarchy_usertags', $continue = true, $feedback = true);
        $dbman->rename_table($tagusertagsbackup, 'hierarchy_usertags_backup', $continue = true, $feedback = true);
        $DB->execute('ALTER TABLE {hierarchy_rolesync} DROP COLUMN id');
        $DB->execute('ALTER TABLE {hierarchy_rolesync} ADD id INT PRIMARY KEY AUTO_INCREMENT');
        $result = true;
    }
    return true;
}
