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
	function validateAdd() {
		if (document.frmAddNode.txtNode.value == '') {
			alert('invalid node name!');
			return false;
		}

		var str = document.frmAddNode.txtNode.value;
		var first = str.substring(0,1);
		if (IsNumeric(first) == true) {
			alert('check name should start with an alphabet!');
			return false;			
		}
		return true;	
	}
	
	function validateAddChecks() {
//		if (document.frmAddNode.txtNode.value == '') {
//			alert('invalid node name!');
//			return false;
//		}
		
		x = document.frmAddChecks.txtThreop.length;
		for(i=0;i<x;i++){
			y = document.frmAddChecks.txtThreop[i].value;
			if (y != '>' && y != '<' && y != '=' && y != '!=' && y != 'gt' && y != 'lt' && y != 'eq' && y != 'ne' && document.frmAddChecks.chkEn[i].value == '1') {
				alert("Invalid operator!");
				return false;
			}
		}					

		x = (document.frmAddChecks.chkEn.length);
		for(i=0;i<x;i++){
			document.frmAddChecks.chkEn[i].checked=true;
		}		
		x = (document.frmAddChecks.chkNy.length);
		for(i=0;i<x;i++){
			document.frmAddChecks.chkNy[i].checked=true;
		}
//		alert(x);
		return true;	
	}
	
	function handleVal(chk) {
		if (chk.checked) {
			chk.value = '1';
		} else {
			chk.value = '0';
		}
	}

	function IsNumeric(strString) {
   		var strValidChars = "0123456789-";
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

echo "<body>";
	
include ("banner.html"); 

echo "<ul id='menubar'>";
echo "  <li><a href='alerts.php' title=''>Alerts</a></li>";
echo "  <li><a href='dashboard.php' title=''>Dashboard</a></li>";
echo "  <li><a href='nodes.php' title='' class='current'>Nodes</a></li>";
echo "  <li><a href='checks.php' title=''>Checks</a></li>";
echo "  <li><a href='relations.php' title=''>Relations</a></li>";
echo "</ul>";

echo "<div class='frame'>";

//draw forms
echo "<table id='searchadd'>";
echo "	<form name='frmAddNode' action='nodes.php?f=a' method='POST' onSubmit=\"return validateAdd()\">";
echo "		<tr>";
echo "		<td class='r' width=10%>Hostname</td>";
echo "		<td class='l' width=20%>";
			echo "	<input class='txtInput' type='text' name='txtNode' value=" . $_POST['txtNode'] . ">";
echo "		</td>";
echo "	</tr>";

//fetch the node type,app & colo
if ($_POST['txtNode']) {
	$sql = "SELECT `nod_type`, `nod_app`, `nod_colo` FROM `tbl_node` WHERE `nod_name` = '" . $_POST['txtNode'] . "'";
	//	print $sql;
	$result = mysql_query($sql,$conn);	
	if (mysql_num_rows($result) < 1 ) {
		//add this node first
		$qry = "INSERT INTO `tbl_node` (`nod_name` , `nod_type` , `nod_app` , `nod_colo`) " . 
				"VALUES ('" . $_POST['txtNode'] . "', '" . $_POST['slctType'] . "', '" .  $_POST['slctApp'] . "', '" . $_POST['slctColo'] . "')";
		$res = mysql_query($qry,$conn);	
		
        // now fetch again
		$sql = "SELECT `nod_type`, `nod_app`, `nod_colo` FROM `tbl_node` WHERE `nod_name` = '" . $_POST['txtNode'] . "'";
		$result = mysql_query($sql,$conn);	
	} 
	$row = mysql_fetch_array($result, MYSQL_NUM);
	
	$nodeType = $row[0]; 
	$nodeApp = $row[1];
	$nodeColo = $row[2];
//	print $nodeType . "," . $nodeApp . "," . $nodeColo;
}

echo "		<tr>";
echo "		<td class='r'>Type</td>";
echo "		<td class='l'>";
echo "		<select name='slctType'>";

$sql = "SELECT * FROM `tbl_node_type`";
$result = mysql_query($sql,$conn);
while ($row = mysql_fetch_array($result, MYSQL_NUM)) {
	if ($nodeType == $row[0]) {$selected = 'selected';} else {$selected = '';} 
	echo "		<option " . $selected . " value='", $row[0] ,"'>", $row[0] ,"</option>";
}
echo "			</select>";
echo "		</td>";
echo "		</tr>";
		
echo "	<tr>";
echo "		<td class='r' width=10%>Application</td>";
echo "		<td class='l' width=20%>";
echo "			<select name='slctApp'>";

$sql = "SELECT * FROM `tbl_app`";
$result = mysql_query($sql,$conn);
while ($row = mysql_fetch_array($result, MYSQL_NUM)) {
		if ($nodeApp == $row[0]) {$selected = 'selected';} else {$selected = '';} 
		echo "	<option " . $selected . " value='", $row[0] ,"'>", $row[0] ,"</option>";
}
echo "			</select>";
echo "		</td>";
echo "		</tr>";

echo "		<tr>";
echo "		<td class='r'>Colocation</td>";			
echo "		<td class='l'>";
echo "		<select name='slctColo'>";

$sql = "SELECT * FROM `tbl_colo`";
	
$result = mysql_query($sql,$conn);
while ($row = mysql_fetch_array($result, MYSQL_NUM)) {
		if ($nodeColo == $row[0]) {$selected = 'selected';} else {$selected = '';} 
		echo "<option " . $selected . " value='", $row[0] ,"'>", $row[0] ,"</option>";
}

echo "		</select>";
echo "		</td>";
echo "		</tr>";
echo "		<tr>";
echo "		<td class='r'> </td>";
echo "		<td class='r'>";
echo "			<input type=submit name='btnAdd' value='Add/Edit'>";
echo "		</td>";
echo "		</tr>";	
echo "	</form>";
echo "</table>";

//<!-- Add/edit checks -->

if ($_GET['f'] == 'a' ) { 
echo "	<form name='frmAddChecks' action='nodes.php?f=ac&h=" . $_POST['txtNode'] . "' method='POST' onSubmit=\"return validateAddChecks()\">";

echo "	<table id='checkadd'>";
echo "	<tr>
			<th class='a' colspan=10>Add/Remove checks for ".$_POST['txtNode']."</th>
		</tr>
		<tr>
			<th class='b' width='5%'>Active</th>
			<th class='b' width='20%'>checkname</th>
			<th class='b' width='5%'>Op</th>
			<th class='b' width='5%'>C.thre</th>
			<th class='b' width='5%'>W.thre</th>
			<th class='b' width='5%'>C.sev</th>
			<th class='b' width='5%'>W.sev</th>
			<th class='b' width='5%'>notify</th>
			<th class='b' width='15%'>notify-email</th>
		</tr>";
		
$sql = "SELECT * FROM `tbl_check_type` WHERE (`cht_node_type` = '" . $nodeType . "' or `cht_node_type` = 'any')";
//print $sql;
$result = mysql_query($sql,$conn);

$checks = array();
while ($row = mysql_fetch_array($result, MYSQL_NUM)) {
	$checks["$row[0]"] = 'n';
}
//print_r($checks);

$sql = "SELECT * FROM `tbl_check` WHERE `chk_node` = '" . $_POST['txtNode'] . "'";
$result = mysql_query($sql,$conn);

//chk_node 	chk_name 	chk_thre_op 	chk_c_thre 	chk_w_thre 	chk_c_sev 	chk_w_sev 	chk_enabled 	chk_notify 	chk_email
//0	      	1	   		:2          	:3         	:4         	:5        	:6        	:7       		:8          :9        

if (mysql_num_rows($result) < 1 ) {
//fill with all possible checks available
//	print "host not found!!";
}


while ($row = mysql_fetch_array($result, MYSQL_NUM)) {

	$checkedEn = '';
	$checkedNy = '';
	if ($row[7] == '1') $checkedEn = ' checked=1 ';
	if ($row[8] == '1') $checkedNy = ' checked=1 ';

//handling existing null values hand entered [not required]	
	if ($row[7] == '') $row[7] = 0;
	if ($row[8] == '') $row[8] = 0;

	echo "<tr>
			<td width='5%'><input id='chkEn' type=checkbox name=chkEn[] value='" . $row[7] . "' " . $checkedEn . " onclick=\"handleVal(this);\"></td> 
			<td class='l' width='20%'><input class='txtNoBord' readonly type=text name=txtCheck[] value='" . $row[1] . "'></input></td>
			<td width='5%'><input class='txtShort' id='txtThreop' type=text name=txtThreop[] value='" . $row[2] . "'></input></td>
			<td width='5%'><input class='txtShort' type=text name=txtCthre[] value='" . $row[3] . "'></input></td>
			<td width='5%'><input class='txtShort' type=text name=txtWthre[] value='" . $row[4] . "'></input></td>
			<td width='5%'>
				<input class='txtShort' type=text name=txtCsev[] value='" . $row[5] . "'></input>
				<input type='hidden' name=txtNew[] value='0'></input>		
			</td>
			<td width='5%'><input class='txtShort' type=text name=txtWsev[] value='" . $row[6] . "'></input></td>
			<td width='5%'><input id='chkNy'	   type=checkbox name=chkNotify[] value='" . $row[8] . "'" . $checkedNy . " onClick=\"handleVal(this);\"></td> 
			<td width='15%'><input class='txtLong' type=text name=txtNymail[] value='" . $row[9] . "'></input></td>

		</tr>";
	if ($checks["$row[1]"] == 'n') {
		$checks["$row[1]"] = 'y';
	}
}

//print_r($checks);

$checkedEn = '';
$checkedNy = '';
	
foreach ($checks as $i => $value) {

	if ($value == 'n') {
	echo "<tr>
			<td width='5%'><input id='chkEn' type=checkbox name=chkEn[] value='" . $chkEnval . "' onClick=\"handleVal(this);\"></td> 
			<td class='l' width='20%'><input class='txtNoBord' readonly type=text name=txtCheck[] value=" . $i . "></input></td>
			<td width='5%'><input class='txtShort' id='txtThreop' type=text name=txtThreop[] value=''></input></td>
			<td width='5%'><input class='txtShort' type=text name=txtCthre[] value=''></input></td>
			<td width='5%'><input class='txtShort' type=text name=txtWthre[] value=''></input></td>
			<td width='5%'>
				<input class='txtShort' type=text name=txtCsev[] value=''></input>
				<input type='hidden' name=txtNew[] value='1'></input>
			</td>
			<td width='5%'><input class='txtShort' type=text name=txtWsev[] value=''></input></td>
			<td width='5%'><input id='chkNy'	   type=checkbox name=chkNotify[] value='" . $chkNyval . "' onClick=\"handleVal(this);\"></td> 
			<td width='15%'><input class='txtLong' type=text name=txtNymail[] value=''></input></td>
		</tr>";
	}
}


echo "</table>";

echo "<div id='barBottom'>";
echo "			<input type=submit name='btnAddChecks' value='Add/Modify'>";
echo "</div>";
echo "</form>";

}

if ($_GET['f'] == 'ac' ) {
//	save checks for $_GET['h']
//	print "saving checks for " . $_GET['h'];
	$arrCheck = $_POST['txtCheck'];
	$arrThreop = $_POST['txtThreop'];
	$arrCthre = $_POST['txtCthre'];
	$arrWthre = $_POST['txtWthre'];
	$arrCsev = $_POST['txtCsev'];
	$arrNew = $_POST['txtNew'];
	$arrWsev = $_POST['txtWsev'];
	$arrEn = $_POST['chkEn'];	
	$arrNotify = $_POST['chkNotify'];
	$arrNymail = $_POST['txtNymail'];

	foreach ($arrCheck as $i => $value) {
//		print '[' . $i . ']--' . "NEW=" . $arrNew[$i] . " | ch=" . $arrCheck[$i] . " | op=" . $arrThreop[$i] . " | C=" . 
//				$arrCthre[$i] . " | W=" . $arrWthre[$i] . " | c=" . $arrCsev[$i] . " | w=" . $arrWsev[$i] . " | en=" . $arrEn[$i] . 
//				" | not=" . $arrNotify[$i] . " | email=" . $arrNymail[$i] . "    ";
		
		if ($arrNew[$i] == '0') {
			$sql = "
					UPDATE 	`tbl_check` 
					SET 	`chk_thre_op` = '" . $arrThreop[$i] . "', `chk_c_thre` = '" . $arrCthre[$i] . "', `chk_w_thre` = '" . $arrWthre[$i] . "', 
							`chk_c_sev` = '" . $arrCsev[$i] . "', `chk_w_sev` = '" . $arrWsev[$i] . "', `chk_enabled` = '" . $arrEn[$i] . "',
							`chk_notify` = '" . $arrNotify[$i] . "', `chk_email` = '" . $arrNymail[$i] . "'  
					WHERE 	`chk_node` = '" . $_GET['h'] . "' AND `chk_name` = '" . $arrCheck[$i] . "'
					";
		} else {
			$sql = "
					INSERT INTO `tbl_check` (`chk_node`, `chk_name`, `chk_thre_op`, `chk_c_thre`, `chk_w_thre`, 
								`chk_c_sev`, `chk_w_sev`, `chk_enabled`, `chk_notify`, `chk_email`) 
					VALUES 		('" . $_GET['h'] . "', '" . $arrCheck[$i] . "', '" . $arrThreop[$i] . "', '" . $arrCthre[$i] . "', 
								'" . $arrWthre[$i] . "', '" . $arrCsev[$i] . "', '" . $arrWsev[$i] . "', '" . $arrEn[$i] . "', 
								'" . $arrNotify[$i] . "', '" . $arrNymail[$i] . "');
					";
		}
		$result = mysql_query($sql,$conn);			

	}

}

echo "	</div>"
?>


</body>
</html>