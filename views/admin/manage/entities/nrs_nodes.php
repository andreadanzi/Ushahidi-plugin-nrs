<?php 
/**
 * Nodes view page.
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
						<li><a href="<?php echo url::site() . 'admin/manage/nrs_nodes' ?>" class="active"><?php echo Kohana::lang('nrs.nodes');?></a></li>
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
				<?php print form::open(NULL, array('id' => 'nrs_nodeListing', 'name' => 'nrs_nodeListing')); ?>
				<!-- HERE THE FORM -->
					<input type="hidden" name="action" id="action" value="">
					<input type="hidden" name="nrs_node_id"  id="nrs_node_id_action"  value="">
					<div class="table-holder">
						<table class="table">
							<thead>
								<tr>
									<th class="col-1"><input id="checkallincidents" type="checkbox" class="check-box" onclick="CheckAll( this.id, 'nrs_node_id[]' )" /></th>
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
								foreach ($nrs_nodes as $nrs_node)
								{
									$nrs_node_id = $nrs_node->id;
									$nrs_node_title = $nrs_node->title;
									$nrs_node_active = $nrs_node->active;
									$last_update = $nrs_node->last_update;
									$nrs_node_description = $nrs_node->description;
									$nrs_node_date = date('Y-m-d H:i:s', strtotime($nrs_node->updated));
									$nrs_node_uid = $nrs_node->node_uid;
									$nrs_environment = $nrs_node->nrs_environment;
									$nrs_env_uid = $nrs_node->nrs_environment->environment_uid;
									$arr_res = sscanf($nrs_node_uid,$nrs_env_uid."%s");
									$nrs_only_node_uid = $arr_res[0];
									$status_descr = "ND";
									$status_id = $nrs_node->status;
									switch ($status_id) {
										case 1:  
											$status_descr = "OFF";
											break;
										case 2:  
											$status_descr = "SLEEPING";
											break;
										case 3:  
											$status_descr = "ON";
											break;
										case 4: 
											$status_descr = "TRANSMITTING";
											break;
									}									
									
									$datastreams_count = ORM::factory('nrs_datastream')->where('nrs_node_id',$nrs_node->id)->count_all();
									?>
									<tr>
										<td class="col-1"><input name="nrs_node_id[]" value="<?php echo $nrs_node_id; ?>" type="checkbox" class="check-box"/></td>
										<td class="col-2">
											<div class="post">

												<h4><a href="<?php echo url::site() . 'admin/manage/nrs_nodes/edit/' . $nrs_node_id; ?>" class="more"><?php echo $nrs_node_title; ?></a>&nbsp;&nbsp;&nbsp;[<a href="<?php echo url::base() . 'admin/manage/nrs_datastreams/node/'.$nrs_node_id ?>"><?php echo  "#".$datastreams_count ." ". Kohana::lang('nrs.datastreams');?></a>]</h4>
												<p><a href="javascript:preview('message_preview_<?php echo $nrs_node_id?>')"><?php echo Kohana::lang('nrs.preview_description') .' with uid='.$nrs_node_uid;?></a></p>
												<div id="message_preview_<?php echo $nrs_node_id?>" style="display:none;">
													<?php echo $nrs_node_description; ?>
												</div>
											</div>
											<ul class="info">
												<li class="none-separator">Status: <strong class="nrs_status_<?php echo $status_id;?>"><?php echo $status_descr;?></strong></li>
<li><?php echo Kohana::lang('ui_main.geolocation_available');?>?: <strong><?php echo ($nrs_environment->location->id) ? utf8::strtoupper(Kohana::lang('ui_main.yes')). " - ".$nrs_environment->location->location_name : utf8::strtoupper(Kohana::lang('ui_main.no'));?></strong></li>
											</ul>
										</td>
										<td class="col-3"><?php echo $nrs_node_date; ?></td>

										<td class="col-4">
												<ul>
													<li class="none-separator"><a href="#add" onClick="fillFields('<?php echo(rawurlencode($nrs_node_id)); ?>','<?php echo(rawurlencode($nrs_node_title)); ?>','<?php echo(rawurlencode($nrs_node_description)); ?>','<?php echo(rawurlencode($nrs_env_uid)); ?>','<?php echo(rawurlencode($nrs_only_node_uid)); ?>','<?php echo(rawurlencode($nrs_node->node_disposition)); ?>','<?php echo(rawurlencode($nrs_node->node_exposure)); ?>','<?php echo(rawurlencode($nrs_node->status)); ?>','<?php echo(rawurlencode($nrs_node->risk_level)); ?>','<?php echo(rawurlencode($nrs_node->nrs_environment_id)); ?>','<?php echo(rawurlencode($nrs_node->last_update)); ?>')"><?php echo Kohana::lang('ui_main.edit');?></a></li>
													<li class="none-separator">
													<?php if($nrs_node_active==1 || $nrs_node_active==2) {?>
													<a href="javascript:nodeAction('h','HIDE',<?php echo rawurlencode($nrs_node_id);?>)" class="status_yes"><?php echo ($nrs_node_active==2? Kohana::lang('nrs.env_status_2') : Kohana::lang('nrs.env_status_1') );?></a>
													<?php } else {?>
													<a href="javascript:nodeAction('v','ACTIVATE',<?php echo rawurlencode($nrs_node_id);?>)" class="status_no"><?php echo  Kohana::lang('nrs.env_status_3');?></a>
													<?php } ?>
													</li>
													<li><a href="javascript:nodeAction('d','DELETE','<?php echo(rawurlencode($nrs_node_id)); ?>')" class="del"><?php echo Kohana::lang('ui_main.delete');?></a></li>
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
						<?php print form::open(NULL,array('id' => 'nrs_nodeMain', 'name' => 'nrs_nodeMain')); ?>
						
						<input type="hidden" id="nrs_node_id" 
							name="nrs_node_id" value="" />
						<input type="hidden" id="node_uid" 
							name="node_uid" value="" />
						<input type="hidden" name="action" 
							id="action" value="a"/>
						<div class="tab_form_item">
							<strong><?php echo Kohana::lang('ui_main.name');?>:</strong><br />
							<?php print form::input('title', '', ' class="text"'); ?>
						</div>
						<div class="tab_form_item">
							<strong><?php echo Kohana::lang('nrs.node_uid');?>:</strong><br />
							<?php print form::input('environment_uid', '', ' readonly="readonly" class="text uid"'); ?>
							<?php print form::input('only_node_uid', '', ' class="text uid"'); ?>
						</div>
						<div class="tab_form_item">
							<strong><?php echo Kohana::lang('nrs.environment');?>:</strong><br />

							<?php print '<span class="sel-holder">' .
								    form::dropdown('nrs_environment_id', $environments_array,'','  onClick=\'fillEnvUID(this,'.json_encode($environment_uids_array).');\'') . '</span>'; ?>
						</div>
						<div class="tab_form_item">
							<strong><?php echo Kohana::lang('nrs.status');?>:</strong><br />

							<?php print '<span class="sel-holder">' .
								    form::dropdown('status', $status_array,'') . '</span>'; ?>
						</div>
						<div class="tab_form_item">
							<strong><?php echo Kohana::lang('nrs.risk_level');?>:</strong><br />

							<?php print '<span class="sel-holder">' .
								    form::dropdown('risk_level', $risk_level_array,'') . '</span>'; ?>
						</div>
						<div style="clear:both"></div>
						<div class="tab_form_item">
							<strong><?php echo Kohana::lang('nrs.location_disposition');?>:</strong><br />
							<?php print form::input('node_disposition', '', ' class="text"'); ?>
						</div>
						<div class="tab_form_item">
							<strong><?php echo Kohana::lang('nrs.location_exposure');?>:</strong><br />
							<?php print form::input('node_exposure', '', ' class="text"'); ?>
						</div>
						<div class="tab_form_item">
							<strong><?php echo Kohana::lang('nrs.last_update');?>:</strong><br />
							<?php print form::input('last_update', '', ' readonly="readonly" class="text"'); ?>
						</div>
						<div class="tab_form_item">
							<strong><?php echo Kohana::lang('nrs.description');?>:</strong><br />
							<?php print form::textarea('description','', ' rows="12" cols="40"') ?>

						</div>
						<div style="clear:both"></div>
						<div class="tab_form_item">
							<input type="submit" class="save-rep-btn" value="<?php echo Kohana::lang('ui_main.save');?>" />
						</div>


						<?php print form::close(); ?>			
					</div>
				</div>

			</div>
