<?php
/**
 * MQTT Subscription js file.
 * 
 * Handles javascript stuff related to MQTT Subscription controller
 *
 * PHP version 5
 * LICENSE: This source file is subject to LGPL license 
 * that is available through the world-wide-web at the following URI:
 * http://www.gnu.org/copyleft/lesser.html
 * @author     Ushahidi Team <team@ushahidi.com> 
 * @package    Ushahidi - http://source.ushahididev.com
 * @module     MQTT Subscription JS View
 * @copyright  Ushahidi - http://www.ushahidi.com
 * @license    http://www.gnu.org/copyleft/lesser.html GNU Lesser General Public License (LGPL) 
 */
?>
// MQTT Subscription JS
function fillFields(nrs_mqtt_subscription_id, mqtt_subscription_topic, mqtt_subscription_name, mqtt_subscription_color,mqtt_host,mqtt_port,mqtt_username,mqtt_password,mqtt_subscription_id)
{
	$("#nrs_mqtt_subscription_id").attr("value", decodeURIComponent(nrs_mqtt_subscription_id));
	$("#mqtt_subscription_name").attr("value", decodeURIComponent(mqtt_subscription_name));
	$("#mqtt_subscription_topic").attr("value", decodeURIComponent(mqtt_subscription_topic));
	$("#mqtt_subscription_color").attr("value", decodeURIComponent(mqtt_subscription_color));
	$("#mqtt_host").attr("value", decodeURIComponent(mqtt_host));
	$("#mqtt_port").attr("value", decodeURIComponent(mqtt_port));
	$("#mqtt_username").attr("value", decodeURIComponent(mqtt_username));
	$("#mqtt_password").attr("value", decodeURIComponent(mqtt_password));
	$("#mqtt_subscription_id").attr("value", decodeURIComponent(mqtt_subscription_id));
}

// Ajax Submission
function mqtt_subscriptionAction ( action, confirmAction, id )
{
	var statusMessage;
	var answer = confirm('<?php echo Kohana::lang('ui_admin.are_you_sure_you_want_to'); ?> ' + confirmAction + '?')
	if (answer){
		// Set Category ID
		$("#nrs_mqtt_subscription_id_action").attr("value", id);
		// Set Submit Type
		$("#action").attr("value", action);		
		// Submit Form
		$("#mqtt_subscriptionListing").submit();
	}
}
