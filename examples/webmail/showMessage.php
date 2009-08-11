<?php
include_once 'config.php';

$connection = new Odimail_Connection();

$pageSize = 5;
$currentMailbox = isset($_GET['mbox']) ? $_GET['mbox'] : 'INBOX';
$messageNumber  = isset($_GET['msg']) ? $_GET['msg'] : 1;

$config['mailbox'] = $currentMailbox;
$connection->open($config);

$message = $connection->getMessage($messageNumber);


$cc = $message->getCc();
$to = $message->getTo();


function isHtmlPart(Odimail_Message_Part $part)
{
    return strtolower($part->getMimeTypeString()) == 'text/html';
}

function isTextPart(Odimail_Message_Part $part)
{
    return strtolower($part->getMimeTypeString()) == 'text/plain';
}

$parts = $message->searchParts('isHtmlPart', 1);

if (empty($parts)) {
    $parts = $message->searchParts('isTextPart', 1);

    if (empty($parts)) {
        die('No body part found');
    } else {
        $bodyPart = $parts[0];
    }
} else {
    $bodyPart = $parts[0];
}

$bodyText = $bodyPart->getContent();
if ($bodyPart->hasParameter('charset') && strtolower($bodyPart->getParameter('charset')) != 'utf-8') {
    $bodyText = utf8_encode($bodyPart->getContent());
}


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
			<h1><?php echo $message->getSubject() ?></h1>
		</div>
		
		<table>
		<tr>
			<td>From:</td>
			<td>
				<span class="email"><?php echo $message->getFrom()->getEmail() ?></span>
			</td>
		</tr>
		<tr>
			<td>To:</td>
			<td>
				<?php foreach ($to as $contact) {	?>
				<span class="email"><?php echo $contact->getEmail() ?></span>
				<?php }	?>
			</td>
		</tr>
		<?php if (!empty($cc)) {?>
		<tr>
			<td>Cc:</td>
			<td>
				<?php foreach ($cc as $contact) { ?>
				<span class="email"><?php echo $contact->getEmail() ?></span>
				<?php }	?>
			</td>
		</tr>
		<?php } ?>
		<tr>
			<td>Date:</td>
			<td><?php echo $message->getDate('l d/m/Y h:i a') ?></td>
		</tr>
		</table>
		
		<hr />
		<div class="message-body">
			<?php echo $bodyText ?>
		</div>

		<?php 
		$attachments = $message->getAttachments();
		if (count($attachments) > 0) {
		?>
			<h3>Attachments:</h3>
			
			<ul>
    		<?php 
    		for ($i = 0; $i < count($attachments); $i++) {
    		    $file = $attachments[$i] ;
    		?>
    			<li>
    				<a href="showAttachment.php?msg=<?php echo $messageNumber ?>&amp;mbox=<?php echo $currentMailbox ?>&amp;section=<?php echo $file->getSection() ?>">
    			        <?php echo $file->getParameter('name') ?></a>
    			</li>
    		<?php 
    		}
    		?>
    		</ul>
    		
    	<?php
		}
		?>

		<div id="ft">
			Odimail - Juan Odicio Arrieta - Lima, Perú 2009
		</div>
		
	</div>
</div>
</body>
</html>
