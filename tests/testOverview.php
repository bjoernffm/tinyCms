<?php

header('Content-Type: text/html; charset=utf-8');

require_once '../tinyCms.class.php';
  
$cms = new tinyCms();
$items = $cms->getOverview('Unsere "Rezepte"');
  
?>
<!DOCTYPE html>
<html>
    <head>
        <title>News us de Küche</title>
    </head>
    <body>
        <h1>News us de Küche</h1>
        <hr />
        <?php
          foreach ($items as $item) {
        ?>
            <h2><?=$item->get('Titel')?> <i>vom <?=$item->get('Datum')?></i></h2>
            <h3><?=$item->get('Kurzbeschreibung')?></h3>
            <p><?=$item->get('Nachricht', 200)?></p>
            <p><a href="<?=$item->getLink('testDetail.php')?>">Jetzt ansehen!</a></p>
            <hr />
        <?php
          }  
        ?>
    </body>
</html>