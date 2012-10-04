<?php
/**
 * NRS Nodejs file.
 * 
 * Handles javascript stuff related to NRS Nodecontroller
 *
 * PHP version 5
 * LICENSE: This source file is subject to LGPL license 
 * that is available through the world-wide-web at the following URI:
 * http://www.gnu.org/copyleft/lesser.html
 * @author     Ushahidi Team <team@ushahidi.com> 
 * @package    Ushahidi - http://source.ushahididev.com
 * @module     NRS NodeJS View
 * @copyright  Ushahidi - http://www.ushahidi.com
 * @license    http://www.gnu.org/copyleft/lesser.html GNU Lesser General Public License (LGPL) 
 */
?>

// Preview Node
function preview ( id ){
	if (id) {
		$('#' + id).toggle(400);
	}
}

// NRS NodeJS
function fillEnvUID(elem,nrs_env_uids)
{
	var selectedId = elem.options[elem.options.selectedIndex].value;
	var valUid = nrs_env_uids[selectedId];
	$("#environment_uid").attr("value", decodeURIComponent(valUid));
}

// NRS NodeJS
function fillFields(nrs_node_id, nrs_node_title, nrs_node_desctiption,nrs_env_uid,nrs_only_node_uid,dispo,expo,status,risk_level,nrs_environment_id)
{
	$("#nrs_node_id").attr("value", decodeURIComponent(nrs_node_id));
	$("#title").attr("value", decodeURIComponent(nrs_node_title));
	$("#description").attr("value", decodeURIComponent(nrs_node_desctiption));
	$("#environment_uid").attr("value", decodeURIComponent(nrs_env_uid));
	$("#only_node_uid").attr("value", decodeURIComponent(nrs_only_node_uid));
	$("#node_exposure").attr("value", decodeURIComponent(expo));
	$("#node_disposition").attr("value", decodeURIComponent(dispo));
	$("#status").attr("value", decodeURIComponent(status));	
	$("#risk_level").attr("value", decodeURIComponent(risk_level));	
	$("#nrs_environment_id").attr("value", decodeURIComponent(nrs_environment_id));
	$("#node_uid").attr("value", decodeURIComponent(nrs_env_uid+nrs_only_node_uid));
}

// Ajax Submission
function nodeAction( action, confirmAction, id )
{
	var statusMessage;
	var answer = confirm('<?php echo Kohana::lang('ui_admin.are_you_sure_you_want_to'); ?> ' + confirmAction + '?')
	if (answer){
		// Set Category ID $("#nrs_env_uid").value
		$("#nrs_node_id_action").attr("value", id);
		// Set Submit Type
		$("#action").attr("value", action);		
		// Submit Form
		$("#nrs_nodeListing").submit();
	}
}

