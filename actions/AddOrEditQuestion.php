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
    $questionLanguage = $_POST["questionLanguage"];
    $questionText = $_POST["questionText"];
    $questionInput = $_POST["questionInput"];
    $questionOutput = $_POST["questionOutput"];

    $currentTime = new DateTime('now', new DateTimeZone($CFG->timezone));
    $currentTime = $currentTime->format("Y-m-d H:i:s");

	if ($questionId > -1) {
	    // Existing question
	    $CODE_DAO->updateQuestion($questionId, $questionLanguage, $questionText, $questionInput, $questionOutput, $currentTime);
    } else {
	    // New question
        $CODE_DAO->createQuestion($_SESSION["code_id"], $questionLanguage, $questionText, $questionInput, $questionOutput, $currentTime);
    }

    header( 'Location: '.addSession('../instructor-home.php') ) ;
}
