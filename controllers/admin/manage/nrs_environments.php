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

		$form_error = FALSE;
		$form_saved = FALSE;
		$form_action = "";

		// POST ACTIONS HERE

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
		$this->template->content->pagination = $pagination;
		$this->template->content->form_error = $form_error;
		$this->template->content->form_saved = $form_saved;
		$this->template->content->form_action = $form_action;

		// Total Reports
		$this->template->content->total_items = $pagination->total_items;

		// Javascript Header
		$this->template->js = new View('admin/manage/entities/nrs_environments_js');

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

}

?>
