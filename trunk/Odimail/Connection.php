<?php
/**
 * odimail-php : An easy-to-use interface to access IMAP and POP3 servers
 * 
 * Licensed under The MIT License
 * 
 * @author			Juan Odicio Arrieta
 * @link			http://code.google.com/p/odimail-php/ 	
 * @license			http://www.opensource.org/licenses/mit-license.php The MIT License
 * @package			Odimail
 * @version			$Id$
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
     * @return Odimail_Connection
     */
    public function __construct(array $config = array())
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
     * Gets the list of mailboxes
     * 
     * @link http://www.php.net/manual/en/function.imap-getmailboxes.php
     * @param string $pattern
     * @return array
     */
    public function getMailboxes($pattern = '*')
    {
        $mailboxes = imap_list($this->_stream, $this->_buildMailboxString(false), $pattern);
        $list = array(); 
        foreach ($mailboxes as $mailbox){
            $list[] = substr($mailbox, strpos($mailbox, '}') + 1);
        }
        
        return $list;
    }
    
    /**
     * Creates a new mailbox
     * 
     * @param string $name Name of the new mailbox
     * @return bool
     */
    public function createMailbox($name)
    {
        $newMailbox = imap_utf7_encode($this->_buildMailboxString(false) . $name);
        return @imap_createmailbox($this->getStream(), $newMailbox);
    }
    
    /**
     * Rename an old mailbox to new mailbox
     * 
     * @param string $oldMailbox
     * @param string $newMailbox
     * @return bool
     */
    public function renameMailbox($oldMailbox, $newMailbox)
    {
        $path = $this->_buildMailboxString(false);
        $oldMailbox = imap_utf7_encode($path . $oldMailbox);
        $newMailbox = imap_utf7_encode($path . $newMailbox);
        
        return @imap_renamemailbox($this->getStream(), $oldMailbox, $newMailbox);
    }
    
    /**
     * Delete a mailbox
     * 
     * @param string $mailbox
     * @return bool
     */
    public function deleteMailbox($mailbox)
    {
        $path = $this->_buildMailboxString(false);
        $mailbox = imap_utf7_encode($path . $mailbox);
        return @imap_deletemailbox($this->getStream(), $mailbox);
    }
    
    /**
     * Open a mailbox
     * 
     * @param string $mailbox
     * @return bool
     */
    public function openMailbox($mailbox) 
    {
        $path = $this->_buildMailboxString(false);
        
        if (@imap_reopen($this->getStream(), imap_utf7_encode($path . $mailbox))) {
            $this->_mailbox = $mailbox;
            $this->_messagesCount = imap_num_msg($this->getStream());
            return true;
                
        } else {
            return false;
            
        }
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
     * @param bool $force If it's true, it will re-check the number of messages
     * @return int
     */
    public function countMessages($force = false)
    {
        if ($force == true) {
            imap_check($this->getStream());
            $this->_messagesCount = imap_num_msg($this->getStream());
        }
        return $this->_messagesCount;
    }
    
    /**
     * Opens an IMAP stream to a mailbox
     * @param array $config
     * 
     * @return bool
     */
    public function open(array $config = array())
    {
        $this->_setup($config);
        
        try {
            $this->_stream = imap_open($this->_buildMailboxString()
                        , $this->_user, $this->_password);
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
        @imap_close($this->_stream);
    }
        
    /**
     * Gets all of the IMAP errors (if any) that have occurred 
     * during this page request
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
     * Gets the protocol
     * 
     * @return string
     */
    public function getProtocol()
    {
        return $this->_protocol;    
    }
    
    /**
     * Builds a string as the needed by the imap_open function
     * 
     * @param bool $includeMailbox
     * @return string
     */
    protected function _buildMailboxString($includeMailbox = true)
    {
        $flags = array_merge(array($this->_protocol), $this->_flags);
        $flagsString = implode('/', $flags);
        
        $connectionString = '{' . $this->_host . ':' . $this->_port 
            . '/' . $flagsString . '}';
        
        if ($includeMailbox == true) {
            $connectionString .= $this->_mailbox;
        }
                    
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
    
    /**
     * Delete all messages marked for deletion
     * 
     * @return bool
     */
    public function expunge()
    {
        return @imap_expunge($this->getStream());    
    }
    
    
}

