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
    $questionInputTest = $_POST["questionInputTest"];
    $questionInputGrade = $_POST["questionInputGrade"];
    $questionSolution = trim($_POST["questionSolution"]);

    $question = array(
        "question_id" => $questionId,
        "question_language" => $questionLanguage,
        "question_text" => $questionText,
        "question_input_test" => $questionInputTest,
        "question_output_test" => "",
        "question_input_grade" => $questionInputGrade,
        "question_output_grade" => "",
        "question_solution" => $questionSolution
    );

    $questionOutputTest = $CODE_DAO->getOutputFrom($question, 'test');
    $questionOutputGrade = $CODE_DAO->getOutputFrom($question, 'grade');;

    $currentTime = new DateTime('now', new DateTimeZone($CFG->timezone));
    $currentTime = $currentTime->format("Y-m-d H:i:s");

	if ($questionId > -1) {
	    // Existing question
	    $CODE_DAO->updateQuestion($questionId, $questionLanguage, $questionText, $questionInputTest, $questionOutputTest, $questionInputGrade, $questionOutputGrade, $questionSolution, $currentTime);
    } else {
	    // New question
        $CODE_DAO->createQuestion($_SESSION["code_id"], $questionLanguage, $questionText, $questionInputTest, $questionOutputTest, $questionInputGrade, $questionOutputGrade, $questionSolution, $currentTime);
    }

    header( 'Location: '.addSession('../instructor-home.php') ) ;
}
