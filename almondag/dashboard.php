<!DOCTYPE html PUBLIC "-//W3C//DTD HTML 4.01//EN">
<html>
<head>
<title>Almondag - Alert Monitoring Dashboard and Aggregator</title>
<style type="text/css">
<?php 
	include ("main.css"); 
	include ("alert.css");
	include ("dashboard.css"); 
?>

</style>


</head>


<body>
<?php 
	include ("banner.html"); 
?>
<?php
$conn = mysql_connect('localhost', 'almondag', 'almondag') or die ('Error connecting to mysql' . mysql_error());
mysql_select_db('almondagdb');
?>
	
<ul id="menubar">
  <li><a href='alerts.php' title=''>Alerts</a></li>
  <li><a href='dashboard.php' title='' class='current'>Dashboard</a></li>
  <li><a href='nodes.php' title=''>Nodes</a></li>
  <li><a href='checks.php' title=''>Checks</a></li>
  <li><a href='relations.php' title=''>Relations</a></li>
</ul>
<div class="frame">

	<!-- Summary section -->
	<div class='alert_options'>
		<!-- Filter -->
		<table width=100% height=100%>
		<tr>
		<form name="filters" action='' method="POST">
			<td align=right> Application </td>
			<td>
			<select name="slctApp">
				echo "<option value='all'>all</option>";
				<?php
					$sql = "SELECT * FROM `tbl_app`";
					$result = mysql_query($sql,$conn);
					while ($row = mysql_fetch_array($result, MYSQL_NUM)) {
						echo "<option value='", $row[0] ,"'>", $row[0] ,"</option>";
					}
				?>
			</select>
			</td>
			<td align=right>Node Type </td>
			<td>
			<select name="slctType" width=20px>
				echo "<option value='all'>all</option>";
				<?php
					$sql = "SELECT * FROM `tbl_node_type`";
					$result = mysql_query($sql,$conn);
					while ($row = mysql_fetch_array($result, MYSQL_NUM)) {
						echo "<option value='", $row[0] ,"'>", $row[0] ,"</option>";
					}
				?>
			</select>
			<td align=right>Colocation </td>
			<td>				
			<select name="slctColo">
				echo "<option value='all'>all</option>";
				<?php
					$sql = "SELECT * FROM `tbl_colo`";
					$result = mysql_query($sql,$conn);
					while ($row = mysql_fetch_array($result, MYSQL_NUM)) {
						echo "<option value='", $row[0] ,"'>", $row[0] ,"</option>";
					}
				?>
			</select>
			</td>
			<td width=5%>
				<input type='submit' name='btnFilter' value='Filter'>
			</td>
		</form>		
		</tr>
		</table>
	</div>
	
	
	<!-- Alerts Details section -->
<?php

	require_once 'memcached-client.php';
    global $memcache;
	$memcache = new memcached(array( 'servers' => array('127.0.0.1:11211'), 'debug' => false, 'compress_threshold' => 10240, 'persistant' => true));
	$cache = $memcache->get('db0.example.com.sys_openports');
//	print "db0.example.com.sys_openports -> " . $cache;
	
	$sql = "SELECT * FROM `tbl_node_type`";
	$result = mysql_query($sql,$conn);
	
	$cats = array();
	while ($row = mysql_fetch_array($result, MYSQL_NUM)) {
		if ($row[0] != 'any') $cats[] = $row[0];
	}
		
	foreach ($cats as $i => $node_type) {
		$sql = "SELECT * FROM `tbl_check_type` WHERE `cht_node_type` = '" . $node_type . "' or `cht_node_type` = 'any'";
		$result = mysql_query($sql,$conn);	
		if (mysql_num_rows($result) > 0) {
			echo "<div class='type_header'>[$node_type]</div>";
			$checks = array();
			while ($row = mysql_fetch_array($result, MYSQL_NUM)) {
				$checks[] = $row[0];
			}
			echo "	<div class='alert_details'>";
			echo "	<table id='details' >";
			echo "		<tr>";
			echo "			<th class='h2' width=15px align=center>#</th> 		<!-- Column 1 -->";
			echo "			<th class='h2' width=200px align=left>Node</th> 		<!-- Column 2 -->";
			foreach ($checks as $i => $check) {
				echo "		<th class='h2' width=80px align=center>" . $check . "</th>";
			}
			echo "		</tr>";
			
			$sql = "SELECT * FROM `tbl_node` WHERE `nod_type` = '" . $node_type . "'";
			$result = mysql_query($sql,$conn);
//			print $sql;
			$nodes = array();	
			while ($row = mysql_fetch_array($result, MYSQL_NUM)) {
				$nodes[] = $row[0];
			}
			
			foreach ($nodes as $j => $node) {
				$c = $j + 1;
				echo "	<tr>";
				echo "		<td align=center>" . $c . "</td> 		<!-- Column 1 -->";
				echo "		<td align=left>" . $node . "</td> 		<!-- Column 2 -->";
				foreach ($checks as $i => $check) {
					$temp_key = $node . '.' . $check;
//					print $temp_key;
					$cache = $memcache->get("$temp_key");
					echo "	<td align=center>" . $cache . "</td>";	
				}
				echo "		</tr>";
			}
			echo "	</table>";
			echo "	</div>	";
		}
	}	
?>

	
</div>
</body>
</html>