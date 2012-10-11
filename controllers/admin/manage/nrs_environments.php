<?php defined('SYSPATH') or die('No direct script access.');

class Nrs_environments_Controller extends Admin_Controller
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
	


	public function id()
	{	
		$this->index();
	}

	function index()
	{
		$this->template->content = new View('admin/manage/entities/nrs_environments');
		
		// Check if the last segment of the URI is numeric and grab it
		$nrs_entity_id = is_numeric($this->uri->last_segment())
					? $this->uri->last_segment()
					: "";
		
		// SQL filter
		$filter = (isset($nrs_entity_id)  AND !empty($nrs_entity_id))
					? " id = '" . $nrs_entity_id . "' "
					: " 1=1";


		if (!empty($_GET['list_filter']))
		{
			$list_filter = $_GET['list_filter']; // SQL filter for the id
			$filter .= " AND ( title LIKE '%" . $list_filter . "%' OR title LIKE '%" . strtoupper($list_filter) . "%' OR  title LIKE '%" . strtolower($list_filter) . "%' OR description LIKE '%" . $list_filter . "%' OR description LIKE '%" . strtoupper($list_filter) . "%' OR  description LIKE '%" . strtolower($list_filter) . "%' OR  environment_uid LIKE '%" . $list_filter . "%' OR environment_uid LIKE '%" . strtoupper($list_filter) . "%' OR environment_uid LIKE '%" . strtolower($list_filter) . "%' OR location_disposition LIKE '%" . $list_filter . "%' OR location_disposition LIKE '%" . strtoupper($list_filter) . "%' OR location_disposition LIKE '%" . strtolower($list_filter) . "%'  OR location_exposure LIKE '%" . $list_filter . "%' OR location_exposure LIKE '%" . strtoupper($list_filter) . "%' OR location_exposure LIKE '%" . strtolower($list_filter) . "%' OR location_name LIKE '%" . $list_filter . "%' OR location_name LIKE '%" . strtoupper($list_filter) . "%' OR location_name LIKE '%" . strtolower($list_filter) . "%' ) ";			
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
				$post->add_rules('environment_uid','required', 'length[3,255]');
			}


			if( $post->validate() )
			{
				$nrs_environment_id = $post->nrs_environment_id;
				
				$nrs_environment = new Nrs_environment_Model($nrs_environment_id);
				
				// Delete Action
				if ( $post->action == 'd' )
				{ 
					ORM::factory('nrs_datapoint')->where('nrs_environment_id', $nrs_environment_id)->delete_all();
					ORM::factory('nrs_datastream')->where('nrs_environment_id', $nrs_environment_id)->delete_all();
					ORM::factory('nrs_node')->where('nrs_environment_id', $nrs_environment_id)->delete_all();
					$nrs_environment->delete( $nrs_environment_id );
					$form_saved = TRUE;
					$form_action = utf8::strtoupper(Kohana::lang('ui_admin.deleted'));
				}
				
				// Hide Action
				else if ($post->action=='h')
				{
					if($nrs_environment->loaded)
					{
						$nrs_environment->active = 3;
						$nrs_environment->save();
						$form_saved = TRUE;
						$form_action = utf8::strtoupper(Kohana::lang('ui_main.hidden'));
					}	
				}
				
				// Show Action
				else if ($post->action == 'v')
				{ 

					if ($nrs_environment->loaded)
					{
						$nrs_environment->active = 1;
						$nrs_environment->save();
						$form_saved = TRUE;
						$form_action = utf8::strtoupper(Kohana::lang('ui_admin.shown'));
					}
				}
				// Save Action
				else
				{ 
					$nrs_environment->title = $post->title;
					$nrs_environment->environment_uid = $post->environment_uid;
					$nrs_environment->description = $post->description;
					$nrs_environment->status = $post->status;
					$nrs_environment->location_disposition = $post->location_disposition;
					$nrs_environment->location_exposure = $post->location_exposure;
					$nrs_environment->location_latitude = $post->latitude;
					$nrs_environment->location_longitude = $post->longitude;

					if (isset($nrs_environment->location_latitude) AND isset($nrs_environment->location_longitude) AND !empty($nrs_environment->location_latitude) AND !empty($nrs_environment->location_longitude))
					{
						$location =  ORM::factory('location')->where('latitude',$nrs_environment->location_latitude)->where('longitude',$nrs_environment->location_longitude)->find();
						if( !isset($location) || $location->id == 0 ) {
							$location = new Location_Model();
							$location->location_name = (isset($post->location_name) AND !empty($post->location_name)) ? $post->location_name : Kohana::lang('ui_admin.unknown');
							$location->latitude = $nrs_environment->location_latitude;
							$location->longitude = $nrs_environment->location_longitude;
							$location->location_date = date("Y-m-d H:i:s",time());
							$location->save();
						}
						$nrs_environment->location_id = $location->id;
					}	
					$nrs_environment->location_name = $location->location_name;
					$nrs_environment->location_elevation = $post->location_elevation;
					$nrs_environment->feed = $post->feed;
					$nrs_environment->updated = date("Y-m-d H:i:s",time());
					$nrs_environment->save();
			
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
			'total_items' => ORM::factory('nrs_environment')
							->where($filter)
							->count_all()
		));

		$nrs_environments = ORM::factory('nrs_environment')
						->where($filter)
						->orderby('updated','desc')
						->find_all($this->items_per_page, $pagination->sql_offset);

		$this->template->content->nrs_environments = $nrs_environments;
		$this->template->content->status_array = $this->_status_array();
		$this->template->content->stroke_width_array = $this->_stroke_width_array();
		$this->template->content->pagination = $pagination;
		$this->template->content->form_error = $form_error;
		$this->template->content->form_saved = $form_saved;
		$this->template->content->form_action = $form_action;

		// Total Reports

		$this->template->content->total_items = $pagination->total_items;


		$this->template->map_enabled = TRUE;
		$this->template->colorpicker_enabled = TRUE;
		$this->template->treeview_enabled = TRUE;
		$this->template->json2_enabled = TRUE;
		$this->template->js = new View('admin/manage/entities/nrs_environments_js');// new View('reports/submit_edit_js');
		$this->template->js->geometries = array();
		$this->template->js->incident_zoom = '';
		$this->template->js->edit_mode = TRUE;
		$this->template->js->default_map = Kohana::config('settings.default_map');
		$this->template->js->default_zoom = Kohana::config('settings.default_zoom');
		$this->template->js->latitude = Kohana::config('settings.default_lat');
		$this->template->js->longitude = Kohana::config('settings.default_lon');

	
		// Javascript Header
		// $this->template->js = new View('admin/manage/entities/nrs_environments_js');

	}

	public function edit($id = FALSE, $saved = FALSE)
	{
	}		
	// Status array function
	private function _status_array()
	{
		$status_array[1] = "DEAD";
		$status_array[2] = "ZOMBIE";
		$status_array[3] = "FROZEN";
		$status_array[4] = "LIVE";
		return $status_array;
	}

	private function _stroke_width_array()
	{
		for ($i = 0.5; $i <= 8 ; $i += 0.5)
		{
			$stroke_width_array["$i"] = $i;
		}
		return $stroke_width_array;
	}
}

?>
