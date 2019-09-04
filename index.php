<?php
require_once('../config.php');
require_once('dao/CODE_DAO.php');

use \Tsugi\Core\LTIX;
use \CODE\DAO\CODE_DAO;

// Retrieve the launch data if present
$LAUNCH = LTIX::requireData();

$p = $CFG->dbprefix;

$CODE_DAO = new CODE_DAO($PDOX, $p);

$currentTime = new DateTime('now', new DateTimeZone($CFG->timezone));
$currentTime = $currentTime->format("Y-m-d H:i:s");

if ( $USER->instructor ) {

    $_SESSION["code_id"] = $CODE_DAO->getOrCreateMain($USER->id, $CONTEXT->id, $LINK->id, $currentTime);

    $hasQuestions = $CODE_DAO->getQuestions($_SESSION["code_id"]);

    if (!$hasQuestions) {
        // No questions check if user wants to see the splash on new instances of tool
        $skipSplash = $CODE_DAO->skipSplash($USER->id);

        if ($skipSplash) {
            header( 'Location: '.addSession('instructor-home.php') ) ;
        } else {
            header('Location: '.addSession('splash.php'));
        }
    } else {
        // Instructor has already setup this instance
        header( 'Location: '.addSession('instructor-home.php') ) ;
    }
} else { // student

    $mainId = $CODE_DAO->getMainID($CONTEXT->id, $LINK->id);

    if (!$mainId) {
        echo ("<h1>Instructor needs to do stuff");
    } else {
        $_SESSION["code_id"] = $mainId;

        header( 'Location: '.addSession('student-home.php') ) ;
    }
}
