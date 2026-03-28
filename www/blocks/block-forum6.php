<?php

if (!defined('BLOCK_FILE')) {
header("Location: ../index.php");
exit;
}




/// FIRST WE MAKE THE HEADER (NON-LOOPED) ///
$content .= "<table border=\"1\" cellspacing=\"0\" cellpadding=\"5\"><tr>".
"<td width=\"90%\" align=\"center\"><b>Тема</b></td>".
"<td  align=\"center\"><b>Автор</b></td>".
"<td  align=\"center\"><b>Ответов</b></td>".
"<td  align=\"center\"><b>Просмотров</b></td>".
"<td  align=\"center\"><nobr><b>Последнее сообщение</b></nobr></td>".
"</tr>";


/// HERE GOES THE QUERY TO RETRIEVE DATA FROM THE DATABASE AND WE START LOOPING ///
$for = sql_query("SELECT * FROM topics ORDER BY lastpost DESC LIMIT 10");

while ($topicarr = mysql_fetch_assoc($for))
{
$topicid = $topicarr["id"];
$topic_title = $topicarr["subject"];
$topic_userid = $topicarr["userid"];
$topic_views = $topicarr["views"];
$views = number_format($topic_views);

/// GETTING TOTAL NUMBER OF POSTS ///
$res = sql_query("SELECT COUNT(*) FROM posts WHERE topicid=$topicid") or sqlerr(__FILE__, __LINE__);
$arr = mysql_fetch_row($res);
$posts = $arr[0];
$replies = max(0, $posts - 1);

/// GETTING USERID AND DATE OF LAST POST ///
$res = sql_query("SELECT * FROM posts WHERE topicid=$topicid ORDER BY id DESC LIMIT 1") or sqlerr(__FILE__, __LINE__);
$arr = mysql_fetch_assoc($res);
$postid = 0 + $arr["id"];
$userid = 0 + $arr["userid"];

/// GET NAME OF LAST POSTER ///
$res = sql_query("SELECT id, username FROM users WHERE id=$userid") or sqlerr(__FILE__, __LINE__);
if (mysql_num_rows($res) == 1) {
$arr = mysql_fetch_assoc($res);
$username = "<a href=\"user/id$userid\"><b>$arr[username]</b></a>";
}
else
$username = "Unknown[$topic_userid]";

/// GET NAME OF THE AUTHOR ///
$res = sql_query("SELECT username FROM users WHERE id=$topic_userid") or sqlerr(__FILE__, __LINE__);
if (mysql_num_rows($res) == 1) {
$arr = mysql_fetch_assoc($res);
$author = "<a href=\"user/id$topic_userid\"><b>$arr[username]</b></a>";
}
else
$author = "Unknown[$topic_userid]";

/// GETTING THE LAST INFO AND MAKE THE TABLE ROWS ///
$r = sql_query("SELECT lastpostread FROM readposts WHERE userid=$userid AND topicid=$topicid") or sqlerr(__FILE__, __LINE__);
$a = mysql_fetch_row($r);
$new = !$a || $postid > $a[0];
$subject = "<a href=\"forum.php?action=viewtopic&topicid=$topicid\"><b>" . encodehtml($topicarr["subject"]) . "</b></a>";

$content .= "<tr><td style='padding-right: 5px'>$subject</td>".
"<td align=\"center\">$author</td>" .
"<td align=\"center\">$replies</td>" .
"<td align=\"center\">$views</td>".
"<td align=\"center\">$username</td>";

$content .= "</tr>";
} // while

$content .= "</table>";







?>