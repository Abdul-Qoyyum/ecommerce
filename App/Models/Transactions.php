<?php
namespace App\Models;

use Core\Model;


use PDO;

class Transactions extends Model{

/**
*save the transaction details in the database
*@param $user_id, $product_id, $transaction_ref, $amount, $quantity
*@return bool
*/
  public function saveUserTransactionsAction($user_id, $product_id, $transaction_ref, $amount, $quantity, $admin_id) :bool{
   $db = $this->connect();

   $sql = "INSERT INTO transactions(user_id, product_id, transaction_ref, amount, quantity, admin_id) VALUES (:user_id, :product_id, :transaction_ref, :amount, :quantity, :admin_id)";

   $stmt = $db->prepare($sql);

   $stmt->bindValue(':user_id', $user_id, PDO::PARAM_INT);

   $stmt->bindValue(':product_id', $product_id, PDO::PARAM_INT);

   $stmt->bindValue(':transaction_ref', $transaction_ref, PDO::PARAM_STR);

   $stmt->bindValue(':amount', $amount, PDO::PARAM_INT);

   $stmt->bindValue(':quantity', $quantity, PDO::PARAM_INT);

   $stmt->bindValue(':admin_id', $admin_id, PDO::PARAM_INT);

   return $stmt->execute();

  }


/**
*Returns all the transactions from the db
*@return array
*/
  public function getTransactionsAction(){
    $db = $this->connect();

    $sql = "SELECT * FROM transactions";

    $stmt = $db->query($sql);

    $stmt->setFetchMode(PDO::FETCH_CLASS, get_called_class());

    $stmt->execute();

    return $stmt->fetchAll();
  }


  /**
  *Confirm the user payment status from Paystack
  *@param string $transaction_ref
  *@return bool
  */
  public function confirmPaymentStatusAction(string $transaction_ref) :bool{
     $db = $this->connect();

     $sql = "UPDATE transactions SET payment_status = 1 WHERE transaction_ref = :transaction_ref";

     $stmt = $db->prepare($sql);

     $stmt->bindValue(':transaction_ref', $transaction_ref, PDO::PARAM_STR);

     return $stmt->execute();

  }




  /**
  *Returns all the transactions the curent admin from the db
  *@param $adminId
  *@return array
  */
    public function getCurrentTransactions(int $start, int $stop, int $adminId){
      $db = $this->connect();

      $sql = "SELECT quantity, transaction_ref, amount, payment_status, products.name AS products, users.username as username,
             delivered_by, is_delivered FROM transactions INNER JOIN products ON transactions.product_id = products.id
             INNER JOIN users ON transactions.user_id = users.id WHERE admin_id = :admin_id LIMIT :start, :stop";

      $stmt = $db->prepare($sql);

      $stmt->bindValue(':admin_id',$adminId, PDO::PARAM_INT);

      $stmt->bindValue(':start',$start, PDO::PARAM_INT);

      $stmt->bindValue(':stop',$stop, PDO::PARAM_INT);

      $stmt->setFetchMode(PDO::FETCH_CLASS, get_called_class());

      $stmt->execute();

      return $stmt->fetchAll();
    }


    /**
    *Returns all the pending transactions for the curent admin from the db
    *@param $adminId
    *@return array
    */
      public function getPendingTransactions(int $start, int $stop, int $adminId){
        $db = $this->connect();

        $sql = "SELECT quantity, transaction_ref, amount, payment_status, products.name AS products, users.username as username,
               delivered_by, is_delivered FROM transactions INNER JOIN products ON transactions.product_id = products.id
               INNER JOIN users ON transactions.user_id = users.id WHERE admin_id = :admin_id  AND payment_status = 0 LIMIT :start, :stop";

        $stmt = $db->prepare($sql);

        $stmt->bindValue(':admin_id',$adminId, PDO::PARAM_INT);

        $stmt->bindValue(':start',$start, PDO::PARAM_INT);

        $stmt->bindValue(':stop',$stop, PDO::PARAM_INT);

        $stmt->setFetchMode(PDO::FETCH_CLASS, get_called_class());

        $stmt->execute();

        return $stmt->fetchAll();
      }




      /**
      *Returns all the pending transactions for the curent admin from the db
      *@param $adminId
      *@return array
      */
        public function getConfirmedTransactions(int $start, int $stop, int $adminId){
          $db = $this->connect();

          $sql = "SELECT quantity, transaction_ref, amount, payment_status, products.name AS products, users.username as username,
                 delivered_by, is_delivered FROM transactions INNER JOIN products ON transactions.product_id = products.id
                 INNER JOIN users ON transactions.user_id = users.id WHERE admin_id = :admin_id  AND payment_status = 1 LIMIT :start, :stop";

          $stmt = $db->prepare($sql);

          $stmt->bindValue(':admin_id',$adminId, PDO::PARAM_INT);

          $stmt->bindValue(':start',$start, PDO::PARAM_INT);

          $stmt->bindValue(':stop',$stop, PDO::PARAM_INT);

          $stmt->setFetchMode(PDO::FETCH_CLASS, get_called_class());

          $stmt->execute();

          return $stmt->fetchAll();
        }



        /**
        *Confirm delivery by updating the delivered_by column in the transactions table
        *with the employee or the admin name
        */
        public function confirmDelivery(string $transaction_ref, string $staff_name){
        $db = $this->connect();

        $sql = "UPDATE transactions SET delivered_by = :delivered_by WHERE transaction_ref = :transaction_ref";

        $stmt = $db->prepare($sql);

        $stmt->bindValue(':delivered_by', $staff_name, PDO::PARAM_STR);

        $stmt->bindValue(':transaction_ref', $transaction_ref, PDO::PARAM_STR);

        return $stmt->execute();

        }

        /**
        *Confirm delivery by the user by updating the is_delivered column in the transactions table
        *with the value 1 (true)
        */
        public function confirmUserDelivery(string $transaction_ref){
        $db = $this->connect();

        $sql = "UPDATE transactions SET is_delivered = 1 WHERE transaction_ref = :transaction_ref";

        $stmt = $db->prepare($sql);

        $stmt->bindValue(':transaction_ref', $transaction_ref, PDO::PARAM_STR);

        return $stmt->execute();

        }



        /**
        *Returns all the transactions the curent user from the db
        *@param $userId
        *@return array
        */
          public function getCurrentUserTransactions(int $start, int $stop, int $userId){
            $db = $this->connect();

            $sql = "SELECT quantity, transaction_ref, amount, payment_status, products.name AS products, users.username as username,
                   delivered_by, is_delivered FROM transactions INNER JOIN products ON transactions.product_id = products.id
                   INNER JOIN users ON transactions.user_id = users.id WHERE users.id = :user_id LIMIT :start, :stop";

            $stmt = $db->prepare($sql);

            $stmt->bindValue(':user_id',$userId, PDO::PARAM_INT);

            $stmt->bindValue(':start',$start, PDO::PARAM_INT);

            $stmt->bindValue(':stop',$stop, PDO::PARAM_INT);

            $stmt->setFetchMode(PDO::FETCH_CLASS, get_called_class());

            $stmt->execute();

            return $stmt->fetchAll();
          }


          /**
          *fetch sales for the admin
          *@return mixed
          */
          public function totalSales(int $admin_id){
            $db = $this->connect();
            $sql = "SELECT COUNT(*) total FROM transactions WHERE admin_id";
            $stmt = $db->prepare($sql);
            $stmt->bindValue(':admin_id',$admin_id,PDO::PARAM_INT);
            $stmt->execute();
            return $stmt->fetch();
          }


    /**
    *fetch new orders for the admin
    *@return mixed
    */
    public function totalNewStoreOrders(int $admin_id){
      $db = $this->connect();
      $sql = "SELECT COUNT(*) total FROM transactions WHERE admin_id = :admin_id AND is_delivered = 0";
      $stmt = $db->prepare($sql);
      $stmt->bindValue(':admin_id',$admin_id,PDO::PARAM_INT);
      $stmt->execute();
      return $stmt->fetch();
    }

    /**
    *fetch failed transactions for the admin
    *@return mixed
    */
    public function totalFailedTransactions(int $admin_id){
      $db = $this->connect();
      $sql = "SELECT COUNT(*) total FROM transactions WHERE admin_id = :admin_id AND payment_status = 0";
      $stmt = $db->prepare($sql);
      $stmt->bindValue(':admin_id',$admin_id,PDO::PARAM_INT);
      $stmt->execute();
      return $stmt->fetch();
    }



}
