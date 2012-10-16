<?php 
/**
 * Overlimits view page.
 *
 * PHP version 5
 * LICENSE: This source file is subject to LGPL license 
 * that is available through the world-wide-web at the following URI:
 * http://www.gnu.org/copyleft/lesser.html
 * @author     Ushahidi Team <team@ushahidi.com> 
 * @package    Ushahidi - http://source.ushahididev.com
 * @module     API Controller
 * @copyright  Ushahidi - http://www.ushahidi.com
 * @license    http://www.gnu.org/copyleft/lesser.html GNU Lesser General Public License (LGPL) 
 */
?>
			<div class="bg">
				<h2>
					<?php admin::manage_subtabs("nrs"); ?>
				</h2>
			
				<!-- tabs -->
				<div class="tabs">
					<!-- tabset -->
					<ul class="tabset">
						<li><a href="<?php echo url::site() . 'admin/manage/nrs' ?>"><?php echo Kohana::lang('nrs.NRS_mqtt_deployments');?></a></li>
						<li><a href="<?php echo url::site() . 'admin/manage/nrs/mqtt_messages' ?>"><?php echo Kohana::lang('nrs.NRS_mqtt_messages');?></a></li>
						<li><a href="<?php echo url::site() . 'admin/manage/nrs_environments' ?>"><?php echo Kohana::lang('nrs.environments');?></a></li>
						<li><a href="<?php echo url::site() . 'admin/manage/nrs_nodes' ?>"><?php echo Kohana::lang('nrs.nodes');?></a></li>
						<li><a href="<?php echo url::site() . 'admin/manage/nrs_datastreams' ?>"><?php echo Kohana::lang('nrs.datastreams');?></a></li>
						<li><a href="<?php echo url::site() . 'admin/manage/nrs_datapoints' ?>"><?php echo Kohana::lang('nrs.datapoints');?></a></li>
						<li><a href="<?php echo url::site() . 'admin/manage/nrs_overlimits' ?>" class="active"><?php echo Kohana::lang('nrs.overlimits');?></a></li>
					</ul>
				
					<!-- tab -->
					<div class="tab">
					</div>
				</div>
				
				<?php
				if ($form_error) {
				?>
					<!-- red-box -->
					<div class="red-box">
						<h3><?php echo Kohana::lang('ui_main.error');?></h3>
						<ul><?php echo Kohana::lang('ui_main.select_one');?></ul>
					</div>
				<?php
				}

				if ($form_saved) {
				?>
					<!-- green-box -->
					<div class="green-box" id="submitStatus">
						<h3><?php echo Kohana::lang('ui_main.messages');?> <?php echo $form_action; ?> <a href="#" id="hideMessage" class="hide"><?php echo Kohana::lang('ui_main.hide_this_message');?></a></h3>
					</div>
				<?php
				}
				?>
				<?php if(isset($nrs_overlimits) && !empty($nrs_overlimits) && count($nrs_overlimits)>0) { ?>
				<div id="visualization" style="width: 800px; height: 400px;"></div>
				<?php
				} else {
				?>
				<span>THERE ARE NO RELEVANT EVENTS AVAILABLE</span>
				<?php
				}
				?>
				<!-- tabs -->
				<div class="tabs">
					<!-- tabset -->
					<a name="add"></a>
					<ul class="tabset">
						<li><a href="#" class="active"><?php echo Kohana::lang('ui_main.create_report');?></a></li>
					</ul>
					<div class="tab">
						<!-- report-table -->
						<?php print form::open(NULL, array('id' => 'nrs_overlimitListing', 'name' => 'nrs_overlimitListing')); ?>
						<!-- HERE THE FORM -->
						<input type="hidden" id="nrs_datastream_id" name="nrs_datastream_id" value="" />
						<input type="hidden" id="updated_timestamp" name="updated_timestamp" value="" />
						<input type="hidden" name="action" id="action" value="g">
						


						<div class="tab_form_item">
							<strong><?php echo Kohana::lang('ui_main.title');?>:</strong><br />
							<?php print form::input('title', '', ' class="text"'); ?>
						</div>

						<div class="tab_form_item">
							<h4><?php echo Kohana::lang('ui_main.date');?> <span><?php echo Kohana::lang('ui_main.date_format');?></span></h4>
							<?php print form::input('updated_date', '', ' readonly="readonly" class="text"'); ?>								
							<?php print $date_picker_js; ?>		
						</div>
						<div class="tab_form_item">
							<br /><strong><?php echo "Link the whole ".Kohana::lang('nrs.node');?>:</strong>
							<?php print form::checkbox('whole_node', '1', True); ?>
						</div>
						<div class="tab_form_item">
							<input type="submit" class="save-rep-btn" value="<?php echo Kohana::lang('ui_main.create');?>" />
						</div>

						<?php print form::close(); ?>

					</div>

				</div>
			</div>
