<?php

header('Content-Type: text/html; charset=utf-8');  

require_once '../tinyCms.class.php';
  
$cms = new tinyCms();
$item = $cms->getItem($_GET['id']);
  
?>
<!DOCTYPE html>
<html>
    <head>
        <title><?=$item->get('Titel')?></title>
    </head>
    <body>
        <h1><?=$item->get('Titel')?> <i>vom <?=$item->get('Datum')?></i></h1>
        <h3>Kurzbeschreibung: <?=$item->get('Kurzbeschreibung')?></h3>
        <h4>Backdauer: <?=$item->get('Backdauer (h)')?> Stunden</h4>
        <p><?=$item->get('Nachricht')?></p>
    </body>
</html>