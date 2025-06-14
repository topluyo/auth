<?php

// TPAuth Class
class TPAuth{

  public static $API_KEY = "";
  
  private static function decrypt($encryptedData, $password) {
    $method = 'aes-256-cbc';
    $password = substr(hash('sha256', $password, true), 0, 32);
    $iv = chr(0x0) . chr(0x0) . chr(0x0) . chr(0x0) . chr(0x0) . chr(0x0) . chr(0x0) . chr(0x0) . chr(0x0) . chr(0x0) . chr(0x0) . chr(0x0) . chr(0x0) . chr(0x0) . chr(0x0) . chr(0x0);
    $decrypted = openssl_decrypt(base64_decode($encryptedData), $method, $password, OPENSSL_RAW_DATA, $iv);
    $checksum = substr($decrypted,0,4);
    $message  = substr($decrypted,4);
    if(substr(md5($message),0,4)==$checksum){
      return $message;
    }else{
      return "";
    }  
  }

  public static function user(){
    if(!isset($_COOKIE['tp_auth'])) return false;
    $response = self::decrypt($_COOKIE['tp_auth'],self::$API_KEY);
    $response = json_decode($response,true);
    if(!$response) return false;
    return $response;
  }
    
  public static function login($options=[]){
    if( !(isset($_GET['>start']) || isset($_POST['>auth']))  ){
      if(self::user()) return self::user();
    }
    

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


    
    // Auth --------------------------------------
    if(isset($_POST['>auth'])){
      $auth_code = $_POST['>auth'];
      $response = self::decrypt($auth_code,self::$API_KEY);
      if($response!=false){
        $response = json_decode($response,true);

        //@ HERE GET AUTH DATAS
        $name   = "tp_auth";
        $value  = $_POST['>auth'];
        $expire = time()+3600*24*7;

        $url = ($options['path'] ? $options['path'] : $options['redirect'])($response);
        
        $parsed = parse_url($url);
        $domain = $parsed['host'] ?? '';
        $path = $parsed['path'] ?? '';        

        setcookie($name, $value, [
          'expires' => $expire,
          'path' => $path,
          'secure' => true, // Required when SameSite=None
          'samesite' => 'None'
        ]);
      
        //@ Echo here redirect url
        $REDIRECT = $options['redirect']($response);
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
  if(window.top==window){
    window.location.href = "https://topluyo.com/!auth/<?= $options['app_id'] ?>";
  }
})
window.addEventListener('message', (event) => {
  if (!event.origin.endsWith("topluyo.com")) return;
  let data = JSON.parse(event.data)
  if(data[">auth"]){

    const form = document.createElement("form");
    form.method = "POST";
    form.action = location.search;

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
      //document.write(e)
      window.top.postMessage(JSON.stringify({action:"<redirect",url:location.href, redirect:e}), '*');
    })
  }

});
</script>

    <?php 
    die();
  } //@ END LOGIN
}//@ END CLASS
