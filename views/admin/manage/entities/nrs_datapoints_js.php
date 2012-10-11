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
<?php if(isset($nrs_parent_datastream) && isset($nrs_datapoints)) { ?>
google.setOnLoadCallback(drawChart);


function drawChart() {
  
 	var data = new google.visualization.arrayToDataTable([

	['At', '<?php echo $nrs_parent_datastream->unit_label;?>(<?php echo $nrs_parent_datastream->unit_symbol;?>)', 'AVG']
	<?php
		foreach ($nrs_datapoints as $nrs_datapoint)
		{
                  $value_at = $nrs_datapoint->value_at;
                  $datetime_at = $nrs_datapoint->datetime_at;
		  $date = DateTime::createFromFormat("YmdHisu",$datetime_at);
                  $formtatted_date_js = $date->format("Y-m-d H:i:s.u");
                  if(isset($nrs_parent_datastream->factor_title) && $nrs_parent_datastream->factor_title!="")
		  {
			$value_at = $nrs_parent_datastream->constant_value + ($nrs_datapoint->value_at - $nrs_parent_datastream->lambda_value)*$nrs_parent_datastream->factor_value;
                  }

	?>   
          ,['<?php echo $formtatted_date_js;?>',  <?php echo $value_at;?>,  <?php echo $avg;?>]

	<?php	
		}
	?>
        ]);
	var options = {
          title: '<?php echo $nrs_parent_datastream->title;?>'
        };


	var chart = new google.visualization.LineChart(document.getElementById('chart_div'));
        chart.draw(data, options);

}

<?php	
}
?>
