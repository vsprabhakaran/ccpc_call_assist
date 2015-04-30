<body>
	<script type="text/javascript">
		$(document).ready(function() {
			$('form').bind("keyup keypress", function(e) {
				var code = e.keyCode || e.which;
				if (code == 13) {
					e.preventDefault();
					return false;
				}
			});
			$("#datepicker").datepicker({
				dateFormat : "dd-mm-yy"
			}).datepicker("setDate", new Date());
			$("#circleMenu").val("Chennai");
		});
	</script>
	<br/>
	<br/>
	<div class='formHeading'>Choose your option</div>
	<form id="formid" action="processChoice" method="post"  class="pure-form pure-form-aligned">
		<div id="searchPanel">
			<div class="pure-control-group">
				<label for="datepicker">Cheque Date : </label>
				<input type="text" id="datepicker" name="datepicker" readonly="readonly"/>
			</div>
			<div class="pure-control-group">
				<label for="circleMenu">Choose the circle : </label>
				<select id="circleMenu" name="circleName">
					<?php
					foreach ($CircleNames as $currentCircleName) {
						echo "<option value='$currentCircleName'>$currentCircleName</option>";
					}
					?>
				</select>
			</div>
			<div class="pure-controls">
				<input type="submit" class="pure-button pure-button-primary" name="filterButton" id="filterButton" value="submit" />
			</div>
		</div>
	</form>
</body>