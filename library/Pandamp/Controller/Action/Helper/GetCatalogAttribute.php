<?php

/**
 * Description of GetCatalogAttribute
 *
 * @author nihki <nihki@madaniyah.com>
 */
class Pandamp_Controller_Action_Helper_GetCatalogAttribute
{
    public function getCatalogAttribute($catalogGuid, $value)
    {
        $rowset = App_Model_Show_Catalog::show()->getCatalogByGuid($catalogGuid);
        if ($rowset) {
            $attr = App_Model_Show_CatalogAttribute::show()->getCatalogAttributeValue($rowset['guid'],$value);

            if(isset($attr) && !empty($attr))
                    return $attr;
            else
                    return 'No-Title';


        }
    }
    
}
