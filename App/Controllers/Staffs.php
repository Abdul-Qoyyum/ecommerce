<?php
namespace App\Controllers;

use App\Models\Users;

use Core\Controller;

use Core\View;

use App\Models\Transactions;

use App\Controllers\Authenticated;

use App\Helpers\Token;

use App\Helpers\Auth;

use App\Helpers\TransactionsPagination;

use App\Models\Products;

class Staffs extends Authenticated{

  public function before() :bool{

    $users = new Users;

    $user = $users->getUserByIdAction(Auth::getUserSessionIdAction(), 'employees');

    if(!isset($user->is_employee)){
       return false;
    }

    return parent::before();
  }

  public function after(){
    //add some flash messages here...
    $this->redirect('staffs/login');

  }


/**
*Renders the index view for the staff
*@return void
*/
  public function indexAction() :void{

    $productObj = new Products();

    $products = $productObj->getProductsAction();

    $users = new Users();

    $user = $users->getUserByIdAction(Auth::getUserSessionIdAction(), 'employees');

    View::twigRender('Home/index.html',
     ['products'=>$products,'user'=> $user, 'session_id' => Auth::getUserSessionIdAction()]);

  }

            /**
            *Rendere the dash board for the employees
            *@return void
            */
            public function dashboard(){

              $users = new Users();

              $users = $users->getUserByIdAction($_SESSION['user_id'],'employees');
              // additional security to confirm a staff
              // if($users->is_employee < 1){
              //    $this->redirect('staffs/index');
              // }
              View::twigRender('Staffs/index.html');
            }



            /**
            *Helper method to paginate and render transaction view for the admin
            *@return void
            */
            public function viewPaginatedTransactionAction(string $table = null):void{
              $user = new Users();

              // $user_id = isset($_SESSION['user_id']) ? $_SESSION['user_id'] : 0;
              // trying to receive admin id fromthe employees table...
              $user = $user->getUserByIdAction($_SESSION['user_id'],'employees');
echo "<pre>";
var_dump($user);
echo "</pre>";
echo "Table : " . $table;
              if($user->is_employee == 1){

              $pagination = new TransactionsPagination();

              $start = isset($this->data['id']) ? $this->data['id'] : 1;

              $paginatedPages = $pagination->paginate($start);

              $transactions_per_page =$pagination->getPaginatedList($table, $user->admin_id);

              $totalTransactions = $pagination->getTransactionsNumber($table, $user->admin_id);

              View::twigRender("Staffs/view_transactions.html",
                ['paginatedPages' => $paginatedPages,
                 'transactions_per_page' => $transactions_per_page,
                 'totalTransactions' => $totalTransactions
                     ]);

                   }

            }


            /**
            *Renders the pending transaction view for the admin
            *@return void
            */
            public function viewPendingTransactionsAction() :void{
               $this->viewPaginatedTransactionAction('pending');
            }



            /**
            *Renders the confirmed transaction view for the admin
            *@return void
            */
            public function viewConfirmedTransactionsAction():void{
              $this->viewPaginatedTransactionAction('confirmed');
            }




     // i'm finding another way to activate staff from admin sending the link...
     public function accountActivation(){

     }


                /**
                *renders the reset password view
                *@return void
                */
                public function requireNewPasswordAction(){
                  View::twigRender('Staffs/require_new_password.html');
                }



               /**
               *save password reset details from forgot password link to the database
               *and send password reset email to the users email
               *@return void
               */
                public function resetPasswordAction(){
                  $user = new Users($_POST);

                  $user = $user->getUserByEmail('employees');

                  if($user){

                     $token = new Token();

                     $token_hash = $token->getHash();

                     $date = (new \DateTime())->add(new \DateInterval("PT10M"));

                     $time = $date->format('Y-m-d H:i:s');

                     if($user->updatePasswordResetDetails($token_hash, $time, 'employees')){


                     $url = "https://" . $_SERVER['HTTP_HOST'] . "/" . "staffs/resetstaffspassword" . "/" . $token_hash;

                     $body = View::twigReturn('Password/password_reset_email.html', ['url'=>$url]);

                     $text = View::twigReturn('Password/password_reset_email.txt', ['url'=>$url]);

                    if(Mail::sendMail('Password Reset',[$user->email], $body, $text)){

                         $this->redirect('staffs/passwordresetnotification');

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
                  View::twigRender('Staffs/password_reset_notification.html');
                }




                /**
                *validates token details from reset email link
                *renders the reset password page if token is vaild
                *else deny user from being able to reset their password
                *@return void
                */
                public function resetStaffsPasswordAction(){
                  $token_hash = isset($this->data['token']) ? $this->data['token'] : '';

                  if($token_hash){

                    $user = new Users();

                    $user = $user->getUserByToken($token_hash, 'password_reset_hash', 'employees');

                    if($user){
                      if(strtotime($user->password_reset_exp) - strtotime("now") > 0){
                        View::twigRender('Staffs/password_reset.html',['id' => $user->id]);
                      }else {
                        View::twigRender('Staffs/password_reset_link_expired.html');
                      }

                   }else {

                     View::twigRender('Staffs/password_activation_failure.html');

                   }


                  }

                }



                /**
                *Update password details in the database
                *shows notification after successfull password update
                *@return void
                */
                public function registerPasswordAction(){
                   $user = new Users($_POST);
                   if($user->resetPassword('employees')){
                     $this->redirect('staffs/resetsuccessfull');
                   }
                }


                /**
                *Confirm transaction delivery by the admin
                */
                 public function confirmDeliveryAction(){
                 // session_start();

                 $reference = isset($this->data['ref']) ? $this->data['ref'] : '';

                 $users = new Users();

                 $user = $users->getUserByIdAction($_SESSION['user_id'], 'employees');

                 $username = $user->fname . " " . $user->lname;

                 $transaction = new Transactions();

                   if($transaction->confirmDelivery($reference, $username)){
                      $this->viewPaginatedTransactionAction();
                   }

                 }


                /**
                *Helper method to render the password reset successfull page
                *@return void
                */
                public function resetSuccessfullAction(){
                  View::twigRender('Staffs/password_reset_successfull.html');
                }







}
