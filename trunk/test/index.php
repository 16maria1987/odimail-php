<?php 
include_once 'config.php';

$connection = new Odimail_Connection();
$connection->open($config);

function showParts(Odimail_Message_Part $part)
{
    echo "<li>";
    echo '<a target="_blank" href="showContent.php?msgnum=' . $part->getMessageNumber(). '&amp;section=' . $part->getSection() . '">';
    echo strtolower($part->getMimeTypeString());
    echo "</a> - ";
    echo '<a target="_blank" href="showDetails.php?msgnum=' . $part->getMessageNumber(). '&amp;section=' . $part->getSection() . '">[+]</a>';
    if ($part->countParts() > 0) {
        echo "<ol>";
        for ($i = 1; $i <= $part->countParts(); $i++) {
            $subpart = $part->getPart($i);
            showParts($subpart);
        }
        echo "</ol>";
    }
    echo "</li>";
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
        showParts($msg);
    }
	?>
	</ol>

</body>
</html>