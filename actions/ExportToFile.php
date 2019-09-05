<?php
require_once "../../config.php";
require_once "../util/PHPExcel.php";
require_once "../dao/CODE_DAO.php";

use \Tsugi\Core\LTIX;
use \CODE\DAO\CODE_DAO;

// Retrieve the launch data if present
$LAUNCH = LTIX::requireData();

$p = $CFG->dbprefix;

$CODE_DAO = new CODE_DAO($PDOX, $p);

if ( $USER->instructor ) {

    $code_id = $_SESSION["code_id"];

    $questions = $CODE_DAO->getQuestions($code_id);

    $rowCounter = 1;

    $questionTotal = count($questions);

    $exportFile = new PHPExcel();

    $exportFile->setActiveSheetIndex(0)->setCellValue('A1', 'Student');
    $exportFile->setActiveSheetIndex(0)->setCellValue('B1', 'Username');
    $exportFile->setActiveSheetIndex(0)->setCellValue('C1', 'Date of Submission');

    $exportFile->getActiveSheet()->getStyle('A1')->getFont()->setBold(true);
    $exportFile->getActiveSheet()->getStyle('B1')->getFont()->setBold(true);
    $exportFile->getActiveSheet()->getStyle('C1')->getFont()->setBold(true);

    $exportFile->getActiveSheet()->getColumnDimension('A')->setWidth(20);
    $exportFile->getActiveSheet()->getColumnDimension('B')->setWidth(10);
    $exportFile->getActiveSheet()->getColumnDimension('C')->setWidth(25);

    $letters = range('C','Z');
    for($x = 1; $x<=$questionTotal; $x++){
        $col1 = $x+2;
        $exportFile->getActiveSheet()->setCellValueByColumnAndRow($col1, $rowCounter, "Question ".$x);

        $cell_name = $letters[$x]."1";
        $exportFile->getActiveSheet()->getStyle($cell_name)->getFont()->setBold(true);
    }

    $StudentList = $CODE_DAO->getUsersWithAnswers($code_id);

    $columnIterator = $exportFile->getActiveSheet()->getColumnIterator();
    $columnIterator->next();

    foreach ($StudentList as $student ) {
        $rowCounter++;

        $UserID = $student["user_id"];

        $Email = $CODE_DAO->findEmail($UserID);
        $UserName = explode("@",$Email);

        $Modified1 = $CODE_DAO->getMostRecentAnswerDate($UserID, $code_id);
        $Modified  =  new DateTime($Modified1);

        $displayName = $CODE_DAO->findDisplayName($UserID);
        $displayName = trim($displayName);

        $lastName = (strpos($displayName, ' ') === false) ? '' : preg_replace('#.*\s([\w-]*)$#', '$1', $displayName);
        $firstName = trim( preg_replace('#'.$lastName.'#', '', $displayName ) );

        $exportFile->getActiveSheet()->setCellValue('A'.$rowCounter, $lastName.', '.$firstName);

        $exportFile->getActiveSheet()->setCellValue('B'.$rowCounter, $UserName[0]);
        $exportFile->getActiveSheet()->setCellValue('C'.$rowCounter, $Modified->format('m/d/y - h:i A '));

        $col = 3;
        foreach ($questions as $question ) {
            $QID = $question["question_id"];
            $A="";

            $answer = $CODE_DAO->getStudentAnswerForQuestion($QID, $UserID);
            if ($answer) {
                $A = $answer["answer_txt"];
                $A = str_replace("&#39;", "'", $A);
            }

            $exportFile->getActiveSheet()->setCellValueByColumnAndRow($col, $rowCounter, $A);
            $col++;
        }
    }
    $columnIterator->next();

    $exportFile->getActiveSheet()->setTitle('Quick_Write');

    foreach($exportFile->getActiveSheet()->getColumnDimension() as $col) {
        $col->setAutoSize(true);
    }
    $exportFile->getActiveSheet()->calculateColumnWidths();

    // Redirect output to a client’s web browser (Excel5)
    header('Content-Type: application/vnd.ms-excel');
    header('Content-Disposition: attachment;filename=Quick_Write.xls');
    header('Cache-Control: max-age=0');
    // If you're serving to IE over SSL, then the following may be needed
    header ('Expires: Mon, 26 Jul 1997 05:00:00 GMT'); // Date in the past
    header ('Last-Modified: '.gmdate('D, d M Y H:i:s').' GMT'); // always modified
    header ('Cache-Control: cache, must-revalidate'); // HTTP/1.1
    header ('Pragma: public'); // HTTP/1.0
    $objWriter = PHPExcel_IOFactory::createWriter($exportFile, 'Excel5');
    $objWriter->save('php://output');
}

