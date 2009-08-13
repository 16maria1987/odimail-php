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
 */

class Odimail_Message extends Odimail_Message_Part
{

    /**
     * 
     * @var string
     */
    protected $_subject = '';
    
    /**
     * 
     * @var Odimail_Contact
     */
    protected $_from = null;
    
    /**
     * 
     * @var array
     */
    protected $_to = array();
    
    /**
     * 
     * @var array
     */
    protected $_cc = array();
    
    /**
     * 
     * @var array
     */
    protected $_bcc = array();
    
    /**
     * 
     * @var Odimail_Contact
     */
    protected $_replyTo = null;
    
    /**
     * Date of the message
     * 
     * @var string
     */
    protected $_date;
  
    /**
     * 
     * @param Odimail_Connection $connection
     * @param int $messageNo
     * @param string $mailbox
     * @return Odimail_Message
     */
    public function __construct($connection, $messageNo, $mailbox)
    {
        parent::__construct($connection, $messageNo, '');
        $this->_mailbox = $mailbox;
        $this->_proccessHeaders();
    }
        
    /**
     * Gets the mailbox name of this message
     * 
     * @return string
     */
    public function getMailbox()
    {
        return $this->_mailbox;
    }
    
    /**
     * Gets the subject of the message
     * 
     * @return string
     */
    public function getSubject()
    {
        return $this->_subject;
    }
    
    /**
     * Gets the From information
     * 
     * @return Odimail_Contact
     */
    public function getFrom()
    {
        return $this->_from;
    }
    
    /**
     * Gets an array with all contacts in the To header
     * 
     * @param int $index
     * @return array | Odimail_Contact
     */
    public function getTo($index = null)
    {
        if (null !== $index && isset($this->_to[$index])) {
            return $this->_to[$index];
        }
        
        return $this->_to; 
    }
    
    /**
     * Gets an array with all contacts in the Cc header
     * 
     * @param int $index
     * @return array | Odimail_Contact
     */
    public function getCc($index = null)
    {
        if (null !== $index && isset($this->_cc[$index])) {
            return $this->_cc[$index];
        }
        
        return $this->_cc;    
    }
    
    /**
     * Gets the Reply-to header information
     * 
     * @return Odimail_Contact
     */
    public function getReplyTo()
    {
        return $this->_replyTo;
    }
        
    /**
     * Gets the date of the message
     * 
     * @param string $format 
     * @return mixed
     */
    public function getDate($format = null)
    {
        if (is_string($format)) {
            return date($format, $this->_date);
        }
        return $this->_date;
    }
    
    /**
     * Mark a message for deletion from current mailbox. Messages marked for deletion 
     * will stay in the mailbox until Odimail_Connection::expunge() is called
     * 
     * @see Odimail_Connection::expunge()
     * @return bool
     */
    public function delete()
    {
        return @imap_delete($this->_connection->getStream(), $this->getMessageNumber());
    }
       
    /**
     * Proccess the headers of the message
     * 
     * @return void
     */
    protected function _proccessHeaders()
    {
        $headerInfo = imap_headerinfo($this->_connection->getStream(), $this->getMessageNumber());
        
        // Subject
        $subject = imap_mime_header_decode($headerInfo->subject);
        $this->_subject = $subject[0]->text;

        // From 
        if (isset($headerInfo->from)) {
            $this->_from = new Odimail_Contact($headerInfo->from[0]);
        }
        
        // Reply to
        if (isset($headerInfo->reply_to)) {
            $this->_replyTo = new Odimail_Contact($headerInfo->reply_to[0]);
        }
        
        // To
        foreach ($headerInfo->to as $contact) {
            $this->_to[] = new Odimail_Contact($contact);
        }
        
        // Cc
        if (isset($headerInfo->cc)) {
            foreach ($headerInfo->cc as $contact) {
                $this->_cc[] = new Odimail_Contact($contact);
            }
        }
        
        // Bcc
        if (isset($headerInfo->bcc)) {
            foreach ($headerInfo->bcc as $contact) {
                $this->_bcc[] = new Odimail_Contact($contact);
            }
        }
        
        // Date
        $this->_date = $headerInfo->udate;
        
    }
    
}
