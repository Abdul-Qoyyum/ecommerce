<?php
namespace App\Controllers;

use Core\Controller;

use Core\View;

use App\Models\Products;

/**
 *
 */

class Cart extends Controller{


	/**displays the cart to the user
	*@return void
	*/
	public function showAction() :void{
		if (empty($_SESSION['cart']) || !isset($_SESSION['cart'])) {

			  View::twigRender('Cart/index.html');

				}else{
							$id = "";
							if(!empty($_SESSION['cart'])){
										foreach ($_SESSION['cart'] as $key => $value) {
												$id .= "$key" . ',';
									 }

						 if($id){
									// formatting ids for sql
									$ids = "";

									$id = preg_replace('/,/','","',$id);

									$ids .= '"' . $id;

									$ids = substr($ids,0,-2);

									$ids =  "(" . $ids . ")";


				         $products  =  new Products();

				         $items = $products->getProductAction($ids);


			         foreach ($_SESSION['cart'] as $key => $value) {
			         	  foreach ($items as $ind => $val) {
			         	  	if ($key == $val->product_id) {

			         	  		$val->quantity = $value['quantity'];

	  		         	  	}
  			         	  }

			         	 }

			         	 View::twigRender('Cart/index.html',['products' => $items]);
     				 }
		  		 }
	     }
	}





				/**
				*Remove items from the cart
				*@return void
				*/
				public function removeCartItemsAction(){
					$id = isset($this->data['id']) ? $this->data['id'] : "";
					if($id){
           if (isset($_SESSION['cart'][$id])) {
           	   unset($_SESSION['cart'][$id]);
							 $this->redirect('cart/show');
           }
				 }
				}





	/**add item to the shopping cart and notify the user with the added message
	*if the item already exists increase the number else add a new one
	* @return void
	*/
		public function addProductAction(){
       // $id = isset($this->data['id']) ? $this->data['id'] : "";
			 $id = isset($_POST['id']) ? $_POST['id'] : "";
      if($id){

	       $product = new Products();

	       $item = $product->getProductForCart($id);

	       if (!isset($_SESSION['cart'][$id])) {

	       	$_SESSION['cart'][$item->id]['quantity'] = 1;

	       }else{

	         $_SESSION['cart'][$item->id]['quantity']++;

					 $response = ["success"=> true];
					 header("Content-Type:application/json; charset=UTF-8");
           echo(json_encode($response));
	       }

		 }

	}




}
