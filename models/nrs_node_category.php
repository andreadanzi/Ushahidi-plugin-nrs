<?php defined('SYSPATH') or die('No direct script access.');

/**
* Model for Categories for each nrs_node
 *
 * PHP version 5
 * LICENSE: This source file is subject to LGPL license 
 * that is available through the world-wide-web at the following URI:
 * http://www.gnu.org/copyleft/lesser.html
 * @author     Ushahidi Team <team@ushahidi.com> 
 * @package    Ushahidi - http://source.ushahididev.com
 * @subpackage Models
 * @copyright  Ushahidi - http://www.ushahidi.com
 * @license    http://www.gnu.org/copyleft/lesser.html GNU Lesser General Public License (LGPL) 
 */

class Nrs_node_Category_Model extends ORM
{
	protected $belongs_to = array('nrs_node', 'category');
	
	// Database table name
	protected $table_name = 'nrs_node_category';
	
	/**
	 * Assigns a category id to an nrs_node if it hasn't already been assigned
	 * @param int $nrs_node_id nrs_node to assign the category to
	 * @param int $category_id category id of the category you want to assign to the nrs_node
	 * @return array
	 */
	public static function assign_category_to_node($nrs_node_id,$category_id)
	{	
		
		// Check to see if it is already added to that category
		//    If it's not, add it.
		
		$nrs_node_category = ORM::factory('nrs_node_category')->where(array('nrs_node_id'=>$nrs_node_id,'category_id'=>$category_id))->find_all();
		
		if( ! $nrs_node_category->count() )
		{
			$new_nrs_node_category = ORM::factory('nrs_node_category');
			$new_nrs_node_category->category_id = $category_id;
			$new_nrs_node_category->nrs_node_id = $nrs_node_id;
			$new_nrs_node_category->save();
		}
		
		return true;
	}
}
