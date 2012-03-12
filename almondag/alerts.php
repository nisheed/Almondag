<html>
<head>
<title>Almondag - Alert Monitoring Dashboard and Aggregator</title>
<style type="text/css">
<?php 
	include ("main.css"); 
	include ("alerts.css");
?>
</style>


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
  <li><a href='alerts.php' title='' class='current'>Alerts</a></li>
  <li><a href='dashboard.php' title=''>Dashboard</a></li>
  <li><a href='nodes.php' title=''>Nodes</a></li>
  <li><a href='checks.php' title=''>Checks</a></li>
  <li><a href='relations.php' title=''>Relations</a></li>
</ul>

<div class="frame">
	<!-- Summary section -->
	<div class='alert_options'>
		<!-- Filter -->
<?php

echo "	<table id='general' width=40%>";
echo "		<form name='frmFilter' action='alerts.php?f=f' method='POST'>";
echo "		<tr>";
echo "			<td class='r'>Type</td>";
echo "			<td class='l'>";
echo "			<select name='slctType'>";
echo "				<option selected value='all'>all</option>";

$sql = "SELECT * FROM `tbl_node_type`";
$result = mysql_query($sql,$conn);
while ($row = mysql_fetch_array($result, MYSQL_NUM)) {
	if ($_POST{'slctType'} == $row[0]) {$selected = 'selected';} else {$selected = '';} 
	if ($row[0] != 'any')  echo "			<option " . $selected . " value='", $row[0] ,"'>", $row[0] ,"</option>";
}
echo "			</select>";
echo "			</td>";
echo "		</tr>";
	
echo "		<tr>";
echo "			<td class='r' width=10%>Application</td>";
echo "			<td class='l' width=20%>";
echo "			<select name='slctApp'>";
echo "				<option selected value='all'>all</option>";

$sql = "SELECT * FROM `tbl_app`";
$result = mysql_query($sql,$conn);
while ($row = mysql_fetch_array($result, MYSQL_NUM)) {
	if ($_POST{'slctApp'} == $row[0]) {$selected = 'selected';} else {$selected = '';} 
	echo "			<option " . $selected . " value='", $row[0] ,"'>", $row[0] ,"</option>";
}
echo "			</select>";
echo "		</td>";
echo "		</tr>";

echo "		<tr>";
echo "			<td class='r'>Colocation</td>";			
echo "			<td class='l'>";
echo "			<select name='slctColo'>";
echo "				<option selected value='all'>all</option>";

$sql = "SELECT * FROM `tbl_colo`";	
$result = mysql_query($sql,$conn);
while ($row = mysql_fetch_array($result, MYSQL_NUM)) {
	if ($_POST{'slctColo'} == $row[0]) {$selected = 'selected';} else {$selected = '';} 
	echo "			<option " . $selected . " value='", $row[0] ,"'>", $row[0] ,"</option>";
}

echo "			</select>";
echo "			</td>";
echo "			<td width=30%>";
echo "				<input type='submit' name='btnFilter' value='Filter'>";
echo "			</td>";
echo "		</tr>";
echo "		</table>";
echo "	</form>";
?>	
	</div>
	<!-- Alerts Details section -->
	<div class='alert_details'>
	<table id='details' width=100%>
		<tr>
			<th class='h1' colspan=7>ALERT</td> 	<!-- Column 1 -->
			<th class='h1' colspan=2>ACTION</td> 			<!-- Column 2 -->
			<th class='h1' colspan=2>PREDICTED CAUSE</td>		<!-- Column 3 -->
		</tr>
		<tr>
			<th class='h2' width=2% align=center>#</td> 				<!-- Column 1 -->
			<th class='h2' width=12% align=left>node/service</td> 		<!-- Column 2 -->
			<th class='h2' width=10% align=left>alert</td>				<!-- Column 3 -->
			<th class='h2' width=8% align=center>first.occ.</td>		<!-- Column 4 -->
			<th class='h2' width=8% align=center>last.occ.</td>			<!-- Column 5 -->
			<th class='h2' width=2% align=center>sev.</td>				<!-- Column 6 -->
			<th class='h2' width=3% align=center>freq</td>				<!-- Column 10 -->
			<th class='h2' width=3% align=center></td>			<!-- Column 8 -->
			<th class='h2' width=20% align=left>comments</td>			<!-- Column 7 -->
			<th class='h2' width=16% align=left>node/service</td>		<!-- Column 9 -->
		</tr>
<?php

//al_node 	al_alert 	al_date_fo 	al_date_lo 	al_severity 	al_status 	al_comment 	al_root 	al_frequency

//SELECT A.* FROM `tbl_alert` A, `tbl_node` B WHERE (A.al_node = B.nod_name) and (B.nod_type = 'web') AND (B.nod_app = 'portal') AND (B.nod_colo = 'abc')

$condition = '';
if ($_GET{'f'} == 'f') {
//	print $_POST{'slctType'} . "" . $_POST{'slctApp'} . "" . $_POST{'slctColo'};
	if ($_POST{'slctType'} != 'all' && $_POST{'slctType'} != '') {
		$condition .= " AND (B.nod_type = '" . $_POST{'slctType'} . "')";
	}
	if ($_POST{'slctApp'} != 'all' && $_POST{'slctApp'} != '') {
		$condition .= " AND (B.nod_App = '" . $_POST{'slctApp'} . "')";
	}
	if ($_POST{'slctColo'} != 'all' && $_POST{'slctColo'} != '') {
		$condition .= " AND (B.nod_Colo = '" . $_POST{'slctColo'} . "')";
	}
}

//print $condition;

$sql = "SELECT A.* FROM `tbl_alert` A, `tbl_node` B WHERE (A.al_node = B.nod_name) " . $condition;	
$result = mysql_query($sql,$conn);
$count = 0;
while ($row = mysql_fetch_array($result, MYSQL_NUM)) {
$count += 1;

$color = "#FFFFFF";
if ($row[4] == '5') $color = "#FA5858";
if ($row[4] == '4') $color = "#FAAC58";
if ($row[4] == '3') $color = "#F4FA58";
if ($row[4] == '2') $color = "#58FAAC";
if ($row[4] == '1') $color = "##58FAF4";

echo "		<tr style='background-color:" . $color . "'>";
echo "			<td align=center>" . $count . "</td>";   
echo "			<td align=left>" . $row[0] . "</td>";  	//al_node
echo "			<td align=left>" . $row[1] . "</td>";					//al_alert
echo "			<td align=center>" . $row[2] . "</td>";					//al_date_fo
echo "			<td align=center>" . $row[3] . "</td>";					//al_date_lo
echo "			<td align=center>" . $row[4] . "</td>";					//al_severity
echo "			<td align=center>" . $row[8] . "</td>";					//al_frequency
echo "			<td align=center><a href='edit.php?f=e&n=" . $row[0] . "&a=" . $row[1] . "'>edit</a></td>";		//al_status
echo "			<td align=left>" . $row[6] . "</td>";					//al_comment
echo "			<td align=left>" . $row[7] . "</td>";					//al_root
echo "		</tr>";
}


?>
	</table>

<table id='legend' width=40%>
<tr>
	<td class='color' style='background-color:#58FAF4'></td>
	<td class='caption'>Sev 1</td>
	<td class='color' style='background-color:#58FAAC'></td>
	<td class='caption'>Sev 2</td>	
	<td class='color' style='background-color:#F4FA58'></td>
	<td class='caption'>Sev 3</td>
	<td class='color' style='background-color:#FAAC58'></td>
	<td class='caption'>Sev 4</td>
	<td class='color' style='background-color:#FA5858'></td>
	<td class='caption'>Sev 5</td>
</tr>	
	</div>		
</div>
</body>
</html>

