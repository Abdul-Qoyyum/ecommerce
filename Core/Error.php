<?php

namespace Core;

use App\Controllers\Admin\Config;

use Core\View;

class Error {

/*
customized error handler..

to exception handler.

*/

public static function myErrorHandler($errno, $errstr, $errfile, $errline){

if(error_reporting() !== 0){

throw new \ErrorException($errstr, 0, $errno, $errfile, $errline);

}//end if...


}//end myErrorHandler...



public static function myExceptionHandler($exception){

$code = $exception->getcode();

//code not found (404) or general error (505)...
if ($code != 404){

	$code = 505;

}

http_response_code($code);
//
$config = new Config();

if ($config->SHOW_ERROR) {
	# code...

	echo "<h1>Fatal error</h1>";

	echo "<p>Uncaught exception: '" . get_class($exception) ."'</p>";

	echo "<p>Message: '" . $exception->getMessage() . "'</p>";

	echo "\nStack trace: <pre>" . $exception->getTraceAsString() . "</pre>";

	echo "<p>Thrown in '" . $exception->getFile() . "' on line " . $exception->getLine() . "</p>";



}else{

	// $logs = dirname(__DIR__) ."/logs/" . date("Y-m-d") . ".txt";
	//
  //   ini_set('error_log', $logs);

	$message = "Uncaught exception: '" . get_class($exception) . "'";

	$message = " with message '" . $exception->getMessage() . "'";

	$message .= "\nStack trace " . $exception->getTraceAsString();

	$message .= "\n thrown in " . $exception->getFile() . " on line " . $exception->getLine();

	// error_log($message);

    View::twigRender("$code.html");

     }

}//end myExceptionHandler....


}//end class...
