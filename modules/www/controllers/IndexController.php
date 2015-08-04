<?php

class IndexController extends Www_Controller_Action {
	public function indexAction() {
		$this->view->headMeta()->setName('description', 'Yadda (yet another daily deal aggregator) gathers into one space the daily deals and specials offered by South Africa\'s popular group buying websites.');
		$this->view->headLink()->appendStylesheet('/css/index/index.css');
		
		$this->view->regions = Yadda_Model_Region::index(false);
		$this->view->sites = Yadda_Model_Site::all();
		$this->view->featured = Yadda_Model_Deal::featured(3);
	}
	
	public function contactAction() {
		$form = new Www_Form_Contact();
		if ($this->getRequest()->isPost()) {
			if ($form->isValid($_POST)) {
				$values = $form->getValues();
				try {
					Yadda_Model_Contact::send($values['name'], $values['email'], $values['comments']);
					$this->getHelper('FlashMessenger')->addMessage('Thanks for your comments! We\'ll get back to you shortly.');
					$this->_redirect($this->view->url(array(), 'contact'));
				} catch (Yadda_Model_Exception $e) {
					$this->view->flashMessages[] = $e->getMessage();
				}
			}
		}
		$this->view->form = $form;
		$this->view->headTitle('yadda. - Contact us', 'SET');
		$this->view->headLink()->appendStylesheet('/css/index/contact.css');
	}
	
	public function sitemapAction() {
		
		// first get together all the URLs
		
		$urls = array(
			'/',
			'/contact',
			'/map'
		);
		
		$regionDb = Yadda_Db_Table::getInstance('region');
		$select = $regionDb
			->select()
			->from('region', array('id'))
			->order('name');
		$regions = $regionDb->fetchAll($select);
		foreach ($regions as $region) {
			$urls[] = $this->view->url(array(), 'search').'?region='.urlencode($region['id']);
		}
		
		// generate XML
		
		$xml = new DOMDocument('1.0', 'utf-8');
		
		$urlset = $xml->createElement('urlset');
		$urlset->setAttribute('xmlns', 'http://www.sitemaps.org/schemas/sitemap/0.9');
		$xml->appendChild($urlset);
		
		foreach ($urls as $url) {
			$loc = $xml->createElement('loc', 'http://'.$_SERVER['HTTP_HOST'].$url);
			$url = $xml->createElement('url');
			$url->appendChild($loc);
			$urlset->appendChild($url);
		}
		
		$this->getResponse()->setHeader('Content-Type', 'text/xml');
		$this->getResponse()->setBody($xml->saveXML(null, LIBXML_NOEMPTYTAG));
		$this->getResponse()->sendResponse();
		die;
		/*
<?xml version="1.0" encoding="UTF-8"?>
<urlset xmlns="http://www.sitemaps.org/schemas/sitemap/0.9"
        xmlns:image="http://www.sitemaps.org/schemas/sitemap-image/1.1"
        xmlns:video="http://www.sitemaps.org/schemas/sitemap-video/1.1">
  <url> 
    <loc>http://www.example.com/foo.html</loc> 
    <image:image>
       <image:loc>http://example.com/image.jpg</image:loc> 
    </image:image>
    <video:video>     
      <video:content_loc>http://www.example.com/video123.flv</video:content_loc>
      <video:thumbnail_loc>http://www.example.com/thumbs/123.jpg</video:thumbnail_loc>
      <video:player_loc allow_embed="yes" autoplay="ap=1">http://www.example.com/videoplayer.swf?video=123</video:player_loc>
      <video:title>Grilling steaks for summer</video:title>  
      <video:description>Get perfectly done steaks every time</video:description>
    </video:video>
  </url>
</urlset>
		 */
	}
	
	public function botAction() {
		$this->view->headTitle('yadda. - Bot', 'SET');
		$this->view->headLink()->appendStylesheet('/css/index/bot.css');
	}
}