<?
if ($_SERVER["REQUEST_METHOD"] == "POST")
{
	if (isset($_POST["id"]) && isset($_POST["type"]))
	{
		$id = (int)$_POST["id"];
		$type=(string)$_POST["type"];
		require("include/bittorrent.php");


		switch($type)
		{
		
			case "halloween":
				$domain = $_SERVER['HTTP_HOST'];
				if ( strtolower( substr($domain, 0, 4) ) == 'www.' )
					$domain = substr($domain, 4);	// Fix the domain to accept domains with and without 'www.'. 
				if ( substr($domain, 0, 1) != '.' )
					$domain = '.'.$domain;	// Add the dot prefix to ensure compatibility with subdomains
				@setcookie("halloween", "off", time() + 86000, '/', $domain);
			break;
		}
	}
}
?>