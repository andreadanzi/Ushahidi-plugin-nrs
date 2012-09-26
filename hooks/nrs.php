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
		if (Router::$controller == 'manage' or Router::$controller == 'nrs')
		{
			Event::add('ushahidi_action.nav_admin_manage', array($this,'_nrs'));
		}
		elseif (strripos(Router::$current_uri, "main") !== false)
		{
			Event::add('ushahidi_action.header_scripts', array($this, 'nrs_js'));
			Event::add('ushahidi_action.main_sidebar', array($this, 'nrs_bar'));
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
}
new nrs;
