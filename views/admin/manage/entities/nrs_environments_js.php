<?php
/**
 * NRS Environmentjs file.
 * 
 * Handles javascript stuff related to NRS Environmentcontroller
 *
 * PHP version 5
 * LICENSE: This source file is subject to LGPL license 
 * that is available through the world-wide-web at the following URI:
 * http://www.gnu.org/copyleft/lesser.html
 * @author     Ushahidi Team <team@ushahidi.com> 
 * @package    Ushahidi - http://source.ushahididev.com
 * @module     NRS EnvironmentJS View
 * @copyright  Ushahidi - http://www.ushahidi.com
 * @license    http://www.gnu.org/copyleft/lesser.html GNU Lesser General Public License (LGPL) 
 */
?>
// Preview Environment
function preview ( id ){
	if (id) {
		$('#' + id).toggle(400);
	}
}

// NRS EnvironmentJS
function fillFields(nrs_environment_id, nrs_environment_title, nrs_environment_desctiption,nrs_environment_uid,loc_name,loc_dispo,loc_expo,loc_lat,loc_lon,loc_elev,feed,status)
{
	$("#nrs_environment_id").attr("value", decodeURIComponent(nrs_environment_id));
	$("#title").attr("value", decodeURIComponent(nrs_environment_title));
	$("#description").attr("value", decodeURIComponent(nrs_environment_desctiption));
	$("#environment_uid").attr("value", decodeURIComponent(nrs_environment_uid));
	$("#location_elevation").attr("value", decodeURIComponent(loc_elev));
	$("#feed").attr("value", decodeURIComponent(feed));
	$("#location_name").attr("value", decodeURIComponent(loc_name));
	$("#location_exposure").attr("value", decodeURIComponent(loc_expo));
	$("#location_disposition").attr("value", decodeURIComponent(loc_dispo));
	$("#location_latitude").attr("value", decodeURIComponent(loc_lat));
	$("#location_longitude").attr("value", decodeURIComponent(loc_lon));
	$("#status").attr("value", decodeURIComponent(status));
	
}

// Ajax Submission
function environmentAction( action, confirmAction, id )
{
	var statusMessage;
	var answer = confirm('<?php echo Kohana::lang('ui_admin.are_you_sure_you_want_to'); ?> ' + confirmAction + '?')
	if (answer){
		// Set Category ID
		$("#nrs_environment_id_action").attr("value", id);
		// Set Submit Type
		$("#action").attr("value", action);		
		// Submit Form
		$("#nrs_environmentListing").submit();
	}
}

