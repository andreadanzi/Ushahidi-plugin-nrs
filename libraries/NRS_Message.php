<?php defined('SYSPATH') or die('No direct script access.');

/**
 * The nrs sender
 */
class Nrs_Message_Core {
	
	public function send($to = NULL, $from = NULL, $message = NULL)
	{
		$nrs = ORM::factory("nrs_message");
		$nrs->nrs_to = $to;
		$nrs->nrs_from = $from;
		$nrs->nrs_message = $message;
		$nrs->nrs_message_date = date("Y-m-d H:i:s",time());
		$nrs->save();
		
		return true;
	}
	
}
