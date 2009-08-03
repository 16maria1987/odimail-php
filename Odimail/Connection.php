<?php
/**
 * odimail-php : An easy-to-use interface to access IMAP and POP3 servers
 * 
 * Licensed under The MIT License
 * 
 * @author			Juan Odicio Arrieta
 * @link			http://code.google.com/p/odimail-php/ 	
 * @license			http://www.opensource.org/licenses/mit-license.php The MIT License
 */

class Odimail_Connection 
{
    
    /**
     * Number of messages in the current mailbox
     * 
     * @var int
     */
    protected $_messagesCount = 0;
    
    /**
     * Stream resource as the returned by the imap_open function
     * 
     * @var resource
     */
    protected $_stream = null;
    
    /**
     * 
     * @var string
     */
    protected $_host = 'localhost';
    
    /**
     * Connection TCP port number
     * 
     * @var int
     */
    protected $_port = 143;
    
    
    /**
     * Protocol to use. It can be 'imap' or 'pop3'
     * 
     * @var string
     */
    protected $_protocol = 'imap';
    
    /**
     * User name
     * 
     * @var string
     */
    protected $_user = '';
    
    /**
     * Password 
     * 
     * @var string
     */
    protected $_password = '';
    
    /**
     * Current Mailbox (INBOX by default)
     * 
     * @var string
     */
    protected $_mailbox = 'INBOX';
    
    /**
     * 
     * @var array
     */
    protected $_flags = array();
    
    /**
     * 
     * @param array $config
     * 
     * @return Odimail_Connection
     */
    public function __construct(array $config)
    {
        $this->_setup($config);
    }
    
    /**
     * Gets the current mailbox
     * 
     * @return string
     */
    public function getCurrentMailbox()
    {
        return $this->_mailbox;
    }
    
    /**
     * Gets information on the mailboxes
     * 
     * @return array
     */
    public function getMailboxes()
    {
        // TODO Mejorar el resultado devuelto
        return imap_getmailboxes($this->_stream, $this->_buildMailboxString(), '*');
    }
    
    /**
     * Open a mailbox
     * 
     * @param string $mailbox
     * @return void
     */
    public function openMailbox($mailbox) 
    {
        imap_reopen($this->getStream(), $this->_buildMailboxString());
        $headers = imap_headers($this->_stream);
        
        // Messages count
        $this->_messagesCount = count($headers);
    }
    
    /**
     * Gets the message in position $messageNo
     * @param int $messageNo
     * 
     * @return Odimail_Message
     */
    public function getMessage($messageNo)
    {
        if ($messageNo > 0 && $messageNo <= $this->_messagesCount) {
            return new Odimail_Message($this, $messageNo, $this->_mailbox);
        }
    }
    
    /**
     * Return the number of messages in the current mailbox
     * 
     * @return int
     */
    public function countMessages()
    {
        return $this->_messagesCount;
    }
    
    /**
     * Opens an IMAP stream to a mailbox
     * @param array $config
     * 
     * @return resource
     */
    public function open(array $config = array())
    {
        $this->_setup($config);
        
        try {
            $this->_stream = imap_open($this->_buildMailboxString(), $this->_user, $this->_password);
            $this->openMailbox($this->_mailbox);            
            return true;
            
        } catch (Exception $ex) {
            return false;
        }
        
    }
    
    /**
     * Close the IMAP stream
     * 
     * @return void
     */
    public function close()
    {
        imap_close($this->_stream);
    }
        
    /**
     * Gets all of the IMAP errors (if any) that have occurred during this page request
     * 
     * @return array
     */
    public function getErrors() 
    {
        return imap_errors();    
    }
    
    /**
     * Return the IMAP stream
     * 
     * @return resource
     */
    public function getStream()
    {
        return $this->_stream; 
    }
    
    /**
     * Builds a string as the needed by the imap_open function
     * 
     * @return string
     */
    protected function _buildMailboxString()
    {
        $flags = array_merge(array($this->_protocol), $this->_flags);
        $flagsString = implode('/', $flags);
        
        $connectionString = '{' . $this->_host . ':' . $this->_port 
            . '/' . $flagsString . '}' . $this->_mailbox;
                    
        return $connectionString;
    }
    
    /**
     * Sets configuration parameters 
     * 
     * @param array $config
     * @return void
     */
    protected function _setup(array $config)
    {
        if (is_array($config)) {
            $allowedProperties = array('host', 'port', 'user', 'password'
                , 'protocol', 'mailbox', 'flags');
            
            foreach ($config as $property => $value) {
                if (in_array($property, $allowedProperties)) {
                    $property = '_' . $property;
                    $this->{$property} = $value;
                }
            }
        }
        
    }
    
    
}



