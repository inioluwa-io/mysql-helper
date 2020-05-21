<?php
// this connects to database and creates the respective tables

declare(strict_types=1);


class ErrorLogging{
    static function logError($location, $error){
        if (!file_exists( $_SERVER['DOCUMENT_ROOT'].$location)) {
            var_dump($_SERVER['DOCUMENT_ROOT']);
            echo "file not found ".$_SERVER['DOCUMENT_ROOT'].$location." \n";
        }
        else{
            error_log("$error \n", 3,  $_SERVER['DOCUMENT_ROOT'].$location);
        }
    }
}

?>