<?php

class Pandamp_Controller_Action_Helper_Cdn extends Zend_View_Helper_Abstract 
{
    static $_types = array(
        'default' => '',
        'images'  => 'http://static.hukumonline.com/frontend/default/images',                
        'styles'  => 'http://static.hukumonline.com/frontend/default/css',
        'scripts' => 'http://js.hukumonline.com',
        'rim' 	  => 'http://images.hukumonline.com'
    );
    
    static function setTypes($types)        
    {
		self::$_types = $types;
    }
    
    public function cdn($type = 'default')        
    {
        if (!isset(self::$_types[$type])) {
			throw new Exception('No CDN set for resource type ' . $type);
        }
        return self::$_types[$type];
    }
	
}
