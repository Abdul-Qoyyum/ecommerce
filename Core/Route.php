<?php
namespace Core;

class Route{

	private static $__route = [];

    public static $__params = [];

public static function addRoute($route,$params = []){

	$route = preg_replace('#\{([a-z-]+)\}#', '(?P<\1>[a-z-]+)', $route);

    $route = preg_replace('#\{([a-z]+):([^\}]+)\}#','(?P<\1>\2)',$route);

    $route = '#^' . $route . '$#i';

	self::$__route[$route] = $params;
 }



public static function getRoute() {
   return self::$__route;
 }



public static function match($url){
	foreach(self::$__route as $key => $value){
		if(preg_match($key,$url,$matches)){
          foreach($matches as $id => $vals){
                if(is_string($id)){
                  $value[$id] = $vals;
                }
          }
           self::$__params = $value;
          // self::$__route[$key] = self::$__params;
           return true;
         }
		}
      return false;
	}


public static function addnamespace(){
  if(array_key_exists("namespace",self::$__params)){
    return self::$__params["namespace"] . "\ ";
  }else{
    return '';
  }
}


/**
*remove query parameters and ampersand from url(i.e Query string)
*@param string url
*@return string formatted url
*/
public static function filterQueryParams(string $url) :string{
	    	$url = preg_replace('#&[a-z]+=#i','/',$url);
        return $url;
}


/**
*redirect the user to the appropriate controller and action
*@param string $url
*@return void
*/
public static function dispatch(string $url) :void{
       $url = self::filterQueryParams($url);
	     if(self::match($url)){
        $path = "\App\Controllers\ ". self::addnamespace() . self::$__params['controller'];
         // $path = str_replace(" ", "",$path);
				 $path = self::convertToStudyCaps($path);
				 // confirm if the class exist
					if(class_exists($path)){
						$controller = new $path(self::$__params);
						$action = self::$__params['action'];
						$action = self::convertToCamelCase($action);
					if (is_callable([$controller, $action])) {
      						$controller->$action();
							}else{
								throw new \Exception("Method action (in controller $controller) noy found.");
 							}
					}else{
						throw new \Exception("Controller class $controller not found.");
         }
          }else{
						throw new \Exception("No route matched", 404);
       }

  }

	//converts action to camel case...
	protected  static function convertToCamelCase(string $actionString) : string {

			$convertedCamel= lcfirst(self::convertToStudyCaps($actionString));

			return($convertedCamel);

	}//end function...


	//converts controller first letter to uppercase
	protected static function convertToStudyCaps(string $controlString) : string{

		$convertedUpper = str_replace(" ", "", ucwords(str_replace("-", " ", $controlString)));

		return $convertedUpper;

	}//end function...



}
