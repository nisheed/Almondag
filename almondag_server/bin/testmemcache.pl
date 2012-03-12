#!/opt/local/bin/perl

use Cache::Memcached; 

#Connect 
my $cache = Cache::Memcached->new(servers => [ "localhost:11211" ]); 

my $key = "UName";
my $data = "NisheeeeedKM";

# Set some data - $data can be a ref, as long as Storable can nfreeze it 
$cache->set($key, $data, 3600); # 1 hour expiry 

my $t = localtime($cache->get('LAST_UPDATE'));
print $t;

# Get the data back 
my $x = $cache->get($key); 

print $x . "\n";

# or get multiple pieces simultaneously 
#my $hashref = $cache->get_multi($key1, $key2, $key3);
