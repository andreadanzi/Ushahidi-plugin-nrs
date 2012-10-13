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
	var data = new google.visualization.DataTable();
	
	data.addColumn('string', 'Datastream');
	data.addColumn('date', 'Date');
	data.addColumn('number', 'Quantity');
	data.addColumn('number', 'Magnitude');
	data.addColumn('string', 'Site');
	data.addRows([
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
	<?php if($ii>0) echo ","; ?>['<?php echo $nrs_overlimit->title;?>', new Date(<?php echo $formtatted_date_js;?>), <?php echo $nrs_overlimit->overlimits_no;?>, <?php echo $nrs_overlimit->overlimits_weight;?>, '<?php echo $nrs_overlimit->nrs_environment_id;?>']

	<?php	
		$ii++;
	}
	?>
	]);

	var motionchart = new google.visualization.MotionChart(
	  document.getElementById('visualization'));
	
		motionchart.draw(data, {'width': 800, 'height': 400});
}

function selectChart() {
	
}

<?php	
}
?>

