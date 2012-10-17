<?php defined('SYSPATH') OR die('No direct access allowed.');
/**
 * Meta helper class.
 * Common functions for handling meta data
 *
 * $Id: valid.php 3917 2009-01-21 03:06:22Z zombor $
 *
 * @package    Ushahidi
 * @category   Helpers
 * @author     Ushahidi Team
 * @copyright  (c) 2008 Ushahidi Team
 * @license    http://www.ushahidi.com/license.html
 */
class meta_Core {
	
	public static function get_meta( $object_id, $object_type, $meta_key, $single = true)
	{	
		$results = array();
		if ( !$object_id = abs( intval($object_id)) )
			return false;

		if ( !$object_type = abs( intval($object_type)) )
			return false;

		$meta_key = stripslashes($meta_key);
		$nrs_metas = ORM::factory('nrs_meta')->where('meta_key', $meta_key)->where('nrs_entity_id', $object_id)->where('nrs_entity_type', $object_type)->find_all();
		foreach($nrs_metas as $nrs_meta)
		{
			$meta_value = $nrs_meta->meta_value;
			if ( meta::is_serialized( $meta_value ) )
				$meta_value = @unserialize($meta_value );
			$results[] = array("meta_key"=>$nrs_meta->meta_key,"meta_value"=>$meta_value);
		}
		if($single && count($results)>0)  
			return $results[0]["meta_value"];
		else if($single && count($results) == 0)
			return false;
		return $results;
	}
	public static function delete_meta( $object_id, $object_type, $meta_key)
	{		
		if ( !$object_id = abs( intval($object_id)) )
			return false;

		if ( !$object_type = abs( intval($object_type)) )
			return false;

		$meta_key = stripslashes($meta_key);
		$nrs_metas = ORM::factory('nrs_meta')->where('meta_key', $meta_key)->where('nrs_entity_id', $object_id)->where('nrs_entity_type', $object_type)->delete_all();
	}

	public static function save_meta( $object_id, $object_type, $meta_key, $meta_value)
	{
		$mids = array();

		if ( !$object_id = abs( intval($object_id)) )
			return false;

		if ( !$object_type = abs( intval($object_type)) )
			return false;

		$meta_key = stripslashes($meta_key);
		$meta_value = meta::stripslashes_deep($meta_value);
		
		$_meta_value = $meta_value;
		if ( is_array( $meta_value ) || is_object( $meta_value ) )
			$meta_value = serialize( $meta_value );

		$nrs_metas = ORM::factory('nrs_meta')->where('meta_key', $meta_key)->where('nrs_entity_id', $object_id)->where('nrs_entity_type', $object_type)->find_all();
		foreach($nrs_metas as $nrs_meta)
		{
			$nrs_meta->meta_value =$meta_value;
			$result = $nrs_meta->save();
			$mids[] = $result->id;
		}

		if (count($mids) == 0 )
		{
			$mids[0] = meta::add_meta($object_id, $object_type, $meta_key, $meta_value);
		}
		return $mids;
	}

	public static function add_meta( $object_id, $object_type, $meta_key, $meta_value, $unique = false)
	{

		if ( !$object_id = abs( intval($object_id)) )
			return false;

		if ( !$object_type = abs( intval($object_type)) )
			return false;

		$meta_key = stripslashes($meta_key);
		$meta_value = meta::stripslashes_deep($meta_value);

		if ( $unique && Database::instance('default')->query(
			"SELECT COUNT(*) FROM nrs_meta WHERE meta_key = ? AND nrs_entity_id = ? AND nrs_entity_type = ?", $meta_key, $object_id,$object_type )  )
			return false;

		$_meta_value = $meta_value;
		if ( is_array( $meta_value ) || is_object( $meta_value ) )
			$meta_value = serialize( $meta_value );

		$nrs_meta = new Nrs_meta_Model();
		$nrs_meta->nrs_entity_id = $object_id;
		$nrs_meta->nrs_entity_type = $object_type;
		$nrs_meta->meta_key = $meta_key;
		$nrs_meta->meta_value =$meta_value;
		$result = $nrs_meta->save();


		if ( ! $result )
			return false;

		$mid = (int) $result->id;

		return $mid;
	}
	
	public function stripslashes_deep($value) {
		if ( is_array($value) ) {
			$value = array_map('meta::stripslashes_deep', $value);
		} elseif ( is_object($value) ) {
			$vars = get_object_vars( $value );
			foreach ($vars as $key=>$data) {
				$value->{$key} = meta::stripslashes_deep( $data );
			}
		} else {
			$value = stripslashes($value);
		}

		return $value;
	}
	

	public static function is_serialized( $data ) {
		// if it isn't a string, it isn't serialized
		if ( ! is_string( $data ) )
			return false;
		$data = trim( $data );
	 	if ( 'N;' == $data )
			return true;
		$length = strlen( $data );
		if ( $length < 4 )
			return false;
		if ( ':' !== $data[1] )
			return false;
		$lastc = $data[$length-1];
		if ( ';' !== $lastc && '}' !== $lastc )
			return false;
		$token = $data[0];
		switch ( $token ) {
			case 's' :
				if ( '"' !== $data[$length-2] )
					return false;
			case 'a' :
			case 'O' :
				return (bool) preg_match( "/^{$token}:[0-9]+:/s", $data );
			case 'b' :
			case 'i' :
			case 'd' :
				return (bool) preg_match( "/^{$token}:[0-9.E-]+;\$/", $data );
		}
	return false;
}
}

