#!/opt/local/bin/perl

my $out;
my $string;
my $outfile = '/usr/local/almondag_client/data/adg_client.dat';
my $check;

# get client checks
#------------------

my $check_path = '/usr/local/almondag_client/checks';
my @checks = `/bin/ls $check_path`;

foreach (@checks) {
   chomp;
   $check = $check_path . '/' . "$_" ; 
#   print "$check\n";
   $out = `$check`; 
   if ( $? == 0 ) {
      chomp($out);
      $string .= $_ . "=" . $out . "|";
   }
}

print "local check result = " . $string . "\n" ;

# get server dynamic checks
#--------------------------

my $cat = `cat /usr/local/almondag_client/conf/adg_category`;
chomp($cat);
print "\ncategory-$cat\n";
my $curlfile = '/usr/local/almondag_client/conf/adg_shared_checks.dat';
my $curled = `/usr/bin/curl --output $curlfile http://almondag.example.com/almondag/shared_checks.dat 2> /dev/null`;
open CONF, "$curlfile" or die "cannot open $curlfile";
   while (<CONF>) {
      chomp;
      if (/^any/ || /^$cat/) {
#         print "$_\n";
         my @arr = split(/:/);
#         my $out = `$arr[2]`;
         my $out = 'OK';
         $string .= $arr[1] . "=" . $out . "|";
      }
   }   
close CONF;

print "local + dynamic check result = " . $string . "\n" ;

# write to the data file
#open DATA, "> $outfile" or die "cannot open $outfile";
#print DATA "$string";
#close DATA;
