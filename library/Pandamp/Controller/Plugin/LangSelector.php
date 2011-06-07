<?php

/**
 * Description of LangSelector
 *
 * @author nihki <nihki@madaniyah.com>
 */
class Pandamp_Controller_Plugin_LangSelector
    extends Zend_Controller_Plugin_Abstract
{
    public function  preDispatch(Zend_Controller_Request_Abstract $request) {
        $lang = $request->getParam('lang','');
   
        if ($lang !== 'id' && $lang !== 'en') 
            $lang = 'id';
        
        $zl = new Zend_Locale();
        $zl->setLocale($lang);
        Zend_Registry::set('Zend_Locale', $zl);
    }
}
