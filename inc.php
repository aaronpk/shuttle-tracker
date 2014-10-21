<?php
date_default_timezone_set('UTC');

include('geo.php');


$redis = new Redis();
$redis->pconnect('127.0.0.1', 6379);


class ErrorHandling {
	
    public static function handle_error($errno, $errstr, $errfile, $errline) {
        if (!(error_reporting() & $errno)) {
            // this error code is not included in error_reporting
            return;
        }

		dieWithError(array(
			'error' => $errstr,
			'errno' => $errno,
			'file' => $errfile,
			'line' => $errline
		));
    }
    
	public static function handle_exception($exception, $call_previous = true) {
		dieWithError(array(
			'error' => $exception->getMessage()
		));
    }
}

set_error_handler(array("ErrorHandling", "handle_error"));
set_exception_handler(array("ErrorHandling", "handle_exception"));

function dieWithError($err) {
	die(json_encode($err));	
}

function respondWithError($err) {
	die(json_encode($err));	
}

