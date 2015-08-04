<?php

class Yadda_Feed_Engine_Cotd extends Yadda_Feed_Engine_Abstract {
	public function import($feed) {

		// fetch
		$xml= $this->_fetch($feed->url);

		// parse
        $xml = new SimpleXMLElement($xml);

        $stubs = array();

        foreach ($xml->xpath('/deals/deal') as $deal) {
            $stub = new Yadda_Feed_Stub();

            $res = $deal->xpath('ID');
            $stub->setGuid('cotd/' . intval($res[0]));

            $res = $deal->xpath('title');
            $stub->setTitle($this->_sanitize((string) $res[0]));

            $res = $deal->xpath('url');
            $stub->setLink($this->_sanitize((string) $res[0]));

            $res = $deal->xpath('description');
            $stub->setDescription($this->_sanitize((string) $res[0]));

            $res = $deal->xpath('image');
            $stub->addImage($this->_sanitize((string) $res[0]));

            $res = $deal->xpath('value');
            $stub->setValue((float) $res[0]);
            
            $res = $deal->xpath('price');
            $stub->setPrice($res[0]);

            $stub->setDiscount(($stub->getValue() - $stub->getPrice()) / $stub->getValue() * 100);

            $stubs[] = $stub;
        }

		return $stubs;
	}
}