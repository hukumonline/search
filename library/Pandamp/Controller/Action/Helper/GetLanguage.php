<?php

/**
 * Description of GetLanguage
 *
 * @author nihki <nihki@madaniyah.com>
 */
class Pandamp_Controller_Action_Helper_GetLanguage
{
    public function getLanguage()
    {
        $zl = Zend_Registry::get('Zend_Locale');

        return $zl->getLanguage();
    }
}
