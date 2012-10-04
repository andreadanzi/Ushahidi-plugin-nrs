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
						<li><a href="<?php echo url::site() . 'admin/manage/nrs_environments' ?>"><?php echo Kohana::lang('nrs.environments');?></a></li>
						<li><a href="<?php echo url::site() . 'admin/manage/nrs_nodes' ?>"><?php echo Kohana::lang('nrs.nodes');?></a></li>
						<li><a href="<?php echo url::site() . 'admin/manage/nrs_datastreams' ?>"><?php echo Kohana::lang('nrs.datastreams');?></a></li>
						<li><a href="<?php echo url::site() . 'admin/manage/nrs_datapoints' ?>"><?php echo Kohana::lang('nrs.datapoints');?></a></li>
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
									$message_date = date('Y-m-d H:i:s', strtotime($message->mqtt_message_datetime));

									$mqtt_subscription_name = $message->nrs_mqtt_subscription->mqtt_subscription_name;
									
									$location_id = 0;// da completare $message->location_id;
									$nrs_entity_id = $message->nrs_entity_id;
									$nrs_entity_type = $message->nrs_entity_type;
									$mqtt_topic_errors = $message->mqtt_topic_errors;
									$mqtt_nrs_action = $message->mqtt_nrs_action;
									$nrs_entity_uid = $message->nrs_entity_uid;
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
												<li class="none-separator"><?php echo Kohana::lang('nrs.NRS_mqtt_client');?>: <strong><?php echo $mqtt_subscription_name; ?></strong>
												<!-- <li><?php echo Kohana::lang('ui_main.geolocation_available');?>?: <strong><?php echo ($location_id) ? utf8::strtoupper(Kohana::lang('ui_main.yes')) : utf8::strtoupper(Kohana::lang('ui_main.no'));?></strong></li> -->
                        <li><?php echo Kohana::lang('nrs.NRS_mqtt_errors');?>:<strong><?php echo ($mqtt_topic_errors) ? utf8::strtoupper(Kohana::lang('ui_main.yes')) : utf8::strtoupper(Kohana::lang('ui_main.no'));?></strong></li>
											</ul>
										</td>
										<td class="col-3"><?php echo $message_date; ?></td>
										<td class="col-4">
											<ul>
												<li class="none-separator"><a href="#add" onClick="fillFields('<?php echo(rawurlencode($message_id)); ?>','<?php echo(rawurlencode($message_title)); ?>','<?php echo(rawurlencode($message_description)); ?>')"><?php echo Kohana::lang('ui_main.edit');?></a></li>
												<?php
												if ($nrs_entity_id != 0) {
													echo "<li class=\"none-separator\"><a href=\"". url::base() . 'admin/manage/nrs/edit_nrs_entity?nrs_type='.$nrs_entity_type.'&amp;nrs_id=' . $nrs_entity_id ."\" class=\"status_yes\"><strong>".Kohana::lang('nrs.view_entity')."</strong></a></li>";
												}
												elseif ($nrs_entity_type>0)
												{
													echo "<li class=\"none-separator\"><a href=\"javascript:messageAction('g','GENERATE NEW ENTITY','".rawurlencode($message_id)."');\">".Kohana::lang('nrs.create_entity')."</a></li>";
												}
                        else
                        {
													echo "<li class=\"none-separator\"><strong>".Kohana::lang('nrs.unable_create_entity')."</strong></li>";
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

				<!-- tabs -->
				<div class="tabs">
					<!-- tabset -->
					<a name="add"></a>
					<ul class="tabset">
						<li><a href="#" class="active"><?php echo Kohana::lang('ui_main.add_edit');?></a></li>
					</ul>
					<!-- tab -->
					<div class="tab">
						<?php print form::open(NULL,array('id' => 'mqtt_messageMain', 'name' => 'mqtt_messageMain')); ?>
						
						<input type="hidden" id="nrs_mqtt_message_id" 
							name="nrs_mqtt_message_id" value="" />
						<input type="hidden" name="action" 
							id="action" value="a"/>
						<div class="tab_form_item">
							<strong><?php echo Kohana::lang('ui_main.name');?>:</strong><br />
							<?php print form::input('title', '', ' readonly="readonly" class="text long"'); ?>
						</div>
						<div style="clear:both"></div>
						<div class="tab_form_item">
							<strong><?php echo Kohana::lang('nrs.description');?>:</strong><br />
							<?php print form::textarea('description','', ' rows="12" cols="100"') ?>

						</div>
						<div style="clear:both"></div>
						<div class="tab_form_item">
							<input type="submit" class="save-rep-btn" value="<?php echo Kohana::lang('ui_main.save');?>" />
						</div>


						<?php print form::close(); ?>			
					</div>
				</div>


			</div>
