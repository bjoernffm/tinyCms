<?
	header('Content-Type: text/html; charset=utf-8');
?>
<!DOCTYPE html>
<html>
	<head>
		<title>Testpage for GUI</title>
	</head>
	<body>
<?
	require_once '../tinyCms.class.php';
	
	$cms = new tinyCms();
	$cms->showGui();
	
?>
	</body>
</html>