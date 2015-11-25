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
 * @copyright 2014 Josh Willcock
 * @license   http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 * @package   block_hierarchy
 */
defined('MOODLE_INTERNAL') || die;
if (is_siteadmin()) {
    // Hierarchy Menu.
    $ADMIN->add('users', new admin_category('hierarchies', new lang_string('hierarchies', 'block_hierarchy')));
        $ADMIN->add('hierarchies', new admin_externalpage('menutags', new lang_string('menutags', 'block_hierarchy'),
            "$CFG->wwwroot/blocks/hierarchy/manageTags.php"));
        $ADMIN->add('hierarchies', new admin_externalpage('menucategories',
            new lang_string('menucategories', 'block_hierarchy'), "$CFG->wwwroot/blocks/hierarchy/manageCategories.php"));
        // Bulk Tool Sub Menu.
    $ADMIN->add('hierarchies', new admin_category('syncsettings', new lang_string('syncsettings', 'block_hierarchy')));
        $ADMIN->add('syncsettings', new admin_externalpage('manualsync', new lang_string('manualsync', 'block_hierarchy'),
            "$CFG->wwwroot/blocks/hierarchy/db/sync.php"));
        $ADMIN->add('syncsettings', new admin_externalpage('aliaslist', new lang_string('aliaslist', 'block_hierarchy'),
            "$CFG->wwwroot/blocks/hierarchy/aliasList.php"));
        $ADMIN->add('syncsettings', new admin_externalpage('rolesync', new lang_string('rolesync', 'block_hierarchy'),
            "$CFG->wwwroot/blocks/hierarchy/rolesync.php"));
    $ADMIN->add('syncsettings', new admin_externalpage('syncsettings', new lang_string('syncsettings', 'block_hierarchy'),
        "$CFG->wwwroot/blocks/hierarchy/syncsettings.php"));
    $ADMIN->add('syncsettings', new admin_externalpage('aliaslist', new lang_string('aliaslist', 'block_hierarchy'),
        "$CFG->wwwroot/blocks/hierarchy/aliasList.php"));
}