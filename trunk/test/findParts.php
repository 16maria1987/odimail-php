<?php
include_once 'config.php';

$connection = new Odimail_Connection();
$connection->open($config);

echo 'Mensajes: ' . $connection->countMessages();
$cont = 0;
for ($i = 1; $i <= $connection->countMessages(); $i++) {
    $msg = $connection->getMessage($i);
    $parts = $msg->getPartsByMime('text', 'html', 1);
    
    echo '<div style="background-color: red">Separador</div>';
    echo 'Msg::' . $msg->getId() . '<br />';
    echo $msg->getSubject();
    echo "<hr />";

    if (!empty($parts)) {
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