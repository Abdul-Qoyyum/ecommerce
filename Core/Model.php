<?php
namespace Core;

use PDO;

use App\Controllers\Admin\Config;

abstract class Model{

  public function connect(){

    static $db;
  try{
    // initialize config variable...
    $config = new Config();

    $dsn = "mysql:host=" . $config->DB_HOST . ";dbname=" . $config->DB_DATABASE . ";charset=utf8";

    if($db === null){
      //db connection
       $db = new PDO($dsn, $config->DB_USERNAME, $config->DB_PASSWORD);
       // set the PDO error mode to exception
       $db->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
       // echo "Connected successfully";
       //        var_dump($db->query("SHOW STATUS LIKE 'Ssl_cipher';")->fetchAll());

     }
   }catch(PDOException $e){

       echo("Connection failed: " . $e->getMessage());

   }

return $db;
  }




}
