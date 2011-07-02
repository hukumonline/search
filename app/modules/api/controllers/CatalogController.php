<?php
class Api_CatalogController extends Zend_Controller_Action 
{
    public function getcatalogsearchAction()
    {
        $this->_helper->layout()->disableLayout();
        $this->_helper->viewRenderer->setNoRender(TRUE);
        
        $r = $this->getRequest();

        $query = ($r->getParam('query'))? $r->getParam('query') : '';
        $category = ($r->getParam('ct'))? $r->getParam('ct') : '';
        $start = ($r->getParam('start'))? $r->getParam('start') : 0;
        $limit = ($r->getParam('limit'))? $r->getParam('limit'): 25;
        $orderBy = ($r->getParam('orderBy'))? $r->getParam('sortBy') : 'publishedDate';
        $sortOrder = ($r->getParam('sortOrder'))? $r->getParam('sortOrder') : ' desc';

        $a = array();

        $a['query']	= $query;

    	$registry = Zend_Registry::getInstance();
    	$config = $registry->get(Pandamp_Keys::REGISTRY_APP_OBJECT);
    	$remoteSearchIn = $config->getOption('in');
    	$remoteSearchEn = $config->getOption('en');
    	
        $zl = Zend_Registry::get('Zend_Locale');

        if ($zl->getLanguage() == "id") 
        	$status = "status:99";
        else 
        	$status = "status:1";
        
        $indexingEngine = Pandamp_Search::manager();
        
        if(empty($query))
        {
            $hits = $indexingEngine->find("fjkslfjdkfjls",0, 1);
            
        } else {
            
            if ($category)
            {
                //$querySolr = $query.' -profile:kutu_doc profile:'.$category.';'.$orderBy.$sortOrder;
                $querySolr = $query.' -profile:kutu_doc profile:'.$category;
            }
            else
            {
                //$querySolr = $query.' -profile:kutu_doc '.$status.';'.$orderBy.$sortOrder;
                $querySolr = $query.' -profile:kutu_doc';
            }

            $hits = $indexingEngine->find($querySolr, $start, $limit);
        }

        $num = $hits->response->numFound;

        //$solrNumFound = count($hits->response->docs);
        $solrNumFound = $num;

        $ii=0;
        if($solrNumFound==0)
        {
            $a['catalogs'][0]['guid']= 'XXX';
            $a['catalogs'][0]['title']= "No Data";
            $a['catalogs'][0]['subTitle']= "";
            $a['catalogs'][0]['createdDate']= '';
            $a['catalogs'][0]['modifiedDate']= '';
        }
        else
        {
            if($solrNumFound>$limit)
                $numRowset = $limit ;
            else
                $numRowset = $solrNumFound;

            for($ii=0;$ii<$numRowset;$ii++)
            {
            	if (isset($hits->response->docs[$ii])) {
                $row = $hits->response->docs[$ii];
                if(!empty($row))
                {
                    if ($row->profile == "klinik")
                    {
                        if (isset($hits->highlighting->{$row->id}->title[0]))
                        {
                            if (isset($hits->highlighting->{$row->id}->kategori[0]))
                                $title = "[<b><font color='#FFAD29'>".$hits->highlighting->{$row->id}->kategori[0]."</font></b>]&nbsp;<a href='".$remoteSearchIn['website']."/klinik/detail/".$row->id."' class='searchlink'>".$hits->highlighting->{$row->id}->title[0]."</a>";
                            else
                                $title = "[<b><font color='#FFAD29'>".$row->kategori."</font></b>]&nbsp;<a href='".$remoteSearchIn['website']."/klinik/detail/".$row->id."' class='searchlink'>".$hits->highlighting->{$row->id}->title[0]."</a>";

                        } else {
                            
                            if (isset($hits->highlighting->{$row->id}->kategori[0]))
                                $title = "[<b><font color='#FFAD29'>".$hits->highlighting->{$row->id}->kategori[0]."</font></b>]&nbsp;<a href='".$remoteSearchIn['website']."/klinik/detail/".$row->id."' class='searchlink'><b>$row->title</b></a>";
                            else
                                $title = "[<b><font color='#FFAD29'>".$row->kategori."</font></b>]&nbsp;<a href='".$remoteSearchIn['website']."/klinik/detail/".$row->id."' class='searchlink'><b>$row->title</b></a>";
                            
                        }

                        if (isset($hits->highlighting->{$row->id}->commentQuestion[0]))
                            $description = ($row->commentQuestion)? "<u>Pertanyaan</u>: ".$hits->highlighting->{$row->id}->commentQuestion[0] : '';
                        else
                            $description = ($row->commentQuestion)? "<u>Pertanyaan</u>: ".$row->commentQuestion : '';

                    }
                    else if ($row->profile == "article")
                    {
                    	if ($zl->getLanguage() == "id") {
	                        if (isset($hits->highlighting->{$row->id}->title[0]))
	                            $title = "<a href='".$remoteSearchIn['website']."/berita/baca/".$row->id."/".$row->shortTitle."' class='searchlink'><b>".$hits->highlighting->{$row->id}->title[0]."</b></a>";
	                        else
	                            $title = "<a href='".$remoteSearchIn['website']."/berita/baca/".$row->id."/".$row->shortTitle."' class='searchlink'><b>$row->title</b></a>";

                    	}
                    	else 
                    	{
                    		if (isset($row->shortTitle)) {
                    			$st = "/".$row->shortTitle;	
                    		}
                    		else 
                    		{
                    			$st = "";
                    		}
                    		
                    		
                    		
	                        if (isset($hits->highlighting->{$row->id}->title[0]))
	                            $title = "<a href='".$remoteSearchIn['website']."/pages/".$row->id.$st."' class='searchlink'><b>".$hits->highlighting->{$row->id}->title[0]."</b></a>";
	                        else
	                            $title = "<a href='".$remoteSearchEn['website']."/pages/".$row->id.$st."' class='searchlink'><b>$row->title</b></a>";	
	                            
                    		
                    		
                    	}
                        
                        $description = (isset($row->description))? $row->description : '';
                    }
                    else if ($row->profile == "kutu_peraturan" || $row->profile == "kutu_peraturan_kolonial" || $row->profile == "kutu_rancangan_peraturan" || $row->profile == "kutu_putusan")
                    {
                        $tblCatalogFolder = new App_Model_Db_Table_CatalogFolder();
                        $rowsetCatalogFolder = $tblCatalogFolder->fetchRow("catalogGuid='$row->id'");
                        if ($rowsetCatalogFolder)
                            $parentGuid= $rowsetCatalogFolder->folderGuid;
                        else
                            $parentGuid='';

                        $node = $this->getNode($parentGuid);

                        if (isset($hits->highlighting->{$row->id}->title[0]))
                            $title = "<a href='".$remoteSearchIn['website']."/pusatdata/detail/".$row->id."/".$node."/".$parentGuid."' class='searchlink'><b>".$hits->highlighting->{$row->id}->title[0]."</b></a>";
                        else
                            $title = "<a href='".$remoteSearchIn['website']."/pusatdata/detail/".$row->id."/".$node."/".$parentGuid."' class='searchlink'><b>$row->title</b></a>";


                        $description = (isset($row->description))? $row->description : '';
                    }
                    else
                    {
                        //$title = (isset($row->title))? $row->title : '';
                        if (isset($row->title)) {
                        	
                        	if ($zl->getLanguage() == "id") {
                        		$title = "<a href='".ROOT_URL."' class='searchlink'><b>$row->title</b></a>";
                        	}
                        	else 
                        	{
                        		if (isset($row->shortTitle)) {
                        			$st = "/".$row->shortTitle;	
                        		}
                        		else 
                        		{
                        			$st = "";
                        		}
                        		
                        		$title = "<a href='".$remoteSearchEn['website']."/pages/".$row->id.$st."' class='searchlink'><b>$row->title</b></a>";	
                        	}
                        	
                        } else {
							$title = "";                        	
                        }
                        	
                        $description = (isset($row->description))? $row->description : '';
                    }


                    if (isset($hits->highlighting->{$row->id}->subTitle[0]))
                        $subTitle = (isset($row->subTitle))? "<span class='subjudul'>".$hits->highlighting->{$row->id}->subTitle[0]."</span><br>" : '';
                    else
                        $subTitle = (isset($row->subTitle))? "<span class='subjudul'>".$row->subTitle."</span><br>" : '';


                    $profileSolr = str_replace("kutu_", "", $row->profile);
                    $profileSolr = str_replace("clinic_category","clinic",$profileSolr);
                    
	                $profileSolr = str_replace("financial_services","financial services",$profileSolr);
	                $profileSolr = str_replace("general_corporate","general corporate",$profileSolr);
	                $profileSolr = str_replace("oil_and_gas","oil and gas",$profileSolr);
	                $profileSolr = str_replace("executive_alert","executive alert",$profileSolr);
	                $profileSolr = str_replace("manufacturing_&_industry","manufacturing & industry",$profileSolr);
	                $profileSolr = str_replace("consumer_goods","consumer goods",$profileSolr);
	                $profileSolr = str_replace("telecommunications_and_media","telecommunications and media",$profileSolr);
	                $profileSolr = str_replace("executive_summary","executive summary",$profileSolr);
	                $profileSolr = str_replace("hot_news","hot news",$profileSolr);
	                $profileSolr = str_replace("hot_issue_ile","hot issue ILE",$profileSolr);
	                $profileSolr = str_replace("hot_issue_ild","hot issue ILD",$profileSolr);
	                $profileSolr = str_replace("hot_issue_ilb","hot issue ILB",$profileSolr);
	                
                    $a['catalogs'][$ii]['profile']= $profileSolr;
                    $a['catalogs'][$ii]['title']= ($title)? $title : '';
                    $a['catalogs'][$ii]['guid']= $row->id;
                    $a['catalogs'][$ii]['score']= "score:".round($row->score,4);

                    $a['catalogs'][$ii]['description'] = $description;
                    $a['catalogs'][$ii]['subTitle']= (isset($subTitle))? $subTitle : '';


                    $array_hari = array(1=>"Senin","Selasa","Rabu","Kamis","Jumat","Sabtu","Minggu");
                    $hari = $array_hari[date("N",strtotime($row->publishedDate))];

                    $a['catalogs'][$ii]['publishedDate']= $hari . ', '. date("d F Y",strtotime($row->publishedDate));
                }
            	}
            }
        }

        echo Zend_Json::encode($a);
    }
    function getNode($node)
    {
        $tblFolder = new App_Model_Db_Table_Folder();
        $rowFolder = $tblFolder->find($node)->current();
        if ($rowFolder) {
            $path = explode("/",$rowFolder->path);
            $rpath = $path[0];
            $rowFolder1 = $tblFolder->find($rpath)->current();
            if ($rowFolder1) {
                $rowFolder2 = $tblFolder->find($rowFolder1->parentGuid)->current();
                if ($rowFolder2) {
                    if ($rowFolder2->title == "Peraturan") {
                        return "nprt";
                    }
                    elseif ($rowFolder2->title == "Putusan") {
                        return "npts";
                    }
                    else
                    {
                        return "node";
                    }
                }
                else
                {
                    return "node";
                }
            }
            else
            {
                    return "node";
            }
        }
        else
        {
            return "node";
        }
    }
}