<?php defined('SYSPATH') or die('No direct script access.');

class Nrs_datapoints_Controller extends Admin_Controller
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
	

	public function datastream()
	{	
		$this->index();
	}
	
	
	public function index()
	{
		$this->template->content = new View('admin/manage/entities/nrs_datapoints');
		
		// Check if the last segment of the URI is numeric and grab it
		$nrs_entity_id = is_numeric($this->uri->last_segment())
					? $this->uri->last_segment()
					: "";
		$filter = "1<>1";
		// SQL filter from the parent entity (the Node)
		if( isset($nrs_entity_id)  AND !empty($nrs_entity_id) )
		{
			$filter = "nrs_datastream_id = " . $nrs_entity_id . " ";
		}

		$form_error = FALSE;
		$form_saved = FALSE;
		$form_action = "";
		
		$total_items = ORM::factory('nrs_datapoint')
						->where($filter)
						->count_all();
		
		$nrs_datapoints = ORM::factory('nrs_datapoint')
						->where($filter)
						->orderby('datetime_at','asc')
						->find_all();

		$this->template->content->nrs_datapoints = $nrs_datapoints;
		$this->template->content->nrs_parent_datastream = $this->_getParent_datastream($nrs_datapoints);
		$this->template->content->environments_array = $this->_environments_array();
		$this->template->content->nodes_array = $this->_nodes_array();
		$this->template->content->datastreams_array = $this->_datastreams_array();
		$this->template->content->form_error = $form_error;
		$this->template->content->form_saved = $form_saved;
		$this->template->content->form_action = $form_action;

		// Total Reports

		$this->template->content->total_items = $total_items;
		$this->template->js = new View('admin/manage/entities/nrs_datapoints_js');
		$this->template->js->nrs_datapoints = $nrs_datapoints;
		$this->template->js->nrs_parent_datastream = $this->_getParent_datastream($nrs_datapoints);
		$this->template->js->total_items = $total_items;
		$this->template->js->avg = $this->_datapoints_avg($filter);
	
	}

	public function edit($id = FALSE, $saved = FALSE)
	{
	}		
	
	private function _getParent_datastream($nrs_datapoints)
	{
		$nrs_parent_datastream = null;
		foreach($nrs_datapoints as $nrs_datapoint)
		{
			$nrs_parent_datastream = $nrs_datapoint->nrs_datastream;
			break;
		}
		return $nrs_parent_datastream;
	}

	// Function environment_array
	private function _environments_array()
	{
		$orm_environments = ORM::factory('nrs_environment')->find_all();
		$environment_array = array();
		foreach($orm_environments as $orm_environment)
		{
			$environment_array[$orm_environment->id] = $orm_environment->title;
		}
		return $environment_array;
	}
	// Function environment_array
	private function _datastreams_array()
	{
		$orm_datastreams = ORM::factory('nrs_datastream')
						->orderby('nrs_environment_id','asc')
						->orderby('nrs_node_id','asc')
                                                ->find_all();
		$datastream_array = array();
		foreach($orm_datastreams as $orm_datastream)
		{
			$arr_res = sscanf($orm_datastream->nrs_node->node_uid,$orm_datastream->nrs_environment->environment_uid."%s");
			$nrs_only_node_uid = $arr_res[0];
			$arr_res = sscanf($orm_datastream->datastream_uid,$orm_datastream->nrs_node->node_uid."%s");
			$nrs_only_datastream_uid = $arr_res[0];
			$datastream_array[$orm_datastream->id] = $orm_datastream->nrs_environment->title . "(".$orm_datastream->nrs_environment->environment_uid.") => " . $orm_datastream->nrs_node->title . "(".$nrs_only_node_uid.") => " . $orm_datastream->title . "(".$nrs_only_datastream_uid.")";
		}
		return $datastream_array;
	}


	// Function nodes_array
	private function _nodes_array()
	{
		$orm_nodes = ORM::factory('nrs_node')->find_all();
		$node_array = array();
		foreach($orm_nodes as $orm_node)
		{
			$arr_res = sscanf($orm_node->node_uid,$orm_node->nrs_environment->environment_uid."%s");
			$nrs_only_node_uid = $arr_res[0];
			$node_array[$orm_node->id] = $orm_node->nrs_environment->title . "(".$orm_node->nrs_environment->environment_uid.") => " . $orm_node->title . "(".$nrs_only_node_uid.")";
		}
		return $node_array;
	}
	
	private function _datapoints_avg($filter)
	{
		$datapoint_avg = 0;
		$sql_query = "SELECT AVG(nrs_datastream.constant_value + (value_at -nrs_datastream.lambda_value )*nrs_datastream.factor_value) AS datapoint_avg
				FROM 
				nrs_datapoint,
				nrs_datastream
				WHERE 
				nrs_datastream.id = nrs_datastream_id AND " .$filter;
		$db_instance = Database::instance('default');
		$results = $db_instance->query($sql_query);
		if(isset($results) && count($results)>0)
		{
			foreach ($results as $nrs_row)
			{
				$datapoint_avg = $nrs_row->datapoint_avg;
			}
		}
		return $datapoint_avg;
	}
}

?>
