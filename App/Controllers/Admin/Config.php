<?php
namespace App\Controllers\Admin;

class Config{

    public function __construct(){

         $this->DB_HOST = 'localhost';

         $this->DB_DATABASE = 'ecommerce';

         $this->DB_USERNAME = 'root';

         $this->DB_PASSWORD = '';

        //paystack configuration
        $this->PAYSTACK_TEST_SECRET = getenv('PAYSTACK_TEST_SECRET');

        //for test inline purposes...
        $this->PAYSTACK_TEST_PUBLIC = getenv('PAYSTACK_TEST_PUBLIC');

        $this->CLOUDINARY_CONFIG = array(
            'cloud_name' => getenv("CLOUD_NAME"),
            'api_key' => getenv("API_KEY"),
            'api_secret' =>getenv("API_SECRET")
        );



        //mail variables
        $this->MAIL_USER = getenv("MAIL_USER");

        $this->MAIL_NAME = getenv("MAIL_NAME");

        $this->SMTP_DOMAIN = getenv("SMTP_DOMAIN");

        $this->SMTP_PORT = getenv("SMTP_PORT");

        $this->SMTP_USERNAME = getenv("SMTP_USERNAME");

        $this->SENDER_NAME = getenv("SENDER_NAME");

        $this->SMTP_PASSWORD = getenv("SMTP_PASSWORD");

        $this->USERNAME =  getenv("USERNAME");

        $this->SENDER =  getenv("SENDER");

        $this->SHOW_ERROR = false;

    }
}
