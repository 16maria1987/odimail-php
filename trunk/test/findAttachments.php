<?php 
include_once 'config.php';

$connection = new Odimail_Connection();
$connection->open($config);

function searchAttachments(Odimail_Message_Part $part) {
    if ($part->hasParameter('name') || $part->hasParameter('filename')) {
        return true;
    }
    return false;
}

function searchHtml(Odimail_Message_Part $part) {
    if (strtolower($part->getMimeTypeString()) == 'text/html') {
        return true;
    }
    return false;
}

?>
<html>
<head>
	<meta http-equiv="Content-Type" content="text/html; charset=utf-8" />
	<title>Odimail-php - Test</title>
</head>
<body>

	<h1>Odimail-php</h1>
	
	<ol>
	<?php 
	for ($i = 1; $i <= $connection->countMessages(); $i++) {
        $msg = $connection->getMessage($i);
        ?>
        <li><?php echo $msg->getSubject() ?>
            <ol>
            <?php
            $attachments = $msg->searchParts('searchAttachments'); 
            foreach ($attachments as $part) { ?>
            	<li>
            	<?php echo '<a target="_blank" href="showContent.php?msgnum=' . $part->getMessageNumber(). '&amp;section=' . $part->getSection() . '">'; ?>
            	<?php echo $part->getParameter('filename') . ' - ' . $part->getSection() . '</a>' ?>
            	</li>
            <?php 
            }
            ?>
            </ol>
        </li>
        <?php 
    }
	?>
	</ol>
	
</body>
</html>