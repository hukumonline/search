<?php
class Pandamp_Application_Resource_Indexing extends Zend_Application_Resource_ResourceAbstract
{
    public function init()
    {
        $sReturn = "http://".$_SERVER['SERVER_NAME'].$_SERVER['REQUEST_URI'];

        $options = array_change_key_case($this->getOptions(), CASE_LOWER);

        switch (strtolower($options['adapter']))
        {
            case 'solr':
                $aWrite = $options['solr']['write'];

                $solrHost = $aWrite['host'];
                $solrPort = $aWrite['port'];
                if (strpos($sReturn,"id"))
                    $solrHomeDir = $aWrite['dir1'];
                else
                    $solrHomeDir = $aWrite['dir2'];

                Zend_Registry::set("Solr_WriteDir", $solrHost.':'.$solrPort.$solrHomeDir);

                $newAdapter = new Pandamp_Search_Adapter_Solr($solrHost, $solrPort, $solrHomeDir);

                $newAdapter->setExtractor('pdf', $options['extractor']['pdf']);
                $newAdapter->setExtractor('word', $options['extractor']['word']);

                return $newAdapter;
                break;
            case 'zendlucene':

                $config = $options['zendlucene'];

                if(isset($config['dir']))
                    $luceneHomeDir = $config['dir'];
                else
                    $luceneHomeDir = null;

                    $newAdapter = new Pandamp_Search_Adapter_ZendLucene($luceneHomeDir);

                    $newAdapter->setExtractor('pdf', $options['extractor']['pdf']);
                    $newAdapter->setExtractor('word', $options['extractor']['word']);

                return $newAdapter;
                break;
                default:
                    throw new Zend_Exception('Indexing adapter: '.$options['adapter']. ' is not supported.');
        }
    }
}