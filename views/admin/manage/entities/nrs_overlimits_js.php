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
           ['Datastream',  'Timestamp', 'Events No', 'Site',    'Magnitude']
	<?php
	$ii=0;
	foreach ($nrs_overlimits as $nrs_overlimit)
	{
		  $date = DateTime::createFromFormat("Y-m-d H:i:s",$nrs_overlimit->updated);
		  $dateTimestamp =  date_timestamp_get($date);
                  $formtatted_date_js = "'" .$date->format("Y") . "'," .
                                        "'". $date->format("m"). "'," .
                                        "'". $date->format("d"). "'," .
                                        "'". $date->format("H"). "'," .
                                        "'". $date->format("i"). "'," .
                                        "'". $date->format("s"). "'";
	?>
	,['<?php echo $nrs_overlimit->title;?>',  <?php echo $dateTimestamp;?>,<?php echo $nrs_overlimit->overlimits_no;?>, '<?php echo  $nrs_overlimit->env_title;?>',  <?php echo $nrs_overlimit->overlimits_weight;?>]

	<?php	
		$ii++;
	}
	?>
	]);


	 var options = {
	      title: 'Number of Events (Events No) and related Magnitude (diameter of the bubble)',
	      hAxis: {title: 'Oldest Events <----------------------> Latest Events', textStyle:{fontSize: 6,color:'transparent'}},
	      vAxis: {title: 'Events No'},
	      bubble: {textStyle: {fontSize: 6,color:'transparent'}, opacity:0.90}
	    };
	var chart = new google.visualization.BubbleChart(document.getElementById('visualization'));

	google.visualization.events.addListener(chart, 'select', function() {
	   selectChart(chart.getSelection(),data);
	  });
    	chart.draw(data, options);
}

function selectChart(objectSelected,data) {
	sObjectSelected = objectSelected[0];
	alert("hei!");
}

<?php	
}
?>

