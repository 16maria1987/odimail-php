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

class Odimail_Contact 
{
    
    /**
     * Email address
     * 
     * @var string
     */
    protected $_email = '';
    
    /**
     * Name
     * 
     * @var string
     */
    protected $_name = '';
    
    /**
     * 
     * @param mixed $config 
     * @return unknown_type
     */
    public function __construct($config)
    {
        if (is_object($config) && isset($config->mailbox)){
            $email = $config->mailbox . '@' . $config->host;
            $name  = (isset($config->personal)) ? $config->personal : '';
            
            $this->_name  = $name;
            $this->_email = $email;
        } 
        
        if (is_array($config) && isset($config['email'])) {
            $this->_email = $config['email'];
            
            if (isset($config['name'])) {
                $this->_name = $config['name'];
            }
            
        }
    }
    
    /**
     * Gets the name
     * 
     * @return string
     */
    public function getName()
    {
        return $this->_name;
    }
    
    /**
     * Gets the email address
     * 
     * @return string
     */
    public function getEmail()
    {
        return $this->_email;    
    }
    
}