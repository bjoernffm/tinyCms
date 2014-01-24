<?
	header('Content-Type: text/html; charset=utf-8');
?>
<!DOCTYPE html>
<html>
	<head>
		<title>Testpage for GUI</title>
	</head>
	<body>
		<h1>News aus de Küche</h1>
		<hr />
<?
	require_once '../tinyCms.class.php';
	
	$cms = new tinyCms();
	
	$items = $cms->getOverview('Unsere "Rezepte"');
	
	foreach ($items as $item) {
?>
		<h2><?=$item->get('Titel')?> <i>vom <?=$item->get('Datum')?></i></h2>
		<h3><?=$item->get('Kurzbeschreibung')?></h3>
		<p><?=$item->get('Nachricht', 200)?></p>
		<p><a href="<?=$item->getLink('testDetail.php')?>">Jetzt ansehen!</a></p>
		<hr />
<?
	}	
?>
	</body>
</html>