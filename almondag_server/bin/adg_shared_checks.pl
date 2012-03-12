#!/opt/local/bin/perl

use strict;
use DBI;
use DBD::mysql;

my $shared_file = '/Library/WebServer/Documents/almondag/shared_checks.dat';

my $database = "almondagdb";
my $host = "localhost";
my $port = "3306";
my $user = "almondag";
my $pw = "almondag";
my $dsn = "dbi:mysql:$database:localhost:3306" or die "cannot open DB\n";
my $db = DBI->connect($dsn, $user, $pw) or die "Unable to connect: $DBI::errstr\n";

my $query = $db->prepare("SELECT * FROM `tbl_check_type` WHERE `cht_location` = 'server'");
$query->execute;

open FILE, ">$shared_file" or die $!;
while (my $ref = $query->fetchrow_hashref()) {
   print FILE  "$ref->{'cht_node_type'}:$ref->{'cht_name'}:$ref->{'cht_code'}\n"; 
}
close FILE;
$query->finish;