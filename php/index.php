<?php
ini_set('display_errors', '1');
ini_set('display_startup_errors', '1');
error_reporting(E_ALL);

require_once __DIR__."/tp-auth/auth.php";
require_once __DIR__."/tp-auth/env.php";

TPAuth::$API_KEY = $TOPLUYO_API_KEY;

if(TPAuth::user() && TPAuth::user()['user_id']!=0){
  $user = TPAuth::user();
}else{
  $user = TPAuth::login([
    "app_id" => "110",
    "path" => function($user,$query){
      return "https://".$_SERVER['SERVER_NAME'] . "/!app/";
    },
    "redirect" => function($user,$query) {
      return "https://".$_SERVER['SERVER_NAME'] . "/!app/";
    }
  ]);
}


print_r($user);
