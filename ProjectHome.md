Odimail-php is a wrapper for the PHP-IMAP functions that makes easy to access mail servers

```
$config = array(
    'user' => 'your.user.name@gmail.com',
    'password' => 'yourpassword',
    'host' => 'imap.gmail.com',
    'mailbox' => 'INBOX',
    'port' => 993,
    'flags' => array('ssl', 'novalidate-cert')
);

$connection = new Odimail_Connection();
$connection->open($config);

for ($i = 1; $i <= $connection->countMessages(); $i++) {
    $message = $connection->getMessage($i);
    
    echo 'Subject: ' . $message->getSubject() . '<br />';
    echo 'From: ' . $message->getFrom()->getEmail() . '<br />';
    echo 'To: ' . $message->getTo(0)->getEmail() . '<br />';
    echo '<hr />';
}

```