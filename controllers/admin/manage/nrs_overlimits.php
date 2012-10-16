<?php defined('SYSPATH') or die('No direct script access.');

require_once('jpgraph/jpgraph.php');
require_once ('jpgraph/jpgraph_bar.php');
require_once ('jpgraph/jpgraph_line.php');

class Nrs_overlimits_Controller extends Admin_Controller
{
	// private $_registered_blocks;

	function __construct()
	{
		parent::__construct();
		$this->template->this_page = 'settings';
		
		// If user doesn't have access, redirect to dashboard
		if ( ! $this->auth->has_permission("manage"))
		{
			url::redirect(url::site().'admin/dashboard');
		}
		
		//$this->_registered_blocks = Kohana::config("settings.blocks");
	}
	
	
	public function index()
	{
		$this->template->content = new View('admin/manage/entities/nrs_overlimits');
		
		// Check if the last segment of the URI is numeric and grab it
		$nrs_entity_id = is_numeric($this->uri->last_segment())
					? $this->uri->last_segment()
					: "";
		$filter = "1<>1";

		if ( !empty($_GET['nrs_datastream_id']))
		{
			$nrs_entity_id = $_GET['nrs_datastream_id'];
		}
		if ( !empty($_GET['nrs_updated']))
		{
			$nrs_updated_get = $_GET['nrs_updated'];
			$updated_filter = "updated='".$nrs_updated_get."'";
		}
		// SQL filter from the parent entity (the Node)
		if( isset($nrs_entity_id)  AND !empty($nrs_entity_id) )
		{
			$filter = "nrs_datastream_id = " . $nrs_entity_id . " ";
			if ( empty($_GET['nrs_datastream_id']))
			{
				$_GET['nrs_datastream_id'] = $nrs_entity_id;
			}
		}

		$form_error = FALSE;
		$form_saved = FALSE;
		$form_action = "";

		if( $_POST )	
		{
			$post = Validation::factory($_POST);
			
			 //  Add some filters
		        $post->pre_filter('trim', TRUE);
	
			// Add Action
			if ($post->action == 'g')		
			{
				// Add some rules, the input field, followed by a list of checks, carried out in order
				$post->add_rules('title','required', 'length[3,250]');
				$post->add_rules('nrs_datastream_id','required', 'length[1,255]');
				$post->add_rules('updated_timestamp','required', 'length[1,255]');
			}

			if( $post->validate() )
			{
				$nrs_datastream_id = $post->nrs_datastream_id;
				$updated_timestamp = $post->updated_timestamp;
				// Generate Report Action
				if ( $post->action == 'g' )
				{
					$this->_generate_report($nrs_datastream_id,$post->title, $updated_timestamp , isset($post->whole_node) ? $post->whole_node: null);
				}
			}

		}




		$this->template->content->date_picker_js = $this->_date_picker_js();
		$this->template->content->hour_array = $this->_hour_array();
		$this->template->content->minute_array = $this->_minute_array();
		$this->template->content->second_array = $this->_minute_array();
		$this->template->content->ampm_array = $this->_ampm_array();
		$this->template->content->nrs_overlimits = $this->_check_overlimits();
		$this->template->content->form_error = $form_error;
		$this->template->content->form_saved = $form_saved;
		$this->template->content->form_action = $form_action;
		// Total Reports

		$this->template->js = new View('admin/manage/entities/nrs_overlimits_js');
		$this->template->js->nrs_overlimits = $this->template->content->nrs_overlimits;
	
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
	private function _check_overlimits()
	{
		$sql_query = "SELECT count( * ) AS overlimits_no, CONVERT( sum( abs(
				CASE WHEN calculated_value >= max_value
				THEN calculated_value - max_value
				ELSE min_value - calculated_value
				END ) ) / sum( abs( max_value - min_value ) ) , DECIMAL( 10, 3 ) ) AS overlimits_weight, 
				nrs_environment_id, nrs_node_id, nrs_datastream_id, nrs_overlimits.updated, nrs_overlimits.title, nrs_environment.title as env_title
				FROM nrs_overlimits, nrs_environment
				WHERE nrs_environment.id = nrs_environment_id AND nrs_overlimits.incident_id = 0
				GROUP BY nrs_environment_id, nrs_node_id, nrs_datastream_id, updated
				ORDER BY updated DESC , nrs_datastream_id ASC";
		$db_instance = Database::instance('default');
		$overlimits_results = $db_instance->query($sql_query);
		return $overlimits_results;
	}

	private function _distinct_updated($filter)
	{
		$distinct_updated_array=array();
		$sql_query = "SELECT DISTINCT `updated` AS UPDATED, count(*) AS NO_ITEMS
				FROM nrs_overlimits
				WHERE ". $filter . " GROUP BY updated
				ORDER BY updated DESC";
		$db_instance = Database::instance('default');
		$distinct_updated_results = $db_instance->query($sql_query);
		foreach($distinct_updated_results as $distinct_updated_result)
		{
			$distinct_updated_array[$distinct_updated_result->UPDATED] = $distinct_updated_result->UPDATED . " (" . $distinct_updated_result->NO_ITEMS . ")";
		}
		$distinct_updated_array['']="None";
		return $distinct_updated_array;
	}

	private function _date_picker_js()
	{
		return "<script type=\"text/javascript\">
				$(document).ready(function() {
				$(\"#incident_date\").datepicker({
				showOn: \"both\",
				buttonImage: \"" . url::base() . "media/img/icon-calendar.gif\",
				buttonImageOnly: true
				});
				});
			</script>";
	}

	// Time functions
	private function _hour_array()
	{
		for ($i=1; $i <= 24 ; $i++)
		{
			// Add Leading Zero
			$hour_array[sprintf("%02d", $i)] = sprintf("%02d", $i);
		}
		return $hour_array;
	}

	private function _minute_array()
	{
		for ($j=0; $j <= 59 ; $j++)
		{
			// Add Leading Zero
			$minute_array[sprintf("%02d", $j)] = sprintf("%02d", $j);
		}
		return $minute_array;
	}

	private function _ampm_array()
	{
		return $ampm_array = array('pm'=>Kohana::lang('ui_admin.pm'),'am'=>Kohana::lang('ui_admin.am'));
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
		$incident->incident_description = "Report Manually Generated from " . $nrs_datastream->nrs_environment->title . ": ". $overlimit_title . " at " . $updated_timestamp;
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

}
?>
