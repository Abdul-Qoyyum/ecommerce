<?php
namespace App\Controllers;

use App\Controllers\Authenticated;

use App\Models\Reviews;

class Comments extends Authenticated{

 public function saveUserCommentsAction(){
   $reviews = new Reviews($_POST);

   $reviews->saveProductReviews($_SESSION['user_id']);

   $this->redirect("Home/$reviews->products_id/viewProductDetails");


 }


}
