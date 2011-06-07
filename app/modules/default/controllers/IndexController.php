<?php

class IndexController extends Zend_Controller_Action 
{
	function indexAction()
	{
        $r = $this->getRequest();
        $sOffset = $r->getParam('sOffset');
        $this->view->sOffset = $sOffset;
        $sLimit = $r->getParam('sLimit');
        $this->view->sLimit = $sLimit;

        $query = ($r->getParam('q'))? $r->getParam('q') : '';
        $this->_helper->layout()->searchQuery = $query;
	}
	function headerAction()
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
		
	}
}