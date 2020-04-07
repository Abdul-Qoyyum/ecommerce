<?php
namespace App\Controllers\Admin;

use Core\Controller;

use Core\View;

use App\Models\Products;

use App\Models\Users;

use App\Helpers\Pagination;

use App\Helpers\Token;

use App\Helpers\Mail;

use App\Helpers\Auth;

use App\Models\Categories;

use App\Models\Transactions;

use App\Helpers\EmployeePagination;

use App\Helpers\TransactionsPagination;

use App\Controllers\Authenticated;


   class Home extends Authenticated{

     public function before() :bool{

       $users = new Users;

       $user = $users->getUserByIdAction(Auth::getUserSessionIdAction());

       if(!isset($user->is_admin)){
          return false;
       }

       return parent::before();
     }


          /**
          *renders the Admin index page
          *@return void
          */
            public function indexAction(){
              $users = new Users();

              $users = $users->getUserByIdAction($_SESSION['user_id']);

              $transactions = new Transactions();

              $totalSales = $transactions->totalSales($_SESSION['user_id']);

              $totalNewOrders  = $transactions->totalNewStoreOrders($_SESSION['user_id']);

              $totalFailedTransactions = $transactions->totalFailedTransactions($_SESSION['user_id']);

              $totals = $totalSales["total"] == 0 ? 1 : $totalSales["total"];

              $bounceRate = ($totalFailedTransactions["total"] /  $totals) * 100;

              $bounceRate = substr($bounceRate,0,2);

              View::twigRender('Admin/index.html',['users'=>$users,
              'totalSales' => $totalSales,
              'totalNewOrders' => $totalNewOrders,
              'bounceRate' => $bounceRate
            ]);

            }


          /**
          *renders the view products page with details of products in stock
          *@return void
          */
            public function viewProductsAction(){

              $this->viewPaginatedProductsAction('Admin/view_products.html');

           }


           /**
           *Renders the add category page
           *@return void
           */
           public function addCategoryIndexAction(){
             View::twigRender('Admin/add_product_category.html');
           }


           /**
           *add product category to the database
           *redirect to the added successful page on success
           *else re-render the add category page with error message
           *@return void
           */
           public function addCategoryAction(){
             $category = new Categories($_POST);
             if($category->saveCategoryDetails()){
               $this->redirect('Admin/home/categorySuccessfullAction');
             }else{
               View::twigRender('Admin/add_product_category.html',['errors'=>$category->errors]);
             }
           }



           /**
           *Renders the added category successfull page
           *@return void
           */
           public function categorySuccessfullAction():void{
             View::twigRender('Admin/add_product_category_successfull.html');
           }

           /**
           *renders the page for the Admin to add products to the store
           *@return void
           */
          public function addProductAction() :void{
            $products = new Products();

            $categories = $products->getProductsCategories();

            View::twigRender('Admin/add_product.html',['categories'=>$categories]);
          }


       /** Transfer products information to the products model
        *redirect to the successful page on success
        *else redisplay the page with the values
        * @return void
        * @throws \Exception
        */
          public function registerProductAction(){

            $product = new Products($_POST);

            if($product->registerProduct($_FILES, $_SESSION['user_id'])){

              $this->redirect('Admin/home/viewRegisteredProductsSuccessfullAction');

            }else{

              View::twigRender('Admin/add_product.html',
              ['errors' => $product->error,
              'product' => $product,
              'categories'=>$product->getProductsCategories()]
              );

            }

          }



          /**Displays the registered product succesfull page
          *return void
          */
          public function viewRegisteredProductsSuccessfullAction() :void{
               View::twigRender('Admin/registered_successfull.html');
          }


          /** Helper method for viewing products
          *@return void
          */
          public function viewRemoveProductsAction() :void{

             $this->viewPaginatedProductsAction('Admin/remove_products.html');

          }


          /** Prepares and paginate the page for view by other methods
          *@param string $template
          *@return void
          */
          public function viewPaginatedProductsAction(string $template) :void{

            $pagination = new Pagination();

            $start = isset($this->data['id']) ? $this->data['id'] : 1;

            $paginatedPages = $pagination->paginate($start);

            $products_per_page =$pagination->getProducts();

            $totalProducts = $pagination->getProductsNumber();

            View::twigRender($template,
            ['paginatedPages' => $paginatedPages,
             'products_per_page' => $products_per_page,
             'totalProducts' => $totalProducts
             ]);

          }


       /** Sends products details to the Products model for deletion
        * @return void
        * @throws \Exception
        */
          public function removeProductsAction() :void {
            $product =  new Products();

            if($product->removeProducts($_POST)){

              $this->redirect('Admin/home/viewremoveproducts');

                // $this->viewPaginatedProductsAction('Admin/remove_products.html');

            }else{

               throw new \Exception("Unable to remove product.");
            }

          }




          /** Renders the add affliate page for the admin
          *@return void
          */
          public function viewAddEmployeeAction(){

             View::twigRender('Admin/add_employee.html');

          }




          /**
          *Sends invitation link to the Employee
          *and displays the invitation successfull page on success
          *else displays invitation error page
          *@return void
          */
          public function addEmployeeAction(){
           // session_start();
            if ($_SERVER['REQUEST_METHOD'] === 'POST') {

                $user = new Users($_POST);

                if($user->validateEmployeeEmail()){

                  $token = new Token();

                  $hash = $token->getHash();

                  $url = "https://" . $_SERVER['HTTP_HOST'] . '/home/acceptinvitation/' . $hash;

                  $template = View::twigReturn('Admin/invitation_template.html',['url'=>$url]);

                  $text = View::twigReturn('Admin/invitation_template.txt',['url'=>$url]);

                  if(Mail::sendMail("Invitation letter",[$user->email],$template,$text)){

                    if($user->saveInvitationDetails($_SESSION['user_id'], $hash)){

                      $this->redirect('Admin/Home/viewinvitationsuccessfull');

                    }else{

                      echo "Something went wrong, Please try again.";

                    }

                  }else{

                    echo "Sending failed";

                  }


                }else{

                  View::twigRender('Admin/add_employee.html',['errors'=> $user->errors]);

                }


            }

          }








          /**
          *Displays the invitation sent successfull page
          *@return void
          */
          public function viewInvitationSuccessfullAction(){
            View::twigRender('Admin/sent_invitation_successfull.html');
          }





          /** Prepares and paginate the employee page for view by other methods
          *@param string $template
          *@return void
          */
            public function viewPaginatedEmployeeAction(string $template) :void{

                $pagination = new EmployeePagination();

                $start = isset($this->data['id']) ? $this->data['id'] : 1;

                $paginatedPages = $pagination->paginate($start);

                $Employees_per_page =$pagination->getPaginatedList();

                $totalEmployees = $pagination->getEmployeesNumber();

                View::twigRender($template,
                  ['paginatedPages' => $paginatedPages,
                   'employees_per_page' => $Employees_per_page,
                   'totalEmployees' => $totalEmployees
                       ]);

            }








          /**
          *Returns the list of employees for the view employees link on admin control panel
          *@return void
          */
          public function viewEmployeesAction(){

            // $user = new Users();

            $this->viewPaginatedEmployeeAction('Admin/view_employees.html');

          }




          /**
          *view the admin pasword reset page
          *@return void
          */
          public function passwordResetAction(){

           $users = new Users();

           $user = $users->getUserByIdAction(Auth::getUserSessionIdAction());

            View::twigRender('Admin/change_password.html',["id" => $user->id]);
          }




          /**
          *Resets the user password and redirect to
          *the reset successfull page on success
          *re-renders the page with error message on failure
          *@return void
          */
          public function resetAdminPasswordAction(){
             $users = new Users($_POST);

             if ($users->resetPassword()) {
               $this->redirect('Admin/home/passwordResetSuccessfullAction');
             }else{
               View::twigRender('Admin/change_password.html',["id" => $users->id,'errors'=>$users->errors]);
             }

          }



          /**
          *view the password reset successfull page
          *@return void
          */
          public function passwordResetSuccessfullAction(){
            View::twigRender('Admin/password_reset_successfull.html');
          }


          /**
          *Renders the transaction view for the admin
          */
          public function viewTransactionsAction() :void{

           $this->viewPaginatedTransactionAction();

          }



          /**
          *Helper method to paginate and render transaction view for the admin
          *@return void
          */
          public function viewPaginatedTransactionAction(string $table = null):void{
            $pagination = new TransactionsPagination();

            $start = isset($this->data['id']) ? $this->data['id'] : 1;

            $paginatedPages = $pagination->paginate($start);

            $transactions_per_page =$pagination->getPaginatedList($table);

            $totalTransactions = $pagination->getTransactionsNumber($table);

            View::twigRender("Admin/view_transactions.html",
              ['paginatedPages' => $paginatedPages,
               'transactions_per_page' => $transactions_per_page,
               'totalTransactions' => $totalTransactions
                   ]);

          }

          /**
          *Renders the pending transaction view for the admin
          *@return void
          */
          public function viewPendingTransactionsAction() :void{
             $this->viewPaginatedTransactionAction('pending');
          }

          /**
          *Renders the confirmed transaction view for the admin
          *@return void
          */
          public function viewConfirmedTransactionsAction():void{
            $this->viewPaginatedTransactionAction('confirmed');
          }




       /**
       *Confirm transaction delivery by the admin
       */
        public function confirmDeliveryAction(){
        $reference = isset($this->data['ref']) ? $this->data['ref'] : '';

        $users = new Users();

        $user = $users->getUserByIdAction($_SESSION['user_id']);

        $transaction = new Transactions();

          if($transaction->confirmDelivery($reference, $user->username)){
             $this->viewTransactionsAction();
          }

        }


        /**
        *Render the removeEmployee page
        *@return void
        */
        public function removeEmployeeIndexAction(){
           $this->viewPaginatedEmployeeAction('Admin/view_remove_employees.html');
        }


       /**
        *Delete the employee record
        * @return void
        * @throws \Exception
        */
        public function removeEmployeeAction(){
          $user = new Users($_POST);
          if($_SERVER['REQUEST_METHOD'] == 'POST'){
            if($user->removeEmployee()){
               $this->redirect('Admin/home/removeemployeeindex');
            }else{
              throw new \Exception("Unable to remove employee.");
            }
         }
        }


        //for test purposes only...
          public function ViewTemplateAction(){
            View::twigRender('users/payment_successfull.html');
          }




}
