<?php
namespace Core;

class Controller{

  /**
  *Make the QUERY Parameters from $the Url available to all Controllers
  *@param array data
  *return void
  */
  public function __construct($data){
  
       $this->data = $data;
  }


  public function __call($method, $arguments){

      $method .= "Action";
      if(method_exists($this, $method)){
         if($this->before()){

            call_user_func_array([$this,$method], $arguments);

         }else{

           $this->after();

         }
       }else{
         throw new \Exception("Method $method does not exist ");
       }


  }




    /**
    *Grant access if the user is authenticated
    *@param void
    *@return bool
    */
      public function before() :bool{
        return true;
      }



    /**
    *
    *
    */
      public function after() {

      }

    /**
    *redirects the user to another page and exit script execution
    *@param string $url
    *@return void
    */
     public function redirect(string $url) :void{
         header("location:" . "http://" . $_SERVER['HTTP_HOST'] . "/" . $url, true, 303);
         exit();
     }


}
