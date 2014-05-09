<?
  header('Content-Type: text/html; charset=utf-8');
?>
<!DOCTYPE html>
<html>
  <head>
    <title>Verwaltung</title>
    <link rel="stylesheet" href="//netdna.bootstrapcdn.com/bootstrap/3.1.1/css/bootstrap.min.css">
  </head>
  <body>
    <div class="container">
<?

  require_once '../tinyCms.class.php';
  
  $cms = new tinyCms();
  $cms->showGui();
  
?>
    </div>
  </body>
</html>