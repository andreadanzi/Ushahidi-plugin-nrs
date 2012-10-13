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

<?php if(isset($nrs_overlimits)) { ?>

google.load('visualization', '1', {packages: ['motionchart']});



google.setOnLoadCallback(drawVisualization);

function drawVisualization() {
	var data = google.visualization.arrayToDataTable([
           ['ID', 'Events No', 'Magnitude',  'Date',    'Location']
	<?php
	$ii=0;
	foreach ($nrs_overlimits as $nrs_overlimit)
	{
		  $date = DateTime::createFromFormat("Y-m-d H:i:s",$nrs_overlimit->updated);
                  $formtatted_date_js = "'" .$date->format("Y") . "'," .
                                        "'". $date->format("m"). "'," .
                                        "'". $date->format("d"). "'," .
                                        "'". $date->format("H"). "'," .
                                        "'". $date->format("i"). "'," .
                                        "'". $date->format("s"). "'";
	?>
	,['<?php echo $nrs_overlimit->title;?>',  <?php echo $nrs_overlimit->overlimits_no;?>, <?php echo $nrs_overlimit->overlimits_weight;?>,'<?php echo  $nrs_overlimit->updated;?>',  <?php echo $nrs_overlimit->overlimits_weight;?>]

	<?php	
		$ii++;
	}
	?>
	]);
	 var options = {
	      title: 'Correlation between life Number of Events and Magnitude',
	      hAxis: {title: 'Events No'},
	      vAxis: {title: 'Magnitude'},
	      bubble: {textStyle: {fontSize: 11}}
	    };
	var chart = new google.visualization.BubbleChart(document.getElementById('visualization'));

	google.visualization.events.addListener(chart, 'select', function() {
	   alert(chart.getSelection());
	  });
    	chart.draw(data, options);
}

function selectChart() {
	
}

<?php	
}
?>

