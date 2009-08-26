<?php
include_once '../Odimail/Connection.php';
include_once '../Odimail/Contact.php';
include_once '../Odimail/Message/Part.php';
include_once '../Odimail/Message.php';

$config = array(
    'user' => 'dexter',
    'password' => '123456',
    'host' => 'mail.one-src.com',
    'mailbox' => 'INBOX',
    'flags' => array('notls')
);