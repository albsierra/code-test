<?php
require_once "../../config.php";
require_once('../dao/CODE_DAO.php');

use \Tsugi\Core\LTIX;
use \CODE\DAO\CODE_DAO;

$LAUNCH = LTIX::requireData();

$p = $CFG->dbprefix;

$CODE_DAO = new CODE_DAO($PDOX, $p);

if ($USER->instructor) {

    $questionId = $_POST["questionId"];
    $questionText = $_POST["questionText"];

    $currentTime = new DateTime('now', new DateTimeZone($CFG->timezone));
    $currentTime = $currentTime->format("Y-m-d H:i:s");

	if ($questionId > -1) {
	    // Existing question
	    $CODE_DAO->updateQuestion($questionId, $questionText, $currentTime);
    } else {
	    // New question
        $CODE_DAO->createQuestion($_SESSION["code_id"], $questionText, $currentTime);
    }

    header( 'Location: '.addSession('../instructor-home.php') ) ;
}
