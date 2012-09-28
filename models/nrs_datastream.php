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

class Nrs_datatstream_Model extends ORM
{
	/**
	 * One-to-many relationship definition
	 * @var array
	 */
	protected $has_many = array('nrs_datapoint');

	protected $belongs_to = array('nrs_node');
	// Database table name
	protected $table_name = 'nrs_datastream';
}
