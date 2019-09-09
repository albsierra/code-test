<?php
namespace CODE\DAO;

class CODE_DAO {

    private $PDOX;
    private $p;

    public function __construct($PDOX, $p) {
        $this->PDOX = $PDOX;
        $this->p = $p;
    }

    function skipSplash($user_id) {
        $query = "SELECT skip_splash FROM {$this->p}code_splash WHERE user_id = :userId";
        $arr = array(':userId' => $user_id);
        $context = $this->PDOX->rowDie($query, $arr);
        return $context["skip_splash"];
    }

    function toggleSkipSplash($user_id) {
        $skip = $this->skipSplash($user_id) ? 0 : 1;
        $query = "INSERT INTO {$this->p}code_splash (user_id, skip_splash) VALUES (:userId, ".$skip.") ON DUPLICATE KEY UPDATE skip_splash = ".$skip;
        $arr = array(':userId' => $user_id);
        $this->PDOX->queryDie($query, $arr);
    }

    function getOrCreateMain($user_id, $context_id, $link_id, $current_time) {
        $main_id = $this->getMainID($context_id, $link_id);
        if (!$main_id) {
            return $this->createMain($user_id, $context_id, $link_id, $current_time);
        } else {
            return $main_id;
        }
    }

    function getMainID($context_id, $link_id) {
        $query = "SELECT code_id FROM {$this->p}code_main WHERE context_id = :context_id AND link_id = :link_id";
        $arr = array(':context_id' => $context_id, ':link_id' => $link_id);
        $context = $this->PDOX->rowDie($query, $arr);
        return $context["code_id"];
    }

    function createMain($user_id, $context_id, $link_id, $current_time) {
        $query = "INSERT INTO {$this->p}code_main (user_id, context_id, link_id, modified) VALUES (:userId, :contextId, :linkId, :currentTime);";
        $arr = array(':userId' => $user_id, ':contextId' => $context_id, ':linkId' => $link_id, ':currentTime' => $current_time);
        $this->PDOX->queryDie($query, $arr);
        return $this->PDOX->lastInsertId();
    }

    function getMainTitle($code_id) {
        $query = "SELECT title FROM {$this->p}code_main WHERE code_id = :codeId";
        $arr = array(':codeId' => $code_id);
        return $this->PDOX->rowDie($query, $arr)["title"];
    }

    function updateMainTitle($code_id, $title, $current_time) {
        $query = "UPDATE {$this->p}code_main set title = :title, modified = :currentTime WHERE code_id = :codeId;";
        $arr = array(':title' => $title, ':currentTime' => $current_time, ':codeId' => $code_id);
        $this->PDOX->queryDie($query, $arr);
    }

    function deleteMain($code_id, $user_id) {
        $query = "DELETE FROM {$this->p}code_main WHERE code_id = :mainId AND user_id = :userId";
        $arr = array(':mainId' => $code_id, ':userId' => $user_id);
        $this->PDOX->queryDie($query, $arr);
    }

    function getQuestions($code_id) {
        $query = "SELECT * FROM {$this->p}code_question WHERE code_id = :codeId order by question_num;";
        $arr = array(':codeId' => $code_id);
        return $this->PDOX->allRowsDie($query, $arr);
    }

    function getQuestionById($question_id) {
        $query = "SELECT * FROM {$this->p}code_question WHERE question_id = :questionId;";
        $arr = array(':questionId' => $question_id);
        return $this->PDOX->rowDie($query, $arr);
    }

    function createQuestion($code_id, $question_language, $question_text, $question_input_test, $question_input_grade, $question_solution, $current_time) {
        $nextNumber = $this->getNextQuestionNumber($code_id);
        $query = "
            INSERT INTO {$this->p}code_question 
                (code_id, question_num, question_language, question_txt, question_input_test, question_input_grade, question_solution, modified) 
            VALUES
                (:codeId, :questionNum, :questionLanguage, :questionText, :questionInputTest, :questionInputGrade, :questionSolution, :currentTime);";
        $arr = array(
            ':codeId' => $code_id,
            ':questionNum' => $nextNumber,
            ':questionLanguage' => $question_language,
            ':questionText' => $question_text,
            ':questionInputTest' => $question_input_test,
            ':questionInputGrade' => $question_input_grade,
            ':questionSolution' => $question_solution,
            ':currentTime' => $current_time
        );
        $this->PDOX->queryDie($query, $arr);
        return $this->PDOX->lastInsertId();
    }

    function updateQuestion($question_id, $question_language, $question_text, $question_input_test, $question_input_grade, $question_solution, $current_time) {
        $query = "UPDATE {$this->p}code_question
            SET
                question_language = :questionLanguage,
                question_txt = :questionText,
                question_input_test = :questionInputTest,
                question_input_grade = :questionInputGrade,
                question_solution = :questionSolution,
                modified = :currentTime
            WHERE question_id = :questionId;";
        $arr = array(
            ':questionId' => $question_id,
            ':questionLanguage' => $question_language,
            ':questionText' => $question_text,
            ':questionInputTest' => $question_input_test,
            ':questionInputGrade' => $question_input_grade,
            ':questionSolution' => $question_solution,
            ':currentTime' => $current_time
        );
        $this->PDOX->queryDie($query, $arr);
    }

    function getNextQuestionNumber($code_id) {
        $query = "SELECT MAX(question_num) as lastNum FROM {$this->p}code_question WHERE code_id = :codeId";
        $arr = array(':codeId' => $code_id);
        $lastNum = $this->PDOX->rowDie($query, $arr)["lastNum"];
        return $lastNum + 1;
    }

    function countAnswersForQuestion($question_id) {
        $query = "SELECT COUNT(*) as total FROM {$this->p}code_answer WHERE question_id = :questionId;";
        $arr = array(':questionId' => $question_id);
        return $this->PDOX->rowDie($query, $arr)["total"];
    }

    function deleteQuestion($question_id) {
        $query = "DELETE FROM {$this->p}code_question WHERE question_id = :questionId;";
        $arr = array(':questionId' => $question_id);
        $this->PDOX->queryDie($query, $arr);
    }

    function fixUpQuestionNumbers($code_id) {
        $query = "SET @question_num = 0; UPDATE {$this->p}code_question set question_num = (@question_num:=@question_num+1) WHERE code_id = :codeId ORDER BY question_num";
        $arr = array(':codeId' => $code_id);
        $this->PDOX->queryDie($query, $arr);
    }

    function getUsersWithAnswers($code_id) {
        $query = "SELECT DISTINCT user_id FROM {$this->p}code_answer a join {$this->p}code_question q on a.question_id = q.question_id WHERE q.code_id = :codeId;";
        $arr = array(':codeId' => $code_id);
        return $this->PDOX->allRowsDie($query, $arr);
    }

    function getStudentAnswerForQuestion($question_id, $user_id) {
        $query = "SELECT * FROM {$this->p}code_answer WHERE question_id = :questionId AND user_id = :userId; ";
        $arr = array(':questionId' => $question_id, ':userId' => $user_id);
        return $this->PDOX->rowDie($query, $arr);
    }

    function getMostRecentAnswerDate($user_id, $code_id) {
        $query = "SELECT max(a.modified) as modified FROM {$this->p}code_answer a join {$this->p}code_question q on a.question_id = q.question_id WHERE a.user_id = :userId AND q.code_id = :codeId;";
        $arr = array(':userId' => $user_id, ':codeId' => $code_id);
        $context = $this->PDOX->rowDie($query, $arr);
        return $context['modified'];
    }

    function createAnswer($user_id, $question_id, $answer_txt, $answer_success, $current_time) {
        $answer_txt = $this->PDOX->quote($answer_txt);
        $query = "
            INSERT INTO {$this->p}code_answer
            (user_id, question_id, answer_txt, answer_success, modified)
            VALUES
            (:userId, :questionId, :answerTxt, :answerSuccess, :currentTime);";
        $arr = array(
            ':userId' => $user_id,
            ':questionId' => $question_id,
            ':answerTxt' => $answer_txt,
            ':answerSuccess' => $answer_success,
            ':currentTime' => $current_time
        );
        $this->PDOX->queryDie($query, $arr);
        return $this->PDOX->lastInsertId();
    }

    function updateAnswer($answer_id, $answer_txt, $answer_success, $current_time) {
        $answer_txt = $this->PDOX->quote($answer_txt);
        $query = "
            UPDATE {$this->p}code_answer
            set
            answer_txt = :answerTxt,
            answer_success = :answerSuccess,
            modified = :currentTime
            where
            answer_id = :answerId;";
        $arr = array(
            ':answerId' => $answer_id,
            ':answerTxt' => $answer_txt,
            ':answerSuccess' => $answer_success,
            ':currentTime' => $current_time
        );
        $this->PDOX->queryDie($query, $arr);
    }

    function getAllAnswersToQuestion($question_id) {
        $query = "SELECT * FROM {$this->p}code_answer WHERE question_id = :questionId;";
        $arr = array(':questionId' => $question_id);
        return $this->PDOX->allRowsDie($query, $arr);
    }

    function getAnswerById($answer_id) {
        $query = "SELECT * FROM {$this->p}code_answer WHERE answer_id = :answerId;";
        $arr = array(':answerId' => $answer_id);
        return $this->PDOX->rowDie($query, $arr);
    }

    function findEmail($user_id) {
        $query = "SELECT email FROM {$this->p}lti_user WHERE user_id = :user_id;";
        $arr = array(':user_id' => $user_id);
        $context = $this->PDOX->rowDie($query, $arr);
        return $context["email"];
    }

    function findDisplayName($user_id) {
        $query = "SELECT displayname FROM {$this->p}lti_user WHERE user_id = :user_id;";
        $arr = array(':user_id' => $user_id);
        $context = $this->PDOX->rowDie($query, $arr);
        return $context["displayname"];
    }

    function getLanguageNameFromId($language_id) {
        switch ($language_id) {
            case 1:
                $languge_name = "PHP";
                break;
            case 2:
                $languge_name = "Java";
                break;
            case 3:
                $languge_name = "Javascript";
                break;
            default:
                $languge_name = "PHP";
                break;
        }
        return $languge_name;
    }

    function gradeAnswer($answerCode, $questionId) {
        $question = $this->getQuestionById($questionId);

        return $this->getOutputFromCode(
            $question["question_solution"], $question['question_language'], $question['question_input_grade']
            )
                ==
            $this->getOutputFromCode(
                $answerCode, $question['question_language'], $question['question_input_grade']
            );
    }

    function getOutputFromCode($answerCode, $language, $input) {
        $tmpfile = tmpfile();
        fwrite($tmpfile, $answerCode);
        $output = $this->launchCode($tmpfile, $language, $input);
        return(nl2br($output));
    }

    function launchCode($file, $language, $input) {
        $pathFile = stream_get_meta_data($file)['uri'];

        $descriptorspec = array(
           0 => array("pipe", "r"),  // stdin is a pipe that the child will read from
           1 => array("pipe", "w"),  // stdout is a pipe that the child will write to
           2 => array("file", "/tmp/error-output.txt", "a") // stderr is a file to write to
        );
        
        $cwd = sys_get_temp_dir(); // '/tmp';
        $env = array();

        switch ($this->getLanguageNameFromId($language)) {
            case 'PHP':
                $fileExtension = "php";
                $command = "php -f $pathFile.$fileExtension " . $input;
                break;
            case 'Java':
                $fileExtension = "java";
                $command = "echo \"" . $input . "\" | java $pathFile.$fileExtension";
                break;
            case 'Javascript':
                $fileExtension = "js";
                $command = "node $pathFile.$fileExtension " . $input;
                break;
        }

        // Add file extension
        rename($pathFile, "$pathFile.$fileExtension");
        
        // Run shell command
        $process = proc_open($command , $descriptorspec, $pipes, $cwd, $env);
        
        if (is_resource($process)) {
            // $pipes now looks like this:
            // 0 => writeable handle connected to child stdin
            // 1 => readable handle connected to child stdout
            // Any error output will be appended to /tmp/error-output.txt
        
            // fwrite($pipes[0], '<?php print_r($_ENV); ? >');
            fclose($pipes[0]);
        
            $output = stream_get_contents($pipes[1]);
            fclose($pipes[1]);
        
            echo(file_get_contents($descriptorspec[2][1]));
            unlink($descriptorspec[2][1]);

            // It is important that you close any pipes before calling
            // proc_close in order to avoid a deadlock
            $return_value = proc_close($process);
            // remove code file
            unlink("$pathFile.$fileExtension");
        
        }
        return $output; 
    }
}
