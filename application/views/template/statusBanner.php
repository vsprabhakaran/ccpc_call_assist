<div id="statusBanner">
<?php if(isset($login_pf)){ ?>
	<div class="banner_headLine">
				<div><b>Your Status</b></div>
				<div>PF Index : <?php echo $login_pf; ?> </div>
				<div>Date : <?php echo $chosen_date; ?></div>
				<div>Pending : <?php echo $pending; ?></div>
				<div>Completed : <?php echo $completed; ?> </div>
	</div>
	<?php } ?>
	<div class="banner_CircleStatus">
		<?php
		foreach ($circleStatus["all_circle_names"] as $cur_circle_name) {
			$confirmed = $circleStatus[$cur_circle_name]['confirmed'];
			$total = $circleStatus[$cur_circle_name]['total'];
			echo "<div> $cur_circle_name : $confirmed / $total </div>";
		}
		?>
	</div>
</div>