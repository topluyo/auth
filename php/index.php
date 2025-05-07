<?php 

require_once __DIR__."/tp-auth/auth.php";

$user = TPAuth::login([
  "app_id" => "91",
  "redirect" => function($user) {
    return "https://kodluyo.bozkurtbot.com/";
  }
]);


?>

<script src="//hasandelibas.github.io/documenter/documenter.js"></script>
<meta charset="utf8">
<meta name="viewport" content="width=device-width, initial-scale=1.0">
<header body-class="show-menu theme-light">
  <div title="">📄 Auth Example</div>
  /<?=  $user['channel_id'] == @$_GET['channel_id'] ? $user['channel_nick'] : "" ?>
  
  <div class="space"></div>
  
  <div flex-x center gap>
    <img src='<?= $user['user_image'] ?>' style="width:42px;height:42px;object-fit:cover;border-radius:100%;">
    <div flex-y>
      <span><?= $user['user_name'] ?></span>
      <span style="font-size:0.8em;opacity:0.8;">@<?= $user['user_nick'] ?></span>
    </div>
  </div>
</header>

# Response Auth Data

```json
<?php echo json_encode($user, JSON_PRETTY_PRINT); ?>
```
