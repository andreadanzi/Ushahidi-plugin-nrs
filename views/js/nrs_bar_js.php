<?php
/**
 * NRS_bar js file.
 * 
 *
 * PHP version 5
 * LICENSE: This source file is subject to LGPL license 
 * that is available through the world-wide-web at the following URI:
 * http://www.gnu.org/copyleft/lesser.html
 * @author     Ushahidi Team <team@ushahidi.com> 
 * @package    Ushahidi - http://source.ushahididev.com
 * @module     NRS Module
 * @copyright  Ushahidi - http://www.ushahidi.com
 * @license    http://www.gnu.org/copyleft/lesser.html GNU Lesser General Public License (LGPL) 
 */
?>

<script type="text/javascript">
$(document).ready(function() {
	
	// NRS Layer[s] Switch Action
	$("a[id^='nrs_mqtt_subscription_']").click(function() {
		var subscriptionID = this.id.substring(6);
	
		if ( $("#nrs_mqtt_subscription_" + subscriptionID).hasClass("active")) {
			map.deleteLayer($("#nrs_mqtt_subscription_" + subscriptionID).html());
			$("#nrs_mqtt_subscription_" + subscriptionID).removeClass("active");
		
		}  else {
			$("#nrs_mqtt_subscription_" + subscriptionID).addClass("active");
			map.addLayer(Ushahidi.NRS, { // Qui c'era Ushahidi.SHARES
							name: $("#nrs_mqtt_subscription_" + subscriptionID).html(),
							url: "json/nrs/index/" + subscriptionID
						});
		}
		
		return false;
	});
});
</script>
