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
 * Custom Profile Field Sync
 *
 * @package    block_hierarchy
 * @copyright  2014 Josh Willcock  http://charitylearning.org
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */
namespace block_hierarchy\task;
GLOBAL $CFG;
require_once($CFG->dirroot.'/blocks/hierarchy/lib.php');

class hierarchy_task extends \core\task\scheduled_task {
    public function get_name() {
        return 'Hierarchies';
    }
    public function execute() {
      $sync = new \block_hierarchy\hierarchysync();
      $sync->sync();
    }
}