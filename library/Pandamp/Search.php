<?php

class Pandamp_Search
{
	public static function factory($adapter, $config = array())
    {
    	switch (strtolower($adapter)) 
    	{
    		case 'solr':
    			$solrHost = $config['host'];
    			$solrPort = $config['port'];
    			$solrHomeDir = $config['dir'];
    			$newAdapter = new Pandamp_Search_Adapter_Solr($solrHost, $solrPort, $solrHomeDir);
    			
    			return $newAdapter;
    			break;
    	}
    	return false;
    }
	
	public static function manager()
	{
		$registry = Zend_Registry::getInstance(); 
		$application = $registry->get(Pandamp_Keys::REGISTRY_APP_OBJECT);
		
		$application->getBootstrap()->bootstrap('indexing');
		
	   	return $application->getBootstrap()->getResource('indexing');
	}
}

?>