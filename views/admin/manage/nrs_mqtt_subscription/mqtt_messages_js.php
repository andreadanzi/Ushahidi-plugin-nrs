
// Preview Message
function preview ( id ){
	if (id) {
		$('#' + id).toggle(400);
	}
}
/**
 * Messages_delete js file.
 *
 * Handles javascript stuff related to Messages_delete function.
 *
 * PHP version 5
 * LICENSE: This source file is subject to LGPL license 
 * that is available through the world-wide-web at the following URI:
 * http://www.gnu.org/copyleft/lesser.html
 * @author     Ushahidi Team <team@ushahidi.com> 
 * @package    Ushahidi - http://source.ushahididev.com
 * @module     API Controller
 * @copyright  Ushahidi - http://www.ushahidi.com
 * @license    http://www.gnu.org/copyleft/lesser.html GNU Lesser General Public License (LGPL) 
 */
// Categories JS
function fillFields(id )
{
	$("#nrs_mqtt_subscription_id").attr("value", decodeURIComponent(id));
	
}

// Form Submission
function messageAction ( action, confirmAction, id )
{
	var statusMessage;
	var answer = confirm('<?php echo Kohana::lang('ui_admin.are_you_sure_you_want_to'); ?> ' + confirmAction + '?')
	if (answer){
		// Set Category ID
		$("#nrs_mqtt_subscription_id_action").attr("value", id);
		// Set Item ID
		$("#nrs_mqtt_message_id_action").attr("value", id);
		// Set Submit Type
		$("#action").attr("value", action);		
		// Submit Form
		$("#mqtt_subscriptionListing").submit();			
	
	} 
//	else{
//		return false;
//	}
}

// Ajax Refresh MQTT Subscriptions
function refreshSubscriptions()
{
	$('#mqtt_deployments_loading').html('<img src="<?php echo url::file_loc('img')."media/img/loading_g.gif"; ?>">');
	$("#action").attr("value", 'r');		
	// Submit Form
	$("#mqtt_subscriptionListing").submit();
}
