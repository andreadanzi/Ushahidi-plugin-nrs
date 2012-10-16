<?php defined('SYSPATH') or die('No direct script access.');
/**
 * Nrs Scheduler Controller
 *
 * PHP version 5
 * LICENSE: This source file is subject to LGPL license
 * that is available through the world-wide-web at the following URI:
 * http://www.gnu.org/copyleft/lesser.html
 * @author     Ushahidi Team <team@ushahidi.com>
 * @package    Ushahidi - http://source.ushahididev.com
 * @subpackage Scheduler
 * @copyright  Ushahidi - http://www.ushahidi.com
 * @license    http://www.gnu.org/copyleft/lesser.html GNU Lesser General Public License (LGPL)
*/

class S_Nrs_Controller extends Controller {

	public function __construct()
 	{
        	parent::__construct();
	}

	public function index()
	{
		// Get all currently active shares
		$overlimits = $this->_check_overlimits();
		foreach ($overlimits as $overlimit)
		{
			$this->_generate_report($overlimit->nrs_datastream_id,$overlimit->title, $overlimit->updated , null);
		}
	}

	private function _check_overlimits()
	{
		$sql_query = "SELECT count( * ) AS overlimits_no, CONVERT( sum( abs(
				CASE WHEN calculated_value >= max_value
				THEN calculated_value - max_value
				ELSE min_value - calculated_value
				END ) ) / sum( abs( max_value - min_value ) ) , DECIMAL( 10, 3 ) ) AS overlimits_weight, 
				nrs_environment_id, nrs_node_id, nrs_datastream_id, nrs_overlimits.updated, nrs_overlimits.title, nrs_environment.title as env_title
				FROM nrs_overlimits, nrs_environment
				WHERE nrs_environment.id = nrs_environment_id AND nrs_overlimits.incident_id = 0 AND nrs_environment.active = 1 AND  nrs_environment.automatic_reports = 1
				GROUP BY nrs_environment_id, nrs_node_id, nrs_datastream_id, updated
				ORDER BY updated DESC , nrs_datastream_id ASC";
		$db_instance = Database::instance('default');
		$overlimits_results = $db_instance->query($sql_query);
		return $overlimits_results;
	}

	private function _generate_report($nrs_datastream_id,$overlimit_title, $updated_timestamp , $whole_node)
	{
		$nrs_datastream = new Nrs_datastream_Model($nrs_datastream_id);
		// Yes! everything is valid
		$location_id = $nrs_datastream->nrs_environment->location->id;
		// STEP 1: SAVE LOCATION
		$location = new Location_Model($location_id);

		// STEP 2: SAVE INCIDENT
		$incident = new Incident_Model(False);
		$incident->incident_dateadd = date("Y-m-d H:i:s",time());
		$incident->location_id = $location_id;
		// Check if the user id has been specified
		if ( ! $incident->loaded AND isset($_SESSION['auth_user']))
		{
			$incident->user_id = $_SESSION['auth_user']->id;
		}
		$incident->incident_title = $overlimit_title;
		$incident->incident_description = "Report Autoamtically Generated from " . $nrs_datastream->nrs_environment->title . ": ". $overlimit_title . " at " . $updated_timestamp;
		$incident->incident_date = $updated_timestamp;
		$incident->incident_mode = 5; // NRS Service TO BE IMPLEMENTED

		$incident->incident_active = 1;
		$incident->incident_verified = 1;
		$incident->incident_alert_status = 1;
		$incident->save();

		// STEP 2b: Record Approval/Verification Action
		reports::verify_approve($incident);

		// STEP 2c: SAVE INCIDENT GEOMETRIES
		// reports::save_report_geometry($post, $incident);

		// STEP 3: SAVE CATEGORIES...COLLEGATO AL NODO
		// Delete Previous Entries

		ORM::factory('incident_category')->where('incident_id', $incident->id)->delete_all();

		foreach ($nrs_datastream->nrs_node->nrs_node_category as $category)
		{
			$incident_category = new Incident_Category_Model();
			$incident_category->incident_id = $incident->id;
			$incident_category->category_id = $category->category_id;
			$incident_category->save();
		}

		// STEP 4: SAVE MEDIA IMMAGINE DEL GRAFICO COLLEGATO A....
		// reports::save_media($post, $incident);



		$databary=$this->_get_bar_array($nrs_datastream_id,$updated_timestamp);
		// New graph with a drop shadow
		$graph = new Graph(600,400);
		$graph->SetShadow();
		// Use a "text" X-scale
		$graph->SetScale("textlin");

		$theme_class=new UniversalTheme;
		$graph->SetTheme($theme_class);

		$graph->SetBox(false);

		$graph->ygrid->Show(true);
		$graph->xgrid->Show(false);
		$graph->yaxis->HideZeroLabel();
		$graph->ygrid->SetFill(true,'#FFFFFF@0.5','#FFFFFF@0.5');
		// $graph->SetBackgroundGradient('#0090DF', '#1FC4FF', GRAD_HOR, BGRAD_PLOT);

		// Set title and subtitle
		$graph->title->Set("NRS Events for Report ". $incident->incident_title );
		if( isset($databary) && count($databary) < 20 ) {
			$graph->xaxis->SetTickLabels($this->_get_label_array($nrs_datastream_id,$updated_timestamp));
		}
		else
		{
			$graph->xaxis->HideLabels();
		}

		// Create the line
		$p1 = new LinePlot($databary);
		$graph->Add($p1);

		$p1->SetFillGradient('#AF0A0A','#6ADF45');
		$p1->SetStepStyle();
		$p1->SetColor('#808000');

		$filename = Kohana::config('upload.directory', TRUE)."overlimits_".$nrs_datastream_id.".png";
		$i = 1;
		// Finally output the  image
		$graph->Stroke($filename);

		$this->_save_media($filename,$incident);


		// STEP 5: SAVE PERSONAL INFORMATION
		reports::save_personal_info($nrs_datastream->nrs_environment, $incident);

		// STEP 6: update nrs_datapoint
		
		$table_prefix = Kohana::config('database.default.table_prefix');
		if(isset($whole_node)) 
		{
			Database::instance()->query('UPDATE `'.$table_prefix.'nrs_datapoint` SET incident_id = ? WHERE nrs_node_id = ? AND updated = ?', $incident->id, $nrs_datastream->nrs_node_id, $updated_timestamp );
			// In the case of WHOLE NODE.......we need to manage the incident properly...Description??
		}
		else {
			Database::instance()->query('UPDATE `'.$table_prefix.'nrs_datapoint` SET incident_id = ? WHERE nrs_datastream_id = ? AND updated = ?',	$incident->id, $nrs_datastream->id, $updated_timestamp );
		}
	}
	
	private function _save_media($filename, $incident)
	{
		// Delete Previous Entries
		ORM::factory('media')->where('incident_id',$incident->id)->where('media_type <> 1')->delete_all();
		
		$i = 1;

		$new_filename = $incident->id.'_'.$i.'_'.time();

		$file_type = strrev(substr(strrev($filename),0,4));
				
		// Name the files for the DB
		$media_link = Kohana::config('upload.directory', TRUE).$new_filename.$file_type;
		$media_medium = Kohana::config('upload.directory', TRUE).$new_filename.'_m'.$file_type;
		$media_thumb = Kohana::config('upload.directory', TRUE).$new_filename.'_t'.$file_type;
		$targetFile = $media_medium;
		$newWidth = 400;		
		$src = imagecreatefrompng($filename);
		list($width, $height) = getimagesize($filename);
		$newHeight = ($height / $width) * $newWidth;
		$tmp = imagecreatetruecolor($newWidth, $newHeight);
		imagecopyresampled($tmp, $src, 0, 0, 0, 0, $newWidth, $newHeight, $width, $height);
		if (file_exists($targetFile)) {
            		unlink($targetFile);
        	}
        	imagepng($tmp, $targetFile, 6);

		$targetFile = $media_thumb;
		$newWidth = 89;		
		$src = imagecreatefrompng($filename);
		list($width, $height) = getimagesize($filename);
		$newHeight = ($height / $width) * $newWidth;
		$tmp = imagecreatetruecolor($newWidth, $newHeight);
		imagecopyresampled($tmp, $src, 0, 0, 0, 0, $newWidth, $newHeight, $width, $height);
		if (file_exists($targetFile)) {
            		unlink($targetFile);
        	}
        	imagepng($tmp, $targetFile, 6);

		copy($filename,$media_link);	
		// Remove the temporary file
		unlink($filename);

		// Save to DB
		$photo = new Media_Model();
		$photo->location_id = $incident->location_id;
		$photo->incident_id = $incident->id;
		$photo->media_type = 1; // Images
		$photo->media_link = $new_filename.$file_type;
		$photo->media_medium = $new_filename.'_m'.$file_type;
		$photo->media_thumb = $new_filename.'_t'.$file_type;
		$photo->media_date = date("Y-m-d H:i:s",time());
		$photo->save();
		$i++;
	}

	private function _get_bar_array($nrs_datastream_id,$updated_timestamp)
	{
		$bar_array = array();
		$sql_query = "SELECT CONVERT( abs(
				CASE WHEN calculated_value >= max_value
				THEN calculated_value - max_value
				ELSE min_value - calculated_value
				END )/ abs( max_value - min_value ) , DECIMAL( 10, 3 ) ) AS overlimits_weight, 
				nrs_environment_id, nrs_node_id, nrs_datastream_id, nrs_overlimits.updated, nrs_overlimits.title, nrs_environment.title as env_title, sample_no, datetime_at
				FROM nrs_overlimits, nrs_environment
				WHERE nrs_environment.id = nrs_environment_id AND nrs_overlimits.incident_id = 0 AND
				nrs_overlimits.nrs_datastream_id = ? AND nrs_overlimits.updated = ? 
				ORDER BY datetime_at ASC , sample_no ASC";
		$overlimits_results =Database::instance('default')->query($sql_query,$nrs_datastream_id,$updated_timestamp);
		$i=0;
		foreach($overlimits_results as $overlimits_result) {
			$bar_array[$i] = $overlimits_result->overlimits_weight;
			$i++;
		}
		return $bar_array;
	}

	private function _get_label_array($nrs_datastream_id,$updated_timestamp)
	{
		$bar_array = array();
		$sql_query = "SELECT CONVERT( abs(
				CASE WHEN calculated_value >= max_value
				THEN calculated_value - max_value
				ELSE min_value - calculated_value
				END )/ abs( max_value - min_value ) , DECIMAL( 10, 3 ) ) AS overlimits_weight, 
				nrs_environment_id, nrs_node_id, nrs_datastream_id, nrs_overlimits.updated, nrs_overlimits.title, nrs_environment.title as env_title, sample_no, datetime_at
				FROM nrs_overlimits, nrs_environment
				WHERE nrs_environment.id = nrs_environment_id AND nrs_overlimits.incident_id = 0 AND
				nrs_overlimits.nrs_datastream_id = ? AND nrs_overlimits.updated = ? 
				ORDER BY datetime_at ASC , sample_no ASC";
		$overlimits_results =Database::instance('default')->query($sql_query,$nrs_datastream_id,$updated_timestamp);
		$i=0;
		foreach($overlimits_results as $overlimits_result) {
			$bar_array[$i] = $overlimits_result->sample_no;
			$i++;
		}
		return $bar_array;
	}

}
