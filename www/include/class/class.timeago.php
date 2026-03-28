<?php
// CLASS FOR CONVERTING TIME TO AGO
class convertToAgo {

    function convert_datetime($str) {
	
   		list($date, $time) = explode(' ', $str);
    	list($year, $month, $day) = explode('-', $date);
    	list($hour, $minute, $second) = explode(':', $time);
    	$timestamp = mktime($hour, $minute, $second, $month, $day, $year);
    	return $timestamp;
    }

    function makeAgo($timestamp){
	
   		$difference = time() - $timestamp;
   		$periods = array("сек.", "мин.", "час(ов)", "день(я)", "нед.", "мес.", "год", "decade");
   		$lengths = array("60","60","24","7","4.35","12","10");
   		for($j = 0; $difference >= $lengths[$j]; $j++)
   			$difference /= $lengths[$j];
   			$difference = round($difference);
   		if($difference != 1) $periods[$j].= "s";
   			$text = "$difference $periods[$j] назад";
   			return $text;
    }
	} // END CLASS
	
/**  
* Склонение существительных по числовому признаку 
* 
* @var integer    Число, по которому производится склонение 
* @var array    Массив форм существительного 
* @return string Существительное в нужной форме 
*  
* Например: 
* $count = 10; 
* sprintf('%d %s', $count, declension($count, array('комментарий', 'комментария', 'комментариев'))); 
* 
* Возвращает: 
* 10 комментариев 
*/ 
function declension($number, $words) { 
    $number = abs($number); 
    if ($number > 20) $number %= 10; 
    if ($number == 1) return $words[0]; 
    if ($number >= 2 && $number <= 4) return $words[1]; 
    return $words[2]; 
} 

/**  
* Приводит дату к заданному формату с учетом русских названий месяцев 
* 
* В качестве параметров функция принимает все допустимые значения функции date(), 
* но символ F заменяется на русское название месяца (вне зависимости от локали), 
* а символ M — на русское название месяца в родительном падеже 
* 
* @var integer    Unix-timestamp времени  
* @var string    Формат даты согласно спецификации для функции date() с учетом замены значения символов F и M 
* @var boolean    Флаг отсекания года, если он совпадает с текущим 
* @return string Отформатированная дата 
*/ 
function r_date($time = '', $format = 'j M Y', $cut_year = true) { 
    if(empty($time)) $time = time(); 
    if($cut_year && date('Y') == date('Y', $time)) $format = preg_replace('/o|y|Y/', '', $format); 
    $month = abs(date('n', $time)-1); 
    $rus = array('января', 'февраля', 'марта', 'апреля', 'мая', 'июня', 'июля', 'августа', 'сентября', 'октября', 'ноября', 'декабря'); 
    $rus2 = array('январь', 'февраль', 'март', 'апрель', 'май', 'июнь', 'июль', 'август', 'сентябрь', 'октябрь', 'ноябрь', 'декабрь'); 
    $format = preg_replace(array("'M'", "'F'"), array($rus[$month], $rus2[$month]), $format); 
    return date($format, $time); 
} 


/**  
* Выводит дату в приблизительном удобочитаемом виде (например, "2 часа и 13 минут назад") 
* 
* Необходимо наличие функции declension() для корректной работы 
* 
* @var integer    Unix-timestamp времени  
* @var integer    Степень детализации 
* @var boolean    Флаг использования упрощенных названий (вчера, позавчера, послезавтра) 
* @var string    Формат даты с учетом замены значения символов F и M, если объявлена функция r_date() 
* @return string Отформатированная дата 
*/ 
function human_date($timestamp, $granularity = 1, $use_terms = true, $default_format = 'j M Y') { 
    $curtime = time(); 
    $original = $timestamp; 
    $output = ''; 
    if($curtime >= $original) { 
        $timestamp = abs($curtime - $original); 
        $tense = 'past'; 
    } else { 
        $timestamp = abs($original - $curtime); 
        $tense = 'future'; 
    } 
    $units = array('years' => 31536000, 
                 'weeks' => 604800,  
                 'days' => 86400,  
                 'hours' => 3600,  
                 'min' => 60,  
                 'sec' => 1); 
    $titles = array('years' => array('год', 'года', 'лет'), 
                 'weeks' => array('неделя', 'недели', 'недель'),  
                 'days' => array('день', 'дня', 'дней'),  
                 'hours' => array('час', 'часа', 'часов'),  
                 'min' => array('минута', 'минуты', 'минут'),  
                 'sec' => array('секунда', 'секунды', 'секунд')); 
    foreach ($units as $key => $value) { 
        if ($timestamp >= $value) { 
            $number = floor($timestamp / $value); 
            $output .= ($output ? ($granularity == 1 ? ' и ' : ' ') : '') . $number .' '. declension($number, $titles[$key]); 
            $timestamp %= $value; 
            $granularity--; 
        } 
    } 
    if($tense == 'future') { 
        $output = 'Через '.$output; 
    } else { 
        $output .= ' назад'; 
    } 
  if($use_terms) { 
      $terms = array('Через 1 день' => 'Послезавтра', 
                     '1 день назад' => 'Вчера', 
                     '2 дня назад' => 'Позавчера' 
                     ); 
      if(isset($terms[$output])) $output = $terms[$output]; 
  } 
  return $output ? $output : (function_exists('r_date') ? r_date($original, $default_format) : date($default_format, $original)); 
} 
/*******************************************************************
Принимает: дату в формате Unix timestamp и двоичное значение режима $sharp.
        $sharp принимает значения:
         true - точный режим: будут выведены все интервалы времени, например:
            22 года и 4 месяца и 2 недели и 6 дней и 18 часов и 29 минут и 58 секунд назад
         
         false - неточный режим: будут выведены  один или два ненулевых интервала:
            22 года назад
            2 месяца и 3 недели
*/
 
function om_ago($time,$sharp = false)
{
    if(!$time) return "никогда!";
   
    //Массив склонений для чисел от 0 до 5
    $cases = array (2, 0, 1, 1, 1, 2);
 
    //Обозначения числительных для разных периодов года
    $strings =array(   
    array ("год", "года", "лет"),
    array ("месяц","месяца", "месяцев"),
    array ("неделя","недели", "недель"),
    array ("день","дня", "дней"),
    array ("час","часа", "часов"),
    array ("минуту","минуты", "минут"),
    array ("секунду","секунды", "секунд")
    );
 
    //Количество секунд в  году, месяце,  неделе, дне,   часе, минуте, секунде
    $seconds = array ( 31557600, 2629800, 604800, 86400, 3600, 60,     1      );
   
    //Число секунд между указанным и настоящим временем
    $time = time() - $time;
   
    if($time==0) return "сейчас";
 
    //Определяем, в прошлом или в будущем лежит указанная дата
    $past = $time>0;
 
    $time = abs($time);
 
    //Выходная строка
    $out = "";
 
    //Число ненулевых интервалов времени
    //При "неточном режиме" не превышает двух
    $count = 0;
   
    //Для всех интервало времени
    for($i=0;$i<count($seconds);$i++)
    {
        //Делим на количество секунд в текущем промежутке времени
        //и получаем интервал между датами в соответствующих единицах
        $interval = floor($time / $seconds[$i]);
 
        //Остаток от деления оставляем на следующий цикл
        $time %= $seconds[$i];
       
        if($interval>0)
        {
            //Разделитель
            $pref = ($count==0) ? "" : " и ";
           
            //Выбираем падеж и число для выбранного интервала
            $string = $strings[$i][ ($interval%100>4 && $interval%100<20)? 2 : $cases[min($interval%10, 5)] ];
 
            //Составляем собственно строчку
            $out .= "$pref$interval $string";
           
            $count++;
           
            //Если в "неточном режиме" интервал больше двух, то останавливаем цикл.
            //Например, если прошло 3 года, то количество месяцев значения уже не имеет.
            if($interval>2 && !$sharp) break;
        }
        else    //В "неточном режиме", если мы уже набрали один интервал и сделующий равен нулю, то останавливаем цикл.
                //Однако, если после месяца идёт 0 недель, то мы всё же напишем интервал в днях. т.е. 1 месяц и 4 дня.
            if(!$sharp && $count>0 && $i!=2) break;
 
        //Если в "неточном режиме" мы уже набрали два интервала, то останавливаем цикл
        if($count>1 && !$sharp) break;
    }
 
    if($past)
        $out .= " назад";   //3 часа назад
    else
        $out = "Ещё ".$out; //Ещё 2 года
 
    return $out;
 
}
?>