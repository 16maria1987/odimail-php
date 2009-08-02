<?php

class Odimail_Message_Attachment extends Odimail_Message_Part
{
    
    
    
    public function __construct()
    {
        
    }
    
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