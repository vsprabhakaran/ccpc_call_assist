<br/>
<br/>
<div class='noMoreDataParent'>
<ul class = 'noMoreData'>
	<li class = 'Heading'>
		<?php echo $formTitle ?>
	</li>
	<li class = 'Content'>
		<?php echo $formMessage ?>
	</li>
	<li class = 'sessionStatus'>
		<div>Session Information</div>
		<div> User : <?php echo $pfIndex ?></div>
		<div> Chosen Circle : <?php echo $chosenCircle ?></div>
		<div> Chosen Date : <?php echo $chosenDate ?></div>
	</li>
</ul>
</div>
<div id="chequeButtonPanel">
	<div class='left'>
	<?php
	if ($formTitle != 'Pending Cheques') {
		echo "<button onclick=\"javascript:window.location='pendingChequesList'\" class='pure-button pure-button-primary' type='button'><< Pending Cheques</button>";
	} else {
		echo "<button onclick=\"javascript:window.location='index'\" class='pure-button pure-button-primary' type='button'><< New Cheques</button>";
	}
	?>
	</div>
	<div class = 'mid'> <button onclick="javascript:window.location='../userchoice/show'" class="pure-button pure-button-primary" type='button'>Home</button> </div>
</div>
<br/>