<html>
<head>
<title>Almondag - Alert Monitoring Dashboard and Aggregator</title>
<style type="text/css">
<?php 
	include ("main.css"); 
	include ("alerts.css");
?>
</style>

<script language="JavaScript">
	function showItem(id) {
		document.frmAdd.txtLocked.disabled = true;
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
  <li><a href='alerts.php' title='' class='current'>Alerts</a></li>
  <li><a href='dashboard.php' title=''>Dashboard</a></li>
  <li><a href='nodes.php' title=''>Nodes</a></li>
  <li><a href='checks.php' title=''>Checks</a></li>
  <li><a href='relations.php' title=''>Relations</a></li>
</ul>

<div class="frame">
	<!-- Summary section -->
<?php 

//<td align=center><a href='edit.php?f=e&n='hostname'&a='check'>

if ( $_GET['f'] == 'e') {
	$node = $_GET['n'];
	$check = $_GET['a'];
	$sql = "SELECT * FROM `tbl_alert` WHERE `al_node` = '" .  $node . "' AND `al_alert` = '" . $check  . "'";	
	$result = mysql_query($sql,$conn);
	$row = mysql_fetch_array($result, MYSQL_NUM);
	$comment = $row[6];
//	print $comment;
}

//edit.php?f=save

if ( $_GET['f'] == 'save') {

$sql = "UPDATE `tbl_alert` SET `al_comment` = '" . $_POST['txtComment'] . "' ". 
		" WHERE `al_node` = '" . $_POST{'txtNode'}  . "' AND `al_alert` = '" . $_POST{'txtCheck'}  . "'";	
//print $sql;
$result = mysql_query($sql,$conn);

echo "<script>";
echo "	window.open('http://almondag.example.com/almondag/alerts.php', '_self');";
echo "</script>";

}

echo "<table id='editComment'>";
echo "	<form name='frmEdit' action='edit.php?f=save' method='POST'>";

echo "		<tr>";
echo "		<td class='r' width=5%>Node</td>";
echo "		<td class='l' width=50%>";
echo "			<input readonly=true class='txtLocked' type='text' name='txtNode' value='" . $node . "'></input>";
echo "		</td>";
echo "		</tr>";

echo "		<tr>";
echo "		<td class='r' >Alert</td>";
echo "		<td class='l'>";
echo "			<input readonly=true class='txtLocked' type='text' name='txtCheck' value='" . $check . "'></input>";
echo "		</td>";
echo "		</tr>";

echo "		<tr>";
echo "		<td class='r'>Comment</td>";
echo "		<td class='l'>";
echo "			<textarea rows='2' class='txtComment' type='text' name='txtComment'>" . $comment . "</textarea>";
echo "		</td>";
echo "		</tr>";

echo "		<tr>";
echo "		<td class='r'> </td>";
echo "		<td class='r'>";
echo "			<input type='submit' name='btnAdd' value='Add'>";
echo "		</td>";
echo "		</tr>	";
		
echo "	</form>";
echo "</table>";
?>

</div>
</body>
</html>

