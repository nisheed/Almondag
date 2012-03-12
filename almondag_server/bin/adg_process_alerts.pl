#!/opt/local/bin/perl

use strict;
use DBI;
use DBD::mysql;
use Cache::Memcached; 
my $cache = Cache::Memcached->new(servers => [ "localhost:11211" ]); 

my $database = "almondagdb";
my $host = "localhost";
my $port = "3306";
my $user = "almondag";
my $pw = "almondag";
my $dsn = "dbi:mysql:$database:localhost:3306" or die "cannot open DB\n";
my $db = DBI->connect($dsn, $user, $pw) or die "Unable to connect: $DBI::errstr\n";

my $query = $db->prepare("SELECT * FROM tbl_check WHERE chk_enabled = '1'");
$query->execute;
print 'total rows = ' . $query->rows . "\n";
print 'total fields = ' . $query->{'NUM_OF_FIELDS'}  . "\n";

while (my $ref = $query->fetchrow_hashref()) {
   if ($ref->{'chk_node'} && $ref->{'chk_name'}) {
   		my $mckey = $ref->{'chk_node'} . "." . $ref->{'chk_name'};
		my $val = $cache->get($mckey);
		next if ($val eq 'NA');
		print "$mckey ($ref->{'chk_c_thre'}) => $val\n";
		
# checking if the value meets the thresholds
		my $problem = 0;
		my $severity;
		if 		($ref->{'chk_thre_op'} eq '>') {
			print "numeric >\n";
			if ($val > $ref->{'chk_w_thre'}) { $problem = 1; $severity = $ref->{'chk_w_sev'}; }
			if ($val > $ref->{'chk_c_thre'}) { $problem = 1; $severity = $ref->{'chk_c_sev'}; }
		} elsif ($ref->{'chk_thre_op'} eq '<') {
			print "numeric <\n";
			if ($val < $ref->{'chk_w_thre'}) { $problem = 1; $severity = $ref->{'chk_w_sev'}; }
			if ($val < $ref->{'chk_c_thre'}) { $problem = 1; $severity = $ref->{'chk_c_sev'}; }
		} elsif ($ref->{'chk_thre_op'} eq '=') {
			print "numeric =\n";
			if ($val == $ref->{'chk_w_thre'}) { $problem = 1; $severity = $ref->{'chk_w_sev'}; }
			if ($val == $ref->{'chk_c_thre'}) { $problem = 1; $severity = $ref->{'chk_c_sev'}; }
		} elsif ($ref->{'chk_thre_op'} eq '!=') {
			print "numeric !=\n";
			if ($val != $ref->{'chk_w_thre'}) { $problem = 1; $severity = $ref->{'chk_w_sev'}; }
			if ($val != $ref->{'chk_c_thre'}) { $problem = 1; $severity = $ref->{'chk_c_sev'}; }
		} elsif ($ref->{'chk_thre_op'} eq 'gt') {
			print "numeric gt\n";
			if ("$val" gt "$ref->{'chk_w_thre'}") { $problem = 1; $severity = $ref->{'chk_w_sev'}; }
			if ("$val" gt "$ref->{'chk_c_thre'}") { $problem = 1; $severity = $ref->{'chk_c_sev'}; }
		} elsif ($ref->{'chk_thre_op'} eq 'lt') {
			print "numeric lt\n";
			if ("$val" lt "$ref->{'chk_w_thre'}") { $problem = 1; $severity = $ref->{'chk_w_sev'}; }
			if ("$val" lt "$ref->{'chk_c_thre'}") { $problem = 1; $severity = $ref->{'chk_c_sev'}; }
		} elsif ($ref->{'chk_thre_op'} eq 'eq') {
			print "numeric eq\n";		
			if ("$val" eq "$ref->{'chk_w_thre'}") { $problem = 1; $severity = $ref->{'chk_w_sev'}; }
			if ("$val" eq "$ref->{'chk_c_thre'}") { $problem = 1; $severity = $ref->{'chk_c_sev'}; }
		} elsif ($ref->{'chk_thre_op'} eq 'ne') {
			print "numeric ne\n";		
			if ("$val" ne "$ref->{'chk_w_thre'}") { $problem = 1; $severity = $ref->{'chk_w_sev'}; }
			if ("$val" ne "$ref->{'chk_c_thre'}") { $problem = 1; $severity = $ref->{'chk_c_sev'}; }
		}
		
		if ($problem == 1) {
			print "$mckey is alerting (sev = $severity)..\n";
   			&add_update_alert($ref,$val,$severity);
		} else {
			&check_remove_alert($ref);
		}
	}
}
$query->finish;

my ($Rhost, $Rcheck, $Rfo);
		
&calculate_root_cause();


sub calculate_root_cause() {
    my $qry = $db->prepare("SELECT * FROM `tbl_alert`");
	$qry->execute or die "SQL Error: $DBI::errstr\n";
	return if ($qry->rows < 1);	
	while (my $ref = $qry->fetchrow_hashref()) {	
		$Rhost = $ref->{'al_node'};
		$Rcheck = $ref->{'al_alert'};
		$Rfo = $ref->{'al_date_fo'};
		
#		&findRCause($ref->{'al_node'}, $ref->{'al_alert'});
		&findRCause($ref);
		
		my $q = $db->prepare("UPDATE `tbl_alert` SET `al_root` = '" . $Rhost . " ($Rcheck)" . "' WHERE `al_node` = '" . $ref->{'al_node'} . "' AND `al_alert` = '" . $ref->{'al_alert'} . "' ;");
		$q->execute or die "SQL Error: $DBI::errstr\n";
		$q->finish;		
	}
	$qry->finish;	
}

sub findRCause($) {
	my $alert = shift;
	
	print "findingRcause for $alert->{'al_node'} & $alert->{'al_alert'}\n";
	
   	my $qry = $db->prepare("SELECT * FROM `tbl_relation` WHERE `rel_dependant` = '" . $alert->{'al_node'} . "' AND `rel_dependant_check` = '" . $alert->{'al_alert'} . "'");
	$qry->execute or die "SQL Error: $DBI::errstr\n";
	if ($qry->rows > 0) {
		while (my $dep = $qry->fetchrow_hashref()) {	
			print "dependency for $alert->{'al_node'} => $dep->{'rel_dependson'}\n";
			if (my $res = isAlert($dep->{'rel_dependson'}, $dep->{'rel_dependson_check'})) {
				print "dependson of $alert->{'al_node'} ($alert->{'al_alert'}) =>  $dep->{'rel_dependson'} ($dep->{'rel_dependson_check'}) is an alert, $res->{'al_date_fo'}\n";
				print "dates $alert->{'al_date_fo'}, $res->{'al_date_fo'}\n";
				if ($alert->{'al_date_fo'} gt $res->{'al_date_fo'}) {
					print "[root cause found]\n";
					$Rhost = $dep->{'rel_dependson'};
					$Rcheck = $dep->{'rel_dependson_check'};
					$Rfo = $res->{'al_date_fo'};
					&findRCause($res);
				}
			} 
		}		
	}

}


sub isAlert($,$) {
	my $h = shift;
	my $c = shift;
	
	my $ar;

	my $q = $db->prepare("SELECT * FROM `tbl_alert` WHERE `al_node` = '" . $h . "' AND `al_alert` = '" . $c . "' ;");
	$q->execute or die "SQL Error: $DBI::errstr\n";	
	if ($q->rows > 0) {
		return $q->fetchrow_hashref(); 
	} 
	return '';
}


sub add_update_alert($,$,$) {
    my $alert = shift;
    my $data = shift;
    my $sev = shift;

#    print $alert->{'chk_node'} . "," . $alert->{'chk_name'} . "\n";
    my $q = $db->prepare("SELECT * FROM `tbl_alert` WHERE `al_node` = '" . $alert->{'chk_node'} . "' AND `al_alert` = '" . $alert->{'chk_name'} . "'");
	$q->execute or die "SQL Error: $DBI::errstr\n";
		
	if ($q->rows > 0) {
		print "updating...\n";
		my $temp_row = $q->fetchrow_hashref();
		my $freq = int($temp_row->{'al_frequency'}) + 1;
		my $qry = $db->prepare("UPDATE `tbl_alert` SET `al_date_lo` = now(), `al_frequency` = '" . $freq . 
							   "' WHERE `al_node` = '" . $alert->{'chk_node'} . "' AND `al_alert` = '" . $alert->{'chk_name'} . "' ;");
		$qry->execute or die "SQL Error: $DBI::errstr\n";
		$qry->finish;
	} else {
		print "inserting...\n";
		my $qry = $db->prepare("INSERT INTO `tbl_alert` (`al_node`, `al_alert`, `al_date_fo`, `al_date_lo`, `al_severity`, `al_frequency`) " . 
							 "VALUES ('" . $alert->{'chk_node'} . "', '" . $alert->{'chk_name'} . "', now(), now(), '" . $sev . "', '1')");
		$qry->execute or die "SQL Error: $DBI::errstr\n";
		$qry->finish;
	}
	print "------------------------------\n";
	$q->finish;
}

sub check_remove_alert($) {
    my $alert = shift;

    print  $alert->{'chk_node'} . "," . $alert->{'chk_name'} . "\n";
    my $q = $db->prepare("SELECT * FROM `tbl_alert` WHERE `al_node` = '" . $alert->{'chk_node'} . "' AND `al_alert` = '" . $alert->{'chk_name'} . "'");
	$q->execute or die "SQL Error: $DBI::errstr\n";
		
	if ($q->rows > 0) {
		print "deleting...\n";
		my $qry = $db->prepare("DELETE FROM `tbl_alert` WHERE `al_node` = '" . $alert->{'chk_node'} . "' AND `al_alert` = '" . $alert->{'chk_name'} . "' ;");
		$qry->execute or die "SQL Error: $DBI::errstr\n";
		$qry->finish;
	}	
	$q->finish;
	print "------------------------------\n";
}
