<?php

class Dms_Widgets_SearchController extends Zend_Controller_Action 
{
	protected $_status;
	
	function preDispatch()
	{
        $zl = Zend_Registry::get('Zend_Locale');

        if ($zl->getLanguage() == "id")
        	$this->_status = "status:99";
        else 
        	$this->_status = "status:1";
        
		
	}
    function fclinicAction()
    {
        $r = $this->getRequest();

        $query = ($r->getParam('query'))? $r->getParam('query') : '';
        $category = ($r->getParam('ct'))? $r->getParam('ct') : '';

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


        $content = 0;
        $data = array();

        foreach($hits->facet_counts->facet_fields->kategoriklinik as $facet => $count)
        {
            if ($count == 0)
            {
                continue;
            }
            else
            {
                $data[$content][0] = $facet;
                $data[$content][1] = $count;
            }

            $content++;
        }

        $this->view->aData = $data;
        $this->view->query = $query;
    }
    function fprofileAction()
    {
        $r = $this->getRequest();

        $query = ($r->getParam('query'))? $r->getParam('query') : '';
        $category = ($r->getParam('ct'))? $r->getParam('ct') : '';

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
                $querySolr = $query.' -profile:kutu_doc';
            }

            $hits = $indexingEngine->find($querySolr,0, 1);
        }


        $content = 0;
        $data = array();

        foreach($hits->facet_counts->facet_fields->profile as $facet => $count)
        {
            if ($count == 0 || in_array($facet, array('comment','partner','kategoriklinik','kutu_signup')))
            {
                continue;
            }
            else
            {
                $f = str_replace(array('kutu_'), "", $facet);
                $f = str_replace("financial_services","financial services",$f);
                $f = str_replace("general_corporate","general corporate",$f);
                $f = str_replace("oil_and_gas","oil and gas",$f);
                $f = str_replace("executive_alert","executive alert",$f);
                $f = str_replace("manufacturing_&_industry","manufacturing & industry",$f);
                $f = str_replace("consumer_goods","consumer goods",$f);
                $f = str_replace("telecommunications_and_media","telecommunications and media",$f);
                $f = str_replace("executive_summary","executive summary",$f);
                $f = str_replace("hot_news","hot news",$f);
                $f = str_replace("hot_issue_ile","hot issue ILE",$f);
                $f = str_replace("hot_issue_ild","hot issue ILD",$f);
                $f = str_replace("hot_issue_ilb","hot issue ILB",$f);
                $f = str_replace("clinic_category","clinic",$f);
                $data[$content][0] = $f;
                $data[$content][1] = $count;
                $data[$content][3] = $facet;
            }

            $content++;
        }

        $this->view->aData = $data;
        $this->view->query = $query;
    }
    function fholdAction()
    {
        $r = $this->getRequest();

        $query = ($r->getParam('query'))? $r->getParam('query') : '';
        $category = ($r->getParam('ct'))? $r->getParam('ct') : '';

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


        $content = 0;
        $data = array();

        foreach($hits->facet_counts->facet_fields->regulationType as $facet => $count)
        {
            if ($count == 0)
            {
                continue;
            }
            else
            {
                $data[$content][0] = $facet;
                $data[$content][1] = $count;
            }

            $content++;
        }

        $this->view->aData = $data;
        $this->view->query = $query;
    }	
}