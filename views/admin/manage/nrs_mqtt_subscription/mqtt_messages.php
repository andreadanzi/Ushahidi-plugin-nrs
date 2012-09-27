<?php 
/**
 * Messages view page.
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
						<li><a href="<?php echo url::site() . 'admin/manage/nrs/mqtt_messages' ?>" class="active"><?php echo Kohana::lang('nrs.NRS_mqtt_messages');?></a></li>
					</ul>
				
					<!-- tab -->
					<div class="tab">
						&nbsp;
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
				<!-- report-table -->
				<?php print form::open(NULL, array('id' => 'mqtt_subscriptionListing', 'name' => 'mqtt_subscriptionListing')); ?>
				<!-- HERE THE FORM -->
					<input type="hidden" name="action" id="action" value="">
					<input type="hidden" name="nrs_mqtt_message_id"  id="nrs_mqtt_message_id_action"  value="">
					<div class="table-holder">
						<table class="table">
							<thead>
								<tr>
									<th class="col-1"><input id="checkallincidents" type="checkbox" class="check-box" onclick="CheckAll( this.id, 'nrs_mqtt_message_id[]' )" /></th>
									<th class="col-2"><?php echo Kohana::lang('nrs.NRS_message_details');?></th>
									<th class="col-3"><?php echo Kohana::lang('ui_main.date');?></th>
									<th class="col-4"><?php echo Kohana::lang('ui_main.actions');?></th>
								</tr>
							</thead>
							<tfoot>
								<tr class="foot">
									<td colspan="4">
										<?php echo $pagination; ?>
									</td>
								</tr>
							</tfoot>
							<tbody>
								<?php
								if ($total_items == 0)
								{
								?>
									<tr>
										<td colspan="4" class="col">
											<h3><?php echo Kohana::lang('ui_main.no_results');?></h3>
										</td>
									</tr>
								<?php	
								}
								foreach ($nrs_mqtt_messages as $message)
								{
									$message_id = $message->id;
									$message_title = $message->mqtt_topic;
									$message_description = $message->mqtt_payload;
									$message_link = $message->nrs_mqtt_subscription->mqtt_subscription_topic;
									$message_date = date('Y-m-d', strtotime($message->mqtt_message_datetime));
									
									$mqtt_subscription_name = $message->nrs_mqtt_subscription->mqtt_subscription_name;
									
									$location_id = 0;// da completare $message->location_id;
									$incident_id = 0;// da completare $message->incident_id;
									?>
									<tr>
										<td class="col-1"><input name="nrs_mqtt_message_id[]" value="<?php echo $message_id; ?>" type="checkbox" class="check-box"/></td>
										<td class="col-2">
											<div class="post">
												<h4><?php echo $message_title; ?></h4>
												<p><a href="javascript:preview('message_preview_<?php echo $message_id?>')"><?php echo Kohana::lang('nrs.NRS_preview_message');?></a></p>
												<div id="message_preview_<?php echo $message_id?>" style="display:none;">
													<?php echo $message_description; ?>
												</div>
											</div>
											<ul class="info">
												<li class="none-separator"><?php echo Kohana::lang('nrs.NRS_mqtt_client');?>: <strong><a href="<?php echo $message_link; ?>"><?php echo $mqtt_subscription_name; ?></a></strong>
												<li><?php echo Kohana::lang('ui_main.geolocation_available');?>?: <strong><?php echo ($location_id) ? utf8::strtoupper(Kohana::lang('ui_main.yes')) : utf8::strtoupper(Kohana::lang('ui_main.no'));?></strong></li>
											</ul>
										</td>
										<td class="col-3"><?php echo $message_date; ?></td>
										<td class="col-4">
											<ul>
												<?php
												if ($incident_id != 0) {
													echo "<li class=\"none-separator\"><a href=\"". url::base() . 'admin/reports/edit/' . $incident_id ."\" class=\"status_yes\"><strong>".Kohana::lang('ui_main.view_report')."</strong></a></li>";
												}
												else
												{
													echo "<li class=\"none-separator\"><a href=\"".url::base().'admin/reports/edit?fid='.$message_id."\">".Kohana::lang('ui_main.create_report')."?</a></li>";
												}
												?>
											<li><a href="javascript:messageAction('d','DELETE','<?php echo(rawurlencode($message_id)); ?>');"><?php echo utf8::strtoupper(Kohana::lang('ui_main.delete'));?></a></li>
											</ul>
										</td>
									</tr>
									<?php
								}
								?>
							</tbody>
						</table>
					</div>


<!--  **************************************************************************  ENDS THE ITEMS -->


				<?php print form::close(); ?>

			</div>
