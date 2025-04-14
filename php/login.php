<?php 

require_once __DIR__."/tp-auth/auth.php";

TPAuth::login([
  "app_id" => "76",
  "redirect" => function($user) {
    return "https://market.topluyo.com/apps/auth/".$user["channel_id"];
  }
]);