<?php
require_once("include/bittorrent.php");
dbconn();

header("Content-Type: text/html; charset=UTF-8");
header("Cache-Control: no-store, no-cache, must-revalidate");
header("Cache-Control: post-check=0, pre-check=0", false);

if (!isset($CURUSER) || !is_array($CURUSER)) {
    die("Not logged in");
}

$userId = (int)$CURUSER["id"];

// инициализируем Memcached
if (!isset($memcache_obj) || !$memcache_obj instanceof Memcached) {
    $memcache_obj = new Memcached();
    $memcache_obj->addServer("127.0.0.1", 11211);
}

$do      = $_POST["action"] ?? "";
$choice  = (int)($_POST["choice"] ?? 0);
$pollId  = (int)($_POST["pollId"] ?? 0);

if ($do === "load") {

    $r_check = sql_query("
        SELECT p.id, p.added, p.question, pa.selection, pa.userid
        FROM polls AS p
        LEFT JOIN pollanswers AS pa ON p.id = pa.pollid AND pa.userid = {$userId}
        ORDER BY p.id DESC LIMIT 1
    ") or sqlerr(__FILE__, __LINE__);

    if (mysqli_num_rows($r_check) === 1) {
        $ar_check = mysqli_fetch_assoc($r_check);

        $a_op = $memcache_obj->get('poll_' . $ar_check["id"]);
        if ($a_op === false) {
            $r_op = sql_query("SELECT * FROM polls WHERE id = " . (int)$ar_check["id"]) or sqlerr(__FILE__, __LINE__);
            $a_op = mysqli_fetch_assoc($r_op);
            $memcache_obj->set('poll_' . $ar_check["id"], $a_op, 600);
        }

        $options = [];
        for ($i = 0; $i < 20; $i++) {
            if (!empty($a_op["option$i"])) {
                $options[$i] = $a_op["option$i"];
            }
        }

        echo "<div id=\"poll_title\">" . htmlspecialchars($ar_check["question"]) . "</div>\n";

        if (is_null($ar_check["userid"])) {
            foreach ($options as $op_id => $op_val) {
                echo "<div align=\"left\">
                    <input type=\"radio\" onclick=\"addvote({$op_id})\" name=\"choices\" value=\"{$op_id}\" id=\"opt_{$op_id}\" />
                    <label for=\"opt_{$op_id}\">&nbsp;" . htmlspecialchars($op_val) . "</label>
                </div>\n";
            }

            echo "<div align=\"left\">
                <input type=\"radio\" onclick=\"addvote(255)\" name=\"choices\" value=\"255\" id=\"opt_255\" />
                <label for=\"opt_255\">&nbsp;Пустой голос! Хочу увидеть результаты</label>
            </div>\n";

            echo "<input type=\"hidden\" value=\"\" name=\"choice\" id=\"choice\"/>";
            echo "<input type=\"hidden\" value=\"" . (int)$ar_check["id"] . "\" name=\"pollId\" id=\"pollId\"/>";
            echo "<div align=\"center\">
                <input type=\"button\" value=\"Голосовать\" style=\"display:none;\" id=\"vote_b\" onclick=\"vote();\"/>
            </div>";

        } else {
            $r = $memcache_obj->get('polls_' . $ar_check["id"]);
            if ($r === false) {
                $a = sql_query("
                    SELECT count(*) as count, selection
                    FROM pollanswers
                    WHERE pollid = " . (int)$ar_check["id"] . " AND selection < 20
                    GROUP BY selection
                ") or sqlerr(__FILE__, __LINE__);

                $cache = [];
                while ($row = mysqli_fetch_assoc($a)) {
                    $cache[] = $row;
                }
                $memcache_obj->set('polls_' . $ar_check["id"], $cache, 600);
                $r = $cache;
            }

            $total = 0;
            $votes = [];
            foreach ($r as $a) {
                $total += $a["count"];
                $votes[$a["selection"]] = (int)$a["count"];
            }

            $results = [];
            foreach ($options as $k => $op) {
                $results[] = [(int)($votes[$k] ?? 0), $op];
            }

            usort($results, function ($a, $b) {
                return $b[0] <=> $a[0];
            });

            // Таблица результатов без видимой рамки
            echo "<table width=\"100%\" border=\"0\" cellspacing=\"0\" cellpadding=\"2\" id=\"results\" class=\"results\" style=\"border:none; border-collapse: collapse;\">";
            $i = 0;
            foreach ($results as $result) {
                $percent = $total > 0 ? ($result[0] / $total * 100) : 0;
                echo "<tr>
                    <td align=\"left\" width=\"40%\" style=\"border:none;\">" . htmlspecialchars($result[1]) . "</td>
                    <td align=\"left\" width=\"60%\" valign=\"middle\" style=\"border:none;\">
                        <div class=\"bar" . ($i === 0 ? "max" : "") . "\" name=\"{$percent}\" id=\"poll_result\">&nbsp;</div>
                    </td>
                    <td style=\"border:none;\">&nbsp;<b><nobr>" . number_format($percent, 2) . "% ({$result[0]})</nobr></b></td>
                </tr>\n";
                $i++;
            }
            echo "</table>";

            $modop = "<b>[</b><a href=\"makepoll.php?action=edit&pollid={$ar_check['id']}&returnto=main\">Редактировать</a><b>]</b> - <b>[</b><a href=\"makepoll.php?action=delete&pollid={$ar_check['id']}&returnto=main\">Удалить</a><b>]</b>";

            echo "<div align=\"center\"><b>Голосов</b> : {$total}</div><br>";
            echo "<div align=\"right\">" . (get_user_class() >= UC_MODERATOR ? $modop : "") . "</div>";
        }

    } else {
        echo "Нет опросов";
    }

} elseif ($do === "vote") {

    if ($pollId === 0) {
        echo json_encode(["status" => 0, "msg" => "Произошла ошибка. Ваш голос не был принят."]);
        exit;
    }

    $check = mysqli_fetch_row(sql_query("
        SELECT count(id)
        FROM pollanswers
        WHERE pollid = {$pollId} AND userid = {$userId}
    "))[0];

    if ($check == 0) {
        sql_query("
            INSERT INTO pollanswers VALUES (0, {$pollId}, {$userId}, {$choice})
        ") or sqlerr(__FILE__, __LINE__);

        if (mysqli_affected_rows($GLOBALS["___mysqli_ston"]) !== 1) {
            echo json_encode(["status" => 0, "msg" => "Ошибка при засчитывании голоса, попробуйте еще раз"]);
        } else {
            echo json_encode(["status" => 1]);
        }

        $memcache_obj->delete('polls_' . $pollId);

    } else {
        echo json_encode(["status" => 0, "msg" => "Двойной голос"]);
    }
}

?>