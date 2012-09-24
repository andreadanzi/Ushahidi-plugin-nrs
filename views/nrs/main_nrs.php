<table class="table-list">
	<thead>
		<tr>
			<th scope="col"><?php echo Kohana::lang('nrs.title'); ?></th>
			<th scope="col"><?php echo Kohana::lang('nrs.location'); ?></th>
			<th scope="col"><?php echo Kohana::lang('nrs.date'); ?></th>
		</tr>
	</thead>
	<tbody>
		<?php
		if ($nrs_total_items == 0)
		{
			?>
			<tr><td colspan="3"><?php echo Kohana::lang('nrs.no_environments'); ?></td></tr>
			<?php
		}
		foreach ($nrs_environments as $nrs_environment)
		{
			$nrs_environment_id = $nrs_environment->id;
			$nrs_environment_title = text::limit_chars($nrs_environment->title, 40, '...', True);
			$nrs_environment_date = $nrs_environment->updated;
			$nrs_environment_date = date('M j Y', strtotime($nrs_environment->updated));
			// $nrs_environment_location = $nrs_environment->location->location_name;
			$nrs_environment_location = $nrs_environment->location_name;
		?>
		<tr>
			<td><a href="<?php echo url::site() . 'nrs_environments/view/' . $nrs_environment_id; ?>"> <?php echo $nrs_environment_title ?></a></td>
			<td><?php echo $nrs_environment_location ?></td>
			<td><?php echo $nrs_environment_date; ?></td>
		</tr>
		<?php
		}
		?>
	</tbody>
</table>
<a class="more" href="<?php echo url::site() . 'nrs_environments' ?>"><?php echo Kohana::lang('nrs.view_more'); ?></a>
<div style="clear:both;"></div>
