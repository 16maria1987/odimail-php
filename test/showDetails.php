<?php
include_once 'config.php';

$connection = new Odimail_Connection();
$connection->open($config);

$section = $_GET['section'];
$sectionParts = array();
if ($section != '') {
    $sectionParts = explode('.', $section);
}
$msgNum  = $_GET['msgnum'];

$msg  = $connection->getMessage($msgNum);
$part = $msg;
foreach($sectionParts as $index) {
    $part = $part->getPart($index);
}
?>
<html>
<head>
	<meta http-equiv="Content-Type" content="text/html; charset=utf-8" />
	<title>Odimail-php - Test</title>
</head>
<body>

	<h1>Odimail-php</h1>
	
	<table border="1">
	<?php 
	if ($part instanceof Odimail_Message) {
	?>    
	<tr>
		<td>Subject: </td>
		<td><?php echo $part->getSubject() ?></td>
	</tr>
	<tr>
		<td>From: </td>
		<td><?php echo $part->getFrom()->getEmail() ?></td>
	</tr>    
	<tr>
		<td>To: </td>
		<td>
		<?php  
		$to = $part->getTo();
		foreach ($to as $contact) {
		    echo $contact->getEmail() . '< ' . $contact->getName() . '><br />';
		}
		?>
		</td>
	</tr>
	<tr>
		<td>Cc: </td>
		<td>
		<?php  
		$cc = $part->getCc();
		foreach ($cc as $contact) {
		    echo $contact->getEmail() . '< ' . $contact->getName() . '><br />';
		}
		?>
		</td>
	</tr>
	<tr>
		<td colspan="2">
		Body:
		<hr />
		<?php 
		$body = $part->getBody();
		if ($part->getBodyCharset() != 'UTF-8') {
		    $body = utf8_encode($body);
		} 
		echo $body;
		?>
		</td>
	</tr>
	<?php     
	} 
	?>
	<tr>
		<td>MIME-Type:</td>
		<td><?php echo $part->getMimeTypeString() ?></td>
	</tr>
	<tr>
		<td>Message Num:</td>
		<td><?php echo $part->getMessageNumber() ?></td>
	</tr>
	<tr>
		<td>Section:</td>
		<td><?php echo $part->getSection() ?>&nbsp;</td>
	</tr>
	</table>
	
	<?php
	if ($parameters = $part->getParameters()) {
	?>
	<table border="1">
	<tr>
		<th>Attribute</th>
		<th>Value</th>
	</tr>
    	<?php 
    	foreach ($parameters as $attribute => $value) {
    	?>
    	<tr>
    		<td><?php echo $attribute ?></td>
    		<td><?php echo $value ?></td>
    	</tr>
    	<?php 
    	}
    	?>
	</table>
	<?php
	} 
	?>

</body>
</html>

