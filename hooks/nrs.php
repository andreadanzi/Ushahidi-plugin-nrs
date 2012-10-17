<?php defined('SYSPATH') or die('No direct script access.');
/**
 * NRS Hooks
 *
 * PHP version 5
 * LICENSE: This source file is subject to LGPL license 
 * that is available through the world-wide-web at the following URI:
 * http://www.gnu.org/copyleft/lesser.html
 * @author	   Ushahidi Team <team@ushahidi.com> 
 * @package    Ushahidi - http://source.ushahididev.com
 * @module	   nrs hooks
 * @copyright  Ushahidi - http://www.ushahidi.com
 * @license    http://www.gnu.org/copyleft/lesser.html GNU Lesser General Public License (LGPL) 
* 
*/


class nrs {
	
	/**
	 * Registers the main event add method
	 */

	protected $user;

	public function __construct()
	{
		// Hook into routing
		Event::add('system.pre_controller', array($this, 'add'));
	}
	
	/**
	 * Adds all the events to the main Ushahidi application
	 */
	public function add()
	{
		// Only add the events if we are on that controller
		if (Router::$controller == 'manage' or Router::$controller == 'nrs' or Router::$controller == 'nrs_overlimits'or Router::$controller == 'nrs_datapoints' or Router::$controller == 'nrs_datastreams' or Router::$controller == 'nrs_environments' or Router::$controller == 'nrs_nodes' or Router::$controller == 'dashboard')
		{
			Event::add('ushahidi_action.nav_admin_manage', array($this,'_nrs'));
			Event::add('ushahidi_action.header_scripts_admin', array($this, 'nrs_css_admin'));
			Event::add('ushahidi_action.header_scripts_admin', array($this, 'nrs_google_js'));
		}
		elseif (strripos(Router::$current_uri, "main") !== false)
		{
			Event::add('ushahidi_action.header_scripts', array($this, 'nrs_js'));
			Event::add('ushahidi_action.main_sidebar', array($this, 'nrs_bar'));
		}
		else
		{
			Event::add('ushahidi_action.header_scripts', array($this, 'nrs_css_admin'));
		}
		Event::add('ushahidi_action.report_delete', array($this, 'delete_incident_id'));
		Event::add('ushahidi_action.category_delete', array($this, 'delete_category_id'));

	}
	
	public function delete_incident_id() {
		$incident_id = Event::$data;
		if($incident_id != FALSE)
		{
			$sql_query = "UPDATE nrs_datapoint SET incident_id = 0 WHERE incident_id = ?";
			$distinct_updated_results = Database::instance('default')->query($sql_query,$incident_id);	
		}
	}

	public function delete_category_id() {
		$deleted_category_id = Event::$data;
		if($deleted_category_id != FALSE )
		{
			$sql_query = "DELETE FROM nrs_node_category WHERE category_id = ?";
			$distinct_updated_results = Database::instance('default')->query($sql_query,$deleted_category_id);	
		}
	}

	public function _nrs()
	{
		$this_sub_page = Event::$data;
		echo ($this_sub_page == "nrs") ? "Nrs" : "<a href=\"".url::site()."admin/manage/nrs\">NRS</a>";
	}

	/**
	 * Loads the nrs bar on the side bar on the main page
	 */
	public function nrs_bar()
	{
		// Get all mqtt subscriptions
		$subscriptions = array();
		foreach (ORM::factory('nrs_mqtt_subscription')
					->find_all() as $subscription)
		{
			$subscriptions[$subscription->id] = array($subscription->mqtt_subscription_name, $subscription->mqtt_subscription_color);
		}

		$nrs_bar = View::factory('nrs/nrs_bar');

		$nrs_bar->subscriptions = $subscriptions;
		$nrs_bar->render(TRUE);
	}
	
	/**
	 * Loads the JavaScript for the nrs sidebar
	 */
	public function nrs_js()
	{
		$js = View::factory('js/nrs_bar_js');
		$js->render(TRUE);
	}
	/**
	 * Loads the JavaScript for the nrs sidebar
	 */
	public function nrs_google_js()
	{

		$js = View::factory('js/google_charts_js');
		$js->render(TRUE);
	}

	/**
	 * Loads the CSS for the nrs admin section
	 */
	public function nrs_css_admin()
	{
		$css = View::factory('css/nrs_css');
		$css->render(TRUE);
	}
}
new nrs;
