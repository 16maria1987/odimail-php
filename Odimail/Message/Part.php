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

class Odimail_Message_Part
{
    
    /**
     * 
     * @var Odimail_Connection
     */
    protected $_connection = null;
    
    /**
     * Mailbox name 
     * 
     * @var string
     */
    protected $_mailbox = '';
    
    /**
     * Message number in the mailbox
     * 
     * @var int
     */
    protected $_messageNo = 0;

    /**
     * Section of the message
     * 
     * @var string
     */
    protected $_section = '';
    
    /**
     * 
     * @var object
     */
    protected $_structure = null;
    
    /**
     * 
     * @var string
     */
    protected $_decodedConent = null;
    
    /**
     * 
     * @var int
     */
    protected $_decodedContentLength = null;
    
    /**
     * 
     * @var string
     */
    protected $_rawContent = null;
    
    /**
     * 
     * @param Odimail_Connection $connection
     * @param int $messageNo
     * @param string $section
     * @return void
     */
    public function __constuct($connection, $messageNo, $section = '')
    {
        $this->_connection = $connection;
        $this->_messageNo  = $messageNo;
        $this->_section    = $section;
        
        $this->_structure = imap_bodystruct($connection->getStream(), $messageNo, $section);
    }
    
    /**
     * Return true if it's a multipart message
     * 
     * @return bool
     */
    public function isMultipart()
    {
        return $this->_structure->type == 1;
    }
    
    /**
     * Gets the MIME type for this message part
     * 
     * @return string
     */
    public function getMimeType()
    {
        switch ($this->_structure->type) {
            case 0:
                return 'text';
                break;
            case 1:
                return 'multipart';
                break;
            case 2:
                return 'message';
                break;
            case 3:
                return 'application';
                break;
            case 4:
                return 'audio';
                break;
            case 5:
                return 'image';
                break;
            case 6:
                return 'video';
                break;
            case 7:
                return 'other';                
        }
        
    }
    
    /**
     * Gets the MIME type and subtype as a string
     * 
     * @return string
     */
    public function getMimeTypeString()
    {
        return $this->getMimeType() . '/' . $this->getMimeSubtype();
    }
    
    /**
     * Gets the subtype of the MIME type
     * 
     * @return string
     */
    public function getMimeSubtype()
    {
        if ($this->_structure->ifsubtype == true) {
            return $this->_structure->subtype;
        } else {
            return '';
        }
    }
    
    /**
     * Gets the enconding
     * 0 => 7BIT, 1 => 8BIT, 2 => BINARY
     * 3 => BASE64, 4 => QUOTED-PRINTABLE, 5 => OTHER
     * @return int
     */
    public function getEncoding()
    {
        return $this->_structure->encoding;
    }
    
    /**
     * Gets the content of the message 
     * 
     * @return string
     */
    public function getRawContent()
    {
        // TODO
    }
    
    /**
     * Gets the decoded content of the message-part
     * 
     * @return string
     */
    public function getContent()
    {
        if ($this->_decodedConent == null) {
            $rawContent = $this->getRawContent();
            
            switch ($this->getEncoding()) {
                case 0: // 7BIT
                case 1: // 8BIT
                    $this->_decodedConent = imap_8bit($rawContent);
                    break;
                case 2: // BINARY
                    $this->_decodedConent = imap_binary($rawContent);
                    break;
                case 3: // BASE64
                    $this->_decodedConent = imap_base64($rawContent);
                    break;
                case 4: // QUOTED-PRINTABLE
                    $this->_decodedConent = quoted_printable_decode($rawContent);
                    break;
                default: // 5 => OTHER
                    $this->_decodedConent = $rawContent;
            }
            
        }
        
        $this->_decodedContentLength = strlen($this->_decodedConent);
        
        return $this->_decodedConent;
    }
    
    /**
     * Gets the size of the message-part before it is decoded
     * 
     * @return int
     */
    public function getRawSize()
    {
        return $this->_structure->bytes;
    }
    
    /**
     * Gets the size of the message-part after it is decoded
     * 
     * @return int
     */
    public function getSize()
    {
        if ($this->_decodedContentLength == null) {
            $this->getContent();    
        }
        
        return $this->_decodedContentLength;
    }
    
    /**
     * Return an object with the same structure as the object 
     * returned by the imap_fetchstructure function
     * @link http://www.php.net/manual/en/function.imap-fetchstructure.php
     * 
     * @return object
     */
    public function getStructure()
    {
        return $this->_structure;
    }
    
}
