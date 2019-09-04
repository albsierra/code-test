<?php
require_once "../../config.php";
require_once "../dao/CODE_DAO.php";

use \Tsugi\Core\LTIX;
use \CODE\DAO\CODE_DAO;

// Retrieve the launch data if present
$LAUNCH = LTIX::requireData();

$p = $CFG->dbprefix;

$CODE_DAO = new CODE_DAO($PDOX, $p);

$question_id = isset($_GET["question_id"]) ? $_GET["question_id"] : false;

if ( $USER->instructor && $question_id ) {

    $CODE_DAO->deleteQuestion($question_id);

    $CODE_DAO->fixUpQuestionNumbers($_SESSION["code_id"]);

    header( 'Location: '.addSession('../instructor-home.php') ) ;
} 
