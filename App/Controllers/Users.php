<?php
namespace App\Controllers;

use Core\Controller;

use Core\View;

use App\Helpers\Token;

use App\Models\Users AS User;

use App\Helpers\Mail;

use App\Models\Transactions;

use App\Helpers\Auth;

use App\Helpers\TransactionsPagination;

class Users extends Controller{

    /**
    *Renders the signup page for the user
    *@return void
    */
      public function signUpAction() :void {

          View::twigRender('Users/signup.html');

      }


      /**
      *Send the users details to the users' model
      *for validation and registration
      *@return void
      */
      public function registerUserAction() :void{
           $user = new User($_POST);

           $token = new Token();

           $token_hash = $token->getHash();

          if($user->saveUserEmailAndPassword($token_hash)){

              $this->sendAccountActivationLinkAction('/users/activateuseraccount/', $token_hash,$user);

           }else{

             View::twigRender('Users/signup.html', ['errors' => $user->errors, 'user' => $user]);

           }

      }


      /**
      *renders the login page
      *@return void
      */
       public function staffsLoginAction(){

            View::twigRender('Staffs/login.html');

       }



       /**
       *Authenticate the staff authentication details
       *from the staff login page
       */
       public function staffsAuthenticateAction(){
           $user = new User($_POST);

           $verifiedUser = $user->verifyEmailAndPassword('employees');

           if($verifiedUser){
             if($verifiedUser->is_active){

             if (isset($user->remember_me)) {

               Auth::setUserCookie($verifiedUser);

             }


               $_SESSION['user_id'] = (int)$verifiedUser->id;

               $this->redirect('Staffs/index');
             }else{


               $user->errors['email'] = "Account is not active, Please check your email to activate your account";

               View::twigRender('Staffs/login.html',['errors' => $user->errors, 'user' => $user]);


             }


           }else{

             View::twigRender('Staffs/login.html',['errors' => $user->errors, 'user' => $user]);

           }

       }


    /**
     *Helper method to send account activation link to the user
     * @param string $link
     * @param string $token_hash
     * @param object $user
     * @return void
     */
        public function sendAccountActivationLinkAction(string $link, string $token_hash, object $user) :void{

            $url = "https://" . $_SERVER['HTTP_HOST'] . $link . $token_hash;

            $body = View::twigReturn('Users/email_activation.html', ['url'=>$url]);

            $text = View::twigReturn('Users/email_activation.txt', ['url'=>$url]);

            if(Mail::sendMail('Account activation',[$user->email], $body, $text)){

                 $this->redirect('users/accountactivationnotification');

            }else{

                 echo "Something went wrong";

            }

      }



      /**
      *Renders the email notification page for account activation
      *on successfull sign up
      *@return void
      */
       public function accountActivationNotificationAction() :void{

         View::twigRender('Users/account_activation_notification.html');

       }



       /**
       *activates user account from email link
       *renders activation successfull page on successfull activation
       *else renders activation failure page
       *@return void
       */
        public function activateUserAccountAction() :void{

             $token_hash = isset($this->data['token']) ? $this->data['token'] : '';

             if($token_hash){

               $user = new User();

               if($user->getUserByToken($token_hash) && $user->activateUserAccount($token_hash)){

                 View::twigRender('Users/email_activation_success.html');

              }else {

                View::twigRender('Users/email_activation_failure.html');

              }


             }

        }







        /**
        *renders the book a store view
        *@return void
        */
        public function bookAStoreAction(){

          View::twigRender('Users/book_a_store.html');

        }



        /**
        *transfer store account details to the user model for validation
        *and registration
        *@return void
        */
        public function registerStoreAction(){

            $user = new User($_POST);
            //
            // echo "<pre>";
            // var_dump($_POST);
            // echo "</pre>";

            $token = new Token();

            $token_hash = $token->getHash();

          if($user->registerStore($token_hash)){

             $this->sendAccountActivationLinkAction('/users/activatestoreaccount/', $token_hash, 'store', $user);

          }else{

             View::twigRender('Users/book_a_store.html', ['errors' => $user->errors, 'user'=>$user]);

          }


        }



        /**
        *handles the get request from the email link to activate
        *admin account
        *@return void
        */
        public function activateStoreAccountAction() :void{
           $this->activateUserAccountAction();
        }




          /**
          *renders the login page
          *@return void
          */
           public function loginAction(){

                View::twigRender('Users/login.html');

           }




           /**
           *Verify users email and password detatils before login
           *redirect to home page on successfull login else diaplays errors
           *@return void
           */
           public function authenticateAction(){
             //delete old session but retain session data to prevent hacker
             session_regenerate_id(true);
              $user = new User($_POST);
              $verifiedUser = $user->verifyEmailAndPassword();
              // check if the account exist...
              if($verifiedUser){
              // check if the account is activated...
                if($verifiedUser->is_active){

                if (isset($user->remember_me)) {

                  Auth::setUserCookie($verifiedUser);

                }


                  $_SESSION['user_id'] = (int)$verifiedUser->id;

                  $this->redirect('Home/index');

                }else{

                  $user->errors['email'] = "Account is not active";

                  View::twigRender('Users/login.html',['errors' => $user->errors, 'user' => $user]);

                }

              }else{

                View::twigRender('Users/login.html',['errors' => $user->errors, 'user' => $user]);

              }

           }



           /**
           *renders the reset password view
           *@return void
           */
           public function requireNewPasswordAction(){
             View::twigRender('Users/require_new_password.html');
           }



          /**
          *save password reset details from forgot password link to the database
          *and send password reset email to the users email
          *@return void
          */
           public function resetPasswordAction(){
             $user = new User($_POST);

             $user = $user->getUserByEmail();

             if($user){

                $token = new Token();

                $token_hash = $token->getHash();

                $date = (new \DateTime())->add(new \DateInterval("PT10M"));

                $time = $date->format('Y-m-d H:i:s');

                if($user->updatePasswordResetDetails($token_hash, $time)){


                $url = "https://" . $_SERVER['HTTP_HOST'] . "/" . "users/resetuserpassword" . "/" . $token_hash;

                $body = View::twigReturn('Password/password_reset_email.html', ['url'=>$url]);

                $text = View::twigReturn('Password/password_reset_email.txt', ['url'=>$url]);

               if(Mail::sendMail('Password Reset',[$user->email], $body, $text)){

                    $this->redirect('users/passwordresetnotification');

               }else{

                    echo "Something went wrong";

               }

                }
             }

           }





           /**
           *Render the password rest notification page
           *@return void
           */
           public function passwordResetNotification() :void{
             View::twigRender('Password/password_reset_notification.html');
           }




           /**
           *validates token details from reset email link
           *renders the reset password page if token is vaild
           *else deny user from being able to reset their password
           *@return void
           */
           public function resetUserPasswordAction(){
             $token_hash = isset($this->data['token']) ? $this->data['token'] : '';

             if($token_hash){

               $user = new User();

               $user = $user->getUserByToken($token_hash, 'password_reset_hash');

               if($user){
                 if(strtotime($user->password_reset_exp) - strtotime("now") > 0){
                   View::twigRender('Password/password_reset.html',['id' => $user->id]);
                 }else {
                   View::twigRender('Password/password_reset_link_expired.html');
                 }

              }else {

                View::twigRender('Password/password_activation_failure.html');

              }


             }

           }


           /**
           *Update password details in the database
           *shows notification after successfull password update
           *@return void
           */
           public function registerPasswordAction(){
              $user = new User($_POST);
              if($user->resetPassword()){
                $this->redirect('users/resetsuccessfull');
              }
           }


           /**
           *Helper method to render the password reset successfull page
           *@return void
            */
           public function resetSuccessfullAction(){
             View::twigRender('Password/password_reset_successfull.html');
           }




                       /**
                       *Helper method to paginate and render transaction view for the admin
                       *@return void
                       */
                       public function viewPaginatedTransactionAction(string $table = null):void{

                         $user = new User();

                         $user = $user->getUserByIdAction($_SESSION['user_id']);

                        if($user){

                         $pagination = new TransactionsPagination();

                         $start = isset($this->data['id']) ? $this->data['id'] : 1;

                         $paginatedPages = $pagination->paginate($start);

                         $transactions_per_page =$pagination->getUserPaginatedList();

                         $totalTransactions = $pagination->getTransactionsNumber(null, $_SESSION['user_id']);

                         View::twigRender("Users/view_transactions.html",
                           ['paginatedPages' => $paginatedPages,
                            'transactions_per_page' => $transactions_per_page,
                            'totalTransactions' => $totalTransactions,
                            'user'=> $user,
                            'session_id' => Auth::getUserSessionIdAction()
                                ]);

                              }

                       }


                       /**
                       *Confirm transaction delivery by the user
                       */
                        public function confirmUserDeliveryAction(){
                        // session_start();

                        $reference = isset($this->data['ref']) ? $this->data['ref'] : '';

                        $users = new User();

                        $user = $users->getUserByIdAction($_SESSION['user_id']);


                        $transaction = new Transactions();

                          if($transaction->confirmUserDelivery($reference)){
                             $this->viewPaginatedTransactionAction();
                          }

                        }




           /**
           *log the user out
           *@return void
           */
           public function logOutAction() :void{

               Auth::destroyUserSessionAction();

               $this->redirect('home/index');
           }








}
