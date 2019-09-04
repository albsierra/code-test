<?php
require_once "../../config.php";
require_once('../dao/CODE_DAO.php');

use \Tsugi\Core\LTIX;
use \CODE\DAO\CODE_DAO;

$LAUNCH = LTIX::requireData();

$p = $CFG->dbprefix;

$CODE_DAO = new CODE_DAO($PDOX, $p);

if ($USER->instructor) {

    if (isset($_POST["toolTitle"])) {
        $currentTime = new DateTime('now', new DateTimeZone($CFG->timezone));
        $currentTime = $currentTime->format("Y-m-d H:i:s");

        $CODE_DAO->updateMainTitle($_SESSION["code_id"], $_POST["toolTitle"], $currentTime);
    }

    if (!isset($_POST["nonav"])) {
        header( 'Location: '.addSession('../instructor-home.php') ) ;
    }
}
