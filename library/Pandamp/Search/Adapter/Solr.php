<?php

/**
 * module search for application
 * 
 * @author Himawan Anindya Putra <putra@langit.biz>
 * @author Nihki Prihadi <nihki@hukumonline.com>
 * @package Kutu
 * 
 */

require_once( 'Apache/Solr/Service.php' );

class Pandamp_Search_Adapter_Solr extends Pandamp_Search_Adapter_Abstract  
{
	private $_index;
	private $_solr;
	private $_registry;
	private $_pdfExtractor;
	private $_wordExtractor;
	
	private $_conn;

        private $_return;




        public function __construct($solrHost, $solrPort, $solrHomeDir)
	{
            $this->_solr = new Apache_Solr_Service( $solrHost, $solrPort, $solrHomeDir );

            $this->_return = "http://".$_SERVER['SERVER_NAME'].$_SERVER['REQUEST_URI'];

            if (strpos($this->_return,"id")) {
                $this->_conn = Zend_Registry::get('db1');
            } else if (strpos($this->_return,"en")) {
                $this->_conn = Zend_Registry::get('db3');
            }
            else
            {
                $this->_conn = Zend_Db_Table_Abstract::getDefaultAdapter();
            }

	}
	public function setExtractor($type, $extractor)
	{
		switch (strtolower($type))
		{
			case 'pdf':
				$this->_pdfExtractor = $extractor;
				break;
			case 'word':
				$this->_wordExtractor = $extractor;
				break;
		}
	}
	public function indexCatalog($guid)
	{
		$solr = &$this->_solr;
		
		$tbl = new App_Model_Db_Table_Catalog();
		
		$rowset = $tbl->find($guid);
		if(count($rowset))
		{
			$row = $rowset->current();
			
			$documents = array();
			
			$documents[] = $this->_createSolrDocument($row);
			
			try {
				$solr->addDocuments( $documents );
				$solr->commit();
			}
			catch ( Exception $e ) {
				throw new Zend_Exception($e->getMessage());
			}
		}
	}
	public function reIndexCatalog()
	{
		gc_enable();
		$this->emptyIndex();
		
		$time_start = microtime(true);
		
		$solr = &$this->_solr;
		
//		$formater = new Pandamp_Lib_Formater();
		
		$title = new Pandamp_Controller_Action_Helper_GetCatalogTitle();
		
//		$tbl = new Pandamp_Core_Orm_Table_Catalog();
//		$rowset = $tbl->fetchAll(); //("profileGuid='Pandamp_peraturan'");
		
//                if (strpos($this->_return,"id")) {
//                    $query="SELECT * FROM KutuCatalog WHERE profileGuid NOT IN ('about_us','kutu_contact','kutu_email','kutu_kotik','kutu_mitra','kutu_signup','kategoriklinik','comment','partner','author')";
//                } else if (strpos($this->_return,"en")) {
//                    $query="SELECT * FROM KutuCatalog WHERE profileGuid IN ('article','consumer_goods','executive_alert','executive_summary','financial_services','general_corporate','hotile','hot_issue_ilb','hot_issue_ild','hot_news','ilb','ild','ile','manufacturing_&_industry','news','oil_and_gas','telecommunications_and_media')";
//                }

                $query="SELECT * FROM KutuCatalog";
		
		$results = $this->_conn->query($query);
		
		$rowset = $results->fetchAll(PDO::FETCH_OBJ);
		  
		$documents = array();
		$rowCount = count($rowset);
		for($iCount=0;$iCount<$rowCount;$iCount++)
		{
			$row = $rowset[$iCount];
//			if($iCount == 100)
//				break;
//			echo 'urutan: '.$iCount .'<br>';
			$nextRow = $rowset[$iCount+1];
//			echo 'current guid: '.$row->guid.'  '.'next guid: '.$nextRow->guid.'<br>';
			
			if ($row->modifiedBy !== $row->createdBy)
				$modified = '<font color=red>[modifiedBy:<i>'.$row->modifiedBy.'</i>]</font>&nbsp;';
			else
				$modified = ''; 
			
			echo '<li><span style="font:11px verdana,arial,helvetica,sans-serif;">[urutan:'.$iCount.']&nbsp;indexing:<font color=green>'.$title->getCatalogTitle($row->guid,'fixedTitle').'</font>[current guid: '.$row->guid.'  '.'next guid: '.$nextRow->guid.'][author:<i>'.$row->createdBy.'</i>]&nbsp;'.$modified.'[createdDate:<i>'.$row->createdDate.'</i>]</span></li>';
			
		  	$documents[] = $this->_createSolrDocument($row);
		  	
  			if($iCount%500 == 0)
		  	{
			  	try 
			  	{
					$solr->addDocuments( $documents );
					$solr->commit();
//					$solr->optimize();
					$documents = array();
				}
				catch ( Exception $e ) 
				{
//					echo "Error occured when processing record starting from number: ". ($iCount - 1000) . ' to '.$iCount.' '.$row->guid;
					//$log->err($e->getMessage());
					throw new Zend_Exception($e->getMessage());
//					echo $e->getMessage().'<br>';

				}
		  	}
		  	flush();
		}
		
		echo '</ul></div></td></tr></table>';
		  
		try {
			$solr->addDocuments( $documents );
			$solr->commit();
			//$solr->optimize();
		}
		catch ( Exception $e ) {
			//$log->err($e->getMessage());
			throw new Zend_Exception($e->getMessage());
//			echo $e->getMessage().'<br>';
		}
		
		$time_end = microtime(true);
		$time = $time_end - $time_start;
		
//		echo'<br>WAKTU EKSEKUSI: '. $time;
//		$log->info("WAKTU EKSEKUSI: ". $time." indexing catalog ".$iCount." dari ".$rowCount ." ".$username);
		
		echo'<br><br><span style="font:11px verdana,arial,helvetica,sans-serif;color:#00FF00">WAKTU EKSEKUSI: '. $time.'<br>indexing catalog '.$iCount.' dari '.$rowCount.'</span>';
		
		// log to assetSetting
                /*
		$tblAssetSetting = new Pandamp_Modules_Dms_Catalog_Model_AssetSetting();
		$rowAsset = $tblAssetSetting->fetchRow("application='INDEX CATALOG'");
		if ($rowAsset)
		{
			$rowAsset->valueText = "Update $rowCount indexing-catalog at ".date("Y-m-d H:i:s").$username;
			$rowAsset->valueInt = $rowAsset->valueInt + 1;			
		}
		else 
		{
			$gman = new Pandamp_Core_Guid();
			$catalogGuid = $gman->generateGuid();
			$rowAsset = $tblAssetSetting->fetchNew();	
			$rowAsset->guid = $catalogGuid;
			$rowAsset->application = "INDEX CATALOG";
			$rowAsset->part = "KUTU";
			$rowAsset->valueType = "INDEX";
			$rowAsset->valueInt = 0;
			$rowAsset->valueText = $rowCount." Indexing catalog at ".date("Y-m-d H:i:s").$username;
		}
		$rowAsset->save();
                 *
                 */
	}
	private function _createSolrDocument(&$row)
	{
		$part = new Apache_Solr_Document();
	  	$part->id = $row->guid;
	  	$part->shortTitle = $row->shortTitle;
	  	$part->profile = $row->profileGuid;
	  	$part->publishedDate = $this->getDateInSolrFormat($row->publishedDate);
	  	$part->expiredDate = $this->getDateInSolrFormat($row->expiredDate);
	  	$part->createdBy = $row->createdBy;
	  	$part->createdDate = $this->getDateInSolrFormat($row->createdDate);
	  	$part->modifiedBy = $row->modifiedBy;
	  	$part->modifiedDate = $this->getDateInSolrFormat($row->modifiedDate);
	  	if(!$row->status==null)
	  		$part->status = $row->status;
	  	$part->url = ''; //TODO what to input here as the URL???
	  	
	  	if ($this->_lang == "id")
	  		$part->serviceId = 'hol';
	  	else 
	  		$part->serviceId = "en";
	  		
	  		
	  	
	  	if ($row->profileGuid == "clinic_category")
	  	{
	  		switch ($row->guid)
	  		{
	  			case "lt48310d3da83db":
  	  				$part->kategori = 'Environmental Law';
  	  				break;
	  			case "lt482c0755f313e":
  	  				$part->kategori = 'Telecommunications Law';
  	  				break;
	  			case "lt482c09820860c":
  	  				$part->kategori = 'Company Law';
  	  				break;
  	  			case "lt482c0a130343d":
  	  				$part->kategori = 'Guarantee and Addiction of Debt';
  	  				break;
  	  			case "lt482c0a7437d49":
  	  				$part->kategori = 'Finance and Banking Law';
  	  				break;
  	  			case "lt48310cf2a8c82":
  	  				$part->kategori = 'Consumer Protection Law';
  	  				break;
  	  			case "lt48310d59db6d8":
  	  				$part->kategori = 'General Law';
  	  				break;
  	  			case "lt482c090b12c96":
  	  				$part->kategori = 'Property Law';
  	  				break;
  	  			case "lt482c09a4e06a6":
  	  				$part->kategori = 'Family Law';
  	  				break;
  	  			case "lt482c0a30d9ace":
  	  				$part->kategori = 'Bankruptcy Law';
  	  				break;
  	  			case "lt48310cb53f93a":
  	  				$part->kategori = 'Employment / Labour Law';
  	  				break;
  	  			case "lt48310d0ddbfcc":
  	  				$part->kategori = 'Mergers and Acquisitions';
  	  				break;
  	  			case "lt482c093dca3fd":
  	  				$part->kategori = 'Human Rights Law';
  	  				break;
  	  			case "lt482c09ca8f83e":
  	  				$part->kategori = 'Criminal Law';
  	  				break;
  	  			case "lt482c0a55ccee6":
  	  				$part->kategori = 'Intellectual Property Law';
  	  				break;
  	  			case "lt48310cd5258aa":
  	  				$part->kategori = 'Agreements';
  	  				break;
	  		}
	  	}
	  	
	  	  
	  	//$rowsetAttr = $row->findDependentRowsetCatalogAttribute();
	  	
	  	$query="SELECT * FROM KutuCatalogAttribute where catalogGuid='".$row->guid."'";
		$results2 = $this->_conn->query($query);
		
		$rowsetAttr = $results2->fetchAll(PDO::FETCH_OBJ);
	  	
	  	$rowCount = count($rowsetAttr);
	  	for($i=0;$i<$rowCount;$i++)
	  	{
	  		$rowAttr = $rowsetAttr[$i];
			switch ($rowAttr->attributeGuid)
			{
				case 'fixedCommentTitle':
	  	  	  	case 'fixedTitle':
	  	  	  		if(empty($rowAttr->value))
	  	  	  		{
	  	  	  			$part->title = $row->shortTitle;
	  	  	  		}
	  	  	  		else 
	  	  	  		{
	  	  	  			$part->title = $rowAttr->value;
	  	  	  		}
//	  	  	  		echo $part->title.'<br>';
	  	  	  		break;
	  	  	  	case 'fixedSubTitle':
	  	  	  		$part->subTitle = $rowAttr->value;
	  	  	  		break;
	  	  	  	case 'fixedContent':
	  	  	  		$part->content = $this->clean_string_input($rowAttr->value);
	  	  	  		break;
	  	  	  	case 'fixedKeywords':
	  	  	  		$part->keywords = $rowAttr->value;
	  	  	  		break;
	  	  	  	case 'fixedDescription':
	  	  	  		$part->description = $rowAttr->value;
	  	  	  		break;
//	  	  	  	case 'fixedMainNews':
//	  	  	  		$part->mainNews = (int)$rowAttr->value;
//	  	  	  		break;
	  	  	  	case 'fixedAuthor':
	  	  	  		$part->author = $rowAttr->value;
	  	  	  		break;
	  	  	  	case 'fixedComments':
	  	  	  		$part->comments = $rowAttr->value;
	  	  	  		break;
	  	  	  	case 'fixedNumber':
	  	  	  	case 'prtNomor':
	  	  	  	case 'ptsNomor':
	  	  	  		$part->number = $rowAttr->value;
	  	  	  		break;
	  	  	  	case 'fixedYear':
	  	  	  	case 'ptsTahun':
	  	  	  	case 'prtTahun':
	  	  	  		$part->year = (int)$rowAttr->value;
	  	  	  		break;
//	  	  	  	case 'fixedDate':
//	  	  	  	case 'prtDisahkan':
//	  	  	  	case 'ptsDibaca':
//	  	  	  		$part->date = $this->getDateInSolrFormat($rowAttr->value);
//	  	  	  		break;
	  	  	  	case 'fixedLanguage':
	  	  	  		$part->language = $rowAttr->value;
	  	  	  		break;
//	  	  	  	case 'fixedCommentTitle':
//	  	  	  		$part->commentTitle = $rowAttr->value;
//	  	  	  		break;
	  	  	  	case 'fixedCommentQuestion':
	  	  	  		$part->commentQuestion = $this->clean_string_input($rowAttr->value);
	  	  	  		break;
	  	  	  	case 'fixedAnswer':
	  	  	  		$part->jawaban = $this->clean_string_input($rowAttr->value);
	  	  	  		break;
//	  	  	  	case 'fixedJudul':
//	  	  	  		$part->judul = $rowAttr->value;
//	  	  	  		break;
//	  	  	  	case 'fixedComment':
//	  	  	  		$part->komentar = $rowAttr->value;
//	  	  	  		break;
	  	  	  	case 'fixedSelectNama':
	  	  	  		$part->kontributor = $rowAttr->value;
	  	  	  		break;
	  	  	  	case 'fixedSelectMitra':
	  	  	  		$part->sumber = $rowAttr->value;
	  	  	  		break;
	  	  	  	case 'fixedKategoriKlinik':
	  	  	  		$part->kategoriklinik = $rowAttr->value;
	  	  	  		switch (strtolower($part->kategoriklinik))
	  	  	  		{
	  	  	  			case 'lt4a0a9ea1c4f76':
	  	  	  				$part->kategori = 'Kenegaraan';
	  	  	  				break;
	  	  	  			case 'lt4a0a9e818ecfb':
	  	  	  				$part->kategori = 'Ilmu Hukum';
	  	  	  				break;
	  	  	  			case 'lt4a0a9e4c78c61':
	  	  	  				$part->kategori = 'Perlindungan Konsumen';
	  	  	  				break;
	  	  	  			case 'lt4a0a9e13a0162':
	  	  	  				$part->kategori = 'Hak Atas Kekayaan Intelektual';
	  	  	  				break;
	  	  	  			case 'lt4a0a9df5142a2':
	  	  	  				$part->kategori = 'Bisnis & Investasi';
	  	  	  				break;
	  	  	  			case 'lt4a0a9db2b4404':
	  	  	  				$part->kategori = 'Buruh & Tenaga Kerja';
	  	  	  				break;
	  	  	  			case 'lt4a0a9d5a0323e':
	  	  	  				$part->kategori = 'Hukum Perdata';
	  	  	  				break;
	  	  	  			case 'lt4a0a9d1c5058a':
	  	  	  				$part->kategori = 'Hukum Keluarga dan Waris';
	  	  	  				break;
	  	  	  			case 'lt4a0a9cf56d663':
	  	  	  				$part->kategori = 'Hukum Pidana';
	  	  	  				break;
	  	  	  			case 'lt4a0a9cb5163c6':
	  	  	  				$part->kategori = 'Hukum Perusahaan';
	  	  	  				break;
	  	  	  			case 'lt4a0a9c81379df':
	  	  	  				$part->kategori = 'Hak Asasi Manusia';
	  	  	  				break;
	  	  	  			case 'lt4a0a830a2938b':
	  	  	  				$part->kategori = 'Telekomunikasi & Teknologi';
	  	  	  				break;
	  	  	  		}
	  	  	  		break;
	  	  	  	case 'prtJenis':
	  	  	  		$part->regulationType = $rowAttr->value;
//	  	  	  		echo 'jenis: '.$rowAttr->value. '<br>';
	  	  	  		//enter the regulation type order. i.e. undang-undang=1, pp=2, dst.
	  	  	  		switch(strtolower($part->regulationType))
	  	  	  		{
	  	  	  			case 'konstitusi':
	  	  	  				$part->regulationOrder = 1;
	  	  	  				break;
	  	  	  			case 'tap mpr':
	  	  	  				$part->regulationOrder = 11;
	  	  	  				break;
	  	  	  			case 'tus mpr':
	  	  	  				$part->regulationOrder = 21;
	  	  	  				break;
	  	  	  			case 'undang-undang':
	  	  	  			case 'uu':
	  	  	  				$part->regulationOrder = 31;
	  	  	  				break;
	  	  	  			case 'undang-undang darurat':
	  	  	  				$part->regulationOrder = 41;
	  	  	  				break;
	  	  	  			case 'perpu':
	  	  	  				$part->regulationOrder = 51;
	  	  	  				break;
	  	  	  			case 'pp':
	  	  	  				$part->regulationOrder = 61;
	  	  	  				break;
	  	  	  			case 'perpres':
	  	  	  				$part->regulationOrder = 71;
	  	  	  				break;
	  	  	  			case 'penpres':
	  	  	  				$part->regulationOrder = 81;
	  	  	  				break;
	  	  	  			case 'keppres':
	  	  	  				$part->regulationOrder = 91;
	  	  	  				break;
	  	  	  			case 'inpres':
	  	  	  				$part->regulationOrder = 101;
	  	  	  				break;
	  	  	  			case 'konvensi internasional':
	  	  	  				$part->regulationOrder = 111;
	  	  	  				break;
	  	  	  			case 'keputusan bersama':
	  	  	  				$part->regulationOrder = 121;
	  	  	  				break;
	  	  	  			case 'keputusan dewan':
	  	  	  				$part->regulationOrder = 131;
	  	  	  				break;
	  	  	  			case 'kepmen':
	  	  	  				$part->regulationOrder = 141;
	  	  	  				break;
	  	  	  			case 'permen':
	  	  	  				$part->regulationOrder = 151;
	  	  	  				break;
	  	  	  			case 'inmen':
	  	  	  				$part->regulationOrder = 161;
	  	  	  				break;
	  	  	  			case 'pengumuman menteri':
	  	  	  				$part->regulationOrder = 171;
	  	  	  				break;
	  	  	  			case 'surat edaran menteri':
	  	  	  				$part->regulationOrder = 181;
	  	  	  				break;
	  	  	  			case 'surat menteri':
	  	  	  				$part->regulationOrder = 191;
	  	  	  				break;
	  	  	  			case 'keputusan asisten menteri':
	  	  	  				$part->regulationOrder = 201;
	  	  	  				break;
	  	  	  			case 'surat asisten menteri':
	  	  	  				$part->regulationOrder = 211;
	  	  	  				break;
	  	  	  			case "keputusan menteri negara/ketua lembaga/badan":
	  	  	  				$part->regulationOrder = 221;
	  	  	  				break;
	  	  	  			case "peraturan menteri negara/ketua lembaga/badan":
	  	  	  				$part->regulationOrder = 231;
	  	  	  				break;
	  	  	  			case "instruksi menteri negara/ketua lembaga/badan":
	  	  	  				$part->regulationOrder = 241;
	  	  	  				break;
	  	  	  			case "pengumuman menteri negara/ketua lembaga/badan":
	  	  	  				$part->regulationOrder = 251;
	  	  	  				break;
	  	  	  			case "surat edaran menteri negara/ketua lembaga/badan":
	  	  	  				$part->regulationOrder = 261;
	  	  	  				break;
	  	  	  			case "surat menteri negara/ketua lembaga/badan":
	  	  	  				$part->regulationOrder = 271;
	  	  	  				break;
	  	  	  			case "keputusan lembaga/badan":
	  	  	  				$part->regulationOrder = 281;
	  	  	  				break;
	  	  	  			case "peraturan lembaga/badan":
	  	  	  				$part->regulationOrder = 291;
	  	  	  				break;
	  	  	  			case "instruksi lembaga/badan":
	  	  	  				$part->regulationOrder = 301;
	  	  	  				break;
	  	  	  			case "pengumuman lembaga/badan":
	  	  	  				$part->regulationOrder = 311;
	  	  	  				break;
	  	  	  			case "surat edaran lembaga/badan":
	  	  	  				$part->regulationOrder = 321;
	  	  	  				break;
	  	  	  			case "surat lembaga/badan":
	  	  	  				$part->regulationOrder = 331;
	  	  	  				break;
	  	  	  			case "keputusan kepala/ketua lembaga/badan":
	  	  	  				$part->regulationOrder = 341;
	  	  	  				break;
	  	  	  			case "peraturan kepala/ketua lembaga/badan":
	  	  	  				$part->regulationOrder = 351;
	  	  	  				break;
	  	  	  			case "instruksi kepala/ketua lembaga/badan":
	  	  	  				$part->regulationOrder = 361;
	  	  	  				break;
	  	  	  			case "pengumuman kepala/ketua lembaga/badan":
	  	  	  				$part->regulationOrder = 371;
	  	  	  				break;
	  	  	  			case "surat edaran kepala/ketua lembaga/badan":
	  	  	  				$part->regulationOrder = 381;
	  	  	  				break;
	  	  	  			case "surat kepala/ketua lembaga/badan":
	  	  	  				$part->regulationOrder = 391;
	  	  	  				break;
	  	  	  			case "keputusan komisi":
	  	  	  				$part->regulationOrder = 401;
	  	  	  				break;
	  	  	  			case "peraturan komisi":
	  	  	  				$part->regulationOrder = 411;
	  	  	  				break;
	  	  	  			case "instruksi komisi":
	  	  	  				$part->regulationOrder = 421;
	  	  	  				break;
	  	  	  			case "pengumuman komisi":
	  	  	  				$part->regulationOrder = 431;
	  	  	  				break;
	  	  	  			case "surat edaran komisi":
	  	  	  				$part->regulationOrder = 441;
	  	  	  				break;
	  	  	  			case "surat komisi":
	  	  	  				$part->regulationOrder = 451;
	  	  	  				break;
	  	  	  			case "keputusan panitia":
	  	  	  				$part->regulationOrder = 461;
	  	  	  				break;
	  	  	  			case "peraturan panitia":
	  	  	  				$part->regulationOrder = 471;
	  	  	  				break;
	  	  	  			case "instruksi panitia":
	  	  	  				$part->regulationOrder = 481;
	  	  	  				break;
	  	  	  			case "pengumuman panitia":
	  	  	  				$part->regulationOrder = 491;
	  	  	  				break;
	  	  	  			case "surat edaran panitia":
	  	  	  				$part->regulationOrder = 501;
	  	  	  				break;
	  	  	  			case "surat panitia":
	  	  	  				$part->regulationOrder = 511;
	  	  	  				break;
	  	  	  			case "keputusan direktur jenderal":
	  	  	  				$part->regulationOrder = 521;
	  	  	  				break;
	  	  	  			case "surat edaran direktur jenderal":
	  	  	  				$part->regulationOrder = 531;
	  	  	  				break;
	  	  	  			case "surat direktur jenderal":
	  	  	  				$part->regulationOrder = 541;
	  	  	  				break;
	  	  	  			case "instruksi direktur jenderal":
	  	  	  				$part->regulationOrder = 551;
	  	  	  				break;
	  	  	  			case "peraturan direktur jenderal":
	  	  	  				$part->regulationOrder = 561;
	  	  	  				break;
	  	  	  			case "peraturan inspektur jenderal":
	  	  	  				$part->regulationOrder = 571;
	  	  	  				break;
	  	  	  			case "instruksi inspektur jenderal":
	  	  	  				$part->regulationOrder = 581;
	  	  	  				break;
	  	  	  			case "pengumuman inspektur jenderal":
	  	  	  				$part->regulationOrder = 591;
	  	  	  				break;
	  	  	  			case "surat edaran inspektur jenderal":
	  	  	  				$part->regulationOrder = 601;
	  	  	  				break;
	  	  	  			case "surat inspektur jenderal":
	  	  	  				$part->regulationOrder = 611;
	  	  	  				break;
	  	  	  			case "peraturan daerah tingkat i":
	  	  	  				$part->regulationOrder = 621;
	  	  	  				break;
	  	  	  			case "peraturan daerah tingkat ii":
	  	  	  				$part->regulationOrder = 631;
	  	  	  				break;
	  	  	  			case "keputusan gubernur":
	  	  	  				$part->regulationOrder = 641;
	  	  	  				break;
	  	  	  			case "peraturan gubernur":
	  	  	  				$part->regulationOrder = 651;
	  	  	  				break;
	  	  	  			case "instruksi gubernur":
	  	  	  				$part->regulationOrder = 661;
	  	  	  				break;
	  	  	  			case "pengumuman gubernur":
	  	  	  				$part->regulationOrder = 671;
	  	  	  				break;
	  	  	  			case "surat edaran gubernur":
	  	  	  				$part->regulationOrder = 681;
	  	  	  				break;
	  	  	  			case "surat gubernur":
	  	  	  				$part->regulationOrder = 691;
	  	  	  				break;
	  	  	  			case "keputusan bupati/walikota":
	  	  	  				$part->regulationOrder = 701;
	  	  	  				break;
	  	  	  			case "peraturan bupati/walikota":
	  	  	  				$part->regulationOrder = 711;
	  	  	  				break;
	  	  	  			case "instruksi bupati/walikota":
	  	  	  				$part->regulationOrder = 721;
	  	  	  				break;
	  	  	  			case "pengumuman bupati/walikota":
	  	  	  				$part->regulationOrder = 731;
	  	  	  				break;
	  	  	  			case "surat edaran bupati/walikota":
	  	  	  				$part->regulationOrder = 741;
	  	  	  				break;
	  	  	  			case "surat bupati/walikota":
	  	  	  				$part->regulationOrder = 751;
	  	  	  				break;
	  	  	  			case "keputusan direksi":
	  	  	  				$part->regulationOrder = 761;
	  	  	  				break;
	  	  	  			case "peraturan direksi":
	  	  	  				$part->regulationOrder = 771;
	  	  	  				break;
	  	  	  			case "instruksi direksi":
	  	  	  				$part->regulationOrder = 781;
	  	  	  				break;
	  	  	  			case "pengumuman direksi":
	  	  	  				$part->regulationOrder = 791;
	  	  	  				break;
	  	  	  			case "surat edaran direksi":
	  	  	  				$part->regulationOrder = 801;
	  	  	  				break;
	  	  	  			case "surat direksi":
	  	  	  				$part->regulationOrder = 811;
	  	  	  				break;
	  	  	  			case "keputusan direktur":
	  	  	  				$part->regulationOrder = 821;
	  	  	  				break;
	  	  	  			case "peraturan direktur":
	  	  	  				$part->regulationOrder = 831;
	  	  	  				break;
	  	  	  			case "instruksi direktur":
	  	  	  				$part->regulationOrder = 841;
	  	  	  				break;
	  	  	  			case "pengumuman direktur":
	  	  	  				$part->regulationOrder = 851;
	  	  	  				break;
	  	  	  			case "surat edaran direktur":
	  	  	  				$part->regulationOrder = 861;
	  	  	  				break;
	  	  	  			case "surat direktur":
	  	  	  				$part->regulationOrder = 871;
	  	  	  				break;
	  	  	  			/*case :
	  	  	  				$part->regulationOrder = 881;
	  	  	  				break;*/
	  	  	  			default:
	  	  	  				$part->regulationOrder = 9999;
	  	  	  				break;
	  	  	  		}
	  	  	  		break;
	  	  	  	case 'ptsJenisLembaga':
	  	  	  		$part->regulationType = $rowAttr->value;
	  	  	  		echo 'jenis: '.$rowAttr->value. '<br>';
	  	  	  		switch(strtolower($part->regulationType))
	  	  	  		{
	  	  	  			case 'ma':
	  	  	  			case 'mk':
	  	  	  				$part->regulationOrder = 1;
	  	  	  				break;
	  	  	  			case 'pt':
	  	  	  			case 'pttun':
	  	  	  			case 'pta':
	  	  	  			case 'mahmiltinggi':
	  	  	  				$part->regulationOrder = 20;
	  	  	  				break;
	  	  	  			case 'pn':
	  	  	  			case 'ptun':
	  	  	  			case 'pa':
	  	  	  			case 'pniaga':
	  	  	  			case 'mahmil':
	  	  	  				$part->regulationOrder = 30;
	  	  	  				break;
	  	  	  			default:
	  	  	  				$part->regulationOrder = 9999;
	  	  	  				break;
	  	  	  		}
	  	  	  		break;
	  	  	  	case 'docMimeType':
	  	  	  		$part->mimeType = $rowAttr->value;
	  	  	  		$docMimeType = $rowAttr->value;
	  	  	  		break;
	  	  	  	case 'docOriginalName':
	  	  	  		$part->fileName = $rowAttr->value;
	  	  	  		$docOriginalName = $rowAttr->value;
	  	  	  		break;
	  	  	  	case 'docSystemName':
	  	  	  		$docSystemName = $rowAttr->value;
	  	  	  		break;
	  	  	  	case 'docSize':
	  	  	  		// $part->fileSize = $rowAttr->value; //TODO conver to float first
	  	  	  		break;
				default:
					if(isset($part->all))
					{
						$part->all .= ' '.$rowAttr->value;
					}
					else 
					{
						$part->all = $rowAttr->value;
					}
			}
			 
	  	}
		if($row->profileGuid=='kutu_doc')
		{
			//extract text from the file
			$sContent = $this->_extractText($row->guid, $docSystemName, $docOriginalName, $docMimeType);
			//$sContent = $this->clean_string_input($sContent);
			if(isset($part->content))
			{
				$part->content .= ' '.$sContent;
			}
			else 
			{
				$part->content = $sContent;
			}
		}
	  	return $part;
	}
	
	
	public function reIndexCatalog_ZendDb()
	{
		$this->emptyIndex();
		
		$time_start = microtime(true);
		
		$solr = &$this->_solr;
		
		$tbl = new App_Model_Db_Table_Catalog();
		$rowset = $tbl->fetchAll(); //("profileGuid='kutu_peraturan'");
		  
		$documents = array();
		$rowCount = count($rowset);
		for($iCount=0;$iCount<$rowCount;$iCount++)
		{
			$row = $rowset->current();
//			if($iCount == 100)
//				break;
			echo 'urutan: '.$iCount .'<br>';
			
		  	$documents[] = $this->_createSolrDocument($row);
		  	$rowset->next();
		  	
		  	if($iCount%1000 == 0)
		  	{
			  	try 
			  	{
					$solr->addDocuments( $documents );
					$solr->commit();
					//$solr->optimize();
					$documents = array();
				}
				catch ( Exception $e ) 
				{
					echo "Error occured when processing record starting from number: ". ($iCount - 1000) . ' to '.$iCount;
					throw new Zend_Exception($e->getMessage());
					//echo $e->getMessage();
				}
		  	}
		}
		  
		try {
			$solr->addDocuments( $documents );
			$solr->commit();
			$solr->optimize();
		}
		catch ( Exception $e ) {
			throw new Zend_Exception($e->getMessage());
			//echo $e->getMessage();
		}
		
		$time_end = microtime(true);
		$time = $time_end - $time_start;
		
		echo'<br>WAKTU EKSEKUSI: '. $time;
		
		
	}
	
	private function _createSolrDocument_ZendDb(&$row)
	{
		
		$part = new Apache_Solr_Document();
	  	$part->id = $row->guid;
	  	$part->shortTitle = $row->shortTitle;
	  	$part->profile = $row->profileGuid;
	  	$part->publishedDate = $this->getDateInSolrFormat($row->publishedDate);
	  	$part->expiredDate = $this->getDateInSolrFormat($row->expiredDate);
	  	$part->createdBy = $row->createdBy;
	  	$part->createdDate = $this->getDateInSolrFormat($row->createdDate);
	  	$part->modifiedBy = $row->modifiedBy;
	  	$part->modifiedDate = $this->getDateInSolrFormat($row->modifiedDate);
	  	if(!$row->status==null)
	  		$part->status = $row->status;
	  	$part->url = ''; //TODO what to input here as the URL???
	  	$part->serviceId = '';
	  	
	  	  
	  	$rowsetAttr = $row->findDependentRowsetCatalogAttribute();
	  	
	  	$rowCount = count($rowsetAttr);
	  	//foreach ($rowsetAttr as $rowAttr)
	  	for($i=0;$i<$rowCount;$i++)
	  	{
	  		$rowAttr = $rowsetAttr->current();
			switch ($rowAttr->attributeGuid)
			{
	  	  	  	case 'fixedTitle':
	  	  	  		if(empty($rowAttr->value))
	  	  	  		{
	  	  	  			$part->title = $row->shortTitle;
	  	  	  		}
	  	  	  		else 
	  	  	  		{
	  	  	  			$part->title = $rowAttr->value;
	  	  	  		}
	  	  	  		echo $part->title.'<br>';
	  	  	  		break;
	  	  	  	case 'fixedSubTitle':
	  	  	  		$part->subTitle = $rowAttr->value;
	  	  	  		break;
	  	  	  	case 'fixedContent':
	  	  	  		$part->content = $rowAttr->value;
	  	  	  		break;
	  	  	  	case 'fixedKeywords':
	  	  	  		$part->keywords = $rowAttr->value;
	  	  	  		break;
	  	  	  	case 'fixedDescription':
	  	  	  		$part->description = $rowAttr->value;
	  	  	  		break;
	  	  	  	case 'fixedComments':
	  	  	  		$part->comments = $rowAttr->value;
	  	  	  		break;
	  	  	  	case 'fixedNumber':
	  	  	  	case 'prtNomor':
	  	  	  	case 'ptsNomor':
	  	  	  		$part->number = $rowAttr->value;
	  	  	  		break;
	  	  	  	case 'fixedYear':
	  	  	  	case 'ptsTahun':
	  	  	  	case 'prtTahun':
	  	  	  		$part->year = (int)$rowAttr->value;
	  	  	  		break;
	  	  	  	case 'fixedDate':
	  	  	  	case 'prtDisahkan':
	  	  	  	case 'ptsDibacakan':
	  	  	  		$part->date = $this->getDateInSolrFormat($rowAttr->value);
	  	  	  		break;
	  	  	  	case 'fixedLanguage':
	  	  	  		$part->language = $rowAttr->value;
	  	  	  		break;
	  	  	  	case 'prtJenis':
	  	  	  		$part->regulationType = $rowAttr->value;
	  	  	  		echo 'jenis: '.$rowAttr->value. '<br>';
	  	  	  		//enter the regulation type order. i.e. undang-undang=1, pp=2, dst.
	  	  	  		switch(strtolower($part->regulationType))
	  	  	  		{
	  	  	  			case 'konstitusi':
	  	  	  				$part->regulationOrder = 1;
	  	  	  				break;
	  	  	  			case 'tap mpr':
	  	  	  				$part->regulationOrder = 11;
	  	  	  				break;
	  	  	  			case 'tus mpr':
	  	  	  				$part->regulationOrder = 21;
	  	  	  				break;
	  	  	  			case 'undang-undang':
	  	  	  			case 'uu':
	  	  	  				$part->regulationOrder = 31;
	  	  	  				break;
	  	  	  			case 'undang-undang darurat':
	  	  	  				$part->regulationOrder = 41;
	  	  	  				break;
	  	  	  			case 'perpu':
	  	  	  				$part->regulationOrder = 51;
	  	  	  				break;
	  	  	  			case 'pp':
	  	  	  				$part->regulationOrder = 61;
	  	  	  				break;
	  	  	  			case 'perpres':
	  	  	  				$part->regulationOrder = 71;
	  	  	  				break;
	  	  	  			case 'penpres':
	  	  	  				$part->regulationOrder = 81;
	  	  	  				break;
	  	  	  			case 'keppres':
	  	  	  				$part->regulationOrder = 91;
	  	  	  				break;
	  	  	  			case 'inpres':
	  	  	  				$part->regulationOrder = 101;
	  	  	  				break;
	  	  	  			case 'konvensi internasional':
	  	  	  				$part->regulationOrder = 111;
	  	  	  				break;
	  	  	  			case 'keputusan bersama':
	  	  	  				$part->regulationOrder = 121;
	  	  	  				break;
	  	  	  			case 'keputusan dewan':
	  	  	  				$part->regulationOrder = 131;
	  	  	  				break;
	  	  	  			case 'kepmen':
	  	  	  				$part->regulationOrder = 141;
	  	  	  				break;
	  	  	  			case 'permen':
	  	  	  				$part->regulationOrder = 151;
	  	  	  				break;
	  	  	  			case 'inmen':
	  	  	  				$part->regulationOrder = 161;
	  	  	  				break;
	  	  	  			case 'pengumuman menteri':
	  	  	  				$part->regulationOrder = 171;
	  	  	  				break;
	  	  	  			case 'surat edaran menteri':
	  	  	  				$part->regulationOrder = 181;
	  	  	  				break;
	  	  	  			case 'surat menteri':
	  	  	  				$part->regulationOrder = 191;
	  	  	  				break;
	  	  	  			case 'keputusan asisten menteri':
	  	  	  				$part->regulationOrder = 201;
	  	  	  				break;
	  	  	  			case 'surat asisten menteri':
	  	  	  				$part->regulationOrder = 211;
	  	  	  				break;
	  	  	  			case "keputusan menteri negara/ketua lembaga/badan":
	  	  	  				$part->regulationOrder = 221;
	  	  	  				break;
	  	  	  			case "peraturan menteri negara/ketua lembaga/badan":
	  	  	  				$part->regulationOrder = 231;
	  	  	  				break;
	  	  	  			case "instruksi menteri negara/ketua lembaga/badan":
	  	  	  				$part->regulationOrder = 241;
	  	  	  				break;
	  	  	  			case "pengumuman menteri negara/ketua lembaga/badan":
	  	  	  				$part->regulationOrder = 251;
	  	  	  				break;
	  	  	  			case "surat edaran menteri negara/ketua lembaga/badan":
	  	  	  				$part->regulationOrder = 261;
	  	  	  				break;
	  	  	  			case "surat menteri negara/ketua lembaga/badan":
	  	  	  				$part->regulationOrder = 271;
	  	  	  				break;
	  	  	  			case "keputusan lembaga/badan":
	  	  	  				$part->regulationOrder = 281;
	  	  	  				break;
	  	  	  			case "peraturan lembaga/badan":
	  	  	  				$part->regulationOrder = 291;
	  	  	  				break;
	  	  	  			case "instruksi lembaga/badan":
	  	  	  				$part->regulationOrder = 301;
	  	  	  				break;
	  	  	  			case "pengumuman lembaga/badan":
	  	  	  				$part->regulationOrder = 311;
	  	  	  				break;
	  	  	  			case "surat edaran lembaga/badan":
	  	  	  				$part->regulationOrder = 321;
	  	  	  				break;
	  	  	  			case "surat lembaga/badan":
	  	  	  				$part->regulationOrder = 331;
	  	  	  				break;
	  	  	  			case "keputusan kepala/ketua lembaga/badan":
	  	  	  				$part->regulationOrder = 341;
	  	  	  				break;
	  	  	  			case "peraturan kepala/ketua lembaga/badan":
	  	  	  				$part->regulationOrder = 351;
	  	  	  				break;
	  	  	  			case "instruksi kepala/ketua lembaga/badan":
	  	  	  				$part->regulationOrder = 361;
	  	  	  				break;
	  	  	  			case "pengumuman kepala/ketua lembaga/badan":
	  	  	  				$part->regulationOrder = 371;
	  	  	  				break;
	  	  	  			case "surat edaran kepala/ketua lembaga/badan":
	  	  	  				$part->regulationOrder = 381;
	  	  	  				break;
	  	  	  			case "surat kepala/ketua lembaga/badan":
	  	  	  				$part->regulationOrder = 391;
	  	  	  				break;
	  	  	  			case "keputusan komisi":
	  	  	  				$part->regulationOrder = 401;
	  	  	  				break;
	  	  	  			case "peraturan komisi":
	  	  	  				$part->regulationOrder = 411;
	  	  	  				break;
	  	  	  			case "instruksi komisi":
	  	  	  				$part->regulationOrder = 421;
	  	  	  				break;
	  	  	  			case "pengumuman komisi":
	  	  	  				$part->regulationOrder = 431;
	  	  	  				break;
	  	  	  			case "surat edaran komisi":
	  	  	  				$part->regulationOrder = 441;
	  	  	  				break;
	  	  	  			case "surat komisi":
	  	  	  				$part->regulationOrder = 451;
	  	  	  				break;
	  	  	  			case "keputusan panitia":
	  	  	  				$part->regulationOrder = 461;
	  	  	  				break;
	  	  	  			case "peraturan panitia":
	  	  	  				$part->regulationOrder = 471;
	  	  	  				break;
	  	  	  			case "instruksi panitia":
	  	  	  				$part->regulationOrder = 481;
	  	  	  				break;
	  	  	  			case "pengumuman panitia":
	  	  	  				$part->regulationOrder = 491;
	  	  	  				break;
	  	  	  			case "surat edaran panitia":
	  	  	  				$part->regulationOrder = 501;
	  	  	  				break;
	  	  	  			case "surat panitia":
	  	  	  				$part->regulationOrder = 511;
	  	  	  				break;
	  	  	  			case "keputusan direktur jenderal":
	  	  	  				$part->regulationOrder = 521;
	  	  	  				break;
	  	  	  			case "surat edaran direktur jenderal":
	  	  	  				$part->regulationOrder = 531;
	  	  	  				break;
	  	  	  			case "surat direktur jenderal":
	  	  	  				$part->regulationOrder = 541;
	  	  	  				break;
	  	  	  			case "instruksi direktur jenderal":
	  	  	  				$part->regulationOrder = 551;
	  	  	  				break;
	  	  	  			case "peraturan direktur jenderal":
	  	  	  				$part->regulationOrder = 561;
	  	  	  				break;
	  	  	  			case "peraturan inspektur jenderal":
	  	  	  				$part->regulationOrder = 571;
	  	  	  				break;
	  	  	  			case "instruksi inspektur jenderal":
	  	  	  				$part->regulationOrder = 581;
	  	  	  				break;
	  	  	  			case "pengumuman inspektur jenderal":
	  	  	  				$part->regulationOrder = 591;
	  	  	  				break;
	  	  	  			case "surat edaran inspektur jenderal":
	  	  	  				$part->regulationOrder = 601;
	  	  	  				break;
	  	  	  			case "surat inspektur jenderal":
	  	  	  				$part->regulationOrder = 611;
	  	  	  				break;
	  	  	  			case "peraturan daerah tingkat i":
	  	  	  				$part->regulationOrder = 621;
	  	  	  				break;
	  	  	  			case "peraturan daerah tingkat ii":
	  	  	  				$part->regulationOrder = 631;
	  	  	  				break;
	  	  	  			case "keputusan gubernur":
	  	  	  				$part->regulationOrder = 641;
	  	  	  				break;
	  	  	  			case "peraturan gubernur":
	  	  	  				$part->regulationOrder = 651;
	  	  	  				break;
	  	  	  			case "instruksi gubernur":
	  	  	  				$part->regulationOrder = 661;
	  	  	  				break;
	  	  	  			case "pengumuman gubernur":
	  	  	  				$part->regulationOrder = 671;
	  	  	  				break;
	  	  	  			case "surat edaran gubernur":
	  	  	  				$part->regulationOrder = 681;
	  	  	  				break;
	  	  	  			case "surat gubernur":
	  	  	  				$part->regulationOrder = 691;
	  	  	  				break;
	  	  	  			case "keputusan bupati/walikota":
	  	  	  				$part->regulationOrder = 701;
	  	  	  				break;
	  	  	  			case "peraturan bupati/walikota":
	  	  	  				$part->regulationOrder = 711;
	  	  	  				break;
	  	  	  			case "instruksi bupati/walikota":
	  	  	  				$part->regulationOrder = 721;
	  	  	  				break;
	  	  	  			case "pengumuman bupati/walikota":
	  	  	  				$part->regulationOrder = 731;
	  	  	  				break;
	  	  	  			case "surat edaran bupati/walikota":
	  	  	  				$part->regulationOrder = 741;
	  	  	  				break;
	  	  	  			case "surat bupati/walikota":
	  	  	  				$part->regulationOrder = 751;
	  	  	  				break;
	  	  	  			case "keputusan direksi":
	  	  	  				$part->regulationOrder = 761;
	  	  	  				break;
	  	  	  			case "peraturan direksi":
	  	  	  				$part->regulationOrder = 771;
	  	  	  				break;
	  	  	  			case "instruksi direksi":
	  	  	  				$part->regulationOrder = 781;
	  	  	  				break;
	  	  	  			case "pengumuman direksi":
	  	  	  				$part->regulationOrder = 791;
	  	  	  				break;
	  	  	  			case "surat edaran direksi":
	  	  	  				$part->regulationOrder = 801;
	  	  	  				break;
	  	  	  			case "surat direksi":
	  	  	  				$part->regulationOrder = 811;
	  	  	  				break;
	  	  	  			case "keputusan direktur":
	  	  	  				$part->regulationOrder = 821;
	  	  	  				break;
	  	  	  			case "peraturan direktur":
	  	  	  				$part->regulationOrder = 831;
	  	  	  				break;
	  	  	  			case "instruksi direktur":
	  	  	  				$part->regulationOrder = 841;
	  	  	  				break;
	  	  	  			case "pengumuman direktur":
	  	  	  				$part->regulationOrder = 851;
	  	  	  				break;
	  	  	  			case "surat edaran direktur":
	  	  	  				$part->regulationOrder = 861;
	  	  	  				break;
	  	  	  			case "surat direktur":
	  	  	  				$part->regulationOrder = 871;
	  	  	  				break;
	  	  	  			/*case :
	  	  	  				$part->regulationOrder = 881;
	  	  	  				break;*/
	  	  	  			default:
	  	  	  				$part->regulationOrder = 9999;
	  	  	  				break;
	  	  	  		}
	  	  	  		break;
	  	  	  	case 'ptsJenisLembaga':
	  	  	  		$part->regulationType = $rowAttr->value;
	  	  	  		echo 'jenis: '.$rowAttr->value. '<br>';
	  	  	  		switch(strtolower($part->regulationType))
	  	  	  		{
	  	  	  			case 'ma':
	  	  	  			case 'mk':
	  	  	  				$part->regulationOrder = 1;
	  	  	  				break;
	  	  	  			case 'pt':
	  	  	  			case 'pttun':
	  	  	  			case 'pta':
	  	  	  			case 'mahmiltinggi':
	  	  	  				$part->regulationOrder = 20;
	  	  	  				break;
	  	  	  			case 'pn':
	  	  	  			case 'ptun':
	  	  	  			case 'pa':
	  	  	  			case 'pniaga':
	  	  	  			case 'mahmil':
	  	  	  				$part->regulationOrder = 30;
	  	  	  				break;
	  	  	  			default:
	  	  	  				$part->regulationOrder = 9999;
	  	  	  				break;
	  	  	  		}
	  	  	  		break;
	  	  	  	case 'docMimeType':
	  	  	  		$part->mimeType = $rowAttr->value;
	  	  	  		$docMimeType = $rowAttr->value;
	  	  	  		break;
	  	  	  	case 'docOriginalName':
	  	  	  		$part->fileName = $rowAttr->value;
	  	  	  		$docOriginalName = $rowAttr->value;
	  	  	  		break;
	  	  	  	case 'docSystemName':
	  	  	  		$docSystemName = $rowAttr->value;
	  	  	  		break;
	  	  	  	case 'docSize':
	  	  	  		// $part->fileSize = $rowAttr->value; //TODO conver to float first
	  	  	  		break;
	  	  	  	default:
					if(isset($part->all))
					{
						$part->all .= ' '.$rowAttr->value;
					}
					else 
					{
						$part->all = $rowAttr->value;
					}
			}
			$rowsetAttr->next();
			 
	  	}
		if($row->profileGuid=='kutu_doc')
		{
			//extract text from the file
			$sContent = $this->_extractText($row->guid, $docSystemName, $docOriginalName, $docMimeType);
			//$sContent = $this->clean_string_input($sContent);
			if(isset($part->content))
			{
				$part->content .= ' '.$sContent;
			}
			else 
			{
				$part->content = $sContent;
			}
		}
	  	return $part;
	}
        
        public function getDateInSolrFormat($date) {
            if($date=='0000-00-00 00:00:00' OR $date=='0000-00-00' OR $date=='' OR $date==NULL) {
                return '1999-12-31T00:00:00Z';
            }
            else
            {
                return date("Y-m-d\\TH:i:s\\Z",strtotime($date));
            }
        }
	
	public function _translateMySqlDateToSolrDate($mysqlDate)
	{
//		if(Zend_Date::isDate($mysqlDate, "yyyy-MM-dd HH:mm:ss"))
		if(true)
		{
			$aDateTime = explode(' ', $mysqlDate);
			if(!empty($aDateTime[0]) && strlen($aDateTime[0])==10)
				$aDateTime[0] .= 'T';
			else 
				$aDateTime[0] = '0000-00-00T';
			if(isset($aDateTime[1]) && !empty($aDateTime[1]))
				$aDateTime[1] .= 'Z';
			else 
				$aDateTime[1] = '00:00:00Z';
				
			$solrDate = $aDateTime[0].$aDateTime[1];
			//echo '<br>'.$solrDate;
			return $solrDate;
		}
		else 
		{
			return '0000-00-00T00:00:00Z';
		}
		
	}
	private function _extractText($guid, $systemName, $fileName, $mimeType)
	{
	    //$c = $this->_registry->get('config');
	    
		//$tblRelatedItem = new Kutu_Core_Orm_Table_RelatedItem();
		//$rowset = $tblRelatedItem->fetchAll("itemGuid='$guid' AND relateAs='RELATED_FILE'");
		
		$query="SELECT * FROM KutuRelatedItem where itemGuid='$guid' AND relateAs='RELATED_FILE'";
		$results = $this->_conn->query($query);
		
		$rowset = $results->fetchAll(PDO::FETCH_OBJ);
		
		if(count($rowset))
		{
			$row = $rowset[0];
			$parentCatalogGuid = $row->relatedGuid;
			
			if(!empty($systemName))
				$fileName = $systemName;
			
			$sDir1 = ROOT_DIR.DIRECTORY_SEPARATOR.'uploads'.DIRECTORY_SEPARATOR.'files'.DIRECTORY_SEPARATOR.$fileName;
			$sDir2 = ROOT_DIR.DIRECTORY_SEPARATOR.'uploads'.DIRECTORY_SEPARATOR.'files'.DIRECTORY_SEPARATOR.$parentCatalogGuid.DIRECTORY_SEPARATOR.$fileName;
			
			$sDir = '';
			if(file_exists($sDir1))
			{
				$sDir = $sDir1;
			}
			else 
				if(file_exists($sDir2))
				{
					$sDir = $sDir2;
				}
				
			if(!empty($sDir))
			{
				$outpath = $sDir.'.txt';
				
				switch ($mimeType)
				{
					case 'application/pdf':
						$pdfExtractor = $this->_pdfExtractor;
						system("$pdfExtractor ".$sDir.' '.$outpath, $ret);
					    if ($ret == 0)
					    {
					        $value = file_get_contents($outpath);
					        unlink($outpath);
					        //echo 'content PDF: '. $sDir.' ' . strlen($value);
					        if(strlen($value) > 20)
					        	return $this->clean_string_input($value);
					        else 
					        {
					        	echo 'content file kosong';
					        	return '';
					        }
					    }
					    if ($ret == 127)
					        //print "Could not find pdftotext tool.";
					        return '';
					    if ($ret == 1)
					        //print "Could not find pdf file.";
					        return '';
						break;
					case 'text/html':
					case 'text/plain':
						$docHtml = Zend_Search_Lucene_Document_Html::loadHTMLFile($sDir);
						return $docHtml->getFieldValue('body');
						break;
					case 'application/x-javascript':
					case 'application/octet-stream':
					case 'application/msword':
						if(strpos(strtolower($fileName), '.doc'))
						{
							$extractor = $this->_wordExtractor;
							system("$extractor -m cp850.txt ".$sDir.' > '.$outpath, $ret);
						    if ($ret == 0)
						    {
						        $value = file_get_contents($outpath);
						        unlink($outpath);
						        //echo $value;
						        return $value;
						    }
						    if ($ret == 127)
						        //print "Could not find pdftotext tool.";
						        return '';
						    if ($ret == 1)
						        //print "Could not find pdf file.";
						        return '';
						}
						else 
						{
							return '';
						}
						break;
					default :
						return '';
						break;
				}
			}
		}
		return '';
	}
	
	private function _extractText_ZendDb($guid, $systemName, $fileName, $mimeType)
	{
	    //$c = $this->_registry->get('config');
	    
		$tblRelatedItem = new Pandamp_Core_Orm_Table_RelatedItem();
		$rowset = $tblRelatedItem->fetchAll("itemGuid='$guid' AND relateAs='RELATED_FILE'");
		if(count($rowset))
		{
			$row = $rowset->current();
			$parentCatalogGuid = $row->relatedGuid;
			
			if(!empty($systemName))
				$fileName = $systemName;
			
			$sDir1 = ROOT_DIR.DIRECTORY_SEPARATOR.'uploads'.DIRECTORY_SEPARATOR.'files'.DIRECTORY_SEPARATOR.$fileName;
			$sDir2 = ROOT_DIR.DIRECTORY_SEPARATOR.'uploads'.DIRECTORY_SEPARATOR.'files'.DIRECTORY_SEPARATOR.$parentCatalogGuid.DIRECTORY_SEPARATOR.$fileName;
			
			$sDir = '';
			if(file_exists($sDir1))
			{
				$sDir = $sDir1;
			}
			else 
				if(file_exists($sDir2))
				{
					$sDir = $sDir2;
				}
				
			if(!empty($sDir))
			{
				$outpath = $sDir.'.txt';
				
				switch ($mimeType)
				{
					case 'application/pdf':
						$pdfExtractor = $this->_pdfExtractor;
						system("$pdfExtractor ".$sDir.' '.$outpath, $ret);
					    if ($ret == 0)
					    {
					        $value = file_get_contents($outpath);
					        unlink($outpath);
					        //echo 'content PDF: '. $sDir.' ' . strlen($value);
					        if(strlen($value) > 20)
					        	return $this->clean_string_input($value);
					        else 
					        {
					        	echo 'content file kosong';
					        	return '';
					        }
					    }
					    if ($ret == 127)
					        //print "Could not find pdftotext tool.";
					        return '';
					    if ($ret == 1)
					        //print "Could not find pdf file.";
					        return '';
						break;
					case 'text/html':
					case 'text/plain':
						$docHtml = Zend_Search_Lucene_Document_Html::loadHTMLFile($sDir);
						return $docHtml->getFieldValue('body');
						break;
					case 'application/x-javascript':
					case 'application/octet-stream':
					case 'application/msword':
						if(strpos(strtolower($fileName), '.doc'))
						{
							$extractor = $this->_wordExtractor;
							system("$extractor -m cp850.txt ".$sDir.' > '.$outpath, $ret);
						    if ($ret == 0)
						    {
						        $value = file_get_contents($outpath);
						        unlink($outpath);
						        //echo $value;
						        return $value;
						    }
						    if ($ret == 127)
						        //print "Could not find pdftotext tool.";
						        return '';
						    if ($ret == 1)
						        //print "Could not find pdf file.";
						        return '';
						}
						else 
						{
							return '';
						}
						break;
					default :
						return '';
						break;
				}
			}
		}
		return '';
	}
	
	public function deleteCatalogFromIndex($catalogGuid)
	{
		$solr = &$this->_solr;
		$solr->deleteById($catalogGuid);
		$solr->commit();
	}
	
	public function optimizeIndex()
	{
		$this->_solr->optimize();
	}
	
	public function emptyIndex()
	{
		$solr = &$this->_solr;
//		$solr->deleteByQuery('profile:klinik'); //deletes ALL documents - be careful :)
		$solr->deleteByQuery('*:*'); //deletes ALL documents - be careful :)
   		$solr->commit();
	}
	
	public function find($query,$start = 0 ,$end = 2000)
	{
            $solr = &$this->_solr;
            $querySolr = $query;
            //$aParams = array('qt'=>'spellCheckCompRH', 'spellcheck'=>'true','spellcheck.collate'=>'true');
            $aParams = array(
                'hl'=>'true',
                'hl.simple.pre' =>'<mark>',
                'hl.simple.post' =>'</mark>',
                'hl.fl' =>'commentQuestion,kategori,description,title,subTitle',
                'fl'=>'*,score',
                'facet'=>'true',
                'facet.field'=>array('profile','kategoriklinik','regulationType','createdBy'),
                'facet.sort'=>'true',
                'facet.method'=>'enum',
                'facet.limit'=>'-1',
                'qt'=>'spellCheckCompRH',
                'spellcheck'=>'true',
                'spellcheck.collate'=>'true');

            return $solr->search( $querySolr,$start, $end, $aParams);
	}
	public function findAndSort($query, $start=0, $limit=20, $sortField)
	{
		$solr = &$this->_solr;
		$querySolr = $query;
		$s = $sortField;
		$aParams=array('sort'=>$s, 'q.op'=>'OR');
  		return $solr->searchByPost( $querySolr,$start, $limit, $aParams);
	}
	
	function clean_string_input($input)
	{
	    $interim = strip_tags($input);
	
	    if(get_magic_quotes_gpc())
	    {
	        $interim=stripslashes($interim);
	    }
	
	    // now check for pure ASCII input
	    // special characters that might appear here:
	    //   96: opening single quote (not strictly illegal, but substitute anyway)
	    //   145: opening single quote
	    //   146: closing single quote
	    //   147: opening double quote
	    //   148: closing double quote
	    //   133: ellipsis (...)
	    //   163: pound sign (this is safe, so no substitution required)
	    // these can be substituted for safe equivalents
	    $result = '';
	    $countInterim = strlen($interim);
	    for ($i=0; $i<$countInterim; $i++)
	    {
	        $char = $interim{$i};
	        $asciivalue = ord($char);
	        if ($asciivalue == 96)
	        {
	            $result .= '\\';
	        }
	        else if (($asciivalue > 31 && $asciivalue < 127) ||
	                 ($asciivalue == 163) || // pound sign
	                 ($asciivalue == 10) || // lf
	                 ($asciivalue == 13)) // cr
	        {
	            // it's already safe ASCII
	            $result .= $char;
	        }
	        else if ($asciivalue == 145) // opening single quote
	        {
	            $result .= '\\';
	        }
	        else if ($asciivalue == 146) // closing single quote
	        {
	            $result .= "'";
	        }
	        else if ($asciivalue == 147) // opening double quote
	        {
	            $result .= '"';
	        }
	        else if ($asciivalue == 148) // closing double quote
	        {
	            $result .= '"';
	        }
	        else if ($asciivalue == 133) // ellipsis
	        {
	            $result .= '...';
	        }
	    }
	
	    return $result;
	}
}

?>