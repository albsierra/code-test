<?php
require_once "../../config.php";
require_once('../dao/CODE_DAO.php');

use \Tsugi\Core\LTIX;
use \CODE\DAO\CODE_DAO;

$LAUNCH = LTIX::requireData();

$p = $CFG->dbprefix;

$CODE_DAO = new CODE_DAO($PDOX, $p);

$currentTime = new DateTime('now', new DateTimeZone($CFG->timezone));
$currentTime = $currentTime->format("Y-m-d H:i:s");
$totalScore = 0.0;

for ($x = 1; $x < ($_POST["Total"]+1); $x++) {
    $answerId = $_POST['AnswerID'.$x];
    $questionId = $_POST['QuestionID'.$x];
    $answerText = ltrim(rtrim($_POST['A'.$x]));

    if (strlen($answerText) > 0) {
        $answerSuccess = $CODE_DAO->gradeAnswer($answerText, $questionId);
        $totalScore += ($answerSuccess ? 1 : 0);

        if ($answerId > -1) {
            // Existing answer check if it needs to be updated
            $oldAnswer = $CODE_DAO->getAnswerById($answerId);

            if ($answerText !== $oldAnswer['answer_txt']) {
                // Answer has changed so update
                $CODE_DAO->updateAnswer($answerId, $answerText, ($answerSuccess ? 1 : 0), $currentTime);
            }
        } else {
            // New answer
            $CODE_DAO->createAnswer($USER->id, $questionId, $answerText, ($answerSuccess ? 1 : 0), $currentTime);
        }
    } elseif($answerId > -1) {
        $oldAnswer = $CODE_DAO->getAnswerById($answerId);
        $totalScore += ($oldAnswer['answer_success'] ? 1 : 0) ;
    }
}

$totalScore = $totalScore / $_POST["Total"];
LTIX::gradeSend($totalScore);

header( 'Location: '.addSession('../student-home.php') ) ;

