<?php

/**
 * Description of CatalogFolder
 *
 * @author nihki <nihki@madaniyah.com>
 */
class App_Model_Db_Table_CatalogFolder extends Zend_Db_Table_Abstract
{
    protected $_name = 'KutuCatalogFolder';
    protected $_referenceMap    = array(
        'Catalog' => array(
            'columns'           => 'catalogGuid',
            'refTableClass'     => 'App_Model_Db_Table_Catalog',
            'refColumns'        => 'guid'
        ),
        'Folder' => array(
            'columns'           => 'folderGuid',
            'refTableClass'     => 'App_Model_Db_Table_Folder',
            'refColumns'        => 'guid'
        )
    );

}
