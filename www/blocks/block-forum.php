<?php

if (!defined('BLOCK_FILE')) {
    header("Location: ../index.php");
    exit;
}

if (!cache_check('forum', 1200)) {
    /// FIRST WE MAKE THE HEADER (NON-LOOPED) ///
    $content = "<table width=\"100%\" cellspacing=\"0\" cellpadding=\"5\" class=\"forum-block\"><thead><tr>
        <th style=\"width:70%; text-align:left\">Тема</th>
        <th style=\"width:15%; text-align:center\">Ответов</th>
        <th style=\"width:15%; text-align:center\">Просмотров</th>
    </tr></thead><tbody>";

    // Используем mysqli вместо mysql
    $for = sql_query("SELECT topics.*, (SELECT COUNT(*) FROM posts WHERE posts.topicid = topics.id) AS posts_c, forums.id AS fid, forums.name AS fname FROM topics JOIN forums ON topics.forumid = forums.id ORDER BY lastpost DESC LIMIT 10");

    while ($topicarr = mysqli_fetch_assoc($for)) { // Используем mysqli_fetch_assoc вместо mysql_fetch_assoc
        $topicid = (int)$topicarr["id"];
        $topic_title = $topicarr["subject"];
        $topic_userid = $topicarr["userid"];
        $topic_views = $topicarr["views"];
        $views = number_format($topic_views);
        $replies = $topicarr['posts_c'];

        $subject = "<a href=\"/forum/view/topic/id$topicid\"><b>" . htmlspecialchars($topic_title) . "</b></a>";
        $subject_forum = "<a href=\"/forum/view/forum/id" . (int)$topicarr["fid"] . "\">" . htmlspecialchars($topicarr["fname"]) . "</a>";
        $content .= "<tr>
            <td style='padding-right: 5px'>$subject -> $subject_forum</td>" .
            "<td align=\"center\">$replies</td>" .
            "<td align=\"center\">$views</td>";
        $content .= "</tr>";
    }
    $content .= "</tbody></table>";
    cache_write('forum', $content);
} else {
    $content = cache_read('forum');
}

?>