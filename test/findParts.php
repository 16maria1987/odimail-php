<?php
include_once 'config.php';

$connection = new Odimail_Connection();
$connection->open($config);

for ($i = 1; $i <= $connection->countMessages(); $i++) {
    $msg = $connection->getMessage($i);
    
    $parts = $msg->getPartsByMime('text', 'html', 1);
    if (!empty($parts)) {
        

        echo '<div style="background-color: red">Separador</div>';
        echo $msg->getSubject();
        echo "<hr />";
        /*
        foreach ($parts as $part) {
            echo $part->getMimeTypeString() . "<br />";
        }
        */
        $body = $parts[0];        
        
        //echo '<pre>';
        if ($body->hasParameter('charset') && $body->getParameter('charset') != 'UTF-8') {
            echo utf8_encode($body->getContent());
        } else {
            echo $body->getContent();
        }
        //echo '</pre>';
    }
    
}