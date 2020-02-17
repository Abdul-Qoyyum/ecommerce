<?php
namespace App\Helpers;

use App\Models\Transactions;

class TransactionsPagination{

/**
*Calculate the total number of pages for paginated employees
*@param int $page_no
*@return array $paginatedPages
*/

  public function paginate(int $page_no) :array{

    $page_no = (int)$page_no;

    $this->no_of_transactions_per_page = 10;

    $this->start = ($page_no - 1) * $this->no_of_transactions_per_page;

    $totalNumberOfTransactions = $this->getTransactionsNumber();

    $totalPages = ceil($totalNumberOfTransactions / $this->no_of_transactions_per_page);

    $paginatedPages = [];

    for($i = 1; $i <= $totalPages; $i++){
     array_push($paginatedPages,$i);
    }

    return $paginatedPages;

  }



  /**
  *get products per page for the admin or employee
  *@return array
  */
  public function getPaginatedList(string $table = null, int $adminId = null) :array{

    $transactions = new Transactions();
    //switch adminId so that employees can view transactions
    $adminId = isset($adminId) ? $adminId : $_SESSION['user_id'];
    switch ($table) {
      case 'pending':
      $paginatedList = $transactions->getPendingTransactions($this->start, $this->no_of_transactions_per_page, $adminId);
        break;
      case 'confirmed';
      $paginatedList = $transactions->getConfirmedTransactions($this->start, $this->no_of_transactions_per_page, $adminId);
        break;
      default:
      $paginatedLists = $transactions->getCurrentTransactions($this->start, $this->no_of_transactions_per_page, $adminId);
      echo "<pre>";
      var_dump($paginatedLists);
      echo "</pre>";
        break;
    }

    return $paginatedList;
  }


  /**
  *Fetch transaction paginated list for users
  *@return mixed
  */
  public function getUserPaginatedList(){
      $transactions = new Transactions();
      return $transactions->getCurrentUserTransactions($this->start, $this->no_of_transactions_per_page, $_SESSION['user_id']);
  }


  /**
  * get the total no of product in stock for either admin or employee
  *depends on weather admin_id parameter has a value or not
  *@return int
  */
  public function getTransactionsNumber(string $table = null, int $adminId = null) :int{
    //table property is not set for users.
    if(!isset($table)){
      return (int)count($this->getUserPaginatedList());
    }
     return isset($adminId) ? (int)count($this->getPaginatedList($table, $adminId)) : (int)count($this->getPaginatedList($table));
  }


}
