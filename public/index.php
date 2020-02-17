<?php
use Core\Route;

//require vendor autoload file
require(dirname(__DIR__).'/vendor/autoload.php');

//handling errors
error_reporting(E_ALL);
set_error_handler('Core\Error::myErrorHandler');
set_exception_handler('Core\Error::myExceptionHandler');

// starting sessions;
session_start();

$url = $_SERVER['QUERY_STRING'];

Route::addRoute("{controller}/{action}");

Route::addRoute('',["controller"=>"home","action"=>"index"]);

Route::addRoute("{controller}/{id:\d+}/{action}");

Route::addRoute("{controller}/{action}/{token:[A-F0-9]+}");

Route::addRoute("Admin/{controller}/{id:\d+}/{action}",["namespace" => "Admin"]);

Route::addRoute("Admin/{controller}/{action}/{ref:OM-\d+}",["namespace" => "Admin"]);

Route::addRoute("{controller}/{action}/{ref:OM-\d+}");

Route::addRoute("Admin/{controller}/{action}",["namespace" => "Admin"]);

Route::addRoute("Admin/{controller}/{action}/{token:[A-F0-9]+}",["namespace" => "Admin"]);

Route::addRoute("{controller}/{action}/{trxref:OM-\d+}/{reference:OM-\d+}");

//print_r(Route::getRoute());

Route::dispatch($url);
