#!/opt/local/bin/perl -w
use strict;
use IO::Socket;

my $client;
my $socket;
my $port=1980;
my $command;
my $buf;
my $host;
my $adg_root = $ENV{'ADG_ROOT'} || '/usr/local'; 

#-----------------------------------------------
#            SUB ROUTINES
#-----------------------------------------------

sub resp_OK($;$){
   my $client = shift;
   my $data = shift;
   print $client "+OK";
   print $client " $data" if defined $data;
   print $client "\n";
}

sub resp_ERR($;$){
   my $client = shift;
   my $data = shift;
   print $client "-ERR";
   print $client " $data" if defined $data;
   print $client "\n";
}

sub cmd_cpuload($) {
   my $client = shift;
   my $result = `/usr/bin/uptime`;
   chomp($result);
   $result =~ /averages:\ ([0-9\.]+)\ .*/;
   resp_OK($client, $1);
   return 1;
}

sub cmd_dfroot($) {
   my $client = shift;
   my $result = `/bin/df -h / | /usr/bin/grep -v Used | /usr/bin/awk '{print \$5}' `;
   chomp($result);
   resp_OK($client, $result);
   return 1;
}

sub cmd_http($) {
   my $client = shift;
   my $result = `/bin/ps aux | /usr/bin/grep http | /usr/bin/grep -v grep | /usr/bin/wc -l | /usr/bin/sed 's/\ \*//g'`;
   chomp($result);
   resp_OK($client, $result);
   return 1;
}

sub cmd_adg_data($) {
   my $client = shift;
   my $datfile = $adg_root . '/almondag_client/data/adg_client.dat';
   print $datfile;
   my $result = `/bin/cat $datfile`;
   chomp($result);
   resp_OK($client, $result);
   return 1;
}



#-----------------------------------------------------------------------------
#------------------------------------- MAIN ----------------------------------
#-----------------------------------------------------------------------------

$socket = IO::Socket::INET->new(
        "Proto" => "tcp",
        "LocalPort" => $port,
        "Listen" => 1) or die "ERROR: $!\n";
print "Listening on port 1980...\n";

#---------- DAEMONIZE --------------------------
#-----------------------------------------------
use POSIX qw(setsid);
chdir '/';
umask 0;
open STDIN, '/dev/null';
open STDERR, '>/dev/null';
my $pid = fork;
exit if $pid;
setsid;

#------ LISTEN & INTERPRET COMMANDS ------------
#-----------------------------------------------
while ($client=$socket->accept()) {
   $host=$client->peerhost();
   print "Connection received from: ", $host . "\n";

   while (defined($buf=<$client>)) {
      next unless ($buf =~ /\S/);
      chomp($buf);
      print "command : $buf";
      if ($buf =~ /cpuload/) {
         cmd_cpuload($client);
      } elsif ($buf =~ /memusage/) {
         cmd_memusage($client);
      } elsif ($buf =~ /dfroot/) {
         cmd_dfroot($client);
      } elsif ($buf =~ /http/) {
         cmd_http($client);
      } elsif ($buf =~ /adg_data/) {
         cmd_adg_data($client);
      } elsif ($buf =~ /quit/) {
         last;
      } else {
         resp_ERR($client, "unknown command $buf");
      }
   }
close $client;
}


