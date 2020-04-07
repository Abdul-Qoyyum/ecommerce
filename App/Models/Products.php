<?php
namespace App\Models;

use Core\Model;

use PDO;

use App\Controllers\Admin\Config;

class Products extends Model{

    public $error = [];

    /**binds the products properties to the Product Model
    *@param array $props
    *@return void
    */
      public function __construct(array $props = []){
         foreach ($props as $key => $value) {
           $this->$key = $value;
         }
      }

  	/**get the list all products in the shop
  	*@return object
  	*/

     public function getProductsAction(){
       $db = $this->connect();

       $sql = "SELECT * FROM products";

       $stmt = $db->query($sql);

       $stmt->setFetchMode(PDO::FETCH_CLASS,get_called_class());

       $stmt->execute();

       return $stmt->fetchAll();

  }

  /**get products by ids from admin_products and products table in the shop
  *@param $id
  *@return object
  */
  public function getProductByPublicIds(string $id){
      $db = $this->connect();
      //
      // $sql = "SELECT products.products_id FROM admin_products INNER JOIN products ON products.products_id = admin_products.products_id
      //         WHERE products.id IN ($id)";

      $sql = "SELECT products_id FROM products WHERE public_id IN ($id)";

      $stmt = $db->prepare($sql);

      $stmt->setFetchMode(PDO::FETCH_CLASS,get_called_class());

      $stmt->execute();

      return $stmt->fetchAll();
  }





      /**get a product in the shop
      *@param $id
      *@return object
      */
      public function getProductForCart(int $id){
          $db = $this->connect();

          $sql =  "SELECT * FROM products WHERE id = :id";

          $stmt = $db->prepare($sql);

          $stmt->bindValue(":id", $id, PDO::PARAM_INT);

          $stmt->setFetchMode(PDO::FETCH_CLASS,get_called_class());

          $stmt->execute();

          return $stmt->fetch();
      }




    /**get a product in the shop
    *@param $id
    *@return object
    */
    public function getProductAction(string $id){
        $db = $this->connect();

        $sql = "SELECT *, products.id AS product_id FROM admin_products INNER JOIN products ON products.products_id = admin_products.products_id
                INNER JOIN users ON admin_products.admin_id = users.id
                WHERE products.id IN $id";

        $stmt = $db->query($sql);

        $stmt->setFetchMode(PDO::FETCH_CLASS,get_called_class());

        $stmt->execute();

        return $stmt->fetchAll();
    }



    /**get a product in the shop for checkout
    *@param $id
    *@return object
    */
    public function getProductForCheckout(int $id){
      $db = $this->connect();
      $sql = "SELECT * , products.id AS product_id FROM products INNER JOIN admin_products ON products.products_id = admin_products.products_id
              INNER JOIN users ON admin_products.admin_id = users.id
              WHERE products.id = :id";
      $stmt =  $db->prepare($sql);
      $stmt->bindValue(':id', $id, PDO::PARAM_INT);
      $stmt->setFetchMode(PDO::FETCH_CLASS,get_called_class());
      $stmt->execute();
      return $stmt->fetch();
    }


    /**get all categories of products
    *@return array
    */
    public function getProductsCategories() :array {
      $db = $this->connect();

      $sql = "SELECT * FROM categories";

      $stmt = $db->query($sql);

      $stmt->setFetchMode(PDO::FETCH_CLASS, get_called_class());

      $stmt->execute();

      return $stmt->fetchAll();

    }


    /**fetch all items in the user's cart
    *@param $id
    *@return array
    */

    public function getCartItemsAction($id){

        $db = $this->connect();

        $sql = "SELECT * FROM products INNER JOIN  categories ON products.category_id = categories.id WHERE products.id IN $id";
        // $sql = "SELECT * FROM products INNER JOIN admin_products ON products.products_id = admin_products.products_id WHERE products.id IN $id";

        $stmt = $db->prepare($sql);

        $stmt->setFetchMode(PDO::FETCH_CLASS, get_called_class());

        $stmt->execute();

        return $stmt->fetchAll();

    }

    /**
    * Gets all products for pagination for the Admin view
    * @param int $start, int $stop
    * @return array
    */
    public function getProductsForPagination(int $start, int $stop) : array {
       $db = $this->connect();

       $sql = "SELECT * FROM products INNER JOIN categories ON products.category_id = categories.id LIMIT :start, :stop";

//         $sql = "SELECT * FROM products INNER JOIN categories ON products.category_id = categories.id
//               INNER JOIN admin_products ON admin_products.products_id = products.products_id LIMIT :start, :stop";

       $stmt = $db->prepare($sql);

       $stmt->bindValue(':start', $start, PDO::PARAM_INT);

       $stmt->bindValue(':stop', $stop, PDO::PARAM_INT);

       $stmt->setFetchMode(PDO::FETCH_CLASS, get_called_class());

       $stmt->execute();

       return $stmt->fetchAll();
    }




    /**
    *Validate products details
    *@return void
    */
     public function validateProductdetails(array $file = []) :void {

       if ($_SERVER['REQUEST_METHOD'] == 'POST') {

           if($this->name == ''){
             $this->error['name'] = "Please enter the name of the product";
           }
           //remember to add validation for price int type
           if($this->price == '') {
             $this->error['price'] = "Please enter the price";
           }elseif(!preg_match('/^[0-9]+$/',$this->price)){
             $this->error['price'] = "Please specify your price in whole number.";
           }
           //Remember to add validation for sku int type
           if($this->sku == '') {
             $this->error['sku'] = 'Please enter the stock quantity';
           }elseif(!preg_match('/^[0-9]+$/',$this->sku)){
             $this->error['sku'] = 'Only whole number is allowed for stock quantity.';
           }

           if($this->category_id == '') {
             $this->error['category_id'] = 'Please select the category';
           }
           if (empty($file['thumbnail']['tmp_name'])) {
             $this->error['thumbnail'] = "Please upload products' image";
           }elseif($file["thumbnail"]["size"] > 1000000){
             $this->error['thumbnail'] = "Image size must be less than 1MB";
           }

           if(empty($this->description)) {
             $this->error['description'] = "Please enter products' description";
           }

       }

     }





/**
*add the products from registerProductAction to the database
*@param array $file
*@param int $adminId
*@return void
*/
 public function registerProduct(array $file, int $adminId){

  $this->validateProductdetails($file);

  if ($file && empty($this->error)) {
    // initialize settings var
    $config = new Config();
    // include the settings file...
     \Cloudinary::config($config->CLOUDINARY_CONFIG);

     //get the temporary location for the file
     $this->thumbnail = $file["thumbnail"]["tmp_name"];

     $thumbnail = \Cloudinary\Uploader::upload($this->thumbnail,["folder" => "/Openmall/"]);

     if($thumbnail){
       $image_link = $thumbnail["secure_url"];
       //grab this for deletion purposes..
       $public_id = $thumbnail["public_id"];

       $db = $this->connect();

       // generates products id here.... and call the function...
       $productId = time();

       if($this->addAdminAndProductId($productId, $adminId)){

       $sql = "INSERT INTO products (name, price, public_id, sku, category_id, thumbnail, products_id, description) VALUES (:name,
               :price,:public_id, :sku, :category_id, :thumbnail, :products_id,:description)";

        $stmt = $db->prepare($sql);

        $stmt->bindValue(':name', $this->name, PDO::PARAM_STR);

        $stmt->bindValue(':price', $this->price, PDO::PARAM_INT);

        $stmt->bindValue(':public_id', $public_id, PDO::PARAM_STR);

        $stmt->bindValue(':sku', $this->sku, PDO::PARAM_INT);

        $stmt->bindValue(':category_id', $this->category_id, PDO::PARAM_INT);

        $stmt->bindValue(':thumbnail', $image_link, PDO::PARAM_STR);

        $stmt->bindValue(':products_id', $productId, PDO::PARAM_STR);

        $stmt->bindValue(':description', $this->description, PDO::PARAM_STR);

        return $stmt->execute();

       }

     }else{

          throw new \Exception("Unable to receive cloudinary image upload property.");
     }

  }

        return false;


}

    /**
    *Reduce stock after successfull purchase of item in the store
    *from checkout page
    */
    public function reduceStockQuantity(int $id,int $skuLeft){
     $db = $this->connect();
       if($db){
         $sql = "UPDATE products SET sku = :sku WHERE id = :id";
         $stmt = $db->prepare($sql);
         $stmt->bindValue(':id',$id,PDO::PARAM_INT);
         $stmt->bindValue(':sku',$skuLeft,PDO::PARAM_INT);
         return $stmt->execute();
       }
       return false;
    }


    /**
    *Add admin and porduct id to the admin_products join table
    *@param string $productId
    *@return bool
    */
    public function addAdminAndProductId(string $productId, int $adminId) :bool{

      $db = $this->connect();

      $sql = "INSERT INTO admin_products(products_id, admin_id) VALUES( :products_id, :admin_id)";

      $stmt = $db->prepare($sql);

      $stmt->bindValue(':products_id', $productId, PDO::PARAM_INT);

      $stmt->bindValue(':admin_id', $adminId, PDO::PARAM_INT);

      return $stmt->execute() !== false;

    }


     /**
     *Delete products form the products table, the cloudinary cloud and the admin_products table
     *@param array $products
     *@return bool
     */
      public function removeProducts(array $products)  {

         if($_SERVER['REQUEST_METHOD'] == 'POST'){

           // database and cloudinary id are handled by public_id
            $id = "";
            if(!empty($products['product'])){
                  foreach ($products['product'] as $key => $value) {
                      $id .= "$value" . ',';
                 }

                 if($id){
                      // formatting ids for sql
                      $ids = "";

                      $id = preg_replace('/,/','","',$id);

                      $ids .= '"' . $id;

                      $ids = substr($ids,0,-2);

                      $productIds = $this->getProductByPublicIds($ids);

                      $admin_productIds = "";

                      foreach ($productIds as $key => $value) {
                          $admin_productIds .= "," . $value->products_id;
                      }

                      $admin_productIds = substr($admin_productIds,1);

                    if ($this->deleteProductsFromAdminProducts($admin_productIds)) {
                        // include the settings file...
                        $config = new Config();
                         \Cloudinary::config($config->CLOUDINARY_CONFIG);
                         // handle cloudinary destroy business here
                         $api = new \Cloudinary\Api();

                         if($api->delete_resources($products['product'],["invalidate"=>true])){
                           //connect...
                           $db = $this->connect();

                           $sql = "DELETE FROM products WHERE public_id IN ($ids)";

                           $stmt = $db->prepare($sql);

                           return $stmt->execute();
                         }
                         return false;
                    }

                  }else{

                  return false;

                }//end if id...

              }else {
                 // if nothing is selected....
                 return true;
              }
           }
                 return false;
        }









      /**
      *delete products from admin_products table
      *@param string $productIds
      *@return bool
      */
      public function deleteProductsFromAdminProducts(string $productIds) :bool{
        $db = $this->connect();

        if($db){

          $sql = "DELETE FROM admin_products WHERE products_id IN ($productIds)";

          $stmt = $db->prepare($sql);

          return $stmt->execute();

        }

        return false;

      }





      /**
      *Get the ids of products from stock
      *@return array
      */
      public function getProductIds() :array{
        $db = $this->connect();

        if($db){

          $sql = "SELECT id FROM products";

          $stmt = $db->prepare($sql);

          $stmt->execute();

          return $stmt->fetchAll();

        }

      }


      /**
      *Get the total number of products from stock
      *@return int
      */
      public function getTotalProducts() :int{
        $db = $this->connect();

        if($db){

          $sql = "SELECT COUNT(*) FROM products";

          $stmt = $db->prepare($sql);

          $stmt->execute();

          return $stmt->rowCount();

        }
          return 0;
      }


}//end products
