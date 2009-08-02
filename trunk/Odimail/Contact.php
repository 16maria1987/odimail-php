<?php

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
        
        if (is_array($config)) {
            if (isset($config['name'])) {
                $this->_name = $config['name'];
            }
            
            if (isset($config['email'])) {
                $this->_name = $config['email'];
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