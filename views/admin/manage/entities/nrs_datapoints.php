<?php 
/**
 * Datapoints view page.
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
						<li><a href="<?php echo url::site() . 'admin/manage/nrs_datapoints' ?>" class="active"><?php echo Kohana::lang('nrs.datapoints');?></a></li>
					</ul>
				
					<!-- tab -->
					<div class="tab">
						<?php print form::open(NULL, array('id' => 'nrs_datapointSearch', 'name' => 'nrs_datapointSearch','method'=>'get')); ?>
						<div class="tab_form_item">
							<?php print '<span class="sel-holder">' . form::dropdown('nrs_datastream_id', $datastreams_array,(!empty($_GET['nrs_datastream_id'])? $_GET['nrs_datastream_id'] : ''),' onchange="onChangeDatastream(this);"') . '</span>'; ?></div>

						<div class="tab_form_item">
							<?php print '<span class="sel-holder">' . form::dropdown('nrs_updated', $distinct_updated_array,(!empty($_GET['nrs_updated'])? $_GET['nrs_updated'] : ''),' onchange="this.form.submit();"') . '</span>'; ?></div>
						<!-- <div class="tab_form_item"><input type="submit" class="search-nrs-btn" value="<?php echo Kohana::lang('ui_main.search');?>" /></div> -->

						<div class="tab_form_item">
						<a class="search-nrs-btn" href="<?php echo url::site() . 'admin/manage/nrs_datastreams?nrs_id='.(!empty($_GET['nrs_datastream_id'])? $_GET['nrs_datastream_id'] : '') ?>"><?php echo "Go to the parent ". Kohana::lang('nrs.datastream');?></a>
						</div>
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
				<?php print form::open(NULL, array('id' => 'nrs_datapointListing', 'name' => 'nrs_datapointListing')); ?>
				<!-- HERE THE FORM -->
					<input type="hidden" name="action" id="action" value="">
					<input type="hidden" name="nrs_datapoint_id"  id="nrs_datapoint_id_action"  value="">
					<div class="table-holder">

						<div id="nrs_dashboard">
							<div id='slider_div' class="nrs_slider"></div>
							<div id='chart_div' class="nrs_chart"><strong>NO DATA AVAILABLE - DATASTREAM NEEDED</strong></div>
						<div>
					</div>

				<?php print form::close(); ?>

			</div>
