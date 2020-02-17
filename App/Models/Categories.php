<?php
namespace App\Models;

use Core\Model;

use PDO;

class Categories extends Model{

      public $errors = [];

      /**binds the categories properties to the categories Model
      *@param array $props
      *@return void
      */
        public function __construct(array $props = []){
           foreach ($props as $key => $value) {
             $this->$key = $value;
           }
        }


   /**
   *Validates the input by the user to prevent incorrect detatil
   *@return void
   */
    public function validateCategoryInput(){
      if($this->product_category == ''){
         $this->errors['product_category'] = "Please enter category name";
      }
    }


    /**
    *get category details from the database
    *@return mixed
    */
    public function getProductsCategory(){
      $db = $this->connect();
      $sql = "SELECT * FROM categories WHERE product_category = :product_category";
      $product_category = strtolower($this->product_category);
      $stmt = $db->prepare($sql);
      $stmt->bindvalue(':product_category', $product_category, PDO::PARAM_STR);
      $stmt->setFetchMode(PDO::FETCH_CLASS, get_called_class());
      $stmt->execute();
      return $stmt->fetch();
    }




  /**
  *Save category details to the database in validation is successfull
  *@return bool
  */
  public function saveCategoryDetails() :bool {
    if($_SERVER['REQUEST_METHOD'] == 'POST'){
       $this->validateCategoryInput();
    if (empty($this->errors)) {
      if(!$this->getProductsCategory()){
        $db = $this->connect();
        $product_category = strtolower($this->product_category);
        $sql = "INSERT INTO categories(product_category) VALUES (:product_category)";
        $stmt = $db->prepare($sql);
        $stmt->bindValue(':product_category',$product_category, PDO::PARAM_STR);
        return $stmt->execute();
        }else{
        $this->errors['product_category'] = "Category name already exist.";
        return false;
      }
    }
  }
    return false;
  }


}//end categories
