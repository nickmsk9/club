<?php
global $mysqli;

// Функция получения процента рейтинга в формате "XX%"
function getRating($id){
    global $mysqli, $CURUSER;
    $total = 0;
    $rows = 0;
    $id_esc = $mysqli->real_escape_string($id);
    $sel = $mysqli->query("SELECT rating_num FROM ratetorrents WHERE rating_id = '$id_esc'");
    if ($sel->num_rows > 0) {
        while ($data = $sel->fetch_assoc()) {
            $total += $data['rating_num'];
            $rows++;
        }
        $perc = ($total / $rows) * 20;
        $newPerc = round($perc, 2);
        return $newPerc . '%';
    } else {
        return '0%';
    }
}

// Функция получения рейтинга "из пяти"
function outOfFive($id){
    global $mysqli, $CURUSER;
    $total = 0;
    $rows = 0;
    $id_esc = $mysqli->real_escape_string($id);
    $sel = $mysqli->query("SELECT rating_num FROM ratetorrents WHERE rating_id = '$id_esc'");
    if ($sel->num_rows > 0) {
        while ($data = $sel->fetch_assoc()) {
            $total += $data['rating_num'];
            $rows++;
        }
        $perc = ($total / $rows);
        return round($perc, 2);
    } else {
        return '0';
    }
}

// Функция подсчёта числа голосов
function getVotes($id){
    global $mysqli, $CURUSER;
    $id_esc = $mysqli->real_escape_string($id);
    $sel = $mysqli->query("SELECT rating_num FROM ratetorrents WHERE rating_id = '$id_esc'");
    $rows = $sel->num_rows;
    if ($rows == 0) {
        return '0 голосов';
    } elseif ($rows == 1) {
        return '1 голос';
    } else {
        return $rows . ' голос.';
    }
}

// Основная функция вывода рейтинга и звёздочек
function pullRating($id, $show5 = false, $showPerc = false, $showVotes = false, $static = NULL){
    global $mysqli, $CURUSER, $DEFAULTBASEURL;
    echo "<link href=\"$DEFAULTBASEURL/css/rating_style.css\" rel=\"stylesheet\" type=\"text/css\" media=\"all\">
    <script type=\"text/javascript\" src=\"$DEFAULTBASEURL/js/rating_update.js\"></script>";

    // Экранируем входные данные
    $id_esc = $mysqli->real_escape_string($id);
    $userid_esc = $mysqli->real_escape_string($CURUSER['id']);

    // Проверяем, голосовал ли уже пользователь
    $sel = $mysqli->query(
        "SELECT id FROM ratetorrents WHERE userid = '$userid_esc' AND rating_id = '$id_esc'"
    ) or die($mysqli->error);

    $text = '';
    if ($sel->num_rows > 0 || $static === 'novote' || isset($_COOKIE['has_voted_'.$id])) {
        // Пользователь голосовал или голосование запрещено
        if ($show5 || $showPerc || $showVotes) {
            $text .= '<div class="rated_text">';
        }
        if ($show5) {
            $text .= 'Оценка <span id="outOfFive_'.$id.'" class="out5Class">'.outOfFive($id).'</span>';
        }
        if ($showPerc) {
            $text .= ' (<span id="percentage_'.$id.'" class="percentClass">'.getRating($id).'</span>)';
        }
        if ($showVotes) {
            $text .= ' (<span id="showvotes_'.$id.'" class="votesClass">'.getVotes($id).'</span>)';
        }
        if ($show5 || $showPerc || $showVotes) {
            $text .= '</div>';
        }

        return $text . '
<ul class="star-rating2" id="rater_'.$id.'">
    <li class="current-rating" style="width:'.getRating($id).';" id="ul_'.$id.'"></li>
    <li><a onclick="return false;" href="#" title="Плохо" class="one-star">1</a></li>
    <li><a onclick="return false;" href="#" title="Пойдёт" class="two-stars">2</a></li>
    <li><a onclick="return false;" href="#" title="Нормально" class="three-stars">3</a></li>
    <li><a onclick="return false;" href="#" title="Хорошо" class="four-stars">4</a></li>
    <li><a onclick="return false;" href="#" title="Отлично" class="five-stars">5</a></li>
</ul>
<div id="loading_'.$id.'"></div>';
    } else {
        // Пользователь ещё не голосовал
        if ($show5 || $showPerc || $showVotes) {
            $text .= '<div class="rated_text">';
        }
        if ($show5) {
            $show5bool = 'true';
            $text .= 'Оценка <span id="outOfFive_'.$id.'" class="out5Class">'.outOfFive($id).'</span>';
        } else {
            $show5bool = 'false';
        }
        if ($showPerc) {
            $showPercbool = 'true';
            $text .= ' (<span id="percentage_'.$id.'" class="percentClass">'.getRating($id).'</span>)';
        } else {
            $showPercbool = 'false';
        }
        if ($showVotes) {
            $showVotesbool = 'true';
            $text .= ' (<span id="showvotes_'.$id.'" class="votesClass">'.getVotes($id).'</span>)';
        } else {
            $showVotesbool = 'false';
        }
        if ($show5 || $showPerc || $showVotes) {
            $text .= '</div>';
        }

        return $text . '
<ul class="star-rating" id="rater_'.$id.'">
    <li class="current-rating" style="width:'.getRating($id).';" id="ul_'.$id.'"></li>
    <li><a onclick="rate(\'1\',\''.$id.'\','.$show5bool.','.$showPercbool.','.$showVotesbool.'); return false;" href="'.$DEFAULTBASEURL.'/includes/rating_process.php?id='.$id.'&rating=1" title="Плохо" class="one-star">1</a></li>
    <li><a onclick="rate(\'2\',\''.$id.'\','.$show5bool.','.$showPercbool.','.$showVotesbool.'); return false;" href="'.$DEFAULTBASEURL.'/includes/rating_process.php?id='.$id.'&rating=2" title="Пойдёт" class="two-stars">2</a></li>
    <li><a onclick="rate(\'3\',\''.$id.'\','.$show5bool.','.$showPercbool.','.$showVotesbool.'); return false;" href="'.$DEFAULTBASEURL.'/includes/rating_process.php?id='.$id.'&rating=3" title="Нормально" class="three-stars">3</a></li>
    <li><a onclick="rate(\'4\',\''.$id.'\','.$show5bool.','.$showPercbool.','.$showVotesbool.'); return false;" href="'.$DEFAULTBASEURL.'/includes/rating_process.php?id='.$id.'&rating=4" title="Хорошо" class="four-stars">4</a></li>
    <li><a onclick="rate(\'5\',\''.$id.'\','.$show5bool.','.$showPercbool.','.$showVotesbool.'); return false;" href="'.$DEFAULTBASEURL.'/includes/rating_process.php?id='.$id.'&rating=5" title="Отлично" class="five-stars">5</a></li>
</ul>
<div id="loading_'.$id.'"></div>';
    }
}

// Функция получения списка топ-рейтингованных записей
function getTopRated($limit, $table, $idfield, $namefield){
    global $mysqli;
    $limit_esc     = $mysqli->real_escape_string($limit);
    $table_esc     = $mysqli->real_escape_string($table);
    $idfield_esc   = $mysqli->real_escape_string($idfield);
    $namefield_esc = $mysqli->real_escape_string($namefield);

    $sql = "
        SELECT 
            COUNT(ratetorrents.id) AS rates,
            ratetorrents.rating_id,
            {$table_esc}.{$namefield_esc} AS thenamefield,
            ROUND(AVG(ratetorrents.rating_num), 2) AS rating
        FROM ratetorrents
        JOIN {$table_esc} ON {$table_esc}.{$idfield_esc} = ratetorrents.rating_id
        GROUP BY rating_id
        ORDER BY rates DESC, rating DESC
        LIMIT {$limit_esc}
    ";

    $sel = $mysqli->query($sql);
    $result  = '<ul class="topRatedList">' . "\n";
    while ($data = $sel->fetch_assoc()) {
        $result .= '<li>' . htmlspecialchars($data['thenamefield']) . ' (' . $data['rating'] . ')</li>' . "\n";
    }
    $result .= '</ul>' . "\n";
    return $result;
}
?>