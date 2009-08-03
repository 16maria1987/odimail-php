<?php 
include_once '../Odimail/Connection.php';
include_once '../Odimail/Contact.php';
include_once '../Odimail/Message/Part.php';
include_once '../Odimail/Message/Attachment.php';
include_once '../Odimail/Message.php';

$config = array(
    'user' => 'dexter',
    'password' => '123456',
    'host' => 'test.com',
    'mailbox' => 'INBOX',
    'flags' => array('notls')
);

$connection = new Odimail_Connection();
$connection->open($config);


if (true) {
    echo "<pre>";
    
    for ($i = 1 ; $i <= $connection->countMessages(); $i++) {
        echo "<hr />";
        $msg = $connection->getMessage($i);
        $countParts = $msg->countParts();
        
        echo "Subject: " . $msg->getSubject() . "\n";
        echo "Parts: " . $countParts . "\n";
        echo "Attachments: " . $msg->countAttachments() . "\n";
        echo $msg->getMimeTypeString() . "\n";
                   
        if ($countParts > 0) {
            echo "<hr />";
            
            for ($j = 1; $j <= $countParts; $j++) {
                $part = $msg->getPart($j);
                echo "Section : " . $part->getSection() . "\n";
                echo "MimeType: " . $part->getMimeTypeString() . "\n";
                
                if ($part->getMimeTypeString() == 'TEXT/HTML') {
                    echo "</pre><hr />[open]";
                    echo $part->getContent();
                    echo "[close]<hr /><pre>";
                }
                              
                // Subparts
                if ($part->countParts() > 0) {
                    for ($y = 1; $y <= $part->countParts(); $y++) {
                        $subpart = $part->getPart($y);
                        echo "\tSection : " . $subpart->getSection() . "\n";
                        echo "\tMimeType: " . $subpart->getMimeTypeString() . "\n";
                        
                        if ($subpart->getMimeTypeString() == 'TEXT/HTML') {
                            echo "</pre><hr />[open]";
                            echo $subpart->getContent();
                            echo "[close]<hr /><pre>";
                        }
                        
                        // Sub-Subparts
                        if ($subpart->countParts() > 0) {
                            for ($ind = 1; $ind <= $subpart->countParts(); $ind++) {
                                $subpart2 = $subpart->getPart($ind);
                                echo "\t\tSection : " . $subpart2->getSection() . "\n";
                                echo "\t\tMimeType: " . $subpart2->getMimeTypeString() . "\n";
                                echo "\n";

                                if ($subpart2->countParts() > 0) {
                                    for ($ind2 = 1; $ind2 <= $subpart2->countParts(); $ind2++) {
                                        $subpart3 = $subpart2->getPart($ind2);

                                        echo "\t\t\tSection : " . $subpart3->getSection() . "\n";
                                        echo "\t\t\tMimeType: " . $subpart3->getMimeTypeString() . "\n";
                                        echo "\n";
                                    }
                                }
                                
                            }
                        }
                        
                        echo "\n";
                    }
                }
                
            }
            
        }
        
    }
    
    exit;
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
<caption>Messages: <?php echo $connection->countMessages() ?></caption>
<tr>
	<th>From</th>
	<th>To</th>
	<th>Subject</th>
	<th>Other</th>
</tr>
<?php

for ($i = 1 ; $i <= $connection->countMessages(); $i++) {
    $msg = $connection->getMessage($i);
?>
<tr>
	<td><?php echo $msg->getFrom()->getEmail() ?></td>
	<td><?php echo $msg->getTo(0)->getEmail() ?></td>
	<td><?php echo $msg->getSubject() ?></td>
	<td>
	<?php 
	for ($j = 1; $j <= $msg->countParts(); $j++){
	    $part = $msg->getPart($j);
	    echo $part->getSection() . "<br />";
	}
	?>
	</td>
</tr>
<?php 
}
?>
</table>

</body>
</html>