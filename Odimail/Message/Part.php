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
     * Section of the message (Format "1.1" , "1.2.1", etc)
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
    protected $_decodedContent = null;
    
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
     * Array with all the parts found by the getPartsByMimeType function
     * 
     * @see Odimail_Message_Part::getPartsByMimeType()
     * @var array
     */
    protected $_foundParts = array();
    
    /**
     * Array with all the results from the searchParts function
     * 
     * @var array
     */
    protected $_searchResults = array();
    
    /**
     * The maximun number of search results
     * 
     * @var int
     */
    protected $_maxResults = 0;
    
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
            foreach ($this->_structure->parameters as $param) {
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
     * Gets the enconding: 0 => 7BIT, 1 => 8BIT, 2 => BINARY, 3 => BASE64
     * , 4 => QUOTED-PRINTABLE, 5 => OTHER
     * 
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
            // TODO verify if it works for all cases 
            $section = $this->getSection();
            if ($section == '') {
                $section = '1';
            }
            $this->_rawContent = imap_fetchbody($this->_connection->getStream()
                    , $this->getMessageNumber(), $section);
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
        
        if ($this->_decodedContent == null) {
            $rawContent = $this->getRawContent();
            
            switch ($this->getEncoding()) {
                case 0: // 7BIT
                case 1: // 8BIT
                    $this->_decodedContent = imap_8bit($rawContent);
                    break;
                case 2: // BINARY
                    $this->_decodedContent = imap_binary($rawContent);
                    break;
                case 3: // BASE64
                    $this->_decodedContent = imap_base64($rawContent);
                    break;
                case 4: // QUOTED-PRINTABLE
                    $this->_decodedContent = quoted_printable_decode($rawContent);
                    break;
                default: // 5 => OTHER
                    $this->_decodedContent = $rawContent;
                    
            }
            
        }
        
        $this->_decodedContentLength = strlen($this->_decodedContent);
        
        return $this->_decodedContent;
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
     * Gets an Odimail_Message_Part
     * 
     * @param int $partNumber
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
        
        return null;
    }
    
	/**
     * Returns all parts with the MIME type equal to $mimeType
     * 
     * @param string $mimeType
     * @param string $subtype if it's null only compares MIME type
     * @param int $maxResults
     * @return array
     */
    public function getPartsByMime($mimeType, $subtype = null, $maxResults = 0)
    {
        $this->_foundParts = array();
        $this->_maxResults = (int) $maxResults;
        
        $this->_findPart($this, strtoupper($mimeType), strtoupper($subtype));
        return $this->_foundParts;
    }
       
    /**
     * Find parts by MIME type
     * 
     * @param Odimail_Message_Part $part
     * @param string $mimeType
     * @param string $mimeSubtype
     * @return void
     */
    protected function _findPart($part, $mimeType, $subtype) 
    {
        if ($this->_maxResults > 0 && count($this->_foundParts) > $this->_maxResults) {
            return true;
        }
        
        if ($part->getMimeType() == $mimeType) {
            if ($subtype === null) {
                $this->_foundParts[] = $part;
            } elseif ($subtype == strtoupper($part->getMimeSubtype())) {
                $this->_foundParts[] = $part;
            }
        }
        
        for ($i = 1; $i <= $part->countParts(); $i++) {
            $subpart = $part->getPart($i);
            if ($this->_findPart($subpart, $mimeType, $subtype)) {
                break;
            }
        }
        
        return false;
    }
    
    /**
     * Search parts using a user-defined comparison function.
     * The user-defined function must have the following signature:
     * bool function comparisonFunctionName(Odimail_Message_part $part)
     * 
     * @link http://www.php.net/manual/en/language.pseudo-types.php#language.types.callback
     * @param callback $callback
     * @param int $maxResults
     * @return array
     */
    public function searchParts($callback, $maxResults = 0) 
    {
        if (false == is_callable($callback)) {
            throw new Exception('Invalid callback');
        }
        
        $this->_searchResults = array();
        $this->_maxResults = (int) $maxResults;
        $this->_recursiveSearch($this, $callback);
        
        return $this->_searchResults;
    }
    
    /**
     * 
     * @param Odimail_Message_Part $part
     * @param callback $callback
     * @return void
     */
    protected function _recursiveSearch($part, $callback) 
    {
        if ($this->_maxResults > 0 && count($this->_searchResults) > $this->_maxResults) {
            return true;
        }
        
        if (false == ($part instanceof Odimail_Message_Part)) {
            throw new Exception('$part is not a valid Odimail_Message_Part object');
        }
        
        if (is_callable($callback)) {
            if (call_user_func($callback, $part)) {
                $this->_searchResults[] = $part;
            }
            
            $partsCount = $part->countParts();
            for ($i = 1; $i <= $partsCount; $i++) {
                $this->_recursiveSearch($part->getPart($i), $callback);
            }
        } 
        
        return false;
    }
    
    /**
     * Returns all attached and embedded files.
     * If it has the parameters "filename" and "name" then it's an attached file
     * If it only has the parameter "name" then it's an embedded file.
     * Note. If you only need the attached files, you can use the function 
     * hasParameter to verify the existence of the parameter "filename"
     * 
     * @return array
     */
    public function getAttachments()
    {
        $this->_searchResults = array();
        $this->_maxResults = 0;
        $this->_recursiveSearch($this, 'Odimail_Message_Part::isFile');
        return $this->_searchResults;
    }
    
    /**
     * Return true if $part is a file
     * 
     * @param Odimail_Message_Part $part
     * @return bool
     */
    static function isFile($part)
    {
        return $part->hasParameter('filename') || $part->hasParameter('name');
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
     * Gets an array with all parameters
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
     * Return an object with the same structure as the object 
     * returned by the imap_fetchstructure function
     * 
     * @link http://www.php.net/manual/en/function.imap-fetchstructure.php
     * @return object
     */
    public function getStructure()
    {
        return $this->_structure;
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
     * Write the content to a file
     * 
     * @param string $path
     * @return int
     */
    public function save($path)
    {
        try {
            return file_put_contents($path, $this->getContent());
        } catch (Exception $ex) {
            return false;
        }
    }
    
}
