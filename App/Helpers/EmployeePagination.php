<?php
namespace App\Helpers;

use App\Models\Users;

class EmployeePagination{


/**
*Calculate the total number of pages for paginated employees
*@param int $page_no
*@return array $paginatedPages
*/

  public function paginate(int $page_no) :array{

    $page_no = (int)$page_no;

    $this->no_of_employees_per_page = 10;

    $this->start = ($page_no - 1) * $this->no_of_employees_per_page;

    $totalNoOfEmployees = $this->getEmployeesNumber();

    $totalPages = ceil($totalNoOfEmployees / $this->no_of_employees_per_page);

    $paginatedPages = [];

    for($i = 1; $i <= $totalPages; $i++){
     array_push($paginatedPages,$i);
    }

    return $paginatedPages;

  }



/**
*get products per page for the admin
*@return array
*/
  public function getPaginatedList() :array{

    $users = new Users();

    $paginatedList = $users->getEmployeesForPagination($this->start, $this->no_of_employees_per_page, $_SESSION['user_id']);

    return $paginatedList;
  }



  /**
  * get the total no of product in stock
  *@return int
  */
  public function getEmployeesNumber() :int{
    return (int)count($this->getPaginatedList());
  }



}
