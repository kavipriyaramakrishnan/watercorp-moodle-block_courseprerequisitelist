<?php
require_once('../../config.php');
require_once($CFG->dirroot.'/blocks/courseprerequisitelist/locallib.php');

$id  = required_param('id', PARAM_INT);

$html = block_courseprerequisite_rows($id);

echo json_encode($html);
