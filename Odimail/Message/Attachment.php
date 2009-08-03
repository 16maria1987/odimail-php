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

class Odimail_Message_Attachment extends Odimail_Message_Part
{
    
    /**
     * Gets the filename of the attachment
     * 
     * @return string
     */
    public function getFileName() 
    {
        if ($this->_structure->dparameters[0]->attribute == 'filename') {
            return $this->_structure->dparameters[0]->value;
        }
    }
    
    /**
     * Save the attachment to disk
     * 
     * @param string $path
     * @return bool
     */
    public function save($path)
    {
        
    }
    
    
}