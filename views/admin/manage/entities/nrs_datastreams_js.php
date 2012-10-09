<?php
/**
 * NRS datastreamjs file.
 * 
 * Handles javascript stuff related to NRS datastreamcontroller
 *
 * PHP version 5
 * LICENSE: This source file is subject to LGPL license 
 * that is available through the world-wide-web at the following URI:
 * http://www.gnu.org/copyleft/lesser.html
 * @author     Ushahidi Team <team@ushahidi.com> 
 * @package    Ushahidi - http://source.ushahididev.com
 * @module     NRS datastreamJS View
 * @copyright  Ushahidi - http://www.ushahidi.com
 * @license    http://www.gnu.org/copyleft/lesser.html GNU Lesser General Public License (LGPL) 
 */
?>


function drawVisualization() {
	var data = null;
	 // Create and populate the data table.
	<?php	
						
	foreach ($nrs_datastreams as $nrs_datastream)
	{
		$nrs_datastream_id = $nrs_datastream->id;
	?>   
        data = google.visualization.arrayToDataTable([
          ['Sample', '<?php echo $nrs_datastream->unit_label;?>(<?php echo $nrs_datastream->unit_symbol;?>)','AVG'],
	<?php
		$max_number = $nrs_datastream->samples_num;
		$nrs_datapoints_count = ORM::factory('nrs_datapoint')
						->where('nrs_datastream_id',$nrs_datastream_id)
						->count_all();
		if($nrs_datapoints_count<$max_number) $max_number = $nrs_datapoints_count;
		$nrs_datapoints = ORM::factory('nrs_datapoint')
						->where('nrs_datastream_id',$nrs_datastream_id)
						->orderby('datetime_at','asc')
						->find_all($max_number,$nrs_datapoints_count-$max_number);
		$avg=0;
		foreach ($nrs_datapoints as $nrs_datapoint)
		{
                  $value_at = $nrs_datapoint->value_at;
                  if(isset($nrs_datastream->factor_title) && $nrs_datastream->factor_title!="")
		  {
			$value_at = $nrs_datastream->constant_value + ($nrs_datapoint->value_at - $nrs_datastream->lambda_value)*$nrs_datastream->factor_value;
                  }
                  $avg = $avg + $value_at;
		}
		$avg=$avg/$max_number;
		foreach ($nrs_datapoints as $nrs_datapoint)
		{
                  $value_at = $nrs_datapoint->value_at;
                  if(isset($nrs_datastream->factor_title) && $nrs_datastream->factor_title!="")
		  {
			$value_at = $nrs_datastream->constant_value + ($nrs_datapoint->value_at - $nrs_datastream->lambda_value)*$nrs_datastream->factor_value;
                  }

	?>   
          ['<?php echo $nrs_datapoint->sample_no;?>',   <?php echo $value_at;?>,  <?php echo $avg;?>],

	<?php	
		}
	?>
        ]);
	var created = null;
	  
        // Create and draw the visualization.
        created = new google.visualization.LineChart(document.getElementById('visualization_<?php echo $nrs_datastream_id;?>')).
            draw(data, {curveType: "function",
                        width: 500, height: 400}
                );
	<?php	
	}
	?>
}




// Preview datastream 
function preview ( id ){
	if (id) {
		$('#' + id).toggle(400);
	}
}

// NRS datastream JS
function fillEnvUID(elem,nrs_env_uids)
{
	var selectedEnvId = elem.options[elem.options.selectedIndex].value;
	var valUid = nrs_env_uids[selectedEnvId];
	$("#environment_uid").attr("value", decodeURIComponent(valUid));
}

// NRS datastream JS
function fillNodeUID(elem, nrs_node_uids)
{
	var selectedNodeId = elem.options[elem.options.selectedIndex].value;
	var valUid = nrs_node_uids[selectedNodeId]['uid'];
	var valEnvUid = nrs_node_uids[selectedNodeId]['env_uid']; 
	var valEnvid = nrs_node_uids[selectedNodeId]['env_id'];
	$("#environment_uid").attr("value", decodeURIComponent(valEnvUid));
	$("#only_node_uid").attr("value", decodeURIComponent(valUid));
	$("#nrs_environment_id").attr("value", decodeURIComponent(valEnvid));
}

// NRS datastream JS
function fillFields(nrs_datastream_id,nrs_datastream_title,nrs_datastream_unit_label,nrs_datastream_unit_type,nrs_datastream_unit_symbol,nrs_datastream_unit_format,nrs_env_uid,nrs_only_node_uid,nrs_only_datastream_uid,tags,current_value,min_value,max_value,nrs_environment_id,nrs_node_id, samples_num, factor_title, factor_value, lambda_value, constant_value)
{
	$("#nrs_datastream_id").attr("value", decodeURIComponent(nrs_datastream_id));
	$("#nrs_node_id").attr("value", decodeURIComponent(nrs_node_id));
	$("#title").attr("value", decodeURIComponent(nrs_datastream_title));
	$("#unit_label").attr("value", decodeURIComponent(nrs_datastream_unit_label));
	$("#unit_type").attr("value", decodeURIComponent(nrs_datastream_unit_type));
	$("#unit_symbol").attr("value", decodeURIComponent(nrs_datastream_unit_symbol));
	$("#unit_format").attr("value", decodeURIComponent(nrs_datastream_unit_format));
	$("#environment_uid").attr("value", decodeURIComponent(nrs_env_uid));
	$("#only_node_uid").attr("value", decodeURIComponent(nrs_only_node_uid));
	$("#only_datastream_uid").attr("value", decodeURIComponent(nrs_only_datastream_uid));
	$("#tags").attr("value", decodeURIComponent(tags));
	
	$("#current_value").attr("value", decodeURIComponent(current_value));
	$("#min_value").attr("value", decodeURIComponent(min_value));
	$("#max_value").attr("value", decodeURIComponent(max_value));
	$("#samples_num").attr("value", decodeURIComponent(samples_num));

	$("#factor_title").attr("value", decodeURIComponent(factor_title));
	$("#factor_value").attr("value", decodeURIComponent(factor_value));
	$("#lambda_value").attr("value", decodeURIComponent(lambda_value));
	$("#constant_value").attr("value", decodeURIComponent(constant_value));
		
	$("#nrs_environment_id").attr("value", decodeURIComponent(nrs_environment_id));
	$("#datastream_uid").attr("value", decodeURIComponent(nrs_env_uid+nrs_only_node_uid+nrs_only_datastream_uid));

					

}




// Ajax Submission
function datastreamAction( action, confirmAction, id )
{
	var statusMessage;
	var answer = confirm('<?php echo Kohana::lang('ui_admin.are_you_sure_you_want_to'); ?> ' + confirmAction + '?')
	if (answer){
		// Set Category ID $("#nrs_env_uid").value
		$("#nrs_datastream_id_action").attr("value", id);
		// Set Submit Type
		$("#action").attr("value", action);		
		// Submit Form
		$("#nrs_datastreamListing").submit();
	}
}

google.setOnLoadCallback(drawVisualization);
