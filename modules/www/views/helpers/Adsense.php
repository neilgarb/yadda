<?php

class Www_View_Helper_Adsense extends Zend_View_Helper_Abstract {
	public function adsense($type) {
		if (APPLICATION_ENVIRONMENT != 'production') {
			return '';
		}
		if ($this->view->showAds !== null && $this->view->showAds === false) {
			return '';
		}
		switch ($type) {
			case 'header':
				$slot = '0598889998';
				break;
				
			case 'list':
				$slot = '3518848451';
				break;
		}
		ob_start();
		?>
<div class="adsense">
	<script type="text/javascript">
	// <![CDATA[
	google_ad_client = "ca-pub-4671565516883843";
	google_ad_slot = "<?php echo $slot ?>";
	google_ad_width = 468;
	google_ad_height = 60;
	// ]]>
	</script>
	<script type="text/javascript" src="http://pagead2.googlesyndication.com/pagead/show_ads.js"></script>
</div>
		<?php
		return ob_get_clean();
	}
}