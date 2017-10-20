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
 * Course prerequisitelist block.
 *
 * @package    block_courseprerequisitelist
 * @author     2017 Priya Ramakrishnan {priya@pukunui.com}
 * @copyright  Pukunui Technology
 */

include_once($CFG->dirroot . '/course/lib.php');
include_once($CFG->libdir . '/coursecatlib.php');

class block_courseprerequisitelist extends block_base {
    const SHOW_ALL_COURSES = -2;
    function init() {
        $this->title = get_string('pluginname', 'block_courseprerequisitelist');
    }

    /**
     * Declare the Page formats in which the block is available
     **/
    public function applicable_formats() {
        return array('all' => true, 'mod' => false, 'tag' => false, 'my-index' => true);
    }

    function get_content() {
        global $CFG, $USER, $DB, $OUTPUT;

        if($this->content !== NULL) {
            return $this->content;
        }

        $this->content = new stdClass;
        $this->content->text = '';
        $this->content->footer = '<font color="red"> * Mandatory courses are in red</font>';
        $module = array('name' => 'ajax',
               'fullpath' => '/blocks/courseprerequisitelist/prerequisite1.js',
                  'requires' => array('event'));
        //$this->page->requires->js_init_call('M.block_courseprerequisitelist.display');
        $this->page->requires->js_init_call('', array(), true, $module);

        // Get all users list
        $catequery = "SELECT cc.id, cc.name
                        FROM {course_categories} cc
                        ORDER BY cc.name DESC";
        $categories = $DB->get_records_sql($catequery);
        $catlist  = array();
        foreach ($categories as $cl) {
           $catlist[$cl->id] = $cl->name;
        }
        if (!empty($catlist)) {
        $this->content->text .= get_string('coursecategories', 'block_courseprerequisitelist');
        $this->content->text .= html_writer::select($catlist, 'catlist', '', 1);
        $this->content->text .= html_writer::span("<br><br>", '');
        $this->content->text .= html_writer::empty_tag('input', array('type' => 'submit', 'value' => get_string('display', 'block_courseprerequisitelist'), 'id' => 'id_display'));
        $this->content->text .= html_writer::span("<br><br>", '');
        }
       
        $sql = "SELECT CONCAT_WS('-', c.id, pc.id),c.id as cid,
                c.fullname, cp.id as prereqid,
                CASE 
                WHEN cp.completedcount IS NULL 
                THEN 'No Prerequisites' 
                ELSE cp.completedcount
                END AS completedcount,
                pc.courseid as prerequisites
                FROM {course} c
                LEFT JOIN {local_courseprerequisite} cp
                ON cp.courseid = c.id
                LEFT JOIN mdl_local_prerequisitecourse pc
         	ON pc.prerequisiteid = cp.id
                WHERE c.visible = 1 AND c.id <> 1
                AND c.category = 3
                ORDER BY c.fullname";
        $courses = $DB->get_records_sql($sql);
        $table = '<table id="tableid">
                  <tr>
                  <td id="course">'.get_string('courses', 'block_courseprerequisitelist').'</td>
                  <td id="prerequisites">'.get_string('prerequisites', 'block_courseprerequisitelist').'</td>
                  </tr>';
        $tempcrsid = 0;
        foreach ($courses as $crs) {
           $courseid = $crs->cid;
           if ($tempcrsid !== $courseid) {
               $table .= '<tr>
                          <td id="crs_"'.$courseid.'><a href="/course/view.php?id='.$courseid.'" target="_blank">'. $crs->fullname .'</td>
                          <td> </td>
                          </tr>';
           }
           if (!strcmp($crs->completedcount, "No Prerequisites")) {
               $table .= '<tr>
                          <td></td>
                          <td id="noprereq"> No Prerequisites </td>
                          </tr>';
           } else {
               $prereqid = $crs->prereqid;
               // Calculate the total umber of prerequisite for this course.
               $noofpre = $DB->count_records('local_prerequisitecourse', array('prerequisiteid' => $prereqid));
               $prerequisitename = $DB->get_field('course', 'fullname', array('id' => $crs->prerequisites));
               if ($noofpre == $crs->completedcount) {
                   $table .= '<tr>
                             <td></td>
                             <td id="pre_"'.$prereqid.'> 
                             <font color="red">'. $prerequisitename.'*</font></td>
                             </tr>';
               } else { 
                   $table .= '<tr>
                             <td></td>
                             <td id="pre_"'.$prereqid.'>'. $prerequisitename.'*</font></td>
                             </tr>';
               }
           }
           $tempcrsid = $courseid;
        }
        $table .= ' </table>';
        $this->content->text .= $table;


        return $this->content ;
    }
}


