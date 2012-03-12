#!/opt/local/bin/perl

use strict;
use DBI;
use DBD::mysql;
use Cache::Memcached; 

my $database = "almondagdb";
my $host = "localhost";
my $port = "3306";
my $user = "almondag";
my $pw = "almondag";
my $dsn = "dbi:mysql:$database:localhost:3306" or die "cannot open DB\n";
my $db = DBI->connect($dsn, $user, $pw) or die "Unable to connect: $DBI::errstr\n";

sub get_hostnames() {
   my %h;
   my $query = $db->prepare("SELECT * FROM tbl_Node");
   $query->execute;
   print 'total rows = ' . $query->rows . "\n";
   print 'total fields = ' . $query->{'NUM_OF_FIELDS'}  . "\n";
   while (my $ref = $query->fetchrow_hashref()) {
     $h{"$ref->{'nod_type'}"} .= $ref->{'nod_name'} . ",";
   }
   $query->finish;
   return %h;
}

sub get_checks_for_types() {
   my %checks;
   my @t;
   my $query = $db->prepare("SELECT * FROM tbl_node_type");
   $query->execute;
   print 'total rows = ' . $query->rows . "\n";
   print 'total fields = ' . $query->{'NUM_OF_FIELDS'}  . "\n";
   while (my $ref = $query->fetchrow_hashref()) {
#     print "nod_type = $ref->{'ndt_name'}, \n";
     push (@t, $ref->{'ndt_name'});
   }
   $query->finish;
   foreach my $type (@t) {
      $query = $db->prepare("SELECT * FROM `tbl_check_type` WHERE `cht_node_type` = '" . $type . "'");
      $query->execute;
      while (my $ref = $query->fetchrow_hashref()) {
         $checks{"$type"} .= $ref->{'cht_name'} . ","; 
#         print "$checks{$type} \n";
      }
   }
   $query->finish;
   return %checks;
}

sub get_adg_data($;$;$) {
   my $host = shift;
   my $port = shift;
   my $cmd = shift;
   my $output;

   eval{
     local $SIG{ALRM} = sub { die "timedout\n" };
     alarm(10);
     $output = `/bin/echo $cmd | /usr/bin/nc $host $port`;
     alarm(0);
   };

   unless ($output) {
      print ("no data from $host\n");
   }

   if ($@ && $@ eq "timedout\n") {
      print "connection to $host timed out!\n";
      $output = "timedout";
   } else {
      $output =~ s/\+OK\ //;
   }

   return $output;
}

#---------------------------------------------------
#                      MAIN
#---------------------------------------------------

my %host_data=();
my %hosts = get_hostnames();
my %cht_checks = get_checks_for_types();

foreach my $host_type (keys %hosts) {
   print " -- $host_type = " . $hosts{"$host_type"} . "\n";
}

print "---------------------\n";

foreach my $cht_nod_type (keys %cht_checks) {
   print " -- $cht_nod_type = " . $cht_checks{"$cht_nod_type"} . "\n";
}

print "---------------------\n";

my $cache = Cache::Memcached->new(servers => [ "localhost:11211" ]); 

foreach my $host_type (keys %hosts) {
   foreach my $cht_nod_type (keys %cht_checks) {
      if (($host_type eq $cht_nod_type) || ($cht_nod_type eq "any")) {
         print "[$host_type AND $cht_nod_type]  $hosts{$host_type} --> $cht_checks{$cht_nod_type}\n";
         my @hosts_db =  split(/,/, $hosts{$host_type});
         foreach my $host (@hosts_db){
            my @checks_db = split(/,/, $cht_checks{"$cht_nod_type"});
            $host_data{$host} = get_adg_data($host,'1980','adg_data');
	    chomp($host_data{$host});
	    my @checks_1980 = split(/\|/, $host_data{$host});
            foreach my $check (@checks_db) {
               my $found_val = 0;
               my $mckey = $host . "." . $check; 
               foreach my $c (@checks_1980) {
	          my ($key, $val) = split(/=/, $c);
                  chomp($check); chomp($key); chomp($val);
                  if ($check eq $key) {
                     $found_val = 1;
		     $cache->set($mckey, $val, 600);
		     print "cache -> set($mckey, $val, 600)\n";
		  }
    	       }
               if ($found_val == 0) {
		  $cache->set($mckey, 'NA', 600);
		  print "cache -> set($mckey, 'NA', 600)\n";
	          print "$mckey not found, set to NA!\n";
	       }
            } 
         }
      }
   }
}

