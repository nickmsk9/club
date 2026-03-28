<?php
require "include/bittorrent.php";
dbconn(true);
loggedinorreturn();
stdhead("Аплоадеры");
begin_main_frame();

if ($CURUSER['class'] >= UC_MODERATOR)
{
    // Запрос к базе — получаем аплоадеров
    $query = "SELECT id, username, added, uploaded, class, downloaded FROM users WHERE class >= " . UC_UPLOADER;
    $result = sql_query($query);
    if (!$result) {
        echo "<p>Ошибка запроса к базе данных!</p>";
        end_main_frame();
        stdfoot();
        exit;
    }

    $num = mysqli_num_rows($result); // количество аплоадеров
    echo "<h2>Информация о аплоадерах</h2>";
    echo "<p>У нас " . $num . " аплоадер" . ($num > 1 ? "ов" : "") . "</p>";

    if ($num > 0)
    {
        echo "<table cellpadding=4 align=center border=0>";
        echo "<tr>";
        echo "<td class=colhead>Номер</td>";
        echo "<td class=colhead>Пользователь</td>";
        echo "<td class=colhead>Раздал&nbsp;/&nbsp;Скачал</td>";
        echo "<td class=colhead>Рейтинг</td>";
        echo "<td class=colhead>Залил&nbsp;торрентов</td>";
        echo "<td class=colhead>Последняя&nbsp;заливка</td>";
        echo "<td class=colhead>Отправить ЛС</td>";
        echo "</tr>";

        $counter = 1;
        // Используем mysqli_fetch_assoc для перебора строк
        while ($row = mysqli_fetch_assoc($result)) {
            $id = (int)$row['id'];
            $username = $row['username'];
            $userclass = $row['class'];
            $added = $row['added'];
            $uploaded = mksize($row['uploaded']);
            $downloaded = mksize($row['downloaded']);
            $uploadedratio = $row['uploaded'];
            $downloadedratio = $row['downloaded'];

            // Запрос к базе для торрентов этого пользователя
            $upperquery = "SELECT added FROM torrents WHERE owner = $id ORDER BY added DESC";
            $upperresult = sql_query($upperquery);

            $numtorrents = mysqli_num_rows($upperresult);

            // Считаем рейтинг
            if ($downloadedratio > 0) {
                $ratio = $uploadedratio / $downloadedratio;
                $ratio = number_format($ratio, 3);
                $color = get_ratio_color($ratio);
                if ($color)
                    $ratio = "<font color=$color>$ratio</font>";
            } elseif ($uploadedratio > 0) {
                $ratio = "Inf.";
            } else {
                $ratio = "---";
            }

            echo "<tr>";
            echo "<td align=center>$counter</td>";
            echo "<td><a href=user/id$id>" . get_user_class_color($userclass, $username) . "</a></td>";
            echo "<td>$uploaded / $downloaded</td>";
            echo "<td>$ratio</td>";
            echo "<td>$numtorrents торрентов</td>";

            // Последняя заливка
            if ($numtorrents > 0) {
                $lastaddedRow = mysqli_fetch_assoc($upperresult);
                $lastadded = $lastaddedRow['added'];
                echo "<td>" . get_elapsed_time($lastadded) . " назад (" . date("d. M Y", $lastadded) . ")</td>";
            } else {
                echo "<td>---</td>";
            }

            echo "<td align=center><a href=message.php?action=sendmessage&amp;receiver=$id><img border=0 src=pic/button_pm.gif></a></td>";
            echo "</tr>";

            $counter++;
        }
        echo "</table>";
    }
}
else {
    stdmsg($lang['error'], $lang['access_denied']);
}

end_main_frame();
stdfoot();
?>