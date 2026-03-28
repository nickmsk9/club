<?
$memcache = new Memcache();
$memcache->connect('127.0.0.1',11211);
$value = 'Hello, World!';
$expire = 60;
$name = 'test';
$memcache->set($name, $value, 0, $expire);

$memcache->get($name);
print_r($memcache);
var_dump($memcache);
?>