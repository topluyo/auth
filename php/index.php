<?php 

session_start();

if(!isset($_SESSION['auth'])){
  header("Location: https://alfa.topluyo.com/!login/auth-test");
}
?>

<script src="//hasandelibas.github.io/documenter/documenter.js"></script>
<meta charset="utf8">
<meta name="viewport" content="width=device-width, initial-scale=1.0">
<header body-class="show-menu theme-light">
  <div title="">📄 Auth-API Example</div>
  <div class="space"></div>
  
  <div flex-x center gap>
    <img src='<?= $_SESSION['auth']['user_image'] ?>' style="width:42px;height:42px;object-fit:cover;border-radius:100%;">
    <div flex-y>
      <span><?= $_SESSION['auth']['user_name'] ?></span>
      <span style="font-size:0.8em;opacity:0.8;">@<?= $_SESSION['auth']['user_nick'] ?></span>
    </div>
  </div>
</header>

# Response Auth Data

```json
<?php echo json_encode($_SESSION['auth'], JSON_PRETTY_PRINT); ?>
```
