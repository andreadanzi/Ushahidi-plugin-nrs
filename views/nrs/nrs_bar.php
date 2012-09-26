<div class="cat-filters clearingfix" style="margin-top:20px;">
			<strong><?php echo Kohana::lang('nrs.NRS_mqtt_deployments');?> <span>[<a href="javascript:toggleLayer('nrs_switch_link','nrs_switch')" id="nrs_switch_link"><?php echo Kohana::lang('ui_main.hide'); ?></a>]</span></strong>
</div>
		<ul id="nrs_switch" class="category-filters">
			<?php
			
			foreach ($subscriptions as $subscription => $subscription_info)
			{
				$subscription_name = $subscription_info[0];
				$subscription_color = $subscription_info[1];
				echo '<li><a href="#" id="nrs_mqtt_subscription_'. $subscription .'"><div class="swatch" style="background-color:#'.$subscription_color.'"></div>
				<div>'.$subscription_name.'</div></a></li>';
			}
			?>
		</ul>
