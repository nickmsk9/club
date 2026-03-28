<?php
declare(strict_types=1);

// Fetch and decode the raw query string
$queryString  = $_SERVER['QUERY_STRING'] ?? '';
$decodedQuery = rawurldecode($queryString);
$cracktrack   = strtolower($decodedQuery);

$wormprotector = array_unique(array(
    'wget ', ' wget', 'wget(', 
    'cmd=', ' cmd', 'cmd ', 'rush=', 'union ','passhash','union+',
    'select+','union ', ' union', 'union(', 'union=', 'echr(', ' echr', 'echr ', 'echr=', 
    'esystem(', 'esystem ', 'cp ', ' cp', 'cp(', 'mdir ', ' mdir', 'mdir(', 
    'mcd ', 'mrd ', ' mcd', ' mrd', ' rm', 
    'mcd(', 'mrd(', 'rm(', 'mcd=', 'mrd=', 'mv ', 'rmdir ', 'mv(', 'rmdir(', 
    'chmod(', 'chmod ', ' chmod', 'chmod(', 'chmod=', 'chown ', 'chgrp ', 'chown(', 'chgrp(', 
    'locate ', 'grep ', 'locate(', 'grep(', 'diff ', 'kill(', 'killall', 
    'passwd ', ' passwd', 'passwd(', 'telnet ', 'vi(', 'vi ', 
    'insert into', 'select ', 'nigga(', ' nigga', 'nigga ', 'fopen', 'fwrite',  
    '$_request', '$_get', '$request', '$get', '.system', 'http_php', '&aim', ' getenv', 'getenv ', 
    'new_password', '&iicq','/etc/password','/etc/shadow', '/etc/groups', '/etc/gshadow', 
    'HTTP_USER_AGENT', 'HTTP_HOST', '/bin/ps', 'wget ', 'uname\x20-a', '/usr/bin/id', 
    '/bin/echo', '/bin/kill', '/bin/', '/chgrp', '/chown', '/usr/bin', 'g\+\+', 'bin/python', 
    'bin/tclsh', 'bin/nasm', 'traceroute ', ' ping ', '/usr/X11R6/bin/xterm', 'lsof ', 
    '/bin/mail', '.conf', 'motd ', 'http/1.', '.inc.php', 'config.php', 'cgi-', '.eml', 
    'file\://', 'window.open', '<script>', 'javascript\://','img src', '.jsp','ftp.exe', 
    'xp_enumdsn', 'xp_availablemedia', 'xp_filelist', 'xp_cmdshell', 'nc.exe', '.htpasswd', 
    'servlet', '/etc/passwd', 'wwwacl', '~root', '~ftp', '.jsp', 'admin_', '.history', 
    'bash_history', '.bash_history', '~nobody', 'server-info', 'server-status', 'reboot ', 'halt ', 
    'powerdown ', '/home/ftp', '/home/www', 'secure_site, ok', 'chunked', 'org.apache', '/servlet/con', 
    '<script', '/robot.txt' ,'mod_gzip_status', 'db_mysql.inc', '.inc', 
    'select from', 'drop ', '.system', 'getenv', 'http_', '_php', 'php_', 'phpinfo()', '<?php', '?>', 'sql=', 'select * from', '/etc/rc.local'
));

if ($cracktrack === '') {
    return;
}

// Replace any pattern from the list, case-insensitive
$checkworm = str_ireplace($wormprotector, '*', $decodedQuery);

if (strtolower($decodedQuery) !== strtolower($checkworm))
{
    $cremotead  = $_SERVER['REMOTE_ADDR']    ?? 'unknown';
    $cuseragent = $_SERVER['HTTP_USER_AGENT'] ?? 'unknown';
    hacker(sprintf(
        '%s : %s<hr/>%s',
        $_SERVER['PHP_SELF'] ?? '',
        strtolower($decodedQuery),
        strtolower($checkworm)
    ));
    
}
?>