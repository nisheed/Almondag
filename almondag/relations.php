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

<script language="javascript">

	function getChecks() {
		if ((document.frmAdd.txtDependant.value == '') && (document.frmAdd.txtDependsOn.value == '')) {
			alert('No hostnames given!');
			return false;
		}
		temp = document.frmAdd.txtDependant.value + "&h2=" + document.frmAdd.txtDependsOn.value
		var url="<?php echo $_SERVER[PHP_SELF];?>?f=gc&h1=" + temp;
		window.open(url, "_self");
	}
		
	
	function validate_Add() {
		if (document.frmAdd.txtDependant.value == '') {
			alert('Dependant Host is empty!');
			return false;
		}
		if (document.frmAdd.txtDependant.value == '') {
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

	function delCheck(id) {
		var str = id.id;
		alert(str);
		var url="<?php echo $_SERVER[PHP_SELF];?>?f=d&relid="+str;
		window.open(url, "_self");
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
  <li><a href='checks.php' title='' >Checks</a></li>
  <li><a href='relations.php' title=''class='current'>Relations</a></li>
</ul>

<div class="frame">

<?php

if ($_GET['f'] == 'a' ) {
	if ($_POST['txtDependant'] != '' && $_POST['txtDependsOn'] != '' && $_POST['slctDependant'] != '' && $_POST['slctDependsOn'] != '' ) {
    	$sql = "INSERT INTO `almondagdb`.`tbl_relation` (`rel_dependant`, `rel_dependant_check`, " . 
														"`rel_dependson`, `rel_dependson_check`, `rel_severity`) 
				VALUES ('" . $_POST['txtDependant'] . "', '" . $_POST['slctDependant'] . "', '" . $_POST['txtDependsOn'] . "', '" 
						. $_POST['slctDependsOn'] . "', '" . $_POST['slctLevel'] . "')";
    	$result = mysql_query($sql,$conn);
		print mysql_error();
		
	} else {
		$error = "ERROR : Insuffcient data input!";
	}
}

if ($_GET['f'] == 'd') {
	print $_GET['relid'];
	if ($_GET['relid'] != '') {
    	$sql = "DELETE FROM `almondagdb`.`tbl_relation` 
				WHERE  `rel_id` = '" . $_GET['relid'] . "'";
    	$result = mysql_query($sql,$conn);
	} else {
		$error = "DEL:check name is mandatory";
	}
}

echo $error;

?>

<!-- draw forms -->


<table id='searchadd'>
	<form name='frmAdd' action='relations.php?f=a' method='POST' onSubmit="return validate_Add()">

		<tr>
		<td class='r' width=300px>Dependant Host</td>
		<td class='l' >
			<input class='txtInput' id='txtDependant' type='text' name='txtDependant' <?php echo "value='" . $_GET['h1'] . "'" ?>>
		</td>
		<td class='l' width=500px>
			<input type='button' name='btnGCDependant' value='Get Checks' onClick="getChecks()">
		</td>
		</tr>
					
		<tr>
		<td class='r'>Dependant Check</td>
		<td class='l'>
		<select name="slctDependant">
		<?php
		if ($_GET['f'] == 'gc') {
			$sql = "SELECT * FROM `tbl_check` WHERE `chk_node` = '" . $_GET['h1'] . "'";	
			$result = mysql_query($sql,$conn);				
			while ($row = mysql_fetch_array($result, MYSQL_NUM)) {
				echo "<option value='", $row[1] ,"'>", $row[1] ,"</option>";
			}
		}
		?>
		</select>
		</td>
		</tr>

		<tr>
		<td class='r' width=300px>Depends On Host</td>
		<td class='l' >
			<input class='txtInput' id='txtDependsOn' type='text' name='txtDependsOn' <?php echo "value='" . $_GET['h2'] . "'" ?>>
		</td>
		<td class='l' width=500px>
			<input type='button' name='btnGCDependsOn' value='Get Checks' onClick="getChecks()">
		</td>
		</tr>
					
		<tr>
		<td class='r'>Depends On Check</td>
		<td class='l'>
		<select name="slctDependsOn">
		<?php
		if ($_GET['f'] == 'gc') {
			$sql = "SELECT * FROM `tbl_check` WHERE `chk_node` = '" . $_GET['h2'] . "'";	
			$result = mysql_query($sql,$conn);				
			while ($row = mysql_fetch_array($result, MYSQL_NUM)) {
				echo "<option value='", $row[1] ,"'>", $row[1] ,"</option>";
			}
		}
		?>
		</select>
		</td>
		</tr>

		<tr>
		<td class='r'>Dependency Level</td>
		<td class='l'>
		<select name="slctLevel">
			<option value='0' selected>0 - Service Down</option>
			<option value='1'>1 - Service Degrade</option>
		}
		?>
		</select>
		</td>
		</tr>		
		
		<tr>
		<td class='r'> </td>
		<td class='r'>
			<input type='submit' name='btnAdd' value='Add Relation'>
		</td>
		</tr>	
		
	</form>
</table>

<?php
	
echo "<form name='frmDel' action='alerts.php?f=d' method='POST'>";

echo "	<table id='checkadd'>";
	echo "	<tr>
			<th class='b' width='25%'>dependant node</th>
			<th class='b' width='15%'>dependant check</th>
			<th class='b' width='25%'>depends on node</th>
			<th class='b' width='15%'>depends on check</th>
			<th class='b' width='15%'>dep.level</th>
			</tr>";
	$sql = "SELECT * FROM `tbl_relation`";
	$result = mysql_query($sql,$conn);
	

	while ($row = mysql_fetch_array($result, MYSQL_NUM)) {
		if ($row[5] == '0') $sev = "0 - Service Down";
		if ($row[5] == '1') $sev = "0 - Service Degrade";
    	echo "<tr>
			<td >", $row[1], "</td> 
			<td >", $row[2], "</th>
			<td >", $row[3], "</th>
			<td >", $row[4], "</th>
			<td >", $sev, "</th>
		</tr>";
	}
echo "</table>";
echo "</form>";

?>


</div>

</div>
</body>
</html>
<? 
mysql_close($conn);
ob_flush(); 
?> 