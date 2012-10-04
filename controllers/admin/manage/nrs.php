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
				// Delete Action
				if ( $post->action == 'd' )
				{ 
					ORM::factory('nrs_mqtt_message')->delete($message_id);
					$form_saved = TRUE;
					$form_action = utf8::strtoupper(Kohana::lang('ui_admin.deleted'));
				}
				else if ( $post->action == 'g') 	// Generate Action
				{
					$nrs_mqtt_message = ORM::factory('nrs_mqtt_message',$message_id);
					$this->_parse_nrs_mqtt_message($nrs_mqtt_message);
				}
				// Save Action
				else if ( $post->action == 'a') 
				{ 
					$mqtt_message = new Nrs_mqtt_message_Model($message_id);

					$mqtt_message->mqtt_payload = trim($post->description);
					$mqtt_message->mqtt_payloadlen = strlen($mqtt_message->mqtt_payload);
					$mqtt_message->save();
			
					$form_saved = TRUE;
					$form_action = utf8::strtoupper(Kohana::lang('ui_admin.created_edited'));
				}

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


	public function edit_nrs_entity()
	{
		// ?nrs_type=1&nrs_id=3
		if ( !empty($_GET['nrs_type']) && !empty($_GET['nrs_id']))
		{
			$type = $_GET['nrs_type'];
			$id = $_GET['nrs_id'];
			switch ($type) {
				case 1:  // 1 - Environment 12 columns
				   	url::redirect('admin/manage/nrs_environments/id/'.$id);
				   	break;
				case 2:  // 2 - Node 6 columns
				   	url::redirect('admin/manage/nrs_nodes?nrs_id='.$id);
				   	
					break;
				case 3:   // 3 - Datastream 9 columns 
				   
					break;
				case 4:   // 4 - Datapoint 3 columns
				  
					break;
			}
		}
	}
	
	private function _manage_bulk_json_nrs_environment($mqtt_topic,$nrs_entity_uid,$json_mqtt_payload)
	{
		$nrs_entity_id = 0;
		$dec_environment = json_decode($json_mqtt_payload);
		$updated_date = date("Y-m-d H:i:s",time());
		if( isset($dec_environment) )
		{
			$current_nrs_environment_uid = $dec_environment->environment_uid;	
			$nrs_environment = ORM::factory('nrs_environment')->where('environment_uid',$current_nrs_environment_uid)->find();
			if( !isset($nrs_environment) || $nrs_environment->id == 0 )
			{
				$nrs_environment = new Nrs_environment_Model();
			}
			$nrs_environment->title = $dec_environment->title;
			$nrs_environment->environment_uid = $current_nrs_environment_uid;
			if(isset($dec_environment->description))
			{
				$nrs_environment->description = $dec_environment->description;
			}
			if(isset($dec_environment->location_name))
			{
				$nrs_environment->location_name = $dec_environment->location_name;
			}
			if(isset($dec_environment->location_exposure))
			{
				$nrs_environment->location_exposure = $dec_environment->location_exposure;
			}
			if(isset($dec_environment->location_latitude))
			{
				$nrs_environment->location_latitude = $dec_environment->location_latitude;
			}
			if(isset($dec_environment->location_longitude))
			{
				$nrs_environment->location_longitude = $dec_environment->location_longitude;
			}
			if(isset($dec_environment->location_elevation))
			{
				$nrs_environment->location_elevation = $dec_environment->location_elevation;
			}

			$location_id = 0;
			// SAVE LOCATION IF LON AND LAT are Set
			if (isset($nrs_environment->location_latitude) AND isset($nrs_environment->location_longitude) AND !empty($nrs_environment->location_latitude) AND !empty($nrs_environment->location_longitude))
			{
				$location =  ORM::factory('location')->where('latitude',$nrs_environment->location_latitude)->where('longitude',$nrs_environment->location_longitude)->find();
				if( !isset($location) || $location->id == 0 ) {
					$location = new Location_Model();
					$location->location_name = (isset($dec_environment->location_name) AND !empty($dec_environment->location_name)) ? $dec_environment->location_name : Kohana::lang('ui_admin.unknown');
					$location->latitude = $nrs_environment->location_latitude;
					$location->longitude = $nrs_environment->location_longitude;
					$location->location_date = $updated_date;
					$location->save();
				}
				$nrs_environment->location_id = $location->id;
			}

			$nrs_environment->status = $nrs_environment->status;
			$nrs_environment->updated = $updated_date;	
			$nrs_environment->save();
			if( isset($dec_environment->nodes) && count($dec_environment->nodes)>0)
			{
				foreach( $dec_environment->nodes as $node )
				{
					$current_nrs_node_uid = $current_nrs_environment_uid . $node->node_uid;

					$nrs_node = ORM::factory('nrs_node')->where('node_uid',$current_nrs_node_uid)->find();
					if( !isset($nrs_node) || $nrs_node->id == 0 )
					{
						$nrs_node = new Nrs_node_Model();
					}
					$nrs_node->title = $node->title;
					$nrs_node->nrs_environment_id = $nrs_environment->id;
					$nrs_node->node_uid = $current_nrs_node_uid;
					if(isset($node->description))
					{
						$nrs_node->description = $node->description;
					}

					if(isset($node->node_disposition))
					{
						$nrs_node->node_disposition = $node->node_disposition;
					}
					if(isset($node->node_exposure))
					{
						$nrs_node->node_exposure = $node->node_exposure;
					}
					$nrs_node->status = $node->status;
					$nrs_node->updated = $updated_date;
					$nrs_node->save();
					if( isset($node->datastreams) && count($node->datastreams)>0)
					{
						foreach( $node->datastreams as $datastream )
						{
							$current_nrs_datastream_uid = $current_nrs_node_uid . $datastream->datastream_uid;
							$nrs_datastream = ORM::factory('nrs_datastream')->where('datastream_uid',$current_nrs_datastream_uid)->find();
							if( !isset($nrs_datastream) || $nrs_datastream->id == 0 )
							{
								$nrs_datastream = new Nrs_datastream_Model();
							}
							$nrs_datastream->title =$datastream->title;
							$nrs_datastream->datastream_uid = $current_nrs_datastream_uid;
							$nrs_datastream->nrs_node_id = $nrs_node->id;
							$nrs_datastream->nrs_environment_id = $nrs_environment->id;

							if(isset($datastream->unit_label))
							{
								$nrs_datastream->unit_label = $datastream->unit_label;
							}
							if(isset($datastream->unit_type))
							{
								$nrs_datastream->unit_type = $datastream->unit_type;
							}
							if(isset($datastream->unit_symbol))
							{
								$nrs_datastream->unit_symbol = $datastream->unit_symbol;
							}
							if(isset($datastream->unit_format))
							{
								$nrs_datastream->unit_format = $datastream->unit_format;
							}
							if(isset($datastream->tags))
							{
								$nrs_datastream->tags = $datastream->tags;
							}
							if(isset($datastream->current_value))
							{
								$nrs_datastream->current_value =  $datastream->current_value;
							}
							if(isset($datastream->min_value))
							{
								$nrs_datastream->min_value =  $datastream->min_value;
							}
							if(isset($datastream->max_value))
							{
								$nrs_datastream->max_value =  $datastream->max_value;
							}

							$nrs_datastream->updated = $updated_date;
							$nrs_datastream->save();
							if( isset($datastream->datapoints) && count($datastream->datapoints)>0)
							{
								foreach( $datastream->datapoints as $datapoint )
								{
									$new_entity = new Nrs_datapoint_Model();
									$new_entity->sample_no = $datapoint->sample_no;
									$at_datetime = DateTime::createFromFormat("Y-m-d\TH:i:s.u\Z",$datapoint->at);
									$new_entity->datetime_at = $at_datetime->format("YmdHisu");
									$new_entity->value_at =  $datapoint->value;
									$new_entity->nrs_environment_id = $nrs_environment->id;
									$new_entity->nrs_node_id = $nrs_node->id;
									$new_entity->nrs_datastream_id = $nrs_datastream->id;
									$new_entity->updated = $updated_date;
									$new_entity->incident_id = 0;
									$new_entity->save();	
								}
							}
						}
					}
				}
			}		
			$nrs_entity_id = $nrs_environment->id;
		}
		return $nrs_entity_id;
	}


	private function _manage_bulk_nrs_environment($mqtt_topic,$nrs_entity_uid,$mqtt_payload)
	{
		$nrs_entity_id = 0;
		// SEARCH FOR nrs_environment_id, nrs_node_id, nrs_datastream_id
		$nrs_environment = null;
		$nrs_node = null;
		$nrs_datastream = null;
		$nrs_datastream_uid = "";	
		$updated_date = date("Y-m-d H:i:s",time());
		$multiline = explode("\n", $mqtt_payload);
		foreach ($multiline as $line)
		{	
			$data = str_getcsv( $line, ";");
			if(isset($data) && count($data) > 17)
			{
				$num = count($data);
				$current_nrs_environment_uid = $data[0];	
				$current_nrs_node_uid = $current_nrs_environment_uid . $data[3];	
				$nrs_environment = ORM::factory('nrs_environment')->where('environment_uid',$current_nrs_environment_uid)->find();
				if( !isset($nrs_environment) || $nrs_environment->id == 0 )
				{
					$nrs_environment = new Nrs_environment_Model();
					$nrs_environment->title = $data[1];
					$nrs_environment->environment_uid = $current_nrs_environment_uid;
					$nrs_environment->description = "Environment with uid=" . $current_nrs_environment_uid;
					$nrs_environment->status = intval($data[2]);
					$nrs_environment->updated = $updated_date;
					$nrs_environment->save();
				}
				$nrs_node = ORM::factory('nrs_node')->where('node_uid',$current_nrs_node_uid)->find();
				if( !isset($nrs_node) || $nrs_node->id == 0 )
				{
					$nrs_node = new Nrs_node_Model();
					$nrs_node->title = $data[4];
					$nrs_node->nrs_environment_id = $nrs_environment->id;
					$nrs_node->node_uid = $current_nrs_node_uid;
					$nrs_node->description = "Node with uid=" . $current_nrs_node_uid;
					$nrs_node->status = intval($data[5]);
					$nrs_node->updated = $updated_date;
					$nrs_node->save();
				}
				$sample_no = $data[6];
				$timestamp = $data[7];
				$at_datetime = DateTime::createFromFormat("Y-m-d\TH:i:s.u\Z",$timestamp);
				$s_at_datetime = $at_datetime->format("YmdHisu");
				$data_stream_no = $data[8];		
				for ($c=0; $c < $data_stream_no; $c++) 
				{
					$current_nrs_datastream_uid = $current_nrs_node_uid . $data[9+2*$c];		
					if($current_nrs_datastream_uid!=$nrs_datastream_uid)
					{
						$nrs_datastream_uid = $current_nrs_datastream_uid;
						$nrs_datastream = ORM::factory('nrs_datastream')->where('datastream_uid',$current_nrs_datastream_uid)->find();
						if( !isset($nrs_datastream) || $nrs_datastream->id == 0 )
						{
							$nrs_datastream = new Nrs_datastream_Model();
							$nrs_datastream->title = $data[10+2*$c];
							$nrs_datastream->datastream_uid = $current_nrs_datastream_uid;
							$nrs_datastream->nrs_node_id = $nrs_node->id;
							$nrs_datastream->updated = $updated_date;
							$nrs_datastream->save();
						}
					}
					$new_entity = new Nrs_datapoint_Model();
					$new_entity->sample_no = intval($sample_no);
					$new_entity->datetime_at =  $s_at_datetime;
					$new_entity->value_at =  floatval($data[15+$c]);
					$new_entity->nrs_environment_id = $nrs_environment->id;
					$new_entity->nrs_node_id = $nrs_node->id;
					$new_entity->nrs_datastream_id = $nrs_datastream->id;
					$new_entity->updated = $updated_date;
					$new_entity->incident_id = 0;
					$new_entity->save();			
				}
			}
		}
		if( !isset($nrs_datastream) ) return 0;
		return $nrs_environment->id;
	}

	private function _manage_nrs_environment($fields,$mqtt_topic,$nrs_entity_uid)
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
			$location =  ORM::factory('location')->where('latitude',$lat)->where('longitude',$lon)->find();
			if(!isset($location) || $location->id == 0) {
				$location = new Location_Model();
				$location->location_name = (isset($location_name) AND !empty($location_name)) ?$location_name: Kohana::lang('ui_admin.unknown');
				$location->latitude = $lat;
				$location->longitude = $lon;
				$location->location_date = date("Y-m-d H:i:s",time());
				$location->save();
			}
			$location_id = $location->id;
		}
		// We need to check for existing Environments!!!
		$nrs_environments = ORM::factory('nrs_environment')->where('environment_uid',$nrs_entity_uid)->find_all();
		if(count($nrs_environments) > 0 )
		{
			foreach( $nrs_environments as $nrs_environment)
			{
				$nrs_environment->title = $title;
				$nrs_environment->environment_uid = $uid;
				$nrs_environment->description = $descr;
				$nrs_environment->status = intval($status);
				$nrs_environment->location_id = $location_id;
				$nrs_environment->location_name = (isset($location_name) AND !empty($location_name)?$location_name: Kohana::lang('ui_admin.unknown'));
				$nrs_environment->location_disposition = $disposition;
				$nrs_environment->location_exposure = $exposure;
				$nrs_environment->location_latitude = $lat;
				$nrs_environment->location_longitude = $lon;
				$nrs_environment->location_elevation = intval($elevation);
				$nrs_environment->feed = $url;
				$nrs_environment->updated = date("Y-m-d H:i:s",time());
				$nrs_entity_id = $nrs_environment->id;
				$nrs_environment->save();// Check if it retrieves the new id
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
			$new_entity->updated = date("Y-m-d H:i:s",time());
			$nrs_entity_id = $new_entity->save()->id;// Check if it retrieves the new id
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
		$topic_splitted = explode("/", $mqtt_topic);
		$environment_uid = $topic_splitted[4];
		// We need to check for existing Nodes!!!
		$nrs_nodes = ORM::factory('nrs_node')->where('node_uid',$environment_uid.$nrs_entity_uid)->find_all();
		if(count($nrs_nodes) > 0 )
		{
			foreach( $nrs_nodes as $nrs_node)
			{
				$new_entity = $nrs_node;
				$new_entity->title = $title;
				$new_entity->node_uid = $environment_uid.$nrs_entity_uid;
				$new_entity->description = $descr;
				$new_entity->status = intval($status);
				$new_entity->node_disposition = $disposition;
				$new_entity->node_exposure = $exposure;
				$nrs_entity_id = $new_entity->id;
				$new_entity->updated = date("Y-m-d H:i:s",time());
				$new_entity->save(); // Check if it retrieves the new id
			}
		}
		else
		{
			// AND IN THIS CASE ALSO for the parent nrs_environment_id
			// parse $mqtt_topic,$nrs_entity_uid for retrieving nrs_environment_uid
			$nrs_environment = ORM::factory('nrs_environment')->where('environment_uid',$environment_uid)->find();
			$new_entity = new Nrs_node_Model();
			$new_entity->title = $title;
			$new_entity->node_uid = $environment_uid.$nrs_entity_uid;
			$new_entity->description = $descr;
			$new_entity->status = intval($status);
			$new_entity->node_disposition = $disposition;
			$new_entity->node_exposure = $exposure;
			$new_entity->nrs_environment_id = $nrs_environment->id;
			$new_entity->updated = date("Y-m-d H:i:s",time());
			$nrs_entity_id = $new_entity->save()->id; // Check if it retrieves the new id
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
		$topic_splitted = explode("/", $mqtt_topic);
		$environment_uid = $topic_splitted[4];
		$node_uid = $environment_uid.$topic_splitted[6];
		// We need to check for existing Datatstream!!!
		$nrs_datastreams = ORM::factory('nrs_datastream')->where('datastream_uid',$node_uid.$nrs_entity_uid)->find_all();
		if(count($nrs_datastreams) > 0 )
		{
			foreach( $nrs_datastreams as $nrs_datastream)
			{
				$new_entity = $nrs_datastream;
				$new_entity->title = $title;
				$new_entity->datastream_uid = $node_uid.$nrs_entity_uid;
				$new_entity->unit_label = $unit_label;
				$new_entity->unit_type = $unit_type;
				$new_entity->unit_symbol = $unit_symbol;
				$new_entity->current_value = floatval($current_value);
				$new_entity->max_value = floatval($max_value);
				$new_entity->min_value = floatval($min_value);
				$nrs_entity_id = $new_entity->id;
				$new_entity->updated = date("Y-m-d H:i:s",time());
				$new_entity->save(); // Check if it retrieves the new id
			}
		}
		else
		{
			// AND IN THIS CASE ALSO for the parent nrs_node_id
			// parse $mqtt_topic,$nrs_entity_uid for retrieving nrs_node_uid
			$nrs_node = ORM::factory('nrs_node')->where('node_uid',$node_uid)->find();
			$new_entity = new Nrs_datastream_Model();
			$new_entity->title = $title;
			$new_entity->datastream_uid = $node_uid.$nrs_entity_uid;
			$new_entity->unit_label = $unit_label;
			$new_entity->unit_type = $unit_type;
			$new_entity->unit_symbol = $unit_symbol;
			$new_entity->current_value = floatval($current_value);
			$new_entity->max_value = floatval($max_value);
			$new_entity->min_value = floatval($min_value);
			$new_entity->nrs_node_id = $nrs_node->id;
			$new_entity->updated = date("Y-m-d H:i:s",time());
			$nrs_entity_id = $new_entity->save()->id; // Check if it retrieves the new id
		}	
		return $nrs_entity_id;	

	}

	private function _manage_nrs_datapoint($fields,$mqtt_topic,$nrs_entity_uid)
	{
		$nrs_entity_id = 0;
		$sample_no = $fields[0];
		$timestamp = $fields[1];
		$value = $fields[2];
		$topic_splitted = explode("/", $mqtt_topic);
		$environment_uid = $topic_splitted[4];
		$node_uid = $environment_uid.$topic_splitted[6];
		$datastream_uid = $node_uid.$topic_splitted[8];
		$at_datetime = DateTime::createFromFormat("Y-m-d\TH:i:s.u\Z",$timestamp);
		$s_at_datetime = $at_datetime->format("YmdHisu");
		// SEARCH FOR nrs_environment_id, nrs_node_id, nrs_datastream_id
		$nrs_environment = ORM::factory('nrs_environment')->where('environment_uid',$environment_uid)->find();
		$nrs_node = ORM::factory('nrs_node')->where('node_uid',$node_uid)->find();
		$nrs_datastream = ORM::factory('nrs_datastream')->where('datastream_uid',$datastream_uid)->find();
		$nrs_datapoints = ORM::factory('nrs_datapoint')->where('nrs_environment_id',$nrs_environment->id)->where('nrs_node_id',$nrs_node->id)->where('nrs_datastream_id',$nrs_datastream->id)->where('sample_no',intval($sample_no))->where('datetime_at',$s_at_datetime)->find_all();

		if(count($nrs_datapoints) > 0 )
		{
			foreach( $nrs_datapoints as $nrs_datapoint)
			{
				$new_entity = $nrs_datapoint;
				$new_entity->value_at = floatval($value);
				$new_entity->updated = date("Y-m-d H:i:s",time());
				$nrs_entity_id = $new_entity->id;
				$new_entity->save(); // Check if it retrieves the new id
			}
		} else {
			// Prepare the new item
			$new_entity = new Nrs_datapoint_Model();
			$new_entity->sample_no = intval($sample_no);
			$new_entity->datetime_at =  $s_at_datetime;
			$new_entity->value_at = floatval($value);
			$new_entity->nrs_environment_id = $nrs_environment->id;
			$new_entity->nrs_node_id = $nrs_node->id;
			$new_entity->nrs_datastream_id = $nrs_datastream->id;
			$new_entity->updated = date("Y-m-d H:i:s",time());
			$new_entity->incident_id = 0;
			$nrs_entity_id = $new_entity->save()->id; // Check if it retrieves the new id
		}
		return $nrs_entity_id;
	}
	
	
	/**
	 * parse subscription and send messages to database
	 */
	private function _parse_nrs_mqtt_subscription($mqtt_mid=null)
	{
		// Max number of message to keep
		$max_messages = 1000;
		if($mqtt_mid==null || (isset($mqtt_mid) && empty($mqtt_mid)))
		{
			$int_type = 1;
			while ($int_type < 5)
			{
				// Get All nrs_mqtt_message From DB nrs_entity_type` >0
				$nrs_mqtt_messages = ORM::factory('nrs_mqtt_message')->where('nrs_entity_type',$int_type)->where('mqtt_topic_errors',0)->where('nrs_entity_id',0)->orderby('mqtt_message_datetime', 'ASC')->find_all();
				foreach ($nrs_mqtt_messages as $nrs_mqtt_message)
				{	
					$this->_parse_nrs_mqtt_message($nrs_mqtt_message);
			
				} // END FOR EACH MESSAGE
				$int_type++;
			} // END WHILE
		}
		else if (isset($mqtt_mid) && !empty($mqtt_mid) )
		{
			$nrs_mqtt_message = ORM::factory('nrs_mqtt_message',$mqtt_mid);
			$this->_parse_nrs_mqtt_message($nrs_mqtt_message);
		}
	}


	/**
	 * parse message item
	 */
	private function _parse_nrs_mqtt_message(&$nrs_mqtt_message)
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
		{
			// We need to check for duplicates!!!
			// Maybe combination of Topic + Date and nrs_entity_uid (Heavy on the Server :-( ) TO BE IMPROVED
			$dupe_count = ORM::factory('nrs_mqtt_message')->where('mqtt_topic',$mqtt_topic)->where('mqtt_message_datetime',date("Y-m-d H:i:s",strtotime($mqtt_message_datetime)))->count_all();
			if ( $mqtt_nrs_action=="b" )
			{
				$json_enc = json_decode($mqtt_payload);
				if(isset($json_enc))
				{
					$nrs_entity_id = $this->_manage_bulk_json_nrs_environment($mqtt_topic,$nrs_entity_uid,$mqtt_payload);
				}
				else
				{
					$nrs_entity_id = $this->_manage_bulk_nrs_environment($mqtt_topic,$nrs_entity_uid,$mqtt_payload);
				}
			}
			else 
			{
				$multiline = explode("\n", $mqtt_payload);  
				foreach ($multiline as $line)
				{
					$fields = explode(";", $line);
					switch ($nrs_entity_type) {
					    case 1:  // 1 - Environment 12 columns
						   if(count($fields) == 12 && $mqtt_nrs_action=="a" ) {
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
		}
		// Associate the new nrs_entity_id
		$nrs_mqtt_message->nrs_entity_id = $nrs_entity_id;
		$nrs_mqtt_message->save();
	}




}

?>
