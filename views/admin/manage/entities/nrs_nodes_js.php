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


function drawVisualization() {
	var data = null;
	 // Create and populate the data table.
	<?php	
        if(!empty($nrs_datapoints))
        {
	foreach ($nrs_datapoints["head"] as $key=>$nrs_datastreams)
	{
		$nrs_node_id = $key;
	?>   
        data = google.visualization.arrayToDataTable([
		 ['Sample'
	<?php		
		foreach ($nrs_datastreams as $nrs_datastream)
		{
			$nrs_datastream_id = $nrs_datastream->id;
	?> 
          ,'<?php echo $nrs_datastream->unit_label;?>(<?php echo $nrs_datastream->unit_symbol;?>)'
	<?php	
		}
	?>
		]
	<?php
		$array_items = $nrs_datapoints["array_items"][$nrs_node_id];
		for($i=0;$i<min(10,count($array_items));$i++)
		{
			$nrs_row = $array_items[$i];
			
	?>   
          ,['<?php echo $nrs_row["sample_no"];?>'
	
		<?php
			foreach ($nrs_datastreams as $nrs_datastream)
			{
				$nrs_datastream_id = $nrs_datastream->id;
				echo ",". $nrs_row["ds".$nrs_datastream_id];
			}
		?>
	  ]

	<?php	
			
		}
	?>
        ]);
	var created = null;
	  
        // Create and draw the visualization.
        created = new google.visualization.LineChart(document.getElementById('visualization_<?php echo $nrs_node_id;?>')).
            draw(data, {curveType: "function",
                        width: 500, height: 400}
                );
	<?php	
	} // END foreach ($nrs_datapoints
	} // END IF NOT EMPTY
	?>
}

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
function fillFields(nrs_node_id, nrs_node_title, nrs_node_desctiption,nrs_env_uid,nrs_only_node_uid,dispo,expo,status,risk_level,nrs_environment_id,last_update)
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
	$("#last_update").attr("value", decodeURIComponent(last_update));
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

google.setOnLoadCallback(drawVisualization);
