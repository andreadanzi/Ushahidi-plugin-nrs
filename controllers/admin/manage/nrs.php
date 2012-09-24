<?php defined('SYSPATH') or die('No direct script access.');

class Nrs_Controller extends Admin_Controller
{
	private $_registered_blocks;

	function __construct()
	{
		parent::__construct();
		$this->template->this_page = 'manage';
		
		// If user doesn't have access, redirect to dashboard
		if ( ! admin::permissions($this->user, "manage"))
		{
			url::redirect(url::site().'admin/dashboard');
		}
		
		$this->_registered_blocks = Kohana::config("settings.blocks");
	}
	
	function index()
	{
		$this->template->content = new View('nrs/admin/nrs');
		//$this->template->content->title = Kohana::lang('ui_admin.blocks');

		// setup and initialize form field names
		$form = array
		(
			'action' => '',
			'feed_id' => '',
			'feed_name' => '',
			'feed_url' => '',
			'feed_active' => ''
		);
		//	copy the form as errors, so the errors will be stored with keys corresponding to the form field names
		$errors = $form;
		$form_error = FALSE;
		$form_saved = FALSE;
		$form_action = "";
		// Mettereci tutta la logica come in manage.php la funzione feeds


		// Pagination
		$pagination = new Pagination(array(
			'query_string' => 'page',
			'items_per_page' => $this->items_per_page,
			'total_items'	 => ORM::factory('nrs_environment')->count_all()
		));

		$feeds = ORM::factory('nrs_environment')
					->orderby('title', 'asc')
					->find_all($this->items_per_page, $pagination->sql_offset);

		$this->template->content->form_error = $form_error;
		$this->template->content->form_saved = $form_saved;
		$this->template->content->form_action = $form_action;
		$this->template->content->pagination = $pagination;
		$this->template->content->total_items = $pagination->total_items;
		$this->template->content->feeds = $feeds;
		$this->template->content->errors = $errors;


	}
}

?>
