<?php 
/**
 * Environments view page.
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
						<li><a href="<?php echo url::site() . 'admin/manage/nrs_environments' ?>" class="active"><?php echo Kohana::lang('nrs.environments');?></a></li>
						<li><a href="<?php echo url::site() . 'admin/manage/nrs_nodes' ?>"><?php echo Kohana::lang('nrs.nodes');?></a></li>
						<li><a href="<?php echo url::site() . 'admin/manage/nrs_datastreams' ?>"><?php echo Kohana::lang('nrs.datastreams');?></a></li>
						<li><a href="<?php echo url::site() . 'admin/manage/nrs_datapoints' ?>"><?php echo Kohana::lang('nrs.datapoints');?></a></li>
						<li><a href="<?php echo url::site() . 'admin/manage/nrs_overlimits' ?>"><?php echo Kohana::lang('nrs.overlimits');?></a></li>
					</ul>
				
					<!-- search tab -->
					<div class="tab">
					<?php print form::open(NULL, array('id' => 'nrs_environmentSearch', 'name' => 'nrs_environmentSearch','method'=>'get')); ?>
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
				<?php print form::open(NULL, array('id' => 'nrs_environmentListing', 'name' => 'nrs_environmentListing')); ?>
				<!-- HERE THE FORM -->
					<input type="hidden" name="action" id="action" value="">
					<input type="hidden" name="nrs_environment_id"  id="nrs_environment_id_action"  value="">
					<div class="table-holder">
						<table class="table">
							<thead>
								<tr>
									<th class="col-1"><input id="checkallincidents" type="checkbox" class="check-box" onclick="CheckAll( this.id, 'nrs_environment_id[]' )" /></th>
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
								foreach ($nrs_environments as $nrs_environment)
								{
									$nrs_environment_id = $nrs_environment->id;
									$overlimit_string="";
									foreach ($overlimits as $overlimit)
									{
										if($nrs_environment_id== $overlimit->nrs_environment_id)
										{
											$overlimit_string .= "<p>There are #".$overlimit->overlimits_no. " events with magnitude=".$overlimit->overlimits_weight. " please check <a href=". url::site() . "admin/manage/nrs_datapoints?nrs_datastream_id=".rawurlencode($overlimit->nrs_datastream_id)."&nrs_updated=".rawurlencode($overlimit->updated).">here</a></p>";
										}
									}
									$nrs_environment_title = $nrs_environment->title;
									$nrs_environment_active = $nrs_environment->active;
									$nrs_environment_description = $nrs_environment->description;
									$nrs_environment_date = date('Y-m-d H:i:s', strtotime($nrs_environment->updated));
									$nrs_environment_uid = $nrs_environment->environment_uid;
									$status_descr = "ND";
									$status_id = $nrs_environment->status;
									switch ($status_id) {
										case 1:  
											$status_descr = "DEAD";
											break;
										case 2:  
											$status_descr = "ZOMBIE";
											break;
										case 3:  
											$status_descr = "FROZEN";
											break;
										case 4: 
											$status_descr = "LIVE";
											break;
									}
									$location_id = $nrs_environment->location_id;
									
									
									$nodes_count = ORM::factory('nrs_node')->where('nrs_environment_id',$nrs_environment->id)->count_all();
									?>
									<tr>
										<td class="col-1"><input name="nrs_environment_id[]" value="<?php echo $nrs_environment_id; ?>" type="checkbox" class="check-box"/></td>
										<td class="col-2">
											<div class="nrspost">

												<h4><a href="<?php echo url::site() . 'admin/manage/nrs_environments/edit/' . $nrs_environment_id; ?>" class="more"><?php echo $nrs_environment_title; ?></a>&nbsp;&nbsp;&nbsp;[<a href="<?php echo url::base() . 'admin/manage/nrs_nodes/environment/'.$nrs_environment_id ?>"><?php echo  "#".$nodes_count ." ". Kohana::lang('nrs.nodes');?></a>]</h4>
												<p><a href="javascript:preview('message_preview_<?php echo $nrs_environment_id?>')"><?php echo Kohana::lang('nrs.preview_description') . ' '.Kohana::lang('nrs.environment').' with uid='.$nrs_environment_uid;?></a></p>
												<div id="message_preview_<?php echo $nrs_environment_id?>" style="display:none;">
													<?php echo $nrs_environment_description . "<br/>".$overlimit_string; ?>
												</div>
											</div>
											<ul class="info">
												<li class="none-separator">Status: <strong class="nrs_status_<?php echo $status_id;?>"><?php echo $status_descr;?></strong></li>
<li><?php echo Kohana::lang('ui_main.geolocation_available');?>?: <strong><?php echo ($location_id) ? utf8::strtoupper(Kohana::lang('ui_main.yes')). " - ".$nrs_environment->location->location_name : utf8::strtoupper(Kohana::lang('ui_main.no'));?></strong></li>
											</ul>
										</td>
										<td class="col-3"><?php echo $nrs_environment_date; ?></td>

										<td class="col-4">
												<ul>
													<li class="none-separator"><a href="#add" onClick="fillFields('<?php echo(rawurlencode($nrs_environment_id)); ?>','<?php echo(rawurlencode($nrs_environment_title)); ?>','<?php echo(rawurlencode($nrs_environment_description)); ?>','<?php echo(rawurlencode($nrs_environment_uid)); ?>','<?php echo(rawurlencode($nrs_environment->location_name)); ?>','<?php echo(rawurlencode($nrs_environment->location_disposition)); ?>','<?php echo(rawurlencode($nrs_environment->location_exposure)); ?>','<?php echo(rawurlencode($nrs_environment->location_latitude)); ?>','<?php echo(rawurlencode($nrs_environment->location_longitude)); ?>','<?php echo(rawurlencode($nrs_environment->location_elevation)); ?>','<?php echo(rawurlencode($nrs_environment->feed)); ?>','<?php echo(rawurlencode($nrs_environment->status)); ?>','<?php echo(rawurlencode($nrs_environment->person_first)); ?>','<?php echo(rawurlencode($nrs_environment->person_last)); ?>','<?php echo(rawurlencode($nrs_environment->person_email)); ?>','<?php echo(rawurlencode($nrs_environment->person_phone)); ?>')"><?php echo Kohana::lang('ui_main.edit');?></a></li>
													<li class="none-separator">
													<?php if($nrs_environment_active==1 || $nrs_environment_active==2) {?>
													<a href="javascript:environmentAction('h','HIDE',<?php echo rawurlencode($nrs_environment_id);?>)" class="status_yes"><?php echo ($nrs_environment_active==2? Kohana::lang('nrs.env_status_2') : Kohana::lang('nrs.env_status_1') );?></a>
													<?php } else {?>
													<a href="javascript:environmentAction('v','ACTIVATE',<?php echo rawurlencode($nrs_environment_id);?>)" class="status_no"><?php echo  Kohana::lang('nrs.env_status_3');?></a>
													<?php } ?>
													</li>
													<li><a href="javascript:environmentAction('d','DELETE','<?php echo(rawurlencode($nrs_environment_id)); ?>')" class="del"><?php echo Kohana::lang('ui_main.delete');?></a></li>
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
						<?php print form::open(NULL,array('id' => 'nrs_environmentMain', 'name' => 'nrs_environmentMain')); ?>
						
						<div class="nrs-col-1">
							<input type="hidden" id="nrs_environment_id" 
								name="nrs_environment_id" value="" />
							<input type="hidden" name="action" 
								id="action" value="a"/>
							<div class="tab_form_item">
								<strong><?php echo Kohana::lang('ui_main.name');?>:</strong><br />
								<?php print form::input('title', '', ' class="text"'); ?>
							</div>
							<div class="tab_form_item">
								<strong><?php echo Kohana::lang('nrs.environment_uid');?>:</strong><br />
								<?php print form::input('environment_uid', '', ' class="text"'); ?>
							</div>
							<div style="clear:both"></div>
							<div class="tab_form_item">
								<strong><?php echo Kohana::lang('nrs.location_name');?>:</strong><br />
								<?php print form::input('location_name', '', ' class="text"'); ?>
							</div>
							<div class="tab_form_item">
								<strong><?php echo Kohana::lang('nrs.status');?>:</strong><br />

								<?php print '<span class="sel-holder">' .
									    form::dropdown('status', $status_array,'') . '</span>'; ?>
							</div>
							<div style="clear:both"></div>
							<div class="tab_form_item">
								<strong><?php echo Kohana::lang('nrs.location_disposition');?>:</strong><br />
								<?php print form::input('location_disposition', '', ' class="text"'); ?>
							</div>
							<div class="tab_form_item">
								<strong><?php echo Kohana::lang('nrs.location_exposure');?>:</strong><br />
								<?php print form::input('location_exposure', '', ' class="text"'); ?>
							</div>
							<div style="clear:both"></div>
							<div class="tab_form_item">
								<strong><?php echo Kohana::lang('nrs.location_latitude');?>:</strong><br />
								<?php print form::input('latitude', '', ' class="text"'); ?>
							</div>
							<div class="tab_form_item">
								<strong><?php echo Kohana::lang('nrs.location_longitude');?>:</strong><br />
								<?php print form::input('longitude', '', ' class="text"'); ?>
							</div>
							<div class="tab_form_item">
								<strong><?php echo Kohana::lang('nrs.location_elevation');?>:</strong><br />
								<?php print form::input('location_elevation', '', ' class="text"'); ?>
							</div>


							<div class="tab_form_item">
								<strong><?php echo Kohana::lang('nrs.person_first');?>:</strong><br />
								<?php print form::input('person_first', '', ' class="text"'); ?>
							</div>
							<div class="tab_form_item">
								<strong><?php echo Kohana::lang('nrs.person_last');?>:</strong><br />
								<?php print form::input('person_last', '', ' class="text"'); ?>
							</div>
							<div class="tab_form_item">
								<strong><?php echo Kohana::lang('nrs.person_email');?>:</strong><br />
								<?php print form::input('person_email', '', ' class="text"'); ?>
							</div>
							<div class="tab_form_item">
								<strong><?php echo Kohana::lang('nrs.person_phone');?>:</strong><br />
								<?php print form::input('person_phone', '', ' class="text"'); ?>
							</div>


							<div style="clear:both"></div>
							<div class="tab_form_item">
								<strong><?php echo Kohana::lang('nrs.feed');?>:</strong><br />
								<?php print form::input('feed', '', ' class="text"'); ?>
							</div>
							<div style="clear:both"></div>
							<div class="tab_form_item">
								<strong><?php echo Kohana::lang('nrs.description');?>:</strong><br />
								<?php print form::textarea('description','', ' rows="12" cols="40"') ?>

							</div>
							<div style="clear:both"></div>
							<div class="tab_form_item">
								<input type="submit" class="save-rep-btn" value="<?php echo Kohana::lang('ui_main.save');?>" />
							</div>
						</div>
						<div class="nrs-col-2">
							<div class="incident-location">
								<h4>LOCATION</h4>
								<div id="divMap" class="map_holder_reports">
									<div id="geometryLabelerHolder" class="olControlNoSelect">
										<div id="geometryLabeler">
											<div id="geometryLabelComment">
												<span id="geometryLabel"><label><?php echo Kohana::lang('ui_main.geometry_label');?>:</label> <?php print form::input('geometry_label', '', ' class="lbl_text"'); ?></span>
												<span id="geometryComment"><label><?php echo Kohana::lang('ui_main.geometry_comments');?>:</label> <?php print form::input('geometry_comment', '', ' class="lbl_text2"'); ?></span>
											</div>
											<div>
												<span id="geometryColor"><label><?php echo Kohana::lang('ui_main.geometry_color');?>:</label> <?php print form::input('geometry_color', '', ' class="lbl_text"'); ?></span>
												<span id="geometryStrokewidth"><label><?php echo Kohana::lang('ui_main.geometry_strokewidth');?>:</label> <?php print form::dropdown('geometry_strokewidth', $stroke_width_array, ''); ?></span>
												<span id="geometryLat"><label><?php echo Kohana::lang('ui_main.latitude');?>:</label> <?php print form::input('geometry_lat', '', ' class="lbl_text"'); ?></span>
												<span id="geometryLon"><label><?php echo Kohana::lang('ui_main.longitude');?>:</label> <?php print form::input('geometry_lon', '', ' class="lbl_text"'); ?></span>
											</div>
										</div>
										<div id="geometryLabelerClose"></div>
									</div>
								</div>
							</div>
							<div class="incident-find-location">
								<div id="panel" class="olControlEditingToolbar"></div>
								<div class="btns" style="float:left;">
									<ul style="padding:4px;">
										<li><a href="#" class="btn_del_last"><?php echo utf8::strtoupper(Kohana::lang('ui_main.delete_last'));?></a></li>
										<li><a href="#" class="btn_del_sel"><?php echo utf8::strtoupper(Kohana::lang('ui_main.delete_selected'));?></a></li>
										<li><a href="#" class="btn_clear"><?php echo utf8::strtoupper(Kohana::lang('ui_main.clear_map'));?></a></li>
									</ul>
								</div>
								<div style="clear:both;"></div>
								<?php print form::input('location_find', '', ' title="'.Kohana::lang('ui_main.location_example').'" class="findtext"'); ?>
								<div class="btns" style="float:left;">
									<ul>
										<li><a href="#" class="btn_find"><?php echo utf8::strtoupper(Kohana::lang('ui_main.find_location'));?></a></li>
									</ul>
								</div>
								<div id="find_loading" class="incident-find-loading"></div>
								<div style="clear:both;"><?php echo Kohana::lang('ui_main.pinpoint_location');?>.</div>
							</div>
						</div>

						<?php print form::close(); ?>			
					</div>
				</div>




			</div>
