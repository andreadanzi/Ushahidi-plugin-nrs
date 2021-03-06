<?php defined('SYSPATH') or die('No direct script access.');

class Nrs_nodes_Controller extends Admin_Controller
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

	public function environment()
	{	
		$this->index();
	}
	
	
	public function index()
	{
		$this->template->content = new View('admin/manage/entities/nrs_nodes');
		
		// Check if the last segment of the URI is numeric and grab it
		$nrs_entity_id = is_numeric($this->uri->last_segment())
					? $this->uri->last_segment()
					: "";
		
		// SQL filter
		$filter = (isset($nrs_entity_id)  AND !empty($nrs_entity_id))
					? " nrs_environment_id = '" . $nrs_entity_id . "' "
					: " 1=1";

		if ( !empty($_GET['nrs_id']))
		{
			$id = $_GET['nrs_id'];
			$filter .= " AND id = " . $id . " ";
		}

		if (!empty($_GET['list_filter']))
		{
			$list_filter = $_GET['list_filter']; // SQL filter for the id
			$filter .= " AND ( title LIKE '%" . $list_filter . "%' OR title LIKE '%" . strtoupper($list_filter) . "%' OR  title LIKE '%" . strtolower($list_filter) . "%' OR description LIKE '%" . $list_filter . "%' OR description LIKE '%" . strtoupper($list_filter) . "%' OR  description LIKE '%" . strtolower($list_filter) . "%' OR  node_uid LIKE '%" . $list_filter . "%' OR node_uid LIKE '%" . strtoupper($list_filter) . "%' OR node_uid LIKE '%" . strtolower($list_filter) . "%' OR node_disposition LIKE '%" . $list_filter . "%' OR node_disposition LIKE '%" . strtoupper($list_filter) . "%' OR node_disposition LIKE '%" . strtolower($list_filter) . "%'  OR node_exposure LIKE '%" . $list_filter . "%' OR node_exposure LIKE '%" . strtoupper($list_filter) . "%' OR node_exposure LIKE '%" . strtolower($list_filter) . "%' ) ";			
		}




		$form_error = FALSE;
		$form_saved = FALSE;
		$form_action = "";

		// Mettereci tutta la logica come in manage.php la funzione feeds
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
			}


			if( $post->validate() )
			{
				$nrs_node_id = $post->nrs_node_id;
				
				$nrs_node = new Nrs_node_Model($nrs_node_id);
				
				// Delete Action
				if ( $post->action == 'd' )
				{ 
					ORM::factory('nrs_datapoint')->where('nrs_node_id', $nrs_node_id)->delete_all();
					ORM::factory('nrs_datastream')->where('nrs_node_id', $nrs_node_id)->delete_all();
					$nrs_node->delete( $nrs_node_id );
					$form_saved = TRUE;
					$form_action = utf8::strtoupper(Kohana::lang('ui_admin.deleted'));
				}
				
				// Hide Action
				else if ($post->action=='h')
				{
					if($nrs_node->loaded)
					{
						$nrs_node->active = 3;
						$nrs_node->save();
						$form_saved = TRUE;
						$form_action = utf8::strtoupper(Kohana::lang('ui_main.hidden'));
					}	
				}
				
				// Show Action
				else if ($post->action == 'v')
				{ 

					if ($nrs_node->loaded)
					{
						$nrs_node->active = 1;
						$nrs_node->save();
						$form_saved = TRUE;
						$form_action = utf8::strtoupper(Kohana::lang('ui_admin.shown'));
					}
				}
				// Save Action
				else
				{ 
					$nrs_node->title = $post->title;
					$nrs_node->node_uid = trim($post->environment_uid).trim($post->only_node_uid);
					$nrs_node->description = $post->description;
					$nrs_node->status = $post->status;
					$nrs_node->node_disposition = $post->node_disposition;
					$nrs_node->node_exposure = $post->node_exposure;
					$nrs_node->nrs_environment_id = $post->nrs_environment_id;
					// $nrs_node->last_update = $post->last_update;
					$nrs_node->updated = date("Y-m-d H:i:s",time());
					$nrs_node->risk_level = $post->risk_level;
					$nrs_node->save();
			

					// nrs_node_category - delete Previous Entries
					ORM::factory('nrs_node_category')->where('nrs_node_id', $nrs_node->id)->delete_all();
		
					foreach ($post->nrs_node_category as $item)
					{
						$node_category = new Nrs_node_Category_Model();
						$node_category->nrs_node_id = $nrs_node->id;
						$node_category->category_id = $item;
						$node_category->save();
					}

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
			'total_items' => ORM::factory('nrs_node')
							->where($filter)
							->count_all()
		));

		$nrs_nodes = ORM::factory('nrs_node')
						->where($filter)
						->orderby('updated','desc')
						->find_all($this->items_per_page, $pagination->sql_offset);

		// Create Categories
		$this->template->content->categories = Category_Model::get_categories(0, FALSE, FALSE);
		$this->template->content->new_categories_form = $this->_new_categories_form_arr();
		$this->template->content->new_category_toggle_js = $this->_new_category_toggle_js();
		$this->template->content->color_picker_js = $this->_color_picker_js();
		$this->template->treeview_enabled = TRUE;

		$this->template->content->nrs_nodes = $nrs_nodes;
		$this->template->content->status_array = $this->_status_array();
		$this->template->content->risk_level_array = $this->_risk_level_array();
		$this->template->content->environments_array = $this->_environments_array();
		$this->template->content->environment_uids_array = $this->_environment_uids_array();
		$this->template->content->pagination = $pagination;
		$this->template->content->form_error = $form_error;
		$this->template->content->form_saved = $form_saved;
		$this->template->content->form_action = $form_action;

		// Total Reports

		$this->template->content->total_items = $pagination->total_items;
		$this->template->js = new View('admin/manage/entities/nrs_nodes_js');

		$this->template->js->nrs_datapoints = $this->_datapoints_array($filter);
	
	}

	public function edit($id = FALSE, $saved = FALSE)
	{
	}		
	// Status array function 
	private function _status_array()
	{
		$status_array[1] = "OFF";
		$status_array[2] = "SLEEPING";
		$status_array[3] = "ON";
		$status_array[4] = "TRANSMITTING";
		return $status_array;
	}
	// Function risk_level_array
	private function _risk_level_array()
	{
		$risk_array[1] = "NULL";
		$risk_array[2] = "LOW";
		$risk_array[3] = "MEDIUM";
		$risk_array[4] = "HIGHT";
		return $risk_array;
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
	// Function _datapoints_array
	private function _datapoints_array($filter)
	{
		if ( !empty($_GET['nrs_id']))
		{
			$id = $_GET['nrs_id'];
			$orm_nodes = ORM::factory('nrs_node')->where('id',$id)->find_all();
		}	
		else {			
			$orm_nodes = ORM::factory('nrs_node')
							->where($filter)
                                                        ->find_all();
		}
		$datapoints_array = array();
		foreach($orm_nodes as $node)
		{
			$nrs_datastreams = ORM::factory('nrs_datastream')->where('nrs_node_id',$node->id)->find_all();
			$sql_query = "SELECT nrs_environment_id, updated, sample_no";
			foreach ($nrs_datastreams as $nrs_datastream)
			{
				$sql_query .= ", SUM(CASE WHEN `nrs_datastream_id` = '".$nrs_datastream->id."' THEN `value_at` ELSE 0 END) AS 'ds".$nrs_datastream->id."'";
			}
			$sql_query .= " FROM nrs_datapoint WHERE nrs_node_id = ";
			$sql_query .= $node->id;
			$sql_query .= " GROUP BY nrs_environment_id ,updated ,sample_no ORDER BY datetime_at DESC LIMIT 0,10";
			$db_instance = Database::instance('default');
			$results = $db_instance->query($sql_query);
			if(isset($results) && count($results)>0)
			{
				$datapoints_array["head"][$node->id] = $nrs_datastreams;
				$array_list=array();
				$i=0;
				foreach ($results as $nrs_row)
				{
					$array_item["sample_no"]=$nrs_row->sample_no;
					foreach ($nrs_datastreams as $nrs_datastream)
					{
						$array_item["ds". $nrs_datastream->id] = $nrs_row->{"ds".$nrs_datastream->id};
					}
					$array_list[$i]=$array_item;
					$i++;
				}
				$datapoints_array["array_items"][$node->id] = array_reverse($array_list);
			}
		}
		return $datapoints_array;
	}

	// Dynamic categories form fields
	private function _new_categories_form_arr()
	{
		return array(
			'category_name' => '',
			'category_description' => '',
			'category_color' => '',
		);
	}

	// Javascript functions
	private function _new_category_toggle_js()
	{
		return "<script type=\"text/javascript\">
				$(document).ready(function() {
				$('a#category_toggle').click(function() {
				$('#category_add').toggle(400);
				return false;
				});
				});
			</script>";
	}

	private function _color_picker_js()
	{
		 return "<script type=\"text/javascript\">
					$(document).ready(function() {
					$('#category_color').ColorPicker({
							onSubmit: function(hsb, hex, rgb) {
								$('#category_color').val(hex);
							},
							onChange: function(hsb, hex, rgb) {
								$('#category_color').val(hex);
							},
							onBeforeShow: function () {
								$(this).ColorPickerSetColor(this.value);
							}
						})
					.bind('keyup', function(){
						$(this).ColorPickerSetColor(this.value);
					});
					});
				</script>";
	}
}

?>
