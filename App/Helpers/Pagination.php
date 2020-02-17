<?php
namespace App\Helpers;

use App\Models\Products;

class Pagination{


  /**
  *Calculate the total number of pages for paginated products
  *@param int $page_no
  *@return array $paginatedPages
  */

  public function paginate(int $page_no) :array{

    $page_no = (int)$page_no;

    $this->no_of_products_per_page = 10;

    $this->start = ($page_no - 1) * $this->no_of_products_per_page;

    $totalNoOfProducts = $this->getProductsNumber();

    $totalPages = ceil($totalNoOfProducts / $this->no_of_products_per_page);

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
    public function getProducts() :array{
      $products = new Products();

      $paginatedProducts = $products->getProductsForPagination($this->start, $this->no_of_products_per_page);

      return $paginatedProducts;
    }



  /**
  * get the total no of product in stock
  *@return int
  */
  public function getProductsNumber() :int{
    return (int)count($this->getProducts());
  }



}
