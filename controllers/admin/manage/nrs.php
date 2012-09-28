<?php defined('SYSPATH') or die('No direct script access.');

class Nrs_Controller extends Admin_Controller
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
		$this->template->content = new View('admin/manage/nrs_mqtt_subscription/main');
		$this->template->content->title = Kohana::lang('ui_admin.settings');

		// setup and initialize form field names
		$form = array
		(
			'action' => '',
			'nrs_mqtt_subscription_id' => '',
			'mqtt_subscription_name' => '',
			'mqtt_subscription_color' => '',
			'mqtt_subscription_topic' => '',
			'mqtt_subscription_active' => '',
			'mqtt_host' => '',
			'mqtt_port' => '',
			'mqtt_subscription_id' => '',
			'mqtt_username' => '',
			'mqtt_password' => ''
		);
		//	copy the form as errors, so the errors will be stored with keys corresponding to the form field names
		$errors = $form;
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
				$post->add_rules('mqtt_subscription_name','required', 'length[3,250]');
				$post->add_rules('mqtt_subscription_topic','required', 'length[3,255]');
				$post->add_rules('mqtt_host','required', 'length[3,255]');
				$post->add_rules('mqtt_subscription_color','required', 'length[6,6]');
			}


			if( $post->validate() )
			{
				$nrs_mqtt_subscription_id = $post->nrs_mqtt_subscription_id;
				
				$mqtt_subscription = new Nrs_mqtt_subscription_Model($nrs_mqtt_subscription_id);
				
				// Delete Action
				if ( $post->action == 'd' )
				{ 
					ORM::factory('nrs_mqtt_message')->where('nrs_mqtt_subscription_id', $nrs_mqtt_subscription_id)->delete_all();
					$mqtt_subscription->delete( $nrs_mqtt_subscription_id );
					$form_saved = TRUE;
					$form_action = utf8::strtoupper(Kohana::lang('ui_admin.deleted'));
				}
				
				// Hide Action
				else if ($post->action=='h')
				{
					if($mqtt_subscription->loaded)
					{
						$mqtt_subscription->mqtt_subscription_active = 3;
						$mqtt_subscription->save();
						$form_saved = TRUE;
						$form_action = utf8::strtoupper(Kohana::lang('ui_main.hidden'));
					}	
				}
				
				// Show Action
				else if ($post->action == 'v')
				{ 

					if ($mqtt_subscription->loaded)
					{
						$mqtt_subscription->mqtt_subscription_active = 1;
						$mqtt_subscription->save();
						$form_saved = TRUE;
						$form_action = utf8::strtoupper(Kohana::lang('ui_admin.shown'));
					}
				}
				
				elseif ($post->action == 'r')
				{
					$this->_parse_nrs_mqtt_subscription();
				}
				// Save Action
				else
				{ 
					$mqtt_subscription->mqtt_subscription_name = $post->mqtt_subscription_name;
					$mqtt_subscription->mqtt_subscription_topic = $post->mqtt_subscription_topic;
					$mqtt_subscription->mqtt_subscription_color = $post->mqtt_subscription_color;
					$mqtt_subscription->mqtt_host = $post->mqtt_host;
					$mqtt_subscription->mqtt_port = $post->mqtt_port;
					$mqtt_subscription->mqtt_username = $post->mqtt_username;
					$mqtt_subscription->mqtt_password = $post->mqtt_password;
					$mqtt_subscription->mqtt_subscription_id = $post->mqtt_subscription_id;
					$mqtt_subscription->save();
			
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
			'total_items'	 => ORM::factory('nrs_mqtt_subscription')->count_all()
		));

		$nrs_mqtt_subscriptions = ORM::factory('nrs_mqtt_subscription')
					->orderby('mqtt_subscription_name', 'asc')
					->find_all($this->items_per_page, $pagination->sql_offset);

		$this->template->content->form_error = $form_error;
		$this->template->content->form_saved = $form_saved;
		$this->template->content->form_action = $form_action;
		$this->template->content->pagination = $pagination;
		$this->template->content->total_items = $pagination->total_items;
		$this->template->content->nrs_mqtt_subscriptions = $nrs_mqtt_subscriptions;
		$this->template->content->errors = $errors;
		// Javascript Header
		$this->template->colorpicker_enabled = TRUE;
		$this->template->js = new View('admin/manage/nrs_mqtt_subscription/nrs_mqtt_subscription_js');

	}

	public function mqtt_messages()
	{
		$this->template->content = new View('admin/manage/nrs_mqtt_subscription/mqtt_messages');
		// Check if the last segment of the URI is numeric and grab it
		$nrs_mqtt_subscription_id = is_numeric($this->uri->last_segment())
					? $this->uri->last_segment()
					: "";
		
		// SQL filter
		$filter = (isset($nrs_mqtt_subscription_id)  AND !empty($nrs_mqtt_subscription_id))
					? " nrs_mqtt_subscription_id = '" . $nrs_mqtt_subscription_id . "' "
					: " 1=1";

		$form_error = FALSE;
		$form_saved = FALSE;
		$form_action = "";


		// Check for form submission
		if ( $_POST )
		{
			$post = Validation::factory($_POST);

			 //	 Add some filters
			$post->pre_filter('trim', TRUE);

			if( $post->validate() )
			{
				$message_id = $this->input->post('nrs_mqtt_message_id');

				ORM::factory('nrs_mqtt_message')->delete($message_id);

				$form_saved = TRUE;
				$form_action = utf8::strtoupper(Kohana::lang('ui_admin.deleted'));
			}
		}

		// Pagination
		$pagination = new Pagination(array(
			'query_string' => 'page',
			'items_per_page' => $this->items_per_page,
			'total_items' => ORM::factory('nrs_mqtt_message')
							->where($filter)
							->count_all()
		));

		$nrs_mqtt_messages = ORM::factory('nrs_mqtt_message')
						->where($filter)
						->orderby('mqtt_message_datetime','desc')
						->find_all($this->items_per_page, $pagination->sql_offset);

		$this->template->content->nrs_mqtt_messages = $nrs_mqtt_messages;
		$this->template->content->pagination = $pagination;
		$this->template->content->form_error = $form_error;
		$this->template->content->form_saved = $form_saved;
		$this->template->content->form_action = $form_action;

		// Total Reports
		$this->template->content->total_items = $pagination->total_items;

		// Javascript Header
		$this->template->js = new View('admin/manage/nrs_mqtt_subscription/mqtt_messages_js');




	}


	/**
	 * Edit an entity
	 * @param bool|int $id The id no. of the report
	 * @param bool|string $saved
	 */
	public function edit_nrs_entity($id = FALSE, $saved = FALSE)
	{
		// Are we creating this Entity from a MQTT Message?
		if ( isset($_GET['mqtt_mid']) AND intval($_GET['mqtt_mid']) > 0 )
		{
			$mqtt_message_id = intval($_GET['mqtt_mid']);
			$mqtt_message = ORM::factory('nrs_mqtt_message', $mqtt_message_id);

			if ($mqtt_message->loaded)
			{
				// Has a report already been created for this Feed item?
				if ($mqtt_message->nrs_entity_id != 0)
				{
					// Redirect to entity $mqtt_message->nrs_entity_id $mqtt_message->nrs_entity_type
					url::redirect('admin/manage/nrs/<NRSTYPE>/edit'. $mqtt_message->nrs_entity_id);
				}
				// FILLE THE FORM OF THE SPECIFIED ENTITY
				/*
				$form['incident_title'] = $mqtt_message->item_title;
				$form['incident_description'] = $mqtt_message->item_description;
				$form['incident_date'] = date('m/d/Y', strtotime($mqtt_message->item_date));
				$form['incident_hour'] = date('h', strtotime($mqtt_message->item_date));
				$form['incident_minute'] = date('i', strtotime($mqtt_message->item_date));
				$form['incident_ampm'] = date('a', strtotime($mqtt_message->item_date));

				// News Link
				$form['incident_news'][0] = $mqtt_message->item_link;

				// Does this newsfeed have a geolocation?
				if ($mqtt_message->location_id)
				{
					$form['location_id'] = $mqtt_message->location_id;
					$form['latitude'] = $mqtt_message->location->latitude;
					$form['longitude'] = $mqtt_message->location->longitude;
					$form['location_name'] = $mqtt_message->location->location_name;
				}
				*/
			}
			else
			{
				$mqtt_message_id = "";
			}
		}
	}

	private function _manage_nrs_environment($fields,$mqtt_topic,$nrs_entity_uid) // title;uid;descr;status;location;location_name;posizionamento;esposizione;lat;lon;altezza_slm;url
	{
		$nrs_entity_id = 0;
		$title = $fields[0];
		$uid = $fields[1];
		$descr = $fields[2];
		$status = $fields[3];
		$location = $fields[4];
		$location_name = $fields[5];
		$disposition = $fields[6];
		$exposure = $fields[7];
		$lat = $fields[8];
		$lon = $fields[9];
		$elevation = $fields[10];
		$url = $fields[11];
		// Does this message has a location??
		$location_id = 0;
		// STEP 1: SAVE LOCATION
		if (isset($lat) AND isset($lon) AND !empty($lat) AND !empty($lon))
		{
			$location = new Location_Model();
			$location->location_name = (isset($location_name) AND !empty($location_name)?$location_name: Kohana::lang('ui_admin.unknown'));
			$location->latitude = $lat;
			$location->longitude = $lon;
			$location->location_date = date("Y-m-d H:i:s",time());
			$location->save();
			$location_id = $location->id;
		}
		// We need to check for existing Environments!!!
		$nrs_environments = ORM::factory('nrs_environment')->where('environment_uid',$nrs_entity_uid)->find_all();
		if(count($nrs_environment) > 0 )
		{
			foreach( $nrs_environments as $nrs_environment)
			{
				$new_entity = new Nrs_environment_Model( $nrs_environment->id);
				$new_entity->title = $title;
				$new_entity->environment_uid = $uid;
				$new_entity->description = $descr;
				$new_entity->status = intval($status);
				$new_entity->location_id = $location_id;
				$new_entity->location_name = (isset($location_name) AND !empty($location_name)?$location_name: Kohana::lang('ui_admin.unknown'));
				$new_entity->location_disposition = $disposition;
				$new_entity->location_exposure = $exposure;
				$new_entity->location_latitude = $lat;
				$new_entity->location_longitude = $lon;
				$new_entity->location_elevation = intval($elevation);
				$new_entity->feed = $url;
				$nrs_entity_id = $new_entity->save();// NN SONO SICURO CHE RESTITUISCA ID
			}
		}
		else
		{
			$new_entity = new Nrs_environment_Model();
			$new_entity->title = $title;
			$new_entity->environment_uid = $uid;
			$new_entity->description = $descr;
			$new_entity->status = intval($status);
			$new_entity->location_id = $location_id;
			$new_entity->location_name = (isset($location_name) AND !empty($location_name)?$location_name: Kohana::lang('ui_admin.unknown'));
			$new_entity->location_disposition = $disposition;
			$new_entity->location_exposure = $exposure;
			$new_entity->location_latitude = $lat;
			$new_entity->location_longitude = $lon;
			$new_entity->location_elevation = intval($elevation);
			$new_entity->feed = $url;
			$nrs_entity_id = $new_entity->save();// NN SONO SICURO CHE RESTITUISCA ID
		}	
		return $nrs_entity_id;	
	}
	
	private function _manage_nrs_node($fields,$mqtt_topic,$nrs_entity_uid) // title;uid;descr;status;posizionamento;esposizione
	{
		$nrs_entity_id = 0;
		$title = $fields[0];
		$uid = $fields[1];
		$descr = $fields[2];
		$status = $fields[3];
		$disposition = $fields[4];
		$exposure = $fields[5];
		// We need to check for existing Nodes!!!
		$nrs_nodes = ORM::factory('nrs_node')->where('node_uid',$nrs_entity_uid)->find_all();
		if(count($nrs_nodes) > 0 )
		{
			foreach( $nrs_nodes as $nrs_node)
			{
				$new_entity = new Nrs_node_Model( $nrs_node->id);
				$new_entity->title = $title;
				$new_entity->node_uid = $uid;
				$new_entity->description = $descr;
				$new_entity->status = intval($status);
				$new_entity->node_disposition = $disposition;
				$new_entity->node_exposure = $exposure;
				$nrs_entity_id = $new_entity->save(); // NN SONO SICURO CHE RESTITUISCA ID
			}
		}
		else
		{
			// AND IN THIS CASE ALSO for the parent nrs_environment_id
			// parse $mqtt_topic,$nrs_entity_uid for retrieving nrs_environment_uid
			$new_entity = new Nrs_node_Model();
			$new_entity->title = $title;
			$new_entity->node_uid = $uid;
			$new_entity->description = $descr;
			$new_entity->status = intval($status);
			$new_entity->node_disposition = $disposition;
			$new_entity->node_exposure = $exposure;
			$nrs_entity_id = $new_entity->save(); // NN SONO SICURO CHE RESTITUISCA ID
		}	
		return $nrs_entity_id;
	}

	private function _manage_nrs_datastream($fields,$mqtt_topic,$nrs_entity_uid) // title;uid;descr;unit_label;unit_type;unit_symbol;current_value;max_value;min_value
	{
		$nrs_entity_id = 0;
		$title = $fields[0];
		$uid = $fields[1];
		$descr = $fields[2];
		$unit_label = $fields[3];
		$unit_type = $fields[4];
		$unit_symbol = $fields[5];
		$current_value = $fields[6];
		$max_value = $fields[7];
		$min_value = $fields[8];
		// We need to check for existing Datatstream!!!
		$nrs_datastreams = ORM::factory('nrs_datastream')->where('datastream_uid',$nrs_entity_uid)->find_all();
		if(count($nrs_datastreams) > 0 )
		{
			foreach( $nrs_datastreams as $nrs_datastream)
			{
				$new_entity = new Nrs_datatstream_Model($nrs_datastream->id);
				$new_entity->title = $title;
				$new_entity->datastream_uid = $uid;
				$new_entity->unit_label = $unit_label;
				$new_entity->unit_type = $unit_type;
				$new_entity->unit_symbol = $unit_symbol;
				$new_entity->current_value = floatval($current_value);
				$new_entity->max_value = floatval($max_value);
				$new_entity->min_value = floatval($min_value);
				$nrs_entity_id = $new_entity->save(); // NN SONO SICURO CHE RESTITUISCA ID
			}
		}
		else
		{
			// AND IN THIS CASE ALSO for the parent nrs_node_id
			// parse $mqtt_topic,$nrs_entity_uid for retrieving nrs_node_uid
			$new_entity = new Nrs_datatstream_Model();
			$new_entity->title = $title;
			$new_entity->datastream_uid = $uid;
			$new_entity->unit_label = $unit_label;
			$new_entity->unit_type = $unit_type;
			$new_entity->unit_symbol = $unit_symbol;
			$new_entity->current_value = floatval($current_value);
			$new_entity->max_value = floatval($max_value);
			$new_entity->min_value = floatval($min_value);
			$nrs_entity_id = $new_entity->save(); // NN SONO SICURO CHE RESTITUISCA ID
		}	
		return $nrs_entity_id;	

	}

	private function _manage_nrs_datapoint($fields,$mqtt_topic,$nrs_entity_uid) // msecs;timestamp;value
	{
		$nrs_entity_id = 0;
		$msecs = $fields[0];
		$timestamp = $fields[1];
		$value = $fields[2];
		$new_entity = new Nrs_datatpoint_Model();
		// SEARCH FOR nrs_environment_id, nrs_node_id, nrs_datastream_id
		$new_entity->msecs = intval($msecs);
		$new_entity->at = DateTime::createFromFormat("Y-m-d\TH:i:s.u\Z",$timestamp);
		$new_entity->value_at = floatval($value);
		$nrs_entity_id = $new_entity->save(); // NN SONO SICURO CHE RESTITUISCA ID
		return $nrs_entity_id;
	}
	


	/**
	 * parse subscription and send messages to database
	 */
	private function _parse_nrs_mqtt_subscription()
	{
		// Max number of message to keep
		$max_messages = 1000;
		$int_type = 1;
		while ($int_type < 5)
		{
			// Get All nrs_mqtt_message From DB nrs_entity_type` >0
			$nrs_mqtt_messages = ORM::factory('nrs_mqtt_message')->where('nrs_entity_type',$int_type)->where('mqtt_topic_errors',0)->where('nrs_entity_id',0)->orderby('mqtt_message_datetime', 'ASC')->find_all();
			foreach ($nrs_mqtt_messages as $nrs_mqtt_message)
			{	
				$nrs_entity_id = 0;
				$mqtt_payload = $nrs_mqtt_message->mqtt_payload; 
				$mqtt_nrs_action = $nrs_mqtt_message->mqtt_nrs_action; 
				$nrs_entity_uid = $nrs_mqtt_message->nrs_entity_uid; 
				$nrs_entity_type = $nrs_mqtt_message->nrs_entity_type; 
				$mqtt_topic = $nrs_mqtt_message->mqtt_topic;
				$mqtt_message_datetime = $nrs_mqtt_message->mqtt_message_datetime;
				// Make sure Payload and Topic are set (at least  )
				if(isset($mqtt_payload) && !empty($mqtt_payload) && isset($mqtt_topic) && !empty($mqtt_topic)  )
					// We need to check for duplicates!!!
					// Maybe combination of Topic + Date and nrs_entity_uid (Heavy on the Server :-( ) TO BE IMPROVED
					$dupe_count = ORM::factory('nrs_mqtt_message')->where('mqtt_topic',$mqtt_topic)->where('mqtt_message_datetime',date("Y-m-d H:i:s",strtotime($mqtt_message_datetime)))->count_all();

					$multiline = explode("\n", $mqtt_payload);  
					foreach ($multiline as $line)
					{
						$fields = explode(";", $line);
						switch ($nrs_entity_type) {
						    case 1:  // 1 - Environment 12 columns
							   if(count($fields) == 12 ) {
								$nrs_entity_id = $this->_manage_nrs_environment($fields,$mqtt_topic,$nrs_entity_uid);
							   }	
							   break;
						    case 2:  // 2 - Node 6 columns
							   if(count($fields) == 6 ) {
								$nrs_entity_id = $this->_manage_nrs_node($fields,$mqtt_topic,$nrs_entity_uid);
							   }	
							break;
						    case 3:   // 3 - Datastream 9 columns 
							   if(count($fields) == 9 ) {
								$nrs_entity_id = $this->_manage_nrs_datastream($fields,$mqtt_topic,$nrs_entity_uid);
							    }
							break;
						    case 4:   // 4 - Datapoint 3 columns
							   if(count($fields) == 3 ) {
								$nrs_entity_id = $this->_manage_nrs_datapoint($fields,$mqtt_topic,$nrs_entity_uid);
							    }
							break;
						}
					} // END FOR EACH MULTILINE
				}
				// Qui bisogna fare l'associazione con entity_id che è stato inserito
				// nrs_entity_id
				$nrs_mqtt_message->nrs_entity_id = $nrs_entity_id;
				$nrs_mqtt_message->save();
			
			} // END FOR EACH MESSAGE
			$int_type++;
		} // END WHILE
	}

}

?>
