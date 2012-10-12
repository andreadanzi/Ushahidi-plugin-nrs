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

// NRS datapoint JS
function onChangeDatastream(elem)
{
	$("#nrs_updated").attr("value", '');
	elem.form.submit();
}


<?php if(isset($nrs_parent_datastream) && isset($nrs_datapoints)) { ?>
google.setOnLoadCallback(drawChart);
google.load('visualization', '1.1', {packages: ['controls']});

function drawChart() {
  
 	var data = new google.visualization.arrayToDataTable([
	<?php if(isset($nrs_parent_datastream->max_value) && isset($nrs_parent_datastream->min_value)) {?>
	['At', '<?php echo $nrs_parent_datastream->unit_label;?>(<?php echo $nrs_parent_datastream->unit_symbol;?>)', 'AVG','MIN','MAX']
	<?php } else { ?>
	['At', '<?php echo $nrs_parent_datastream->unit_label;?>(<?php echo $nrs_parent_datastream->unit_symbol;?>)', 'AVG']
	<?php } ?>
	<?php
		$sampleno=0;
		foreach ($nrs_datapoints as $nrs_datapoint)
		{
		  $sampleno++;
                  $value_at = $nrs_datapoint->value_at;
                  $datetime_at = $nrs_datapoint->datetime_at;
		  $date = DateTime::createFromFormat("YmdHisu",$datetime_at);
                  $formtatted_date_js = $date->format("Y-m-d H:i:s.u");
                  if(isset($nrs_parent_datastream->factor_title) && $nrs_parent_datastream->factor_title!="")
		  {
			$value_at = $nrs_parent_datastream->constant_value + ($nrs_datapoint->value_at - $nrs_parent_datastream->lambda_value)*$nrs_parent_datastream->factor_value;
                  }

	?>   
	
	<?php if(isset($nrs_parent_datastream->max_value) && isset($nrs_parent_datastream->min_value)) {?>
          ,[<?php echo $sampleno;?>,  <?php echo $value_at;?>,  <?php echo $avg;?>, <?php echo $nrs_parent_datastream->min_value;?>, <?php echo $nrs_parent_datastream->max_value;?>]
	<?php } else { ?>
          ,[<?php echo $sampleno;?>,  <?php echo $value_at;?>,  <?php echo $avg;?>]
	<?php } ?>
	<?php	
		}
	?>
        ]);
	var options = {
 	  chartType: 'LineChart',
          title: '<?php echo $nrs_parent_datastream->title;?>',
          containerId: 'chart_div'
        };

	// Define a NumberRangeFilter slider control for the 'Age' column.
	var slider = new google.visualization.ControlWrapper({
		'controlType': 'NumberRangeFilter',
		'containerId': 'slider_div',
		'options': {
		'filterColumnLabel': 'At',
		'minValue': 0,
		'maxValue': <?php echo $sampleno;?>
		}
	});

	var chart = new google.visualization.ChartWrapper(options);
        
	// Create the dashboard.
        var dashboard = new google.visualization.Dashboard(document.getElementById('nrs_dashboard')).
	// Configure the slider to affect the bar chart
	bind(slider, chart).
	// Draw the dashboard
	draw(data);

}

<?php	
}
?>
