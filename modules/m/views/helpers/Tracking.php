<?php

class M_View_Helper_Tracking extends Zend_View_Helper_Abstract {
	public function tracking() {
		if (APPLICATION_ENVIRONMENT != 'production') {
			return '';
		}
		ob_start();
		?>
<script type="text/javascript">
// <![CDATA[
  var _gaq = _gaq || [];
  _gaq.push(['_setAccount', 'UA-24121068-2']);
  _gaq.push(['_trackPageview']);

  (function() {
    var ga = document.createElement('script'); ga.type = 'text/javascript'; ga.async = true;
    ga.src = ('https:' == document.location.protocol ? 'https://ssl' : 'http://www') + '.google-analytics.com/ga.js';
    var s = document.getElementsByTagName('script')[0]; s.parentNode.insertBefore(ga, s);
  })();
// ]]>
</script>
		<?php
		return ob_get_clean();
	}
}