<?php

// Get API Key from market.topluyo.com ---------
// Change below code 
$API_KEY  = "5555a7b2bfe142d8f5b9b27ef56eabc3";            // get this key from market.topluyo.com/?app=XXXX > API
$REDIRECT = "https://market.topluyo.com/apps/auth-test/";  // Redirect here your index.php file

// CORS Access ---------

ini_set('session.cookie_samesite', 'None');
session_set_cookie_params(['samesite' => 'None']);
session_start();
header('Access-Control-Allow-Credentials: true');
header('Access-Control-Allow-Methods: GET,PUT,POST,DELETE,OPTIONS');
header('Access-Control-Allow-Headers: Content-Type, Authorization');
header('Access-Control-Allow-Origin: ' . (isset($_SERVER['HTTP_ORIGIN']) ? $_SERVER['HTTP_ORIGIN'] : '*'));
header('X-Frame-Options: ALLOWALL');
header('Content-Security-Policy: frame-ancestors *');


// LIBRARY --------------------------
function decodeChecksum(string $message) {
  if (strlen($message) < 2) {
    return false;
  }
  $checksumChar = $message[0];
  $actualMessage = substr($message, 1);
  $sum = 0;
  for ($i = 0; $i < strlen($actualMessage); $i++) {
    $sum += ord($actualMessage[$i]);
  }
  $expectedChecksum = chr($sum % 255);
  if ($checksumChar === $expectedChecksum) {
    return $actualMessage;
  } else {
    return false;
  }
}

function decrypt($encryptedData, $password) {
  $method = 'aes-256-cbc';
  $password = substr(hash('sha256', $password, true), 0, 32);
  $iv = chr(0x0) . chr(0x0) . chr(0x0) . chr(0x0) . chr(0x0) . chr(0x0) . chr(0x0) . chr(0x0) . chr(0x0) . chr(0x0) . chr(0x0) . chr(0x0) . chr(0x0) . chr(0x0) . chr(0x0) . chr(0x0);
  $decrypted = openssl_decrypt(base64_decode($encryptedData), $method, $password, OPENSSL_RAW_DATA, $iv);
  $decrypted = decodeChecksum($decrypted);
  return $decrypted;
}

// Auth --------------------------------------
if(isset($_POST['>auth'])){
  $auth_code = $_POST['>auth'];
  $response = decrypt($auth_code,$API_KEY);
  if($response!=false){
    $response = json_decode($response,true);

    //@ HERE GET AUTH DATAS
    /*
    $user_id       = $response["user_id"];
    $channel_id    = $response["channel_id "];
    $group_id      = $response["group_id"];
    $team_id       = $response["team_id"];
    $user_nick     = $response["user_nick"];
    $user_name     = $response["user_name"];
    $user_image    = $response["user_image"];
    $group_name    = $response["group_name"];
    $group_nick    = $response["group_nick"];
    $group_image   = $response["group_image"];
    $channel_name  = $response["channel_name"];
    $power         = $response["power"];
    */
    $_SESSION['auth'] = $response;

    //@ Echo here redirect url
    if(isset($_POST['redirect']) && $_POST['redirect']==1){
      // Open in new window
      echo $REDIRECT;
    }else{
      // In APP
      header("Location: ".$REDIRECT);
    }
  }else{
    echo "[Auth problem]";
  }
  exit();
}


?>
<!-- HTML Content For Loading -->
<div style="position:fixed;bottom:50%;left:50%;transform:translate(-50%,-50%);font-family:system-ui;font-size:5vmin;opacity:0.7;"> 
  Yönlendiriliyor
</div>

<svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 512 512" style="position:fixed;bottom:10%;left:50%;transform:translate(-50%);height:12%;aspect-ratio:1;border:none;background-color: transparent;border-radius:20%;">
  <path id="left-eye-up"   class="eye" d="m210,178 l 0,-68" stroke-width="18" stroke-linecap="round" stroke="#DE11BA" fill="transparent"></path>
  <path id="left-eye-down" class="eye" d="m210,178 l 0,68" stroke-width="18" stroke-linecap="round" stroke="#DE11BA" fill="transparent"></path>

  <path id="right-eye-up"   class="eye" d="m302,178 l 0,-68" stroke-width="18" stroke-linecap="round" stroke="#DE11BA" fill="transparent"></path>
  <path id="right-eye-down" class="eye" d="m302,178 l 0,68" stroke-width="18" stroke-linecap="round" stroke="#DE11BA" fill="transparent"></path>

  <path class="mounth" id="mounth-right" d="M 255.29 411.9245 C 276.773 411.9245 293.4386 406.6049 299.8556 404.2055 C 349.7594 385.5125 384.6065 334.6415 388.6427 274.61" stroke-width="18" stroke-linecap="round" stroke="#DE11BA" fill="transparent"></path>
  <path class="mounth" id="mounth-left" d="M256.6106 411.9245C235.1276 411.9245 218.462 406.6049 212.045 404.2055 162.1412 385.5125 127.2941 334.6415 123.2579 274.61" stroke-width="18" stroke-linecap="round" stroke="#DE11BA" fill="transparent"></path>
</svg>


<!-- Auth Script Code -->
<script>
window.addEventListener("DOMContentLoaded",function(){
  window.top.postMessage(JSON.stringify({action:"<auth",url:location.href}), '*');
})
window.addEventListener('message', (event) => {
  if (!event.origin.endsWith("topluyo.com")) return;
  let data = JSON.parse(event.data)
  if(data[">auth"]){

    const form = document.createElement("form");
    form.method = "POST";
    form.action = "?";

    const input = document.createElement("input");
    input.type = "hidden";
    input.name = ">auth";
    input.value = data[">auth"];

    form.appendChild(input);
    document.body.appendChild(form);

    form.submit();
  }

  if(data[">login"]){
    fetch("?", {
      method: "POST",
      headers: {
        "Content-Type": "application/x-www-form-urlencoded",
      },
      body: new URLSearchParams({ ">auth": data[">login"], "redirect":1 }),
    }).then(e=>e.text()).then(e=>{
      window.top.postMessage(JSON.stringify({action:"<redirect",url:location.href, redirect:e}), '*');
    })
  }

});
</script>
