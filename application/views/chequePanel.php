<br/>
<script type="text/javascript">
	var pf;
	var baseURL = "<?php echo base_url(); ?>";
	$(document).ready(function() {
		$(".row").click(function() {
			$(".row").css("background-color", "white");
			$(this).css("background-color", "aquamarine");
		});
		var i = 1;
	});

	function fetchImage(chequeNo, accNo,amount) {
		var micr = $('input[name=micr_code]').val();
		var tc = $('input[name=tc]').val();
		var stdAmt = pad(amount, 16);
		var imageName = micr + "_" + chequeNo + "_" + tc + "_" + stdAmt + "0_1.jpg";
		$("#chequeImage").attr("src", baseURL+"assets/img/StateBankofIndia_IN.png" );//+ imageName);
	}

	function pad(str, max) {
		str = str.toString();
		return str.length < max ? pad("0" + str, max) : str;
	}

	function doPOST_Request(phpURL, chequeNo, accNo, typeCall) {
		var returnMsg = '';
		$.ajax({
			type : 'POST',
			url : phpURL,
			data : {
				cheqNo : chequeNo,
				accno : accNo,
				type : typeCall
			},
			success : function(msg) {
				if (msg != "")
					returnMsg = msg;
				else
					alert("not Found");
			},
			error : function(msg) {
				alert("fail : " + msg);
			},
			async : false
		});
		return returnMsg;
	}

	function validateAndLoadNextAccount() {
		window.location.href = "mainPage.php";
	}
</script>
<div id="chequePanel">
	<form name="chequeProcessingForm" id="chequeProcessingForm" method="post" action="updateCallingStatus">
		<?php
		echo form_hidden('date_chosen', 'none');
		echo form_hidden('circle_chosen', 'none');
		echo form_hidden('processingChequesCategory', $chequeCategory);
		echo form_hidden('micr_code', $chequeSet[0] -> MICRCODE);
		echo form_hidden('tc', $chequeSet[0] -> TC);
		echo form_hidden('accountNumber', $chequeSet[0] -> ACCOUNTNO);
		if ($chequeCategory === 'pendingCheques') {
			echo form_hidden('pendingChequeSetSequenceNumber', $pendingChequeSetSequenceNumber);
		}
		?>
		<?php
		if ($chequeCategory === 'pendingCheques') {
			echo "<div id='pendingRibbonParent'><div id='pendingRibbon'>";
			echo($pendingChequeSetSequenceNumber + 1) . "  of  " . $totalNumberofChequeset;
			echo "</div></div>";
			echo "<div class='formHeading'>Pending Cheques Processing</div>";
		} else {
			echo "<div class='formHeading'>New Cheques Processing";
			if ($chequeCategory === 'lockedCheques')
				echo " (resume from last session)";
			echo "</div>";
		}
		?>
		<div id="chequeImagePanel">
			<img id="chequeImage" src="" height=300 width=660 />
		</div>
		<br/>
		<div id="chequeListPanel">
			<table id="chequeList" class="pure-table" style="border-collapse:collapse">
				<thead>
					<tr>
						<th>Sno</th>
						<th>Cheque No.</th>
						<th>Account No.</th>
						<th>Beneficiary name</th>
						<th>Amount</th>
						<th>Status</th>
					</tr>
				</thead>
				<tbody>
					<?php
					$sno = 1;
					$totalAmount = 0;
					$chequeNumbers = "";
					foreach ($chequeSet as $currentCheque) {
						echo "<tr class='row'><td>" . $sno . "</td>";
						$sno++;
						echo "<td class='chequeNo'><a onClick='javascript:fetchImage($currentCheque->CHEQUENO,$currentCheque->ACCOUNTNO,$currentCheque->AMOUNT)'>" . $currentCheque -> CHEQUENO . "</a></td>";
						echo "<td>" . $currentCheque -> ACCOUNTNO . "</td>";
						echo "<td>" . $currentCheque -> NAME . "</td>";
						echo "<td class='amount'>" . $currentCheque -> AMOUNT . "</td>";
						echo "<td><select id='callStatus_$currentCheque->CHEQUENO' name='callStatus_$currentCheque->CHEQUENO'>" . "<option value=''>None</option>" . "<option value='CC'>Confirmed by Customer</option>" . "<option value='CBP'>Confirmed by branch through phone</option>" . "<option value='CBM'>Confirmed by branch through mail</option>" . "<option value='DC'>Denied by Customer</option>" . "<option value='DBP'>Denied by branch through phone</option>" . "<option value='DBM'>Denied by branch through mail</option>" . "</select></td>" . "</tr>";
						$totalAmount += $currentCheque -> AMOUNT;
						$chequeNumbers .= $currentCheque -> CHEQUENO . ",";
					}
					?>
				</tbody>
			</table>
			<?php
			$chequeNumbers = rtrim($chequeNumbers, ",");
			echo form_hidden('chequeNumbers', $chequeNumbers);
			?>
		</div>
		<br/>
		<br/>
		<div id="chequeDetailsPanel">
			<label for="branchCode">Branch Code:</label>
			<input type="text" id="branchCode" name="branchCode" value="<?php echo $branchCode ?>" disabled style="width:5%"/>
			<label for="branchName">Branch Name:</label>
			<input type="text" id="branchName" name="branchName" value="<?php echo $branchName ?>" disabled />
			<label for="accountNumber">Account Number:</label>
			<input type="text" id="accountNumber" name="accountNumber" value=<?php echo $chequeSet[0] -> ACCOUNTNO; ?>
			disabled style="width:10%"/> <label for="aggregateAmount">Aggregate Amount:</label>
			<input type="text" id="aggregateAmount" name="aggregateAmount" value="<?php echo $totalAmount; ?>" disabled style="width:7%"/>
			<br/>
			<br/>
			<label for="phoneNumber">Phone Number:</label>
			<input type="text" id="phoneNumber" name="phoneNumber" style="width:10%"/>
			<label for="callStatus">Call Status:</label>
			<select id="callStatus" name="callStatus">
				<option value="none">None</option>
				<option value="PB">Customer number busy</option>
				<option value="PNR">Customer number not reachable</option>
			</select>
			
		</div>
		<div id="chequeButtonPanel">
			<div class='left'>
			<?php
			if($chequeCategory != 'pendingCheques'){
				echo "<button onclick=\"javascript:window.location='pendingChequesList'\" class='pure-button pure-button-primary' type='button'><< Pending Cheques</button>";
			}
			else{
				echo "<button onclick=\"javascript:window.location='index'\" class='pure-button pure-button-primary' type='button'><< New Cheques</button>";
			}
			?>
			</div>
			<div class = 'mid'> <button onclick="javascript:window.location='../userchoice/show'" class="pure-button pure-button-primary" type='button'>Home</button> </div>
			<div class = 'right'><input type="submit" name="nextButton" id="nextButton" class="pure-button pure-button-primary" value="Next >>" /></div>
		</div>
		<?php echo form_close(); ?>
		<br/>
</div>