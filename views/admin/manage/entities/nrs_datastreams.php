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
						<li><a href="<?php echo url::site() . 'admin/manage/nrs_nodes' ?>"><?php echo Kohana::lang('nrs.nodes');?></a></li>
						<li><a href="<?php echo url::site() . 'admin/manage/nrs_datastreams' ?>" class="active"><?php echo Kohana::lang('nrs.datastreams');?></a></li>
						<li><a href="<?php echo url::site() . 'admin/manage/nrs_datapoints' ?>"><?php echo Kohana::lang('nrs.datapoints');?></a></li>
						<li><a href="<?php echo url::site() . 'admin/manage/nrs_overlimits' ?>"><?php echo Kohana::lang('nrs.overlimits');?></a></li>
					</ul>
				
					<!-- search tab -->
					<div class="tab">
					<?php print form::open(NULL, array('id' => 'nrs_datastreamSearch', 'name' => 'nrs_datastreamSearch','method'=>'get')); ?>
						<div class="tab_form_item"><?php print form::input('list_filter', (!empty($_GET['list_filter'])? $_GET['list_filter'] : ''), ' class="text"'); ?></div>
						<div class="tab_form_item"><input type="submit" class="search-nrs-btn" value="<?php echo Kohana::lang('ui_main.search');?>" /></div>
					<?php print form::close(); ?>

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
				<?php print form::open(NULL, array('id' => 'nrs_datastreamListing', 'name' => 'nrs_datastreamListing')); ?>
				<!-- HERE THE FORM -->
					<input type="hidden" name="action" id="action" value="">
					<input type="hidden" name="nrs_datastream_id"  id="nrs_datastream_id_action"  value="">
					


					<div class="table-holder">
						<table class="table">
							<thead>
								<tr>
									<th class="col-1"><input id="checkallincidents" type="checkbox" class="check-box" onclick="CheckAll( this.id, 'nrs_datastream_id[]' )" /></th>
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
								foreach ($nrs_datastreams as $nrs_datastream)
								{
									$nrs_datastream_id = $nrs_datastream->id;
									$nrs_datastream_title = $nrs_datastream->title;
									$nrs_datastream_active = $nrs_datastream->active;
									$nrs_datastream_unit_label = $nrs_datastream->unit_label;
									$nrs_datastream_unit_type = $nrs_datastream->unit_type;
									$nrs_datastream_unit_symbol = $nrs_datastream->unit_symbol;
									$nrs_datastream_unit_format = $nrs_datastream->unit_format;
									$nrs_datastream_description = $nrs_datastream_title . " (".$nrs_datastream_unit_label.") with type " .$nrs_datastream_unit_type. " (".$nrs_datastream_unit_symbol.")";
									$nrs_datastream_date = date('Y-m-d H:i:s', strtotime($nrs_datastream->updated));
									$nrs_datastream_uid = $nrs_datastream->datastream_uid;
									$nrs_environment = $nrs_datastream->nrs_node->nrs_environment;
									$nrs_node = $nrs_datastream->nrs_node;
									$nrs_node_uid = $nrs_datastream->nrs_node->node_uid;
									$nrs_env_uid = $nrs_environment->environment_uid;
									$arr_res = sscanf($nrs_node_uid,$nrs_env_uid."%s");
									$nrs_only_node_uid = $arr_res[0];
									$arr_res = sscanf($nrs_datastream_uid,$nrs_node_uid."%s");
									$nrs_only_datastream_uid = $arr_res[0];								
									
									$datapoint_count = ORM::factory('nrs_datapoint')->where('nrs_datastream_id',$nrs_datastream->id)->count_all();
									?>
									<tr>
										<td class="col-1"><input name="nrs_datastream_id[]" value="<?php echo $nrs_datastream_id; ?>" type="checkbox" class="check-box"/></td>
										<td class="col-2">
											<div class="nrspost">

												<h4><a href="<?php echo url::site() . 'admin/manage/nrs_datastreams/edit/' . $nrs_datastream_id; ?>" class="more"><?php echo $nrs_datastream_title; ?></a>&nbsp;&nbsp;&nbsp;[<a href="<?php echo url::base() . 'admin/manage/nrs_datapoints/datastream/'.$nrs_datastream_id ?>"><?php echo  "#".$datapoint_count ." ". Kohana::lang('nrs.datapoints');?></a>]</h4>
												<p><a href="javascript:preview('message_preview_<?php echo $nrs_datastream_id?>')"><?php echo Kohana::lang('nrs.preview_description'). ' '. Kohana::lang('nrs.datastream') .' with uid='.$nrs_datastream_uid;?></a></p>
												<div id="message_preview_<?php echo $nrs_datastream_id?>" style="display:none;">
													<?php echo $nrs_datastream_description; ?>
													<div id="visualization_<?php echo $nrs_datastream_id?>" style="width: 500px; height: 400px;"></div>
												</div>
											</div>
											<ul class="info">
												<li class="none-separator">Node: <strong><a href="<?php echo url::site() . 'admin/manage/nrs_nodes?nrs_id=' . $nrs_node->id; ?>"><?php echo $nrs_node->title;?></a></strong></li>
<li><?php echo Kohana::lang('ui_main.geolocation_available');?>?: <strong><a href="<?php echo url::site() . 'admin/manage/nrs_environments/id/' . $nrs_environment->id; ?>"><?php echo ($nrs_environment->location->id) ? utf8::strtoupper(Kohana::lang('ui_main.yes')). " - ".$nrs_environment->location->location_name : utf8::strtoupper(Kohana::lang('ui_main.no')). " - ".$nrs_environment->title;?></a></strong></li>
											</ul>
										</td>
										<td class="col-3"><?php echo $nrs_datastream_date; ?></td>

										<td class="col-4">
												<ul>
													<li class="none-separator"><a href="#add" onClick="fillFields('<?php echo(rawurlencode($nrs_datastream_id)); ?>','<?php echo(rawurlencode($nrs_datastream_title)); ?>','<?php echo(rawurlencode($nrs_datastream_unit_label)); ?>','<?php echo(rawurlencode($nrs_datastream_unit_type)); ?>','<?php echo(rawurlencode($nrs_datastream_unit_symbol)); ?>','<?php echo(rawurlencode($nrs_datastream_unit_format)); ?>','<?php echo(rawurlencode($nrs_env_uid)); ?>','<?php echo(rawurlencode($nrs_only_node_uid)); ?>','<?php echo(rawurlencode($nrs_only_datastream_uid)); ?>','<?php echo(rawurlencode($nrs_datastream->tags)); ?>','<?php echo(rawurlencode($nrs_datastream->current_value)); ?>','<?php echo(rawurlencode($nrs_datastream->min_value)); ?>','<?php echo(rawurlencode($nrs_datastream->max_value)); ?>','<?php echo(rawurlencode($nrs_datastream->nrs_environment_id)); ?>','<?php echo(rawurlencode($nrs_datastream->nrs_node_id)); ?>','<?php echo(rawurlencode($nrs_datastream->samples_num)); ?>','<?php echo(rawurlencode($nrs_datastream->factor_title)); ?>','<?php echo(rawurlencode($nrs_datastream->factor_value)); ?>','<?php echo(rawurlencode($nrs_datastream->lambda_value)); ?>','<?php echo(rawurlencode($nrs_datastream->constant_value)); ?>')"><?php echo Kohana::lang('ui_main.edit');?></a></li>
													<li class="none-separator">
													<?php if($nrs_datastream_active==1 || $nrs_datastream_active==2) {?>
													<a href="javascript:datastreamAction('h','HIDE',<?php echo rawurlencode($nrs_datastream_id);?>)" class="status_yes"><?php echo ($nrs_datastream_active==2? Kohana::lang('nrs.env_status_2') : Kohana::lang('nrs.env_status_1') );?></a>
													<?php } else {?>
													<a href="javascript:datastreamAction('v','ACTIVATE',<?php echo rawurlencode($nrs_datastream_id);?>)" class="status_no"><?php echo  Kohana::lang('nrs.env_status_3');?></a>
													<?php } ?>
													</li>
													<li><a href="javascript:datastreamAction('d','DELETE','<?php echo(rawurlencode($nrs_datastream_id)); ?>')" class="del"><?php echo Kohana::lang('ui_main.delete');?></a></li>
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
						<?php print form::open(NULL,array('id' => 'nrs_datastreamMain', 'name' => 'nrs_datastreamMain')); ?>
						
						<input type="hidden" id="nrs_datastream_id" 
							name="nrs_datastream_id" value="" />
						<input type="hidden" id="datastream_uid" 
							name="datastream_uid" value="" />
						<input type="hidden" id="nrs_environment_id" 
							name="nrs_environment_id" value="" />
						<input type="hidden" name="action" 
							id="action" value="a"/>
						<div class="tab_form_item">
							<strong><?php echo Kohana::lang('ui_main.name');?>:</strong><br />
							<?php print form::input('title', '', ' class="text"'); ?>
						</div>
						<div class="tab_form_item">
							<strong><?php echo Kohana::lang('nrs.datastream_uid');?>:</strong><br />
							<?php print form::input('environment_uid', '', ' readonly="readonly" class="text uid"'); ?>
							<?php print form::input('only_node_uid', '', ' readonly="readonly" class="text uid"'); ?>
							<?php print form::input('only_datastream_uid', '', ' class="text uid"'); ?>
						</div>
						<div class="tab_form_item">
							<strong><?php echo Kohana::lang('nrs.node');?>:</strong><br />

							<?php print '<span class="sel-holder">' .
								    form::dropdown('nrs_node_id', $nodes_array,'','  onClick=\'fillNodeUID(this,'.json_encode($nodes_uids_array).');\'') . '</span>'; ?>
						</div>
						<div style="clear:both"></div>
						<div class="tab_form_item">
							<strong><?php echo Kohana::lang('nrs.unit_label');?>:</strong><br />
							<?php print form::input('unit_label', '', ' class="text"'); ?>
						</div>
						<div class="tab_form_item">
							<strong><?php echo Kohana::lang('nrs.unit_type');?>:</strong><br />
							<?php print form::input('unit_type', '', ' class="text"'); ?>
						</div>
						<div class="tab_form_item">
							<strong><?php echo Kohana::lang('nrs.unit_symbol');?>:</strong><br />
							<?php print form::input('unit_symbol', '', ' class="text small"'); ?>
						</div>
						<div class="tab_form_item">
							<strong><?php echo Kohana::lang('nrs.unit_format');?>:</strong><br />
							<?php print form::input('unit_format', '', ' class="text"'); ?>
						</div>
						<div style="clear:both"></div>
						<div class="tab_form_item">
							<strong><?php echo Kohana::lang('nrs.current_value');?>:</strong><br />
							<?php print form::input('current_value', '', ' class="text"'); ?>
						</div>
						<div class="tab_form_item">
							<strong><?php echo Kohana::lang('nrs.min_value');?>:</strong><br />
							<?php print form::input('min_value', '', ' class="text"'); ?>
						</div>
						<div class="tab_form_item">
							<strong><?php echo Kohana::lang('nrs.max_value');?>:</strong><br />
							<?php print form::input('max_value', '', ' class="text"'); ?>
						</div>
						<div class="tab_form_item">
							<strong><?php echo Kohana::lang('nrs.samples_num');?>:</strong><br />
							<?php print form::input('samples_num', '', ' class="text"'); ?>
						</div>
						<div style="clear:both"></div>
						<div class="tab_form_item">
							<strong><?php echo Kohana::lang('nrs.factor_title');?>:</strong><br />
							<?php print form::input('factor_title', '', ' class="text"'); ?>
						</div>
						<div class="tab_form_item">
							<strong><?php echo Kohana::lang('nrs.factor_value');?>:</strong><br />
							<?php print form::input('factor_value', '', ' class="text"'); ?>
						</div>
						<div class="tab_form_item">
							<strong><?php echo Kohana::lang('nrs.lambda_value');?>:</strong><br />
							<?php print form::input('lambda_value', '', ' class="text"'); ?>
						</div>
						<div class="tab_form_item">
							<strong><?php echo Kohana::lang('nrs.constant_value');?>:</strong><br />
							<?php print form::input('constant_value', '', ' class="text"'); ?>
						</div>
						<div style="clear:both"></div>
						<div class="tab_form_item">
							<strong><?php echo Kohana::lang('nrs.tags');?>:</strong><br />
							<?php print form::input('tags', '', ' class="text long"'); ?>
						</div>
						<div style="clear:both"></div>
						<div class="tab_form_item">
							<input type="submit" class="save-rep-btn" value="<?php echo Kohana::lang('ui_main.save');?>" />
						</div>


						<?php print form::close(); ?>			
					</div>
				</div>

			</div>
