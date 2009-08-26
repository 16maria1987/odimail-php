<?php
include_once '../../Odimail/Connection.php';
include_once '../../Odimail/Contact.php';
include_once '../../Odimail/Message/Part.php';
include_once '../../Odimail/Message.php';

// Genaral example
$config = array(
    'user' => 'user',
    'password' => '123456',
    'host' => 'mail.yourserver.com',
    'mailbox' => 'SENT',
    'flags' => array('notls')
);

// Gmail example
$config = array(
    'user' => 'your.user.name@gmail.com',
    'password' => 'yourpassword',
    'host' => 'imap.gmail.com',
    'mailbox' => 'INBOX',
    'port' => 993,
    'flags' => array('ssl', 'novalidate-cert')
);
