<?php
namespace App\Controllers;

use App\Models\Products;

use Core\Controller;

use Core\View;

use App\Controllers\Admin\Config;

use Yabacon\Paystack;

use App\Models\Users;

use App\Controllers\Authenticated;

use App\Models\Transactions;

class Checkout extends Authenticated{

     public function initializeAction(){

          $id = $this->getProductIdFromHomeOrCartAction();

          if(!$id){
            $id = (int)$this->data['id'];
          }


          $product = new Products();

          $users = new Users();
          //swap between employees and users record...
          if(isset($users->is_employee)){
            //use session variable as id
            $user = $users->getUserByIdAction($_SESSION['user_id'],'employees');

          }else{

            $user = $users->getUserByIdAction($_SESSION['user_id']);

          }

          $item = $product->getProductForCheckout($id);

          $_SESSION["product_id"] = $item->product_id;

          $reference = $this->setUniqueTransactionReferenceNoAction();

          $quantity = $this->getProductQuantityAction($id);

          $stockQuantityRemaining = $item->sku - $quantity;

          $_SESSION["skuLeft"] = $stockQuantityRemaining;

       if($stockQuantityRemaining > 0){

          $amount = $item->price * $quantity * 100;

          $callback_url = "https://" . $_SERVER['HTTP_HOST'] . "/Checkout/verify";
          // add config variables
          $config = new Config();

        	$paystack = new Paystack($config->PAYSTACK_TEST_SECRET);

          if($paystack){
            $tranx = $paystack->transaction->initialize([
            'amount'=>$amount,       // in kobo
            'email'=>$user->email,         // unique to customers
            'reference'=>$reference, // unique to transactions
            'callback_url' => $callback_url,
            'metadata' => [
             'custom_fields' => [
               [
                "display_name" => "Cart Items",
                "variable_name" => "cart_items",
                "value" => $quantity . " " . $item->name ]
               ]
            ]
       ]);

           if($tranx){

             $transaction = new Transactions();

             if($transaction->saveUserTransactionsAction($user->id, $item->product_id, $reference, $amount, $quantity, $item->admin_id)){
                 // redirect to page so User can pay
                 header('Location: ' . $tranx->data->authorization_url);

              }else{
                  //handle error ere..
                   // echo "Oops transaction Failed !";
                   throw new \Exception("Transaction failed.");
                  }

               }else {
                     throw new Paystack\Execption\ApiException;

               }



          }else{
            echo "Application error : Unable to initialize paystack object. <br>";
            throw new Paystack\Exception\ApiException;

          }
     }else{
          View::twigRender("Cart/invalid_quantity.html",["item" => $item]);
     }


     }






    /**
    *Verify the user transaction
    *@return void
    */
     public function verifyAction(){
         $reference = isset($this->data['reference']) ? $this->data['reference'] : '';

         if(!$reference){
           die('Payment failed');
         }
         // initialize config var
         $config = new Config();
         // initiate the Library's Paystack Object
         $paystack = new Paystack($config->PAYSTACK_TEST_SECRET);

        if($paystack){
          // verify using the library
          $tranx = $paystack->transaction->verify([
            'reference'=>$reference, // unique to transactions
          ]);

          if ('success' === $tranx->data->status) {
            // transaction was successful...
            // please check other things like whether you already gave value for this ref
            // if the email matches the customer who owns the product etc
            // Give value
            // echo "Transaction was successful.";
            View::twigRender('users/payment_successfull.html');
          }

        }else {
              echo "Verification failed <br>";
              throw new Paystack\Exception\ApiException;

        }

     }




    /**
    *Similar to IPN in paypal used to take records
    *@return void
    */
    public function chargeSuccessAction(){
     // Retrieve the request's body and parse it as JSON
        $event = Paystack\Event::capture();
        http_response_code(200);

        /* It is a important to log all events received. Add code *
         * here to log the signature and body to db or file       */

        openlog('MyPaystackEvents', LOG_CONS | LOG_NDELAY | LOG_PID, LOG_USER | LOG_PERROR);
        syslog(LOG_INFO, $event->raw);
        closelog();

        $config = new Config();

        /* Verify that the signature matches one of your keys*/

        $my_keys = [
                    //'live'=>'sk_live_blah',
                    'test'=> $config->PAYSTACK_TEST_SECRET
                  ];
        $owner = $event->discoverOwner($my_keys);
        if(!$owner){
            // None of the keys matched the event's signature
            throw new \Exception("Invalid key request from paystack", 1);

            die();
        }

        // Do something with $event->obj
        // Give value to your customer but don't give any output
        // Remember that this is a call from Paystack's servers and
        // Your customer is not seeing the response here at all
        switch($event->obj->event){
            // charge.success
            case 'charge.success':
                if('success' === $event->obj->data->status){
                    // TIP: you may still verify the transaction
                    // via an API call before giving value.
                 if(isset($_SESSION["product_id"]) && isset($_SESSION["skuLeft"])){
                   $product = new Products();
                   $product->reduceStockQuantity($_SESSION["product_id"], $_SESSION["skuLeft"]);
                   unset($_SESSION["product_id"]);
                   unset($_SESSION["skuLeft"]);
                  }
                 $transaction = new Transactions();

                 $transaction->confirmPaymentStatusAction($event->obj->data->reference);

                }
                break;
        }

    }







    /**
    *Gets product id from the homepage or shopping cart
    *@return mixed
    */
    public function getProductIdFromHomeOrCartAction(){
      if(!empty($_POST)){
           foreach ($_POST as $key => $value) {
             if (is_int($key)) {
               $id = (int)$key;
             }
           }
           return $id;
      }

    }



    /**
    *Set unique reference number for each transaction
    *@return string
    */
    public function setUniqueTransactionReferenceNoAction() :string{
      $date = new \DateTime();

      $date =  $date->format('Y-m-dH:i:s.u');

      $reference = "OM-" . strtotime($date);

      // $reference = str_replace('-', '', $date);
      //
      // $reference = str_replace(':', '', $reference);
      //
      // $reference = str_replace('.', '', $reference);

      return $reference;
    }






    /**
    *get product quantity from user shopping cart
    *@param int $id
    *@return int $quantity
    */
    public function getProductQuantityAction(int $id){

      if($_SERVER['REQUEST_METHOD'] == 'POST'){
      foreach ($_SESSION['cart'] as $key => $value) {
         if($key == $id ){
          $quantity = (int)$value['quantity'];
          }
        }
          return $quantity;
      }

        $quantity = 1;

        return $quantity;

    }



}
