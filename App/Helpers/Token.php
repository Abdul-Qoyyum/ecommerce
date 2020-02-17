<?php
namespace App\Helpers;

class Token{

  public function __construct(string $token = ''){
     if ($token) {
         $this->token = $token;
     }else {
         $this->token = random_bytes(16);
     }
    }

    /**
    *get the instance of the generated token
    *@return string
    */
    public function getToken() :string{
      return $this->token;
    }

    /**
    *get the Hash of the token instance
    *@return string
    */
    public function getHash() :string{
      return bin2hex($this->token);
    }

}
