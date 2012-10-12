<?php defined('SYSPATH') or die('No direct script access.');

class Nrs_datastreams_Controller extends Admin_Controller
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
	

	public function node()
	{	
		$this->index();
	}
	
	
	public function index()
	{
		$this->template->content = new View('admin/manage/entities/nrs_datastreams');
		
		// Check if the last segment of the URI is numeric and grab it
		$nrs_entity_id = is_numeric($this->uri->last_segment())
					? $this->uri->last_segment()
					: "";
		
		// SQL filter from the parent entity (the Node)
		$filter = (isset($nrs_entity_id)  AND !empty($nrs_entity_id))
					? " nrs_node_id = '" . $nrs_entity_id . "' "
					: " 1=1";

		if ( !empty($_GET['nrs_id']))
		{
			$id = $_GET['nrs_id']; // SQL filter for the id
			$filter .= " AND id = " . $id . " ";
		}

		if (!empty($_GET['list_filter']))
		{
			$list_filter = $_GET['list_filter']; // SQL filter for the id
			$filter .= " AND ( title LIKE '%" . $list_filter . "%' OR title LIKE '%" . strtoupper($list_filter) . "%' OR  title LIKE '%" . strtolower($list_filter) . "%' OR  unit_label LIKE '%" . $list_filter . "%' OR unit_label LIKE '%" . strtoupper($list_filter) . "%' OR  unit_label LIKE '%" . strtolower($list_filter) . "%' OR  unit_type LIKE '%" . $list_filter . "%' OR unit_type LIKE '%" . strtoupper($list_filter) . "%' OR  unit_type LIKE '%" . strtolower($list_filter) . "%' OR  factor_title LIKE '%" . $list_filter . "%' OR factor_title LIKE '%" . strtoupper($list_filter) . "%' OR factor_title LIKE '%" . strtolower($list_filter) . "%'  OR  tags LIKE '%" . $list_filter . "%' OR tags LIKE '%" . strtoupper($list_filter) . "%' OR tags LIKE '%" . strtolower($list_filter) . "%' OR  datastream_uid LIKE '%" . $list_filter . "%' OR datastream_uid LIKE '%" . strtoupper($list_filter) . "%' OR datastream_uid LIKE '%" . strtolower($list_filter) . "%') ";			
		}
		$form_error = FALSE;
		$form_saved = FALSE;
		$form_action = "";

		//
		if( $_POST )	
		{
			$post = Validation::factory($_POST);
			
			 //  Add some filters
		        $post->pre_filter('trim', TRUE);
	
			// Add Action
			if ($post->action == 'a')		
			{
				// Add some rules, the input field, followed by a list of checks, carried out in order
				$post->add_rules('title','required', 'length[3,250]');
				$post->add_rules('environment_uid','required', 'length[1,255]');
				$post->add_rules('only_node_uid','required', 'length[1,255]');
				$post->add_rules('only_datastream_uid','required', 'length[1,255]');
			}


			if( $post->validate() )
			{
				$nrs_datastream_id = $post->nrs_datastream_id;
				
				$nrs_datastream = new Nrs_datastream_Model($nrs_datastream_id);
				
				// Delete Action
				if ( $post->action == 'd' )
				{ 
					ORM::factory('nrs_datapoint')->where('nrs_datastream_id', $nrs_datastream_id)->delete_all();
					$nrs_datastream->delete( $nrs_datastream_id );
					$form_saved = TRUE;
					$form_action = utf8::strtoupper(Kohana::lang('ui_admin.deleted'));
				}
				
				// Hide Action
				else if ($post->action=='h')
				{
					if($nrs_datastream->loaded)
					{
						$nrs_datastream->active = 3;
						$nrs_datastream->save();
						$form_saved = TRUE;
						$form_action = utf8::strtoupper(Kohana::lang('ui_main.hidden'));
					}	
				}
				
				// Show Action
				else if ($post->action == 'v')
				{ 

					if ($nrs_datastream->loaded)
					{
						$nrs_datastream->active = 1;
						$nrs_datastream->save();
						$form_saved = TRUE;
						$form_action = utf8::strtoupper(Kohana::lang('ui_admin.shown'));
					}
				}
				// Save Action
				else
				{ 
					$nrs_datastream->title = $post->title;
					$nrs_datastream->datastream_uid = trim($post->environment_uid).trim($post->only_node_uid).trim($post->only_datastream_uid);
					$nrs_datastream->unit_label = $post->unit_label;
					$nrs_datastream->unit_type = $post->unit_type;
					$nrs_datastream->unit_symbol = $post->unit_symbol;
					$nrs_datastream->unit_format = $post->unit_format;
					$nrs_datastream->tags = $post->tags;
					$nrs_datastream->current_value = (empty($post->current_value)? NULL: $post->current_value);
					$nrs_datastream->min_value = (empty($post->min_value)? NULL: $post->min_value);
					$nrs_datastream->max_value = (empty($post->max_value)? NULL: $post->max_value);
					$nrs_datastream->nrs_environment_id = $post->nrs_environment_id;
					$nrs_datastream->nrs_node_id = $post->nrs_node_id;
					$nrs_datastream->samples_num = (empty($post->samples_num)? NULL: $post->samples_num);
					$nrs_datastream->factor_title = $post->factor_title;
					$nrs_datastream->factor_value = $post->factor_value;
					$nrs_datastream->lambda_value = $post->lambda_value;
					$nrs_datastream->constant_value = $post->constant_value;
					$nrs_datastream->updated = date("Y-m-d H:i:s",time());
					$nrs_datastream->save();
			
					$form_saved = TRUE;
					$form_action = utf8::strtoupper(Kohana::lang('ui_admin.created_edited'));
				}
				
			}
			else
			{
				// Repopulate the form fields
				$form = arr::overwrite($form, $post->as_array());
	
			        // Populate the error fields, if any
				$errors = arr::overwrite($errors, $post->errors('nrs'));
				$form_error = TRUE;
			}


		}

		// Pagination
		$pagination = new Pagination(array(
			'query_string' => 'page',
			'items_per_page' => $this->items_per_page,
			'total_items' => ORM::factory('nrs_datastream')
							->where($filter)
							->count_all()
		));

		$nrs_datastreams = ORM::factory('nrs_datastream')
						->where($filter)
						->orderby('updated','desc')
						->find_all($this->items_per_page, $pagination->sql_offset);

		$this->template->content->nrs_datastreams = $nrs_datastreams;
		$this->template->content->environments_array = $this->_environments_array();
		$this->template->content->environment_uids_array = $this->_environment_uids_array();
		$this->template->content->nodes_uids_array = $this->_nodes_uids_array();
		$this->template->content->nodes_array = $this->_nodes_array();
		$this->template->content->pagination = $pagination;
		$this->template->content->form_error = $form_error;
		$this->template->content->form_saved = $form_saved;
		$this->template->content->form_action = $form_action;

		// Total Reports

		$this->template->content->total_items = $pagination->total_items;
		$this->template->js = new View('admin/manage/entities/nrs_datastreams_js');
		$this->template->js->nrs_datastreams = $nrs_datastreams;
	
	}

	public function edit($id = FALSE, $saved = FALSE)
	{
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
	// Function environment_uid_array
	private function _environment_uids_array()
	{
		$orm_environments = ORM::factory('nrs_environment')->find_all();
		$environment_array = array();
		foreach($orm_environments as $orm_environment)
		{
			$environment_array[$orm_environment->id] = $orm_environment->environment_uid;
		}
		return $environment_array;
	}

	// Function nodes_uids_array
	private function _nodes_uids_array()
	{
		$orm_nodes = ORM::factory('nrs_node')->find_all();
		$node_array = array();
		foreach($orm_nodes as $orm_node)
		{
			$arr_res = sscanf($orm_node->node_uid,$orm_node->nrs_environment->environment_uid."%s");
			$nrs_only_node_uid = $arr_res[0];
			$node_array[$orm_node->id] = array("id"=>$orm_node->id, "title" =>  $orm_node->title , "uid" => $nrs_only_node_uid, "env_uid" => $orm_node->nrs_environment->environment_uid, "env_id" => $orm_node->nrs_environment->environment_uid);
		}
		return $node_array;
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
}

?>
