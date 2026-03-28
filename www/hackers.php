<?php

### security protection by n-sw-bit ::: hackers info ###

require_once("./include/bittorrent.php");
dbconn();
if($CURUSER['class']<UC_SYSOP)die();

if(isset($_GET['del'])){
	$del = (int) $_GET['del'];
	mysql_query("DELETE FROM hackers WHERE id=".$del);
	header("Location: /hackers.php");exit;
}

if(isset($_GET['truncate2'])){
	mysql_query("TRUNCATE TABLE hackers");
	header("Location: /hackers.php");exit;
}

stdhead("Хакеры");

begin_main_frame("Хакеры");

$q = sql_query("SELECT COUNT(*) FROM hackers");
$q = mysql_fetch_row($q);
$count = $q[0];

if($count==0){
	echo "Записей нет!";
	end_main_frame();
	stdfoot();
	exit;
}

list($pagertop, $pagerbottom, $limit) = pager(500, $count, "/ hackers.php?");

echo $pagertop;

begin_table();
begin_table("5%");
print("<tr><td class=row2>ID</td><td class=row2>IP</td><td class=\"row2\" align=center width=40>system</td><td class=\"row2\" align=center width=40>referrer</td>
<td class=\"row2\" align=center width=40>GET</td><td class=\"row2\" align=center width=40>POST</td><td class=\"row2\" align=center>Событие</td></tr>");




$q = sql_query("SELECT * FROM hackers ORDER BY id ".$limit);

$allips = array();


while($row = mysql_fetch_assoc($q)){
	$ip=$row['ip'];
	$addusers = array();	

	$a = sql_query("SELECT users.id FROM users WHERE users.ip ='".$ip."' UNION SELECT userid AS id FROM peers WHERE peers.ip = '".$ip."'  ") or die(mysql_error());
	if(mysql_num_rows($a)>0){
		$ip = "<a href=/usersearch.php?ip=".$ip." style=\"color:red;\">".$ip."</a>";
		while($u=mysql_fetch_assoc($a)){
			$k = sql_query("SELECT username,class FROM users WHERE id=".$u['id']);$k = mysql_fetch_assoc($k);
			$addusers[] = '<a href="user/id'.$u['id'].'">'.get_user_class_color($k['class'],$k['username']).'</a>';
		}
	}else{
		$ip = "<a target='_blank' href=http://www.dnsstuff.com/tools/whois.ch?ip=".$ip." style=\"color:green;\">".$ip."</a>";
	}

	$events = explode("||",$row['event']);
	$get = nl2br(htmlspecialchars(print_r(unserialize($events[0]),true)));
	$post = nl2br(htmlspecialchars(print_r(unserialize($events[1]),true)));
	$event = nl2br(htmlspecialchars(print_r($events[2],true)));
	$event .= "<hr>".$events[3];
	$ref = $events[3];
	
	if(!in_array($row['ip'],$allips)){
		$allips[] = $row['ip'];
		$ipq = explode(".",$row['ip']);
		$ipq = $ipq[0].".".$ipq[1].".".$ipq[2].".0";
		$iptables .= "iptables -A INPUT -s ".$ipq."/255.255.255.0 -p tcp -m multiport --dports 80 -j DROP\n";
	}
	echo "<tr>
	<td class=row1>".$row['id']." <a href=/hackers.php?del=".$row['id']."><img src='/pic/12-em-cross.png' border='0'></a></td>
	<td class=row1>".$ip."<br>".join(",",$addusers)."</td>
	<td class=row1>".$row['system']."</td>
	<td class=row1>".$ref."</td>
	<td class=row1>".$get."</td>
	<td class=row1>".$post."</td>
	<td class=row1>".$event."<hr>".elapsedtime($row['added'])." назад</td>
	</tr>\n";			
}
end_table();

echo $pagertop;

end_main_frame();

begin_main_frame("iptables");
echo "<textarea cols=160 rows=10>".$iptables."</textarea>";
end_main_frame();

stdfoot();


function elapsedtime($date,$showseconds=true,$unix=false){
	if($date == "0000-00-00 00:00:00") return "---";
	if(!$unix){$U = date('U',strtotime($date));}else{$U=$date;};
	$N = time();
	$diff = $N-$U;
	//year (365 days) = 31536000
	//month (30 days) = 2592000
	//week = 604800
	//day = 86400
	//hour = 3600

	if($diff>=31536000){
		$Iyear = floor($diff/31536000);
		$diff = $diff-($Iyear*31536000);
	}
	if($diff>=2629800){	//2592000 seconds in month with 30 days
		$Imonth = floor($diff/2629800);
		$diff = $diff-($Imonth*2629800);
	}
	if($diff>=604800){
		$Iweek = floor($diff/604800);
		$diff = $diff-($Iweek*604800);
	}
	if($diff>=86400){
		$Iday = floor($diff/86400);
		$diff = $diff-($Iday*86400);
	}
	if($diff>=3600){
		$Ihour = floor($diff/3600);
		$diff = $diff-($Ihour*3600);
	}
	if($diff>=60){
		$Iminute = floor($diff/60);
		$diff = $diff-($Iminute*60);
	}
	if($diff>0){
		$Isecond = floor($diff);
	}
	
	$j = " ";

	$ret = "";

	if(isset($Iyear)) $ret .= $Iyear." ".rusdate($Iyear,'year').$j;
	if(isset($Imonth)) $ret .= $Imonth ." ".rusdate($Imonth ,'month').$j;
	if(isset($Iweek)) $ret .= $Iweek ." ".rusdate($Iweek ,'week').$j;
	if(isset($Iday)) $ret .= $Iday ." ".rusdate($Iday ,'day').$j;
	if(isset($Ihour)) $ret .= $Ihour ." ".rusdate($Ihour ,'hour').$j;
	if(isset($Iminute)) $ret .= $Iminute ." ".rusdate($Iminute ,'minute').$j;

//	if($showseconds==false && $Iminute<1)$Iminute=0;
	if($showseconds==false && $Iminute<1 && $Ihour<1 && $Iday<1 && $Iweek<1 && $Imonth<1 && $Iyear<1)return rusdate(0 ,'minute');
	
	if(($Isecond>0 OR $ret=="") AND $showseconds==true){
		if($ret=="" AND !isset($Isecond))$Isecond=0;
		$ret .= $Isecond ." ".rusdate($Isecond ,'second').$j;
	}
	return $ret;
}

function rusdate($num,$type){
	$rus = array (
		"year"    => array( "лет", "год", "года", "года", "года", "лет", "лет", "лет", "лет", "лет"),
		"month"  => array( "месяцев", "месяц", "месяца", "месяца", "месяца", "месяцев", "месяцев", "месяцев", "месяцев", "месяцев"),
		"week"  => array( "недель", "неделю", "недели", "недели", "недели", "недель", "недель", "недель", "недель", "недель"),
		"day"   => array( "дней", "день", "дня", "дня", "дня", "дней", "дней", "дней", "дней", "дней"),
		"hour"    => array( "часов", "час", "часа", "часа", "часа", "часов", "часов", "часов", "часов", "часов"),
		"minute" => array( "минут", "минуту", "минуты", "минуты", "минуты", "минут", "минут", "минут", "минут", "минут"),
		"second" => array( "секунд", "секунду", "секунды", "секунды", "секунды", "секунд", "секунд", "секунд", "секунд", "секунд"),
	);
	
	$num = intval($num);
	if ( 10 < $num && $num < 20) return $rus[$type][0];
	return $rus[$type][$num % 10];
}
?>