<?php

class Dms_SearchController extends Zend_Controller_Action 
{
	protected $_status;
	
	function preDispatch()
	{
		$this->_helper->layout->setLayout('layout-search-result');
		
        $zl = Zend_Registry::get('Zend_Locale');

        if ($zl->getLanguage() == "id")
        	$this->_status = "status:99";
        else 
        	$this->_status = "status:1";
        
	}
	function advancedAction()
	{
        $sReturn = "http://".$_SERVER['SERVER_NAME'].$_SERVER['REQUEST_URI'];
        $sReturn = base64_encode($sReturn);

		$identity = Pandamp_Application::getResource('identity');
		$loginUrl = $identity->loginUrl;
		$logoutUrl = $identity->logoutUrl;
		$signUp = $identity->signUp;
		$profile = $identity->profile;
		
		$this->view->loginUrl = $loginUrl.'?returnTo='.$sReturn;
		$this->view->logoutUrl = $logoutUrl.'/'.$sReturn;
		$this->view->signUp = $signUp;
		$this->view->profile = $profile;
		
        if ($this->getRequest()->isPost())
        {
            $value = $this->getRequest()->getPost();
            $this->_forward("advpost", "search", "dms", array('value'=>$value));
        }
	}
	function advpostAction()
	{
        $time_start = microtime(true);

        $r = $this->getRequest();

        $searchQuery = ($r->getParam('as_q'))? $r->getParam('as_q') : '';
        $as_epq = $r->getParam('as_epq');
        $as_oq = $r->getParam('as_oq');
        $as_eq = $r->getParam('as_eq');
        $number = ($r->getParam('nomor'))? $r->getParam('nomor') : '';
        $year = ($r->getParam('tahun'))? $r->getParam('tahun') : '';
        $regulationType = ($r->getParam('jenis_peraturan'))? $r->getParam('jenis_peraturan') : '';
        $lembaga_peradilan = ($r->getParam('lembaga_peradilan'))? $r->getParam('lembaga_peradilan') : '';
        $hakim = ($r->getParam('hakim'))? $r->getParam('hakim') : '';
        $pihak = ($r->getParam('pihak'))? $r->getParam('pihak') : '';
        $pengacara = ($r->getParam('pengacara'))? $r->getParam('pengacara') : '';

        $advQuery = array();
        
        if (isset($as_epq))
        {
            array_push($advQuery, $as_epq);
        }
        if ($as_oq)
        {
            array_push($advQuery, "+".$as_oq);
        }
        if ($as_eq)
        {
            array_push($advQuery, "-".$as_eq);
        }
        if ($number)
        {
            array_push($advQuery, "number:".$number);
        }
        if ($year)
        {
            array_push($advQuery, "year:".$year);
        }
        if ($regulationType)
        {
            array_push($advQuery, "regulationOrder:".$regulationType);
        }
        if ($lembaga_peradilan)
        {
            array_push($advQuery, "regulationOrder:".$lembaga_peradilan);
        }
        if ($hakim)
        {
            array_push($advQuery, "all:".$hakim);
        }
        if ($pihak)
        {
            array_push($advQuery, "all:".$pihak);
        }
        if ($pengacara)
        {
            array_push($advQuery, "all:".$pengacara);
        }

        for ($i=0;$i<count($advQuery);$i++)
        {
            if ($searchQuery != "") { $searchQuery.= " "; }
            $searchQuery .= $advQuery[$i];
        }

        $queries = array(
            $searchQuery
        );
        
        foreach ($queries as $query)
        {
            $indexingEngine = Pandamp_Search::manager();
            if(empty($query))
            {
                $hits = $indexingEngine->find("fjkslfjdkfjls",0, 1);
            } else {

                if ($category)
                {
                    $querySolr = $query.' -profile:kutu_doc profile:'.$category;
                }
                else
                {
                    //$querySolr = $query.' -profile:kutu_doc '.$this->_status.';publishedDate desc';
                    $querySolr = $query.' -profile:kutu_doc';
                }

                $hits = $indexingEngine->find($querySolr,0, 1);
            }

        }


        $solrNumFound = $hits->response->numFound;

        $a = array();

        $a['query'] = $query;

        $a['totalCount'] = $solrNumFound;
        $a['limit'] = 20;

        $ii=0;

        if($a['totalCount']==0)
        {
            $a['catalogs'][0]['guid']= 'XXX';
            $a['catalogs'][0]['title']= "No Data";
            $a['catalogs'][0]['subTitle']= "";
            $a['catalogs'][0]['createdDate']= '';
            $a['catalogs'][0]['modifiedDate']= '';
        }


        $this->view->aData = $a;
        $this->view->hits = $hits;

        $this->view->query = $query;
        
        $time_end = microtime(true);
        $time = $time_end - $time_start;

        $this->view->time = round($time,2);
	}
	function resultAction()
	{
        $time_start = microtime(true);

        $r = $this->getRequest();

        $query = ($r->getParam('keyword'))? $r->getParam('keyword') : '';
        $category = ($r->getParam('category'))? $r->getParam('category') : '';

        $indexingEngine = Pandamp_Search::manager();
        if(empty($query))
        {
            $hits = $indexingEngine->find("fjkslfjdkfjls",0, 1);
        } else {

            if ($category)
            {
                //$querySolr = $query.' -profile:kutu_doc '.$this->_status.' profile:'.$category.';publishedDate desc';
                $querySolr = $query.' -profile:kutu_doc profile:'.$category;
            }
            else
            {
                //$querySolr = $query.' -profile:kutu_doc '.$this->_status.';publishedDate desc';
                $querySolr = $query.' -profile:kutu_doc';
            }

            $hits = $indexingEngine->find($querySolr,0, 1);
        }

        $solrNumFound = $hits->response->numFound;

        $a = array();

        $a['query'] = $query;

        $a['totalCount'] = $solrNumFound;
        $a['limit'] = 20;

        $ii=0;

        if($a['totalCount']==0)
        {
            $a['catalogs'][0]['guid']= 'XXX';
            $a['catalogs'][0]['title']= "No Data";
            $a['catalogs'][0]['subTitle']= "";
            $a['catalogs'][0]['createdDate']= '';
            $a['catalogs'][0]['modifiedDate']= '';
        }


        $this->view->aData = $a;
        $this->view->hits = $hits;

        $this->_helper->layout()->searchQuery = $query;

        switch ($category)
        {
            case "kutu_peraturan":
            case "kutu_putusan":
            case "kutu_rancangan_peraturan":
            case "kutu_peraturan_kolonial":
                $ct = "(kutu_peraturan OR kutu_peraturan_kolonial OR kutu_rancangan_peraturan OR kutu_putusan)";
                break;
            case "isuhangat":
                $ct = "article";
                break;
            default :
                $ct = $category;
                break;
        }

        $this->_helper->layout()->categorySearchQuery = $ct;
        $this->view->query = $query;
        $this->view->ct = $category;

        $time_end = microtime(true);
        $time = $time_end - $time_start;

        $this->view->time = round($time,2);
	}
}