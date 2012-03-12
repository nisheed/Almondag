<? ob_start(); ?>


<!DOCTYPE html PUBLIC "-//W3C//DTD HTML 4.01//EN">
<html>
<head>
<title>Almondag - Alert Monitoring Dashboard and Aggregator</title>
<style type="text/css">
<?php 
	include ("main.css"); 
	include ("nodes_checks.css"); 

?>

</style>

<script language="JavaScript">
	function showItem(id) {
		if (document.frmAdd.chkCode.checked) {
//			document.getElementById(id).style.display = 'block';
			document.frmAdd.txtCode.disabled = false;
		} else {
//			document.getElementById(id).style.display = 'none';
			document.frmAdd.txtCode.disabled = true;
		}
	}
	function delCheck(id) {
		var str = id.id;
		var url="<?php echo $_SERVER[PHP_SELF];?>?f=d&check="+str;
		window.open(url, "_self");
	}
		
	function validate_Add() {
		if (document.frmAdd.txtCheck.value == '') {
			alert('invalid check!');
			return false;
		}
		if (document.frmAdd.slctType.value == '') {
			alert('type cannot be empty!');
			return false;
		}
		
		var str = document.frmAdd.txtCheck.value;
		var first = str.substring(0,1);
		if (IsNumeric(first) == true) {
			alert('check name should start with an alphabet!');
			return false;			
		}
		return true;	
	}
	
	function IsNumeric(strString) {
   		var strValidChars = "0123456789.-";
   		var strChar;
   		var blnResult = true;

   		if (strString.length == 0) return false;
   		for (i = 0; i < strString.length && blnResult == true; i++) {
      		strChar = strString.charAt(i);
      		if (strValidChars.indexOf(strChar) == -1) {
         		blnResult = false;
         	}
      	}
   		return blnResult;
   }

	
</script>

</head>

<?php
$conn = mysql_connect('localhost', 'almondag', 'almondag') or die ('Error connecting to mysql' . mysql_error());
mysql_select_db('almondagdb');
?>

<body>
	<?php 
	include ("banner.html"); 
?>

<ul id="menubar">
  <li><a href='alerts.php' title=''>Alerts</a></li>
  <li><a href='dashboard.php' title=''>Dashboard</a></li>
  <li><a href='nodes.php' title=''>Nodes</a></li>
  <li><a href='checks.php' title='' class='current'>Checks</a></li>
  <li><a href='relations.php' title=''>Relations</a></li>
</ul>

<div class="frame">

<?php

if ($_GET['f'] == 'a' ) {
	if ($_POST['txtCheck'] != '' && $_POST['slctType'] != '') {
		$mode = 'client';
		if ($_POST['chkCode'] == on) $mode = 'server';
    	$sql = "INSERT INTO `almondagdb`.`tbl_check_type` (`cht_name`, `cht_node_type`, `cht_location`, `cht_code`) 
				VALUES ('" . $_POST['txtCheck'] . "', '" . $_POST['slctType'] . "', '" . $mode . "', '" . $_POST['txtCode'] . "')";
    	$result = mysql_query($sql,$conn);
	} else {
		$error = "ADD:check name and type are mandatory.";
	}
}

if ($_GET['f'] == 'd') {
	if ($_GET['check'] != '') {
    	$sql = "DELETE FROM `almondagdb`.`tbl_check_type` 
				WHERE  `cht_name` = '" . $_GET['check'] . "'";
    	$result = mysql_query($sql,$conn);
	} else {
		$error = "DEL:check name is mandatory";
	}
}

echo $error;

?>

<!-- draw forms -->


<table id='searchadd'>
	<form name='frmAdd' action='checks.php?f=a' method='POST' onSubmit="return validate_Add()">

		<tr>
		<td class='r' width=5%>Alert</td>
		<td class='l' >
			<input class='txtInput' id='txtCheck' type='text' name='txtCheck'>
		</td>
		</tr>
					
		<tr>
		<td class='r'>Type</td>
		<td class='l'>
		<select name="slctType">
<?php

$sql = "SELECT * FROM `tbl_check_type` GROUP BY `cht_node_type`";
	
$result = mysql_query($sql,$conn);
while ($row = mysql_fetch_array($result, MYSQL_NUM)) {
			echo "<option value='", $row[1] ,"'>", $row[1] ,"</option>";
}
?>
		</select>
		</td>
		</tr>
		
		<tr>
		<td class='r'></td>
		<td class='l'>
			<input type="checkbox" name="chkCode" onclick="showItem('txtCode')"> Server Side One-Liner </input>
		</td>
		</tr>

		<tr>
		<td class='r'></td>
		<td class='l'>
			<div id='boxCode'> 
			<input id='txtCode' class='txtCode' type='text' name='txtCode'> </input>	
			</div>
		</td>
		</tr>
		
		<tr>
		<td class='r'> </td>
		<td class='r'>
			<input type='submit' name='btnAdd' value='Add'>
		</td>
		</tr>	
		
	</form>
</table>

<!-- Fetch and display checks -->
<?php
	
$result = mysql_query($sql,$conn);
while ($row = mysql_fetch_array($result, MYSQL_NUM)) {
    $nodes[] = $row[1];  
}


echo "<form name='frmDel' action='checks.php?f=d' method='POST'>";

echo "	<table id='checkadd'>";
	echo "	<tr>
			<th class='b' width='10%'>NODE TYPE</th>
			<th class='b' width='20%'>CHECK NAME</th>
			<th class='b' width='5%'>LOCATION</th>
			<th class='b' width='60%'>CODE</th>
			<th class='b'> </th>
			</tr>";
foreach ($nodes as $i => $value) {

	$sql = "SELECT * FROM `tbl_check_type` WHERE `cht_node_type` = '$value'";
	$result = mysql_query($sql,$conn);
	
	echo "	<tr>
			<th class='c' colspan=5>[", $value, "]</th>
			</tr>";
			
	while ($row = mysql_fetch_array($result, MYSQL_NUM)) {
    	echo "<tr>
			<td width='10%'>", $row[1], "</td> 
			<td width='20%'>", $row[0], "</th>
			<td width='5'>", $row[2], "</th>
			<td width='60%'>", $row[3], "</th>
			<td style='padding: 0 0 0 0'><input type='button' id='" . $row[0] . "' value='Delete' onClick='javascript:delCheck(" . $row[0] . ")'> </input></th>
		</tr>";
	}
}
echo "</table>";
echo "</form>";

?>


<script language="JavaScript">
	showItem('boxCode');
</script>

</div>

</div>
</body>
</html>
<? 
mysql_close($conn);
ob_flush(); 
?> 