<?php
namespace App\Helpers;

use App\Models\Users;

use App\Helpers\Token;


class Auth{


    /**
    *get the login session id of the user if
    *it exist else get user session object to remove user_id  and false otherwise.
    *@return mixed
    */
    public static function getUserSessionIdAction(){

      if (self::validateUserCookie()) {

        $_SESSION['user_id'] =  (self::validateUserCookie())->user_id;

      }else{

        $user = 0;

      }

      return isset($_SESSION['user_id']) ? $_SESSION['user_id'] : $user;

    }






      /**
      *destroy user session
      *@return void
      */
      public static function destroyUserSessionAction(){
        // session_start();
        // Initialize the session.
        // If you are using session_name("something"), don't forget it now!
        // session_start();

        // Unset all of the session variables.
        $_SESSION = array();

        // If it's desired to kill the session, also delete the session cookie.
        // Note: This will destroy the session, and not just the session data!
        if (ini_get("session.use_cookies")) {
            $params = session_get_cookie_params();
            setcookie(session_name(), '', time() - 42000,
                $params["path"], $params["domain"],
                $params["secure"], $params["httponly"]
            );
        }
        // check if the record has been deleted from the database...
        if(self::deleteUsercookieRecord()){
        // delete user cookie here
        setcookie("remember_me", "", time() - 10368000, "/");
        }
        // Finally, destroy the session.
        session_destroy();

      }





      /**
      *get the user session user_id
      *@return string cookie on success or false on failure
      */
      public static function getUserSession(){
        // session_start();

        return isset($_SESSION['user_id']) ? $_SESSION['user_id'] : "";
      }




// comment to be added here

      public static function deleteUsercookieRecord(){
        $cookie = self::getUserCookie();

          if ($cookie) {
              $token = new Token($cookie);

              $cookie_hash = $token->getHash();

              $user = new Users(["tokens_id" => $cookie_hash ]);

              if($user->deleteUsercookie()){

                 return true;

              }

          }
               return false;
      }




     /**
     *get user cookie datails
     *@return mixed
     */
     public static function getUserCookie(){
       return isset($_COOKIE["remember_me"]) ? $_COOKIE["remember_me"] : "";
     }




      /**
      *get the user cookie rememder_me
      *@return object user on success or false on failure
      */
      public static function validateUserCookie(){

            $cookie = self::getUserCookie();

              if($cookie){

                  $token = new Token($cookie);

                  $cookie_hash = $token->getHash();

                  $user = new Users(["tokens_id" => $cookie_hash ]);

                  $user = $user->rememberUserFromCookie();

                  if($user){
                    if((strtotime($user->expires_at) - strtotime("now")) > 0){
                       // return $user;
                       return false;
                    }

                  }
              }

         return false;

      }





      /**
      *check if user cookie or session ids exist
      *@return bool
      */
      public static function validateUserByCookieAndSession() :bool{
        if(self::getUserSession() || self::validateUserCookie()){
            return true;
        }
        return false;
      }




      /**
      *set user cookie token in the browser
      *@return void
      */
      public static function setUserCookie(object $verifiedUser) :void {

        $token = new Token();

        $cookie =  $token->getToken();

        $cookie_hash = $token->getHash();

        $datenow = new \Datetime();

        $exp = $datenow->add(new \DateInterval('P2M'));

        $expires_at = $exp->format('Y-m-d H:i:s');

        $exp = strtotime($exp->format('Y-m-d H:i:s'));

        $rememberLogin = $verifiedUser->getUserByIdFromRemberedLogin();

        if($rememberLogin){

          $user = new Users($rememberLogin);

            if($user->updateUserCookieHashValue($cookie_hash, $expires_at)){
              setcookie("remember_me", $cookie, $exp, "/");  /* expire in 2 months */
            }

          }elseif($verifiedUser->saveUserCookieHashValue($cookie_hash, $expires_at)){

            setcookie("remember_me", $cookie, $exp, "/");  /* expire in 2 months */

          }





      }




}
