<?php
/**
 * Course prerequisitelist block.
 *
 * @package    block_courseprerequisitelist
 * @author     2017 Priya Ramakrishnan {priya@pukunui.com}
 * @copyright  Pukunui Technology
 */
function block_courseprerequisite_rows($id) {
     global $DB, $CFG;
     
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
             AND c.category = $id
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
                          <td id="crs_"'.$courseid.'> <a href="/course/view.php?id='.$courseid.'" target="_blank">'. $crs->fullname .'</a></td>
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
        return $table;
}
