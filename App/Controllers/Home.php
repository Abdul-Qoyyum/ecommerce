<?php
namespace App\Controllers;

use Core\Controller;

use Core\View;

use App\Models\Products;

use App\Models\Users;

use App\Helpers\Auth;

use App\Models\Reviews;


class Home extends Controller{

    public function indexAction(){
        $users = new Users();

        $user = $users->getUserByIdAction(Auth::getUserSessionIdAction());

        View::twigRender('Home/index.html',
          ['user'=> $user, 'session_id' => Auth::getUserSessionIdAction()]);

  }

  public function storeIndexAction(){

      $productObj = new Products();

      //get ids for calculate function...
      $productIds = $productObj->getProductIds();

      $products = $productObj->getProductsAction();

      $users = new Users();

      $user = $users->getUserByIdAction(Auth::getUserSessionIdAction());

      //fetch ratings for all products...
      $ratings = $this->calculateProductsRatingsAction($productIds);

      View::twigRender('Home/store.html',
       ['products'=>$products,'user'=> $user, 'session_id' => Auth::getUserSessionIdAction(),'ratings'=>$ratings]);


}


     public function viewProductDetailsAction(){

       $productId = isset($this->data['id']) ? $this->data['id'] : "";

       $id = "('" . $productId . "')";

       if ($productId) {
         $productObj = new Products();

         $product  = $productObj->getProductAction($id);

         $review = new Reviews();

         $reviews = $review->getProductReviewDetails($productId);

         $individual_rating = $this->getEachStarRatingForAProduct($productId);

         if (isset($_SESSION['user_id'])) {
           $show_comments = $review->getUserReview($_SESSION['user_id'], $productId);
         }else{
           $show_comments = 0;
         }

           View::twigRender('Home/product_details.html', [
           'individual_rating' => $individual_rating,
           'products'=>$product,
           'reviews'=>$reviews,
           'productId'=>$productId,
             'show_comments' => $show_comments
         ]);
       }
     }



    /**
    *get each star rating for the product
    *@return array
    */
     public function getEachStarRatingForAProduct($productId) :array{

       $reviews = new Reviews();

       // declare ratings for each products the structure is id follow by each number of stars...
       $ratings = [];

       for($i=1;$i<=5;$i++){

         if($i == 1){
           $starOne = $reviews->star($productId, 1);
         }
         if($i == 2){
           $starTwo = $reviews->star($productId, 2);
         }
         if($i == 3){
           $starThree = $reviews->star($productId, 3);
         }
         if($i == 4){
           $starFour = $reviews->star($productId, 4);
         }
         if($i == 5){
           $starFive = $reviews->star($productId, 5);
         }

       }

       $totalRatings = $starOne + $starTwo + $starThree + $starFour + $starFive;

         if($totalRatings == 0){
           $totalRatings = 1;
         }

         $ratings[] = [
           "id" => $productId,
           "starOne" => $starOne,
           "starTwo" => $starTwo,
           "starThree" => $starThree,
           "starFour" => $starFour,
           "starFive" => $starFive,
           "totalratings" => $totalRatings
         ];

       return $ratings;


     }




     /**
     *calculate ratings for all products
     *@param array $productIds
     *@return array $ratings
     */
     public function calculateProductsRatingsAction($productIds){

             $reviews = new Reviews();

             // declare ratings for each products the structure is id follow by each number of stars...
             $ratings = [];

             foreach ($productIds as $key => $value) {

               for($i=1;$i<=5;$i++){

                 if($i == 1){
                   $starOne = $reviews->star($value['id'], 1);
                 }
                 if($i == 2){
                   $starTwo = $reviews->star($value['id'], 2);
                 }
                 if($i == 3){
                   $starThree = $reviews->star($value['id'], 3);
                 }
                 if($i == 4){
                   $starFour = $reviews->star($value['id'], 4);
                 }
                 if($i == 5){
                   $starFive = $reviews->star($value['id'], 5);
                 }

               }
                 // each products will now have each rating
                 // check current rating is not zero
              if($starOne || $starTwo || $starThree || $starFour || $starFive){
               $ratings[] = [
                 "id" => $value['id'],
                 "rank" => ($starOne * 1 + $starTwo * 2 + $starThree * 3 + $starFour * 4 + $starFive * 5) / ($starOne + $starTwo + $starThree + $starFour + $starFive)
               ];
             }else {
               $ratings[] = [
                 "id" => $value['id'],
                 "rank" => 0
               ];
             }
             }

             return $ratings;
     }





      /** handles the emloyee invitation request link on click from their email
      *@return void
      */
      public function acceptInvitationAction(){
        // session_start();

        $token = isset($this->data['token']) ? $this->data['token'] : '';

        if($token){

          $user = new Users();

          $invitation = $user->getInvitationByToken($token);

          if($invitation){
               //Remember to destroy this session variable later...
               $_SESSION['tokens_id'] = $invitation->tokens_id;

              if((strtotime($invitation->expires_at) - strtotime("now")) > 0){

                   View::twigRender('Home/employee_create.html', ['invitation' => $invitation]);

              }else{

                echo "Expired invitation";

              }


          }else{

            echo "Invalid invitation.";

          }

        }else {

          echo "Invalid token.";

        }

      }





        /**
        *Transfers the employee's details from the employee_create page to createemploye
        *model for registeration
        *@return void
        */
         public function createEmployeeAction(){
           // session_start();

           $user = new Users($_POST);
           //trying to get the admin_id from the invitation table...
           $token = isset($_SESSION['tokens_id']) ? $_SESSION['tokens_id'] : '';

           $invitation = $user->getInvitationByToken($token);

           if($invitation){
             if($user->createEmployee($invitation->admin_id)){

               if($user->deleteEmployeeInvitation($_SESSION['tokens_id'])){

                 unset($_SESSION['tokens_id']);

                 $this->redirect('home/employeesuccess');

               }else {

                 echo "Something went wrong. We are very sorry.";

               }

             }else{

               View::twigRender('Home/employee_create.html',
               ['errors' => $user->errors,
                'admin_id' => $user->admin_id,
                'user' => $user]);
             }
         }


     }







          /**
           * Helper method to render the employee success page on successfull registeration
           *@return void
           */
               public function employeeSuccessAction() :void{

                 View::twigRender('Home/employee_success.html');

               }








}//end home...
