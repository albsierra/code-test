<?php

// The SQL to uninstall this tool
$DATABASE_UNINSTALL = array(
    // Nothing
);

// The SQL to create the tables if they don't exist
$DATABASE_INSTALL = array(
    array( "{$CFG->dbprefix}code_splash",
        "create table {$CFG->dbprefix}code_splash (
    user_id       INTEGER NOT NULL DEFAULT 0,
    skip_splash   BOOL NOT NULL DEFAULT 0,
    PRIMARY KEY(user_id)
	
) ENGINE = InnoDB DEFAULT CHARSET=utf8"),
    array( "{$CFG->dbprefix}code_main",
        "create table {$CFG->dbprefix}code_main (
    code_id       INTEGER NOT NULL AUTO_INCREMENT,
    user_id     INTEGER NOT NULL,
    context_id  INTEGER NOT NULL,
	link_id     INTEGER NOT NULL,
	title       VARCHAR(255) NULL,
    modified    datetime NULL,
    
    PRIMARY KEY(code_id)
	
) ENGINE = InnoDB DEFAULT CHARSET=utf8"),    
    array( "{$CFG->dbprefix}code_question",
        "create table {$CFG->dbprefix}code_question (
    question_id   INTEGER NOT NULL AUTO_INCREMENT,
    code_id         INTEGER NOT NULL,
    question_num  INTEGER NULL,
    question_language  INTEGER NOT NULL DEFAULT 1,
    question_txt  TEXT NULL,   
    question_input_test  VARCHAR(255) NULL,
    question_input_grade  VARCHAR(255) NULL,
    question_solution  TEXT NULL,
    modified      datetime NULL,
    
    CONSTRAINT `{$CFG->dbprefix}code_ibfk_1`
        FOREIGN KEY (`code_id`)
        REFERENCES `{$CFG->dbprefix}code_main` (`code_id`)
        ON DELETE CASCADE,

    PRIMARY KEY(question_id)
	
) ENGINE = InnoDB DEFAULT CHARSET=utf8"),
    array( "{$CFG->dbprefix}code_answer",
        "create table {$CFG->dbprefix}code_answer (
    answer_id    INTEGER NOT NULL AUTO_INCREMENT,
    user_id      INTEGER NOT NULL,
    question_id  INTEGER NOT NULL,
    answer_txt   TEXT NULL,
    answer_success BOOLEAN NULL,
    modified     datetime NULL,
    
    CONSTRAINT `{$CFG->dbprefix}code_ibfk_2`
        FOREIGN KEY (`question_id`)
        REFERENCES `{$CFG->dbprefix}code_question` (`question_id`)
        ON DELETE CASCADE,
    
    PRIMARY KEY(answer_id)
    
) ENGINE = InnoDB DEFAULT CHARSET=utf8")
);
