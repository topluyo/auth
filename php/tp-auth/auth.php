<?php


/*


OUT IFRAME:
  > if not logged
    redirect topluyo.com/!auth/APP_ID
      <auth  -> send auth request to topluyo    
      >login -> topluyo to app
      >auth  -> app to app server
        {path,redirect} 
      <redirect -> app to topluyo
      -> redirect link opening

In Iframe:
  <auth -> send auth request to topluyo
  >auth -> topluyo to app
  >auth -> app to app server
    {path,redirect}
    redirect in iframe auto
  


*/



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
    //echo "LOGIN REQUEST\n";
    if( isset($_GET['>start']) ){
      if(self::user() ) return self::user();
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
      //echo "AUTH CODE INCOMING\n";
      $response = self::decrypt($auth_code,self::$API_KEY);
      //echo $response != false ? "USER SUCCESS\n" : "USER FAIL\n"; 
      
      if($response!=false){
        $response = json_decode($response,true);

        //@ HERE GET AUTH DATAS
        $name   = "tp_auth";
        $value  = $_POST['>auth'];
        $expire = time()+3600*24*7;

        $query = json_decode($_REQUEST["query"],true);

        $url = (isset($options['path']) ? $options['path'] : $options['redirect'])($response,$query);
        
        //echo $url;
        
        $parsed = parse_url($url);
        $domain = $parsed['host'] ?? '';
        $path = $parsed['path'] ?? '';        
        
        /*
        setcookie($name, $value, [
          'expires' => $expire,
          'path' => $path,
          'secure' => true, // Required when SameSite=None
          'samesite' => 'None'
        ]);
        */
        //echo "SUCCESS SAVE CODE\n";

        //@ Echo here redirect url
        $REDIRECT = $options['redirect']($response,$query);
        if(isset($_POST['redirect']) && $_POST['redirect']==1){
          // Open in new window
          echo json_encode(["state"=>"success","path"=>$path,"redirect"=>$REDIRECT]);
          exit();
          //echo $REDIRECT;
        }else{
          // In APP
          header("Location: ".$REDIRECT);
        }
      }else{
        echo json_encode(["state"=>"error","message"=>"[Auth Problem]"]);
      }
      exit();
    }
    ?>

<!-- HTML Content For Loading -->
<div id="message" style="position:fixed;bottom:50%;left:50%;transform:translate(-50%,-50%);font-family:system-ui;font-size:5vmin;opacity:0.7;"> 
  Routing
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
  debugger
  if(window.parent) 
    window.parent.postMessage(JSON.stringify({action:"<auth",url:location.href}), '*');
  if(window.parent==window){
    window.location.href = "https://topluyo.com/!auth/<?= $options['app_id'] ?>";
  }
})
const queryParams = JSON.stringify( Object.fromEntries(new URLSearchParams(window.location.search)) );
window.addEventListener('message', (event) => {
  console.log(":::: MESSAGE INCOMING ::::")
  if (!event.origin.endsWith("topluyo.com")) return;
  let data = JSON.parse(event.data)
  debugger
  if(data[">auth"]){
    fetch("?fetch=fetch", {
      method: "POST",
      headers: {
        "Content-Type": "application/x-www-form-urlencoded",
      },
      body: new URLSearchParams({ ">auth": data[">auth"], "redirect":1 ,"query":queryParams }),
    }).then(e=>e.json()).then(e=>{
      if(e.state=="error") return document.getElementById("message").innerHTML = e.message;
      //document.write(e)
      debugger
      const expiry = new Date();
      expiry.setDate(expiry.getDate() + 7); // 7 gün sonra süresi dolacak
      document.cookie = `tp_auth=${data['>auth']}; path=${e.path}; expires=${expiry.toUTCString()}; SameSite=None; Secure`;
      location.href = e.redirect
    })
  }

  if(data[">login"]){
    fetch("?fetch=fetch", {
      method: "POST",
      headers: {
        "Content-Type": "application/x-www-form-urlencoded",
      },
      body: new URLSearchParams({ ">auth": data[">login"], "redirect":1,"query":queryParams }),
    }).then(e=>e.json()).then(e=>{
      if(e.state=="error") return document.getElementById("message").innerHTML = e.message;
      //document.write(e)
      debugger
      const expiry = new Date();
      expiry.setDate(expiry.getDate() + 7); // 7 gün sonra süresi dolacak
      document.cookie = `tp_auth=${data['>login']}; path=${e.path}; expires=${expiry.toUTCString()}; SameSite=None; Secure`;
      window.top.postMessage(JSON.stringify({action:"<redirect",url:location.href, redirect:e.redirect}), '*');
    })
  }

});
</script>

    <?php 
    die();
  } //@ END LOGIN
}//@ END CLASS
