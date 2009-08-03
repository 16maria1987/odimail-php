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
     * 
     * @var Odimail_Message_Part
     */
    protected $_parent = null;
    
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
     * @var string
     */
    protected $_rawContent = null;
    
    /**
     * 
     * @var int
     */
    protected $_decodedContentLength = null;
    
    /**
     * 
     * @var array
     */
    protected $_parameters = array();
        
    /**
     * 
     * @param Odimail_Connection $connection
     * @param int $messageNo
     * @param string $section
     * @return void
     */
    public function __construct($connection, $messageNo, $section = '')
    {
        $this->_connection = $connection;
        $this->_messageNo  = $messageNo;
        $this->_section    = $section;
        
        $struct = imap_fetchstructure($connection->getStream(), $messageNo);
        
        if ($section !== '') {
            $sectionParts = explode('.', $section);   
             
            foreach ($sectionParts as $index) {
                $index = intval($index) - 1;
                if (isset($struct->parts) && array_key_exists($index, $struct->parts)) {
                    $struct = $struct->parts[$index];
                }
            }
        }
        
        $this->_structure = $struct;
        
        if ($this->_structure->ifparameters == 1) {
            foreach ($this->_structure->parameters as $key => $value) {
                $this->_parameters[$param->attribute] = $param->value;    
            }
        }
        
        if ($this->_structure->ifdparameters == 1) {
            foreach ($this->_structure->dparameters as $param) {
                $this->_parameters[$param->attribute] = $param->value;    
            }
        }
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
                return 'TEXT';
                break;
            case 1:
                return 'MULTIPART';
                break;
            case 2:
                return 'MESSAGE';
                break;
            case 3:
                return 'APPLICATION';
                break;
            case 4:
                return 'AUDIO';
                break;
            case 5:
                return 'IMAGE';
                break;
            case 6:
                return 'VIDEO';
                break;
            case 7:
                return 'OTHER';                
        }
        
    }
    
    /**
     * Gets the MIME type and subtype as a string
     * 
     * @return string
     */
    public function getMimeTypeString()
    {
        $subtype   = $this->getMimeSubtype();
        $separator = ($subtype == '') ? '' : '/';
        return $this->getMimeType() . $separator . $subtype;
    }
    
    /**
     * Gets the subtype of the MIME type
     * 
     * @return string
     */
    public function getMimeSubtype()
    {
        if ($this->_structure->ifsubtype == 1) {
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
        if ($this->_rawContent == null) {
            $this->_rawContent = imap_fetchbody($this->_connection->getStream()
                    , $this->getMessageNumber(), $this->getSection());
        }
        
        return $this->_rawContent;
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
     * Return the number of parts
     * 
     * @return int
     */
    public function countParts() 
    {
        return count($this->_structure->parts);    
    }
    
    /**
     * 
     * @param int $partNumber
     * 
     * @return Odimail_Message_Part
     */
    public function getPart($partNumber)
    {
        $partNumber = (int) $partNumber;
        if ($partNumber > 0 && $partNumber <= $this->countParts()) {
            if ($this->_section == '') {
                $section = $partNumber;
            } else {
                $section = $this->_section . '.' . $partNumber;
            }
            
            return new Odimail_Message_Part($this->getConnection()
                                , $this->getMessageNumber(), $section);
        }
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
     * Gets the message number
     * 
     * @return int
     */
    public function getMessageNumber()
    {
        return $this->_messageNo;
    }
    
    /**
     * Gets an stream as the returned by the imap_open function
     * 
     * @return Odimail_Connection
     */
    public function getConnection()
    {
        return $this->_connection;
    }
    
    /**
     * Gets the section of the message part
     * 
     * @return string
     */
    public function getSection()
    {
        return $this->_section;
    }
    
    /**
     * Gets the value of the parameter $key
     * 
     * @return string
     */
    public function getParameter($key)
    {
        $key = (string) $key;
        if (key_exists($key, $this->_parameters)) {
            return $this->_parameters[$key];
        } else {
            return null;
        }
    }
    
    /**
     * Gets all parameters
     * 
     * @return array
     */
    public function getParameters()
    {
        return $this->_parameters;    
    }
    
    /**
     * Return true if the $key parameter exists
     * 
     * @param string $key
     * @return bool
     */
    public function hasParameter($key)
    {
        return array_key_exists($key, $this->_parameters);
    }
    
    /**
     * 
     * @return Odimail_Message_Part
     */
    public function getParent()
    {
        return $this->_parent;
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
