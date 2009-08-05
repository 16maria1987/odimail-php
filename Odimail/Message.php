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
     * Number of attachments
     * 
     * @var int
     */
    protected $_attachmentsCount = null;
    
    /**
     * Array of Odimail_Message_Attachment objects
     * 
     * @var array
     */
    protected $_attachments = array();
    
    /**
     * Array with a $attachmentNumber-$messagePartNumber mapping
     * Where:
     * $attachmentNumber => $messagePartNumber
     * 
     * @var array
     */
    protected $_attachmentPartMap = array();
    
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
     * Gets a collection with all contacts in the To header
     * 
     * @param int $index
     * @return Odimail_Contact | array
     */
    public function getTo($index = null)
    {
        if (null !== $index && isset($this->_to[$index])) {
            return $this->_to[$index];
        }
        
        return $this->_to; 
    }
    
    /**
     * Gets a collection with all contacts in the Cc header
     * 
     * @param int $index
     * @return Odimail_Contact | array
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
     * Gets the body of the message
     * 
     * @return string
     */
    public function getBody()
    {
        if ($this->isMultipart()) {
            $this->_findBody($this, 'TEXT/HTML');                   
            
            if ($this->_body == null) {
                $this->_findBody($this, 'TEXT/PLAIN');
            }
            
            return $this->_body;   
        } 
        
        return $this->getContent();
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
     * Returns the number of attachments
     * 
     * @return int
     */
    public function countAttachments()
    {
        if ($this->_attachmentsCount == null) {
            $this->_attachmentsCount = 0;
            
            $cont = 0;
            
            if ($this->countParts() > 0) {
                for ($i = 1; $i <= $this->countParts(); $i++) {
                    $part = $this->getPart($i);
                    
                    if ($part->hasParameter('filename')) {
                        $cont += 1;
                        $attachment = new Odimail_Message_Attachment($this->_connection,
                                        $this->getMessageNumber(), $i);
                                        
                        $this->_attachments[$cont] = $attachment; 
                    }
                }
            }
            
        }
        
        return $this->_attachmentsCount;
    }
    
    /**
     * Returns an attachment
     * 
     * @param int $attachmentNo
     * @return Odimail_Message_Attachment
     */
    public function getAttachment($attachmentNo)
    {
        if ($attachmentNo <= $this->countAttachments() && $attachmentNo != 0) {
            $partNumber = $this->_attachmentPartMap[$attachmentNo];
            $attachment = new Odimail_Message_Attachment($this, $this->_structure->parts[$partNumber], $partNumber);
            return $attachment;
        }
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
