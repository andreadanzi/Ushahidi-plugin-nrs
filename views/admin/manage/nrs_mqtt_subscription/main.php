<?php 
/**
 * NRS MQTT_Subscription maintenance view page.
 *
 * PHP version 5
 * LICENSE: This source file is subject to LGPL license 
 * that is available through the world-wide-web at the following URI:
 * http://www.gnu.org/copyleft/lesser.html
 * @author     Ushahidi Team <team@ushahidi.com> 
 * @package    Ushahidi - http://source.ushahididev.com
 * @module     NRS MQTT_Subscription maintenance view
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
						<li><a href="<?php echo url::site() . 'admin/manage/nrs' ?>" class="active"><?php echo Kohana::lang('nrs.NRS_mqtt_deployments');?></a></li>
						<li><a href="<?php echo url::site() . 'admin/manage/nrs_mqtt_messages' ?>"><?php echo Kohana::lang('nrs.NRS_mqtt_messages');?></a></li>
					</ul>
					
					<!-- tab -->
					<div class="tab">
						<ul>
							<li><a href="javascript:refreshSubscriptions();"><?php echo Kohana::lang('nrs.NRS_refresh_mqtt_deployments');?></a></li><span id="mqtt_deployments_loading"></span>
						</ul>
					</div>
				</div>

				<?php
				if ($form_error) {
				?>
					<!-- red-box -->
					<div class="red-box">
						<h3><?php echo Kohana::lang('ui_main.error');?></h3>
						<ul>
						<?php
						foreach ($errors as $error_item => $error_description)
						{
							// print "<li>" . $error_description . "</li>";
							print (!$error_description) ? '' : "<li>" . $error_description . "</li>";
						}
						?>
						</ul>
					</div>
				<?php
				}

				if ($form_saved) {
				?>
					<!-- green-box -->
					<div class="green-box">
						<h3><?php echo $form_action; ?>!</h3>
					</div>
				<?php
				}
				?>
				<!-- report-table -->
				<div class="report-form">
					<?php print form::open(NULL,array('id' => 'mqtt_subscriptionListing',
					 	'name' => 'mqtt_subscriptionListing')); ?>
						<input type="hidden" name="action" id="action" value="">
						<input type="hidden" name="nrs_mqtt_subscription_id" id="nrs_mqtt_subscription_id_action" value="">
						<div class="table-holder">
							<table class="table">
								<thead>
									<tr>
										<th class="col-1">&nbsp;</th>
										<th class="col-2"><?php echo Kohana::lang('nrs.NRS_mqtt_client');?></th>
										<th class="col-3"><?php echo Kohana::lang('ui_main.color');?></th>
										
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
									foreach ($nrs_mqtt_subscriptions as $nrs_mqtt_subscription)
									{
										$nrs_mqtt_subscription_id = $nrs_mqtt_subscription->id;
										$mqtt_subscription_name =$nrs_mqtt_subscription->mqtt_subscription_name;
										$mqtt_subscription_color = $nrs_mqtt_subscription->mqtt_subscription_color;
										$mqtt_subscription_topic = $nrs_mqtt_subscription->mqtt_subscription_topic;
										$mqtt_subscription_active = $nrs_mqtt_subscription->mqtt_subscription_active;
										$mqtt_host = $nrs_mqtt_subscription->mqtt_host;
										$mqtt_port = $nrs_mqtt_subscription->mqtt_port;
										$mqtt_subscription_id = $nrs_mqtt_subscription->mqtt_subscription_id;
										$mqtt_username = $nrs_mqtt_subscription->mqtt_username;
										$mqtt_password = $nrs_mqtt_subscription->mqtt_password;
										?>
										<tr>
											<td class="col-1">&nbsp;</td>
											<td class="col-2">
												<div class="post">
													<h4><?php echo $mqtt_subscription_name; ?></h4>
												</div>
												<ul class="links">
													<?php
													if($mqtt_subscription_topic)
													{
														?><li class="none-separator"><strong><?php echo text::auto_link("MQTT Server ".$mqtt_host.":".($mqtt_port==''?'1883':$mqtt_port)." for topic ".$mqtt_subscription_topic); ?></strong></li>
<li class="none-separator"><strong><?php echo text::auto_link(Kohana::lang('nrs.mqtt_username') . " ".($mqtt_username==''?'NONE':$mqtt_username)." ".Kohana::lang('nrs.mqtt_sub_id') ." ".($mqtt_subscription_id==''?'NONE':$mqtt_subscription_id)); ?></strong></li>
<?php
													}
													?>
												</ul>
											</td>
											<td class="col-3">
											<?php echo "<img src=\"".url::base()."swatch/?c=".$mqtt_subscription_color."&w=30&h=30\">"; ?>
											</td>
									
											<td class="col-4">
												<ul>
													<li class="none-separator"><a href="#add" onClick="fillFields('<?php echo(rawurlencode($nrs_mqtt_subscription_id)); ?>','<?php echo(rawurlencode($mqtt_subscription_topic)); ?>','<?php echo(rawurlencode($mqtt_subscription_name)); ?>','<?php echo(rawurlencode($mqtt_subscription_color)); ?>','<?php echo(rawurlencode($mqtt_host)); ?>','<?php echo(rawurlencode($mqtt_port)); ?>','<?php echo(rawurlencode($mqtt_username)); ?>','<?php echo(rawurlencode($mqtt_password)); ?>','<?php echo(rawurlencode($mqtt_subscription_id)); ?>')"><?php echo Kohana::lang('ui_main.edit');?></a></li>
													<li class="none-separator">
													<?php if($mqtt_subscription_active==1 || $mqtt_subscription_active==2) {?>
													<a href="javascript:mqtt_subscriptionAction('h','STOP',<?php echo rawurlencode($nrs_mqtt_subscription_id);?>)" class="status_yes"><?php echo ($mqtt_subscription_active==2? Kohana::lang('nrs.mqtt_client_status_running') : Kohana::lang('nrs.mqtt_client_status_active') );?></a>
													<?php } else {?>
													<a href="javascript:mqtt_subscriptionAction('v','ACTIVATE',<?php echo rawurlencode($nrs_mqtt_subscription_id);?>)" class="status_yes"><?php echo  Kohana::lang('nrs.mqtt_client_status_stopped');?></a>
													<?php } ?>
													</li>
<li><a href="javascript:mqtt_subscriptionAction('d','DELETE','<?php echo(rawurlencode($nrs_mqtt_subscription_id)); ?>')" class="del"><?php echo Kohana::lang('ui_main.delete');?></a></li>
												</ul>
											</td>
										</tr>
										<?php									
									}
									?>
								</tbody>
							</table>
						</div>
					<?php print form::close(); ?>
				</div>
				
				<!-- tabs -->
				<div class="tabs">
					<!-- tabset -->
					<a name="add"></a>
					<ul class="tabset">
						<li><a href="#" class="active"><?php echo Kohana::lang('ui_main.add_edit');?></a></li>
					</ul>
					<!-- tab -->
					<div class="tab">
						<?php print form::open(NULL,array('id' => 'mqtt_subscriptionMain', 'name' => 'mqtt_subscriptionMain')); ?>
						<input type="hidden" id="nrs_mqtt_subscription_id" 
							name="nrs_mqtt_subscription_id" value="" />
						<input type="hidden" name="action" 
							id="action" value="a"/>
						<div class="tab_form_item">
							<strong><?php echo Kohana::lang('ui_main.name');?>:</strong><br />
							<?php print form::input('mqtt_subscription_name', '', ' class="text"'); ?>
						</div>
						<div class="tab_form_item">
							<strong><?php echo Kohana::lang('nrs.mqtt_topic');?>:</strong><br />
							<?php print form::input('mqtt_subscription_topic', '', ' class="text long"'); ?>
						</div>
						<div class="tab_form_item">
							<strong><?php echo Kohana::lang('nrs.mqtt_host');?>:</strong><br />
							<?php print form::input('mqtt_host', '', ' class="text long"'); ?>
						</div>
						<div class="tab_form_item">
							<strong><?php echo Kohana::lang('nrs.mqtt_port');?>:</strong><br />
							<?php print form::input('mqtt_port', '', ' class="text long"'); ?>
						</div>
						<div class="tab_form_item">
							<strong><?php echo Kohana::lang('nrs.mqtt_username');?>:</strong><br />
							<?php print form::input('mqtt_username', '', ' class="text long"'); ?>
						</div>
						<div class="tab_form_item">
							<strong><?php echo Kohana::lang('nrs.mqtt_password');?>:</strong><br />
							<?php print form::input('mqtt_password', '', ' class="text long"'); ?>
						</div>
						<div class="tab_form_item">
							<strong><?php echo Kohana::lang('nrs.mqtt_sub_id');?>:</strong><br />
							<?php print form::input('mqtt_subscription_id', '', ' class="text long"'); ?>
						</div>
						<div class="tab_form_item">
							<strong><?php echo Kohana::lang('ui_main.color');?>:</strong><br />
							<?php print form::input('mqtt_subscription_color', '', ' class="text"'); ?>
							<script type="text/javascript" charset="utf-8">
								$(document).ready(function() {
									$('#mqtt_subscription_color').ColorPicker({
										onSubmit: function(hsb, hex, rgb) {
											$('#mqtt_subscription_color').val(hex);
										},
										onChange: function(hsb, hex, rgb) {
											$('#mqtt_subscription_color').val(hex);
										},
										onBeforeShow: function () {
											$(this).ColorPickerSetColor(this.value);
										}
									})
									.bind('keyup', function(){
										$(this).ColorPickerSetColor(this.value);
									});
								});
							</script>
						</div>
						<div style="clear:both"></div>
						<div class="tab_form_item">
							<input type="submit" class="save-rep-btn" value="<?php echo Kohana::lang('ui_main.save');?>" />
						</div>
						<?php print form::close(); ?>			
					</div>
				</div>
			</div>
