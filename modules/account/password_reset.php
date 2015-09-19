<form action="index.php?m=account&a=update_reset" id="resetForm" method="POST">
<table class="ing" style="margin-top: 20px;">
	<tr>
		<th>Password Reset</th>
	</tr>
	<tr>
		<td>
			E-Mail Address: <input type="text" name="email"/>
		</td>
	</tr>
	<tr>
		<td>
		Enter Secure Code:<input type="text" name="captcha_code" size="10" maxlength="6" /><br/>
		<div style="width: 250px; float: left;">
		<img id="captcha" src="libs/securimage/securimage_show.php" alt="CAPTCHA Image" />
		<a href="#" onclick="document.getElementById('captcha').src = 'libs/securimage/securimage_show.php?' + Math.random(); return false">[ Get Different Image ]</a>
		</div>
		</td>
	</tr>
	<tr>
	<td align="center"><input type="submit" value="Reset" class="button" style="width: 150px;"/></td>
	</tr>
</table>
</form>

