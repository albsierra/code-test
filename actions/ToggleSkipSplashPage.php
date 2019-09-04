<?php
require_once('../../config.php');
require_once('../dao/CODE_DAO.php');

use \Tsugi\Core\LTIX;
use \CODE\DAO\CODE_DAO;

// Retrieve the launch data if present
$LAUNCH = LTIX::requireData();

$p = $CFG->dbprefix;

$CODE_DAO = new CODE_DAO($PDOX, $p);

if ($USER->instructor) {
    $CODE_DAO->toggleSkipSplash($USER->id);
    echo("success");
}

exit;