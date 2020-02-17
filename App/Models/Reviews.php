<?php
namespace App\Models;

use Core\Model;

use PDO;

class Reviews extends Model{

  public $errors = [];

  public function __construct(array $data = []){
    foreach ($data as $key => $value) {
       $this->$key = $value;
    }
  }

  /**
  *Gives number of rating depending on the star number
  *@return int
  */
  public function star(int $productId, int $rating) :int{

     $db = $this->connect();

     $sql = "SELECT * FROM reviews WHERE rating = :rating AND products_id = :id";

     $stmt = $db->prepare($sql);

     $stmt->bindValue(':id', $productId, PDO::PARAM_INT);

     $stmt->bindValue(':rating', $rating, PDO::PARAM_INT);

     $stmt->execute();

     return $stmt->rowCount();

  }

  /**
  *fetch reviews of product
  *@param int $productId
  *@return mixed
  */
  public function getProductReviewDetails(int $productId){

     $db = $this->connect();

     $sql = "SELECT * FROM reviews INNER JOIN users ON reviews.user_id = users.id WHERE products_id = :id ORDER BY reviews.id DESC LIMIT 10";

     $stmt = $db->prepare($sql);

     $stmt->bindValue(':id', $productId, PDO::PARAM_INT);

     $stmt->setFetchMode(PDO::FETCH_CLASS, get_called_class());

     $stmt->execute();

     return $stmt->fetchAll();

  }


  public function validateReviews(){
    if ($this->rating == '') {
        $this->errors['rating'] = "Please specify rating";
    }
    if ($this->products_id == '') {
        $this->errors['products_id'] = "Product rating is reqiured";
    }
    if(empty($this->description)) {
      $this->errors['description'] = "Please enter your opinion";
    }
  }


  /**
  *Saves review details into the reviews table
  *@param int $userId
  *@return mixed
  */
  public function saveProductReviews(int $userId){

    if($_SERVER['REQUEST_METHOD'] == 'POST'){

      //VALIDATE PRODUCTS DETAILS
       $this->validateReviews();

      if (empty($this->errors)) {
        $db = $this->connect();

        $sql = "INSERT INTO reviews(user_id, products_id, rating, description) VALUES (:user_id, :products_id, :rating, :description)";

        $stmt = $db->prepare($sql);

        $stmt->bindValue(':user_id', $userId,PDO::PARAM_INT);

        $stmt->bindValue(':products_id', $this->products_id, PDO::PARAM_INT);

        $stmt->bindValue(':rating', $this->rating, PDO::PARAM_INT);

        $stmt->bindValue(':description', $this->description, PDO::PARAM_STR);

        return $stmt->execute();
      }

        return false;

    }

    return false;

  }


  /**
  *checks if the user already have reviews
  *@param int $userId
  *@return mixed
  */
   public function getUserReview(int $userId, int $productId){
     $db = $this->connect();
     if($db){
     $sql = "SELECT * FROM reviews WHERE user_id = :user_id AND products_id = :products_id";
     $stmt = $db->prepare($sql);
     $stmt->bindValue(':user_id', $userId, PDO::PARAM_INT);
     $stmt->bindValue(':products_id', $productId, PDO::PARAM_INT);
     $stmt->setFetchMode(PDO::FETCH_CLASS, get_called_class());
     $stmt->execute();
     return $stmt->fetch();
    }

     return false;
   }
}
