<?php
include_once 'config.php';

$connection = new Odimail_Connection();
$connection->open($config);

$section = $_GET['section'];
$sectionParts = explode('.', $section);
$msgNum  = $_GET['msgnum'];

$msg  = $connection->getMessage($msgNum);
$part = $msg;
foreach($sectionParts as $index) {
    $part = $part->getPart($index);
}

header('Content-type: ' . $part->getMimetypeString());
echo $part->getContent();
