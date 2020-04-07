<?php
namespace App\Models;

use Core\Model;

use PDO;

class Users extends Model{

//hold error details for users...
  public $errors = [];

  public function __construct(array $details = []){

    foreach($details as $key => $value){

      $this->$key = $value;

    }

  }


  /** fetch user by their id
  *@param int $id
  *@return Object $user of false otherwise
  */
  public function getUserByIdAction(int $id){
   $db = $this->connect();
   // switch tables
   $users = 'users';

   $sql = "SELECT * FROM $users WHERE id = :id";

   $stmt = $db->prepare($sql);

   $stmt->bindValue(':id', $id, PDO::PARAM_INT);

   $stmt->setFetchMode(PDO::FETCH_CLASS, get_called_class());

   $stmt->execute();

   return $stmt->fetch();
  }



  /** fetch user by their email
  *@return user object or false otherwise
  */
  public function getUserByEmail(){

    $db = $this->connect();

    $users = 'users';

    $sql = "SELECT * FROM $users WHERE email = :email";

    $stmt = $db->prepare($sql);

    $stmt->bindValue(':email', $this->email, PDO::PARAM_STR);

    $stmt->setFetchMode(PDO::FETCH_CLASS, get_called_class());

    $stmt->execute();

    return $stmt->fetch();

  }


  /** fetch user by their email
  *@return user object or false otherwise
  */
  public function getUserByUsername(){

    $db = $this->connect();
    // switch table value for reusability...
    $users = 'users';

    $sql = "SELECT * FROM $users WHERE username = :username";

    $stmt = $db->prepare($sql);

    $stmt->bindValue(':username', $this->username, PDO::PARAM_STR);

    $stmt->setFetchMode(PDO::FETCH_CLASS, get_called_class());

    $stmt->execute();

    return $stmt->fetch();

  }




  /**
  *validates the employee's email field from send invitation page
  *@return bool
  */
  public function validateEmployeeEmail() :bool{
        if($this->email == ''){
          $this->errors['email'] = 'Email field is required.';
          return false;
        }

        return true;
  }



  /** Add the invitation details to the database
  *@param int $admin_id
  *@param string $tokens_id
  *@return bool
  */
  public function saveInvitationDetails(int $admin_id, string $tokens_id) :bool {

    $db = $this->connect();

    if($db){

          $date = new \DateTime();

          $date->add(new \DateInterval('P2D'));

          $exp = $date->format('Y-m-d H:i:s');

          $sql =  "INSERT INTO invitations(admin_id, tokens_id, expires_at) VALUES
                  (:admin_id, :tokens_id, :expires_at)";

          $stmt = $db->prepare($sql);

          $stmt->bindValue(':admin_id', $admin_id, PDO::PARAM_INT);

          $stmt->bindValue(':tokens_id', $tokens_id, PDO::PARAM_STR);

          $stmt->bindValue(':expires_at', $exp, PDO::PARAM_STR);

          return $stmt->execute();

     }

   return false;

  }



    /**
    *fetch the employee invitation details
    *@param string $tokens_id
    *@return object or mixed
    */
     public function getInvitationByToken(string $tokens_id){

       $db = $this->connect();

       $sql = "SELECT * FROM invitations WHERE tokens_id = :tokens_id";

       $stmt = $db->prepare($sql);

       $stmt->bindValue(':tokens_id', $tokens_id, PDO::PARAM_STR);

       $stmt->setFetchMode(PDO::FETCH_CLASS, get_called_class());

       $stmt->execute();

       return $stmt->fetch();

     }

     /**
     *Helper method to validate employee details from create employee page
     *@return void
     */
     public function validateEmployeeDetails() :void {

       if ($_SERVER['REQUEST_METHOD'] == 'POST'){
         if($this->fname == ''){
            $this->errors['fname'] = "Please enter your firstname.";
         }
         if($this->lname == ''){
            $this->errors['lname'] = "Please enter your lastname.";
         }

         $this->validateUserEmailAndPassword();

         if(empty($this->address)){
           $this->errors['address'] = "Address field is required.";
         }
       }


     }


     protected function validateEmail(string $email) : bool{

     //remove dangerous characters from email...
     $Email = filter_var($email, FILTER_SANITIZE_EMAIL);

         if(filter_var($email, FILTER_VALIDATE_EMAIL)){

         return true;

     }

       return false;

 }//end emailvalidation...



     /**
     *validates user email and password from the sign up page
     *@return void
     */
    public function validateUserEmailAndPassword(){

      if($this->email == ''){
         $this->errors['email'] = "Please enter your email.";
      }elseif(!$this->validateEmail($this->email)){
        $this->errors['email'] = "Invalid email.";
      }

      if($this->password == ''){
         $this->errors['password'] = "Please enter your password.";
      }elseif(strlen($this->password) < 6){

          $this->errors['password'] = "Password must be at least 6 characters long";

      }


    }

    /**
    *validates store name validate store_name
    *@return void
    */
    public function validateUserName(){
      if($this->username == ''){
          $this->errors["name"] = "User name is required.";
      }
    }


    /**
    *deletes empolyee Account
    *@return bool
    */
    public function removeEmployee() :bool{
      $db = $this->connect();
      if($db){
      $sql = "DELETE FROM employees WHERE id = :id";
      $stmt = $db->prepare($sql);
      $stmt->bindValue(':id',$this->id,PDO::PARAM_INT);
      return $stmt->execute();
      }
      return false;
    }


    /**
    *Save user email and password to the database
    *if validation is successfull
    *@param string $token_hash
    *@return bool
    */
    public function saveUserEmailAndPassword(string $token_hash) :bool{

      if($_SERVER['REQUEST_METHOD'] == 'POST'){

        $this->validateUserEmailAndPassword();

        $this->validateUserName();

        if(empty($this->errors)){

          if(!$this->getUserByUsername()){
           // save details if account does not exist on users and employees table...
           if(!$this->getUserByEmail()){

            //remember to hash password here for security
            $password_hash = password_hash($this->password, PASSWORD_DEFAULT);

            $db = $this->connect();

            $sql = "INSERT INTO users (username, email, password, activation_hash) VALUES (:username, :email, :password, :token_hash)";

            $stmt = $db->prepare($sql);

            $stmt->bindValue(':username', $this->username, PDO::PARAM_STR);

            $stmt->bindValue(':email', $this->email, PDO::PARAM_STR);

            $stmt->bindValue(':password', $password_hash, PDO::PARAM_STR);

            $stmt->bindValue(':token_hash', $token_hash, PDO::PARAM_STR);

            return $stmt->execute();

              }else{

                $this->errors['email'] = "Account already exist.";

                return false;
              }
            }else{

                $this->errors["username"] = "Username already exist";

                return false;

          }

       }

  }

        return false;
}




     /**
     *Add the employee's details from the create_employee page to the employee's table
     *on successfull validation
     *returns true on successful operation else return false
     *@return bool
     */
     public function createEmployee(int $adminId) :bool {

       $this->validateEmployeeDetails();

       if(empty($this->errors)){

         $db =  $this->connect();

         $staffId = strrev(strtotime("now"));

         $password_hash = password_hash($this->password, PASSWORD_DEFAULT);

         $sql = "INSERT INTO employees(id, fname, lname, email, password, address, admin_id, is_active) VALUES
                (:id, :fname, :lname, :email, :password, :address, :admin_id, 1)";

         $stmt =  $db->prepare($sql);

         $stmt->bindValue(':id', $staffId, PDO::PARAM_STR);

         $stmt->bindValue(':fname', $this->fname, PDO::PARAM_STR);

         $stmt->bindValue(':lname', $this->lname, PDO::PARAM_STR);

         $stmt->bindValue(':email', $this->email, PDO::PARAM_STR);

         $stmt->bindValue(':password', $password_hash, PDO::PARAM_STR);

         $stmt->bindValue(':address', $this->address, PDO::PARAM_STR);

         $stmt->bindValue(':admin_id', $adminId, PDO::PARAM_INT);

         return $stmt->execute();

       }

         return false;

     }





     /**
     *Deletes the employee invitation record from database
     *returns true on success and false on failure
     *@param string $tokens_id
     *@return bool
     */
     public function deleteEmployeeInvitation(string $tokens_id) :bool{

      $db = $this->connect();

      $sql = "DELETE FROM invitations WHERE tokens_id = :tokens_id";

      $stmt = $db->prepare($sql);

      $stmt->bindValue(':tokens_id', $tokens_id, PDO::PARAM_STR);

      return $stmt->execute() !== false;
     }



     /**
     *fetch the details of the employees for the admin
     *@return array of employees objects
     */
     public function getEmployees() :array{
       $db = $this->connect();

       $sql = "SELECT * FROM employees";

       $stmt = $db->query($sql);

       $stmt->setFetchMode(PDO::FETCH_CLASS, get_called_class());

       $stmt->execute();

       return $stmt->fetchAll();
     }




     /**
     * Gets all employees for pagination for the current Admin view
     * @param int $start,
     *@param int $stop
     *@param int $admin_id
     * @return array
     */
     public function getEmployeesForPagination(int $start, int $stop, int $admin_id) : array {

        $db = $this->connect();

        $sql = "SELECT CONCAT(fname, '  ', lname) AS names, id, email, address FROM employees WHERE admin_id = :admin_id LIMIT :start, :stop";

        $stmt = $db->prepare($sql);

        $stmt->bindValue(':start', $start, PDO::PARAM_INT);

        $stmt->bindValue(':stop', $stop, PDO::PARAM_INT);

        $stmt->bindValue(':admin_id', $admin_id, PDO::PARAM_INT);

        $stmt->setFetchMode(PDO::FETCH_CLASS, get_called_class());

        $stmt->execute();

        return $stmt->fetchAll();
     }




     /**
     *activates user account
     *@param string $token_hash
     *return bool
     */
     public function activateUserAccount(string $token_hash) :bool{
       $db = $this->connect();

       $sql = "UPDATE users SET is_active = 1 WHERE activation_hash =  :token_hash";

       $stmt = $db->prepare($sql);

       $stmt->bindValue(':token_hash', $token_hash, PDO::PARAM_STR);

       return $stmt->execute();

     }




     /**
     *gets user details from the database
     *@param string $token_hash
     *@param string column optional
     *@param string table optional
     *@return mixed
     */
     public function getUserByToken(string $token_hash, string $column = '', string $table = ''){
         $db = $this->connect();

         if($column){
           $token = $column;
         }else{
           $token = 'activation_hash';
         }

         if($table){
            $users = $table;
         }else {
            $users = 'users';
         }

        if($db){

           $sql = "SELECT * FROM $users WHERE $token = :token_hash";

           $stmt = $db->prepare($sql);

           $stmt->bindValue(':token_hash', $token_hash, PDO::PARAM_STR);

           $stmt->setFetchMode(PDO::FETCH_CLASS, get_called_class());

           $stmt->execute();

           return $stmt->fetch();

       }

        return false;

     }


     /**
     *Save store information to the database
     *if email and password validation is successfull
     *@param string $token_hash
     *@return bool
     */
     public function registerStore(string $token_hash) :bool{

       if($_SERVER['REQUEST_METHOD'] == 'POST'){

         $this->validateUserEmailAndPassword();

         $this->validateUserName();

         if(empty($this->errors)){
            // Allow for store account creation if user does not already exist...
             if(!$this->getUserByUsername()){

             if(!$this->getUserByEmail()){

               //remember to hash password here for security
             $password_hash = password_hash($this->password, PASSWORD_DEFAULT);

             $db = $this->connect();

             $sql = "INSERT INTO users (username, email, password, activation_hash, is_admin) VALUES (:username, :email, :password, :token_hash, 1)";

             $stmt = $db->prepare($sql);

             $stmt->bindValue(':username', $this->username, PDO::PARAM_STR);

             $stmt->bindValue(':email', $this->email, PDO::PARAM_STR);

             $stmt->bindValue(':password', $password_hash, PDO::PARAM_STR);

             $stmt->bindValue(':token_hash', $token_hash, PDO::PARAM_STR);

             return $stmt->execute();
            }else{

              $this->errors["email"] = "Email already exist";

              return false;

           }

           }else{

             $this->errors["username"] = "Username already exist.";

             return false;

           }
         }
      }

      return false;

     }



     /**
     * Verify if user's email matches the password
     *on attempt to login
     *returns user object on success and false on failure
     *@return mixed
     */
     public function verifyEmailAndPassword(string $table = ''){

       $table = isset($table) ? $table : null;

       if($_SERVER['REQUEST_METHOD'] == 'POST'){

         $this->validateUserEmailAndPassword();

         if(empty($this->errors)){

           $user = $this->getUserByEmail($table);

           if($user){

             if(password_verify($this->password,$user->password)){

                  return $user;

             }else{

               $this->errors['password'] = "Invalid password.";

               return false;

             }


           }else{

              $this->errors['email'] = "Invalid email and or password.";

              return false;

           }

         }

       }

       return false;

     }







    /**
    *save user cookie hash value and user id to the rememdered_logins table
    *@param string $cookie_hash
    *@param string $exp
    *@return bool
    */
     public function saveUserCookieHashValue(string $cookie_hash, string $exp) :bool {

        $db = $this->connect();

        $sql = "INSERT INTO remembered_logins(tokens_id, user_id, expires_at) VALUES (:tokens_id, :user_id, :expires_at)";

        $stmt = $db->prepare($sql);

        $stmt->bindValue(':tokens_id', $cookie_hash, PDO::PARAM_STR);

        $stmt->bindValue(':user_id', $this->id, PDO::PARAM_INT);

        $stmt->bindValue(':expires_at', $exp);

        return $stmt->execute();

     }


     /**
     * get user by id from remembered_logins table
     *
     *@return mixed
     */
     public function getUserByIdFromRemberedLogin(){
       $db = $this->connect();
       $sql = "SELECT * FROM  remembered_logins WHERE user_id = :user_id";
       $stmt = $db->prepare($sql);
       $stmt->bindValue(':user_id',$this->id,PDO::PARAM_INT);
       $stmt->execute();
       return $stmt->fetch();
     }


     /**
     *
     */
     public function updateUserCookieHashValue($cookie_hash, $expires_at){
       $db = $this->connect();

       if($db){
           $sql = "UPDATE remembered_logins SET tokens_id = :tokens_id, expires_at = :expires_at WHERE user_id = :user_id";
           $stmt = $db->prepare($sql);
           $stmt->bindValue(':tokens_id',$cookie_hash, PDO::PARAM_STR);
           $stmt->bindValue(':expires_at',$expires_at);
           $stmt->bindValue(':user_id',$this->user_id, PDO::PARAM_INT);
           return $stmt->execute();
       }

          return false;

     }



     /**
     *Get user cookie details from rememdered_logins table
     *@param string $cookie_hash
     *@return mixed
     */
     public function rememberUserFromCookie(){
        $db = $this->connect();

        $sql =  "SELECT * FROM remembered_logins WHERE tokens_id = :tokens_id";

        $stmt = $db->prepare($sql);

        $stmt->bindValue(':tokens_id', $this->tokens_id, PDO::PARAM_STR);

        $stmt->setFetchMode(PDO::FETCH_CLASS, get_called_class());

        $stmt->execute();

        return $stmt->fetch();
     }



     /**
     *Deletes user cookie record from the remembered_logins table
     *@return void
     */
      public function deleteUsercookie(){
        $db = $this->connect();

        $sql = "DELETE FROM remembered_logins WHERE tokens_id = :tokens_id";

        $stmt = $db->prepare($sql);

        $stmt->bindValue(':tokens_id', $this->tokens_id, PDO::PARAM_STR);

        return $stmt->execute();
      }


       // continue here
      public function validateNewPassword(){
          if($_SERVER['REQUEST_METHOD'] == 'POST'){
              if($this->new_password == ''){
                 $this->errors['new_password'] = "Please specify your new password";
              }
          }
      }


      /**
      *reset user's password
      *@return bool
      */
      public function resetPassword(string $table = '') :bool{

        $this->validateNewPassword();

        if(empty($this->errors)){
        $db =  $this->connect();

        if($table){
          $users = $table;
        }else {
          $users = 'users';
        }

        $password_hash = password_hash($this->new_password, PASSWORD_DEFAULT);

        $sql = "UPDATE $users SET password = :password WHERE id = :id";

        $stmt = $db->prepare($sql);

        $stmt->bindValue(':password', $password_hash, PDO::PARAM_STR);

        $stmt->bindValue(':id', $this->id, PDO::PARAM_INT);

        return $stmt->execute();
      }

        return false;

      }




      /**
      *update the password_reset_hash and password_reset_exp details on users table
      *@param string password_reset_hash
      *@param string password_reset_exp
      *@param string $column optional to switch tables
      *@return bool
      */
      public function updatePasswordResetDetails(string $password_reset_hash, string $password_reset_exp, string $users = ''){
        $db = $this->connect();

        if($users){
          $users = $users;
        }else{
          $users = 'users';
        }

        $sql = "UPDATE $users set password_reset_hash = :password_reset_hash , password_reset_exp = :password_reset_exp
              WHERE email = :email";

        $stmt = $db->prepare($sql);

        $stmt->bindValue(':password_reset_hash', $password_reset_hash, PDO::PARAM_STR);

        $stmt->bindValue(':password_reset_exp', $password_reset_exp, PDO::PARAM_STR);

        $stmt->bindValue(':email', $this->email, PDO::PARAM_STR);

        return $stmt->execute();

      }






}
