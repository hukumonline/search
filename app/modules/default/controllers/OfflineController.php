<?php

class OfflineController extends Zend_Controller_Action 
{
	function preDispatch()
	{
		$this->_helper->layout->setLayout('layout-search-result');		
	}
	function temporaryAction() {}
}