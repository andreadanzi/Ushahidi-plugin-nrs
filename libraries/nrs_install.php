<?php
/**
 * Performs install/uninstall methods for the NRS plugin
 *
 * PHP version 5
 * LICENSE: This source file is subject to LGPL license 
 * that is available through the world-wide-web at the following URI:
 * http://www.gnu.org/copyleft/lesser.html
 * @author	   Ushahidi Team <team@ushahidi.com> 
 * @package    Ushahidi - http://source.ushahididev.com
 * @module	   NRS Installer
 * @copyright  Ushahidi - http://www.ushahidi.com
 * @license    http://www.gnu.org/copyleft/lesser.html GNU Lesser General Public License (LGPL) 
 */

class Nrs_Install {

	/**
	 * Constructor to load the shared database library
	 */
	public function __construct()
	{
		$this->db = Database::instance();
	}

	/**
	 * Creates the required database tables for the NRS plugin
	 */
	public function run_install()
	{
		// ****************************************
		// DATABASE STUFF
		// Is the NRS Service already installed?
		$exists = ORM::factory('service')
			->where('service_name', 'NRS')
			->find();
			
		if ( ! $exists->loaded)
		{
			$service = ORM::factory('service');
			$service->service_name = "NRS";
			$service->service_description = "NRS MQTT Messages";
			$service->save();
		}

		// Create the database tables.
		// Also include table_prefix in name
		$this->db->query("
			CREATE TABLE IF NOT EXISTS `".Kohana::config('database.default.table_prefix')."nrs_settings` (
				id int(11) unsigned NOT NULL AUTO_INCREMENT,
				nrs_secret varchar(100) DEFAULT NULL,
				PRIMARY KEY (`id`)
			);
		");
		// mqtt_subscription_topic=mosquitto_sub -h <host-name> -u <api-key> -t /v2/environments/+/nodes/+/datastreams/+/datapoints/+/
		$this->db->query("
			CREATE TABLE IF NOT EXISTS `".Kohana::config('database.default.table_prefix')."nrs_mqtt_subscription` (
				id int(11) unsigned NOT NULL AUTO_INCREMENT,
				mqtt_subscription_name varchar(250) NOT NULL,
				mqtt_subscription_color varchar(20) DEFAULT 'CC0000',
				mqtt_subscription_topic varchar(255) NOT NULL, 
				mqtt_host varchar(255) DEFAULT NULL,
				mqtt_port varchar(255) DEFAULT NULL,
				mqtt_subscription_id varchar(250) DEFAULT NULL,
				mqtt_subscription_active tinyint(4) NOT NULL DEFAULT 1,
				mqtt_username varchar(255) DEFAULT NULL,
				mqtt_password varchar(255) DEFAULT NULL,
				mqtt_will_topic varchar(255) DEFAULT NULL,
				mqtt_will_payload text DEFAULT NULL,
				mqtt_will_retain tinyint(4) DEFAULT NULL,
				mqtt_qos tinyint(4) DEFAULT '0',
				PRIMARY KEY (id)
			);
		");

		$this->db->query("
			CREATE TABLE IF NOT EXISTS `".Kohana::config('database.default.table_prefix')."nrs_mqtt_message` (
				id bigint(20) unsigned NOT NULL AUTO_INCREMENT,
				nrs_mqtt_subscription_id int(11) unsigned NOT NULL,
				mqtt_mid int(11) unsigned NULL,
				mqtt_topic varchar(250) NULL,
				mqtt_payloadlen int(11) unsigned NULL,
				mqtt_payload text NULL,
				mqtt_qos tinyint(4) NULL,
				mqtt_retain tinyint(4) NULL,
				mqtt_message_datetime timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP,
				nrs_entity_type tinyint(4) NOT NULL default '0' COMMENT '0 - NONE, 1 - ENVIRONMENT, 2 - NODE, 3 - DATASTREAM, 4 - DATAPOINT, -1 OTHER',
				nrs_entity_id int(11) unsigned NOT NULL DEFAULT 0,
				mqtt_topic_errors tinyint(4) NOT NULL DEFAULT 0,
				mqtt_nrs_action varchar(4) DEFAULT NULL,
				nrs_entity_uid varchar(32) DEFAULT NULL,
				PRIMARY KEY (id)
			);
		");


		$this->db->query("
			CREATE TABLE IF NOT EXISTS `".Kohana::config('database.default.table_prefix')."nrs_environment` (
				id int(11) unsigned NOT NULL AUTO_INCREMENT,
				title varchar(100) NOT NULL,
				environment_uid varchar(32) NOT NULL,
				description text,
				active tinyint(4) NOT NULL DEFAULT 1,
				status tinyint(4) NOT NULL default '3' COMMENT '1 - DEAD, 2 - ZOMBIE, 3 - FROZEN, 4 - LIVE',
				updated datetime DEFAULT NULL,
				location_id bigint(20) DEFAULT NULL,
				location_name varchar(100) DEFAULT NULL,
				location_disposition varchar(255) DEFAULT NULL,
				location_exposure varchar(255) DEFAULT NULL,
				location_latitude varchar(255) DEFAULT NULL,
				location_longitude varchar(255) DEFAULT NULL,
				location_elevation int(11) unsigned DEFAULT '0',
				automatic_reports tinyint(4) NOT NULL DEFAULT 0,
				person_first varchar(200) DEFAULT NULL,
				person_last varchar(200) DEFAULT NULL,
				person_email varchar(120) DEFAULT NULL,
				person_phone varchar(60) DEFAULT NULL,

				feed text,
				PRIMARY KEY (id)
			);
		");


		$this->db->query("
			CREATE TABLE IF NOT EXISTS `".Kohana::config('database.default.table_prefix')."nrs_node` (
				id int(11) unsigned NOT NULL AUTO_INCREMENT,
				nrs_environment_id int(11) NOT NULL DEFAULT '0',
				title varchar(100) DEFAULT NULL,
				node_uid varchar(32) NOT NULL,
				description text,
				active tinyint(4) NOT NULL DEFAULT 1,
				status tinyint(4) NOT NULL default '3' COMMENT '1 - OFF, 2 - SLEEPING, 3 - ON, 4 - TRANSMITTING',
				node_disposition varchar(255) DEFAULT NULL,
				node_exposure varchar(255) DEFAULT NULL,
				last_update datetime DEFAULT NULL,
				risk_level tinyint(4) NOT NULL default '1' COMMENT '1 - NULL, 2 - LOW, 3 - MEDIUM, 4 - HIGHT',
				updated datetime DEFAULT NULL,
				PRIMARY KEY (id)
			);
		");

		$this->db->query("
			CREATE TABLE IF NOT EXISTS `".Kohana::config('database.default.table_prefix')."nrs_datastream` (
				id int(11) unsigned NOT NULL AUTO_INCREMENT,
				nrs_environment_id int(11)  NOT NULL DEFAULT '0',
				nrs_node_id int(11)  NOT NULL DEFAULT '0',
				title varchar(100) DEFAULT NULL,
				datastream_uid varchar(32) NOT NULL,
				active tinyint(4) NOT NULL DEFAULT 1,
				unit_label varchar(100) DEFAULT NULL,
				unit_type varchar(100) DEFAULT NULL,
				unit_symbol varchar(100) DEFAULT NULL,
				unit_format varchar(100) DEFAULT NULL,
				tags text,
				current_value decimal(10,6) DEFAULT NULL,
				min_value decimal(10,6) DEFAULT NULL,
				max_value decimal(10,6) DEFAULT NULL,
				updated datetime DEFAULT NULL,
				samples_num int(11) DEFAULT '10',
				factor_title varchar(100) DEFAULT NULL,
				factor_value DECIMAL(10,6) NOT NULL DEFAULT '1.00',
				lambda_value DECIMAL(10,6) NOT NULL DEFAULT '0.00',
				constant_value DECIMAL(10,6) NOT NULL DEFAULT '0.00',
				trigger_type tinyint(4) NULL default NULL COMMENT '1 - value_at => max_value, 2 - value_at <= min_value, 3 - max_value <= value_at or value_at <= min_value, 4 - min_value <= value_at <= max_value, 5 - AVG(value_at) >= max_value, 6 - AVG(value_at) <= min_value',
				PRIMARY KEY (id)
			);
		");

		$this->db->query("
			CREATE TABLE IF NOT EXISTS `".Kohana::config('database.default.table_prefix')."nrs_datapoint` (
				id int(11) unsigned NOT NULL AUTO_INCREMENT,
				nrs_environment_id int(11)  NOT NULL DEFAULT '0',
				nrs_node_id int(11)  NOT NULL DEFAULT '0',
				nrs_datastream_id int(11)  NOT NULL DEFAULT '0',
				incident_id int(11) NOT NULL DEFAULT '0',
				updated datetime DEFAULT NULL,
				datetime_at varchar(30) NOT NULL DEFAULT '0000-00-00 00:00:00.000000',
				sample_no int(11) NOT NULL DEFAULT '0',
				value_at DECIMAL(10,6) NOT NULL DEFAULT '0',
				PRIMARY KEY (id)
			);
		");

		$this->db->query("
			CREATE TABLE IF NOT EXISTS `".Kohana::config('database.default.table_prefix')."nrs_csv_client` (
				id int(11) unsigned NOT NULL AUTO_INCREMENT,
				active tinyint(4) NOT NULL DEFAULT 1,
				folder text,
				file_name text,
				sha256sum text,
                                noitems int(11) NOT NULL DEFAULT '0',
				saved_folder text,
				updated timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP,
				PRIMARY KEY (id)
			);
		");


		$this->db->query("
			CREATE VIEW `".Kohana::config('database.default.table_prefix')."nrs_overlimits` AS 
				SELECT 
				nrs_datapoint.nrs_environment_id,
				nrs_datapoint.nrs_node_id,
				nrs_datapoint.nrs_datastream_id,
				nrs_datapoint.incident_id,
				nrs_datapoint.id as nrs_datapoint_id,
				nrs_datapoint.sample_no,
				constant_value + (nrs_datapoint.value_at - lambda_value)*factor_value AS calculated_value,
				nrs_datastream.max_value,
				nrs_datastream.min_value,
				nrs_datapoint.datetime_at,
				nrs_datapoint.updated,
				nrs_datastream.title
				FROM nrs_datapoint, nrs_datastream ,nrs_node ,nrs_environment 
				WHERE 
				nrs_environment.id = nrs_datapoint.nrs_environment_id AND
				nrs_environment.active= 1 AND
				nrs_node.id = nrs_datapoint.nrs_node_id AND
				nrs_node.active= 1 AND
				nrs_datastream.id = nrs_datapoint.nrs_datastream_id AND
				nrs_datastream.active= 1 AND (
				constant_value + (nrs_datapoint.value_at - lambda_value)*factor_value <= nrs_datastream.min_value 
				OR
				constant_value + (nrs_datapoint.value_at - lambda_value)*factor_value >= nrs_datastream.max_value )
				ORDER BY nrs_datapoint.nrs_datastream_id, nrs_datapoint.datetime_at, nrs_datapoint.updated, nrs_datapoint.sample_no;
		");

		$this->db->query("
			CREATE TABLE IF NOT EXISTS `".Kohana::config('database.default.table_prefix')."nrs_node_category` (
				id int(11) unsigned NOT NULL AUTO_INCREMENT,
				nrs_node_id int(11) unsigned NOT NULL DEFAULT '0',				
				category_id int(11) unsigned NOT NULL DEFAULT '5',				
				PRIMARY KEY (id),
				UNIQUE KEY node_category_ids (nrs_node_id,category_id)
			);
		");
	
		$this->db->query("
			CREATE TABLE IF NOT EXISTS `".Kohana::config('database.default.table_prefix')."nrs_meta` (
				id int(11) unsigned NOT NULL AUTO_INCREMENT,
				nrs_entity_id int(11) unsigned NOT NULL DEFAULT '0',	
				nrs_entity_type	tinyint(4) NOT NULL default '0' COMMENT '1 - Environment, 2 - Node, 3 - Datastream, 4 - Datapoint',		
				meta_key varchar(255) NULL DEFAULT NULL,
				meta_value longtext NULL DEFAULT NULL,
				PRIMARY KEY (id),
				KEY nrs_entity_id (nrs_entity_id),
				KEY meta_key (meta_key)
			);
		");


		//Dump the NRS scheduler item from bundled SQL dump file
		$this->db->query("DELETE FROM `".Kohana::config('database.default.table_prefix')."scheduler` where scheduler_name = 'Nrs' ");
		$db_insert = fopen (dirname(dirname(__FILE__)).'/sql/s_nrs.sql', 'r');
		$rows = fread ($db_insert, filesize(dirname(dirname(__FILE__)).'/sql/s_nrs.sql'));
		
		//split by ; to get the sql statement for inserting each row
		$rows = explode(';\n',$rows);

		foreach($rows as $query) 
		{
			$this->db->query($query);
		}


	}

	/**
	 * Deletes the database tables for the actionable module
	 */
	public function uninstall()
	{
		ORM::factory('service')
			->where('service_name', 'NRS')
			->delete_all();
		$this->db->query('DROP TABLE `'.Kohana::config('database.default.table_prefix').'nrs_settings`');
		$this->db->query('DROP TABLE `'.Kohana::config('database.default.table_prefix').'nrs_datapoint`');
		$this->db->query('DROP TABLE `'.Kohana::config('database.default.table_prefix').'nrs_datastream`');
		$this->db->query('DROP TABLE `'.Kohana::config('database.default.table_prefix').'nrs_node`');
		$this->db->query('DROP TABLE `'.Kohana::config('database.default.table_prefix').'nrs_environment`');
		$this->db->query('DROP TABLE `'.Kohana::config('database.default.table_prefix').'nrs_mqtt_message`');
		$this->db->query('DROP TABLE `'.Kohana::config('database.default.table_prefix').'nrs_mqtt_subscription`');
		$this->db->query('DROP TABLE `'.Kohana::config('database.default.table_prefix').'nrs_csv_client`');
		$this->db->query('DROP VIEW `'.Kohana::config('database.default.table_prefix').'nrs_overlimits`');
		$this->db->query('DROP TABLE `'.Kohana::config('database.default.table_prefix').'nrs_node_category`');
		$this->db->query('DROP TABLE `'.Kohana::config('database.default.table_prefix').'nrs_meta`');
	}
}
