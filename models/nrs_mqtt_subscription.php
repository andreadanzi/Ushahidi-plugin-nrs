<?php defined('SYSPATH') or die('No direct script access.');
/**
 * NRS Setting Model
 *
 * PHP version 5
 * LICENSE: This source file is subject to LGPL license 
 * that is available through the world-wide-web at the following URI:
 * http://www.gnu.org/copyleft/lesser.html
 * @author     Andrea Danzi <andrea@danzi.tn.it> 
 * @package    Ushahidi - http://source.ushahididev.com
 * @module     NRS_Setting Model  
 * @copyright  Rockfall Defence - http://www.rockfalldefence.com
 * @license    http://www.gnu.org/copyleft/lesser.html GNU Lesser General Public License (LGPL) 
 */

class Nrs_mqtt_subscription_Model extends ORM
{
	/**
	 * One-to-many relationship definition
	 * @var array
	 */
	protected $has_many = array('nrs_mqtt_message');

	/**
	 * Database table name
	 * @var string
	 */
	protected $table_name = 'nrs_mqtt_subscription';

	/**
	 * Validates and optionally saves a new mqtt_subscription record from an array
	 *
	 * @param array $array Values to check
	 * @param bool $save Saves the record when validation succeeds
	 * @return bool
	 */
	public function validate(array & $array, $save = FALSE)
	{
		// Instantiate validation
		$array = Validation::factory($array)
				->pre_filter('trim', TRUE)
				->add_rules('mqtt_subscription_name','required', 'length[3,70]')
				->add_rules('mqtt_host','required', 'length[3,255]')
				->add_rules('mqtt_subscription_topic','required', 'length[3,255]');
		
		return parent::validate($array, $save);
	}
	
	/**
	 * Checks if the specified mqtt subscription exists in the database
	 *
	 * @param int $feed_id Database record ID of the feed to check
	 * @return bool
	 */
	public static function is_valid_nrs_mqtt_subscription($nrs_mqtt_subscription_id)
	{
		return (intval($nrs_mqtt_subscription_id) > 0)
			? self::factory('nrs_mqtt_subscription', intval($nrs_mqtt_subscription_id))->loaded
			: FALSE;
	}


}
