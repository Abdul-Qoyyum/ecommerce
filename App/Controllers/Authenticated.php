<?php
namespace App\Controllers;

use Core\Controller;

use App\Helpers\Auth;

use Core\View;

class Authenticated extends Controller{

  /**
  *Give admin the pass if logged in
  *@return bool
  */
  public function before() :bool{

     return Auth::validateUserByCookieAndSession();

  }


  public function after(){
    //add some flash messages here...
    $this->redirect('users/login');
    // View::twigRender('users/login');
  }




}
