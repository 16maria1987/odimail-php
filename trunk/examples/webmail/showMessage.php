<?php
include_once 'config.php';

$connection = new Odimail_Connection();

$pageSize = 5;
$currentMailbox = isset($_GET['mbox']) ? $_GET['mbox'] : 'INBOX';
$messageNumber  = isset($_GET['msg']) ? $_GET['msg'] : 1;

$config['mailbox'] = $currentMailbox;
$connection->open($config);

$message = $connection->getMessage($messageNumber);

?>
<!DOCTYPE html PUBLIC "-//W3C//DTD XHTML 1.0 Transitional//EN" 
	"http://www.w3.org/TR/xhtml1/DTD/xhtml1-transitional.dtd">
<html xmlns="http://www.w3.org/1999/xhtml" xml:lang="en" lang="en" dir="ltr">
<head>
	<meta http-equiv="Content-Type" content="text/html; charset=utf-8" />
	<link href="style.css" type="text/css" rel="stylesheet" />
	
	<title>Webmail</title>
</head>

<body>
<div id="page">
	<div class="margin">
		<div id="hd">
			<h1>Webmail</h1>
		</div>
	
		
		
		<?php 
		$attachments = $message->getAttachments();
		
		for ($i = 0; $i < count($attachments); $i++) {
		?>
			
		<?php 
		}
		?>

		<div id="ft">
			Odimail - Juan Odicio Arrieta - Lima, Peru 2009
		</div>
		
	</div>
</div>
</body>
</html>
