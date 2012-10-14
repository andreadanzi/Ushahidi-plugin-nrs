<?php defined('SYSPATH') or die('No direct script access.');

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
		/* da rivedere e togliere $updated_filter = "1=1";
		$distinct_updated_array = $this->_distinct_updated($filter);
		if(!isset($updated_filter) ) {
			if(isset($distinct_updated_array) && !empty($distinct_updated_array) ) {
				foreach($distinct_updated_array as $key=>$updated_item) {
					$updated_filter = "updated='".$key."'";
					break;
				}
				if ( empty($_GET['nrs_updated']))
				{
					$_GET['nrs_updated'] = $updated_filter;
				}

			} else {
				$updated_filter = "1=1";
			}
		}
		*/
		$updated_filter = "1=1";
		$nrs_overlimits = ORM::factory('nrs_overlimits')
						->where($filter)
						->where($updated_filter)
						->orderby('updated','desc')
						->orderby('nrs_datastream_id','asc')
						->find_all();

		$this->template->content->nrs_overlimits = $nrs_overlimits;
		$this->template->content->form_error = $form_error;
		$this->template->content->form_saved = $form_saved;
		$this->template->content->form_action = $form_action;
		// Total Reports

		$this->template->js = new View('admin/manage/entities/nrs_overlimits_js');
		$this->template->js->nrs_overlimits = $this->_check_overlimits();
	
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
				WHERE nrs_environment.id = nrs_environment_id
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

}
?>
