<?php
    error_reporting(15);
    include("config.php");
    include("gapi.class.php");

    $ga = new gapi($u,$p);

####получаем пользователи/просмотры/посещения за все время###
  //  $ga->requestReportData($id, array('day','month','year'), array('visitors','visits','pageviews'), 'month', null, $date3MonthStart, $date3MonthFinish, 1, 1000);
$ga->requestReportData($id, array('day','month','year'), array('visitors','visits','pageviews'), array('year','month'), null, $datestart, $date1MonthFinish, 1, 1000);  	
    #переменная для записи резалта
    $output = "";


    #получаем и обрабатываем результаты
    foreach($ga->getResults() as $result) {
    $d = $result; //день
    $visitors = $result->getVisitors(); //посетители
    $pageviews = $result->getPageviews(); //просмотры
    $visits = $result->getVisits(); //посещения

    #приводим дату к удобочитаемому виду ,мменяем пробелы на точки
    $d = str_replace(" ",".",$d);

    #формируем строку
    $output .= $d.";".$visitors.";".$pageviews.";".$visits."\n";
    }

    #пишем в файл
    $fp = fopen($path.$visitors3CSV,"w");
    fputs($fp,trim($output));
    fclose($fp);	

####получаем географию посещений за последний месяц###
    $ga->requestReportData($id, array('country'), array('visits'), '-visits', null, $date1MonthStart, $date1MonthFinish, 1, $countryRows);

    #переменная для записи резалта
    $output = "";

    #получаем общее число посещений для всех стран
    $total_visits=$ga->getVisits();

    #получаем и обрабатываем результаты
    foreach($ga->getResults() as $result) {
    $country = $result->getCountry(); //страна
    $visits = $result->getVisits(); //кол-во посещений

    #нот сет переводим на русский
    $country = str_replace("(not set)","не определено",$country);

    #формируем строку
    $output .= $country.";".$visits."\n";
    }

    #пишем в файл
    $fp = fopen($path.$countryCSV,"w");
    fputs($fp,trim($output));
    fclose($fp);
	
####получаем ГОРОДА за последний месяц###
    $ga->requestReportData($id, array('city'), array('visits'), '-visits', null, $date1MonthStart, $date1MonthFinish, 1, $cityRows);

    #переменная для записи резалта
    $output="";

    #получаем общее число посещений для всех стран
    $total_visits = $ga->getVisits();

    #получаем и обрабатываем результаты
    foreach($ga->getResults() as $result) {
    $city = $result->getCity(); //страна
    $visits = $result->getVisits(); //кол-во посещений

    #нот сет переводим на русский
    $city = str_replace("(not set)", "не определено", $city);

    #формируем строку
    $output .= $city.";".$visits."\n";
    }

    #пишем в файл
    $fp = fopen($path.$cityCSV,"w");
    fputs($fp,trim($output));
    fclose($fp);

####получаем разрешения экрана за всё время###
    $ga->requestReportData($id, array('screenResolution'), array('visits'), '-visits', null, $datestart, $datefinish, 1, $resolutionRows);

    #переменная для записи резалта
    $output="";

    #получаем общее число посещений для всех стран
    $total_visits = $ga->getVisits();

    #получаем и обрабатываем результаты
    foreach($ga->getResults() as $result) {
    $resolution = $result->getscreenResolution(); //страна
    $visits = $result->getVisits(); //кол-во посещений

    #формируем строку
    $output .= $resolution.";".$visits."\n";
    }

    #пишем в файл
    $fp = fopen($path.$resolutionCSV,"w");
    fputs($fp,trim($output));
    fclose($fp);

####получаем ОС за всё время###
    $ga->requestReportData($id, array('operatingSystem'), array('visits'), '-visits', null, $datestart, $datefinish, 1, $osRows);

    #переменная для записи резалта
    $output="";

    #получаем общее число посещений для всех стран
    $total_visits = $ga->getVisits();

    #получаем и обрабатываем результаты
    foreach($ga->getResults() as $result) {
    $os = $result->getoperatingSystem(); //страна
    $visits = $result->getVisits(); //кол-во посещений
	
    #нот сет переводим на русский
    $os = str_replace("(not set)", "не определено", $os);	

    #формируем строку
    $output .= $os.";".$visits."\n";
    }

    #пишем в файл
    $fp = fopen($path.$osCSV,"w");
    fputs($fp,trim($output));
    fclose($fp);

####получаем браузер за всё время###
    $ga->requestReportData($id, array('browser'), array('visits'), '-visits', null, $datestart, $datefinish, 1, $browserRows);

    #переменная для записи резалта
    $output="";

    #получаем общее число посещений для всех стран
    $total_visits = $ga->getVisits();

    #получаем и обрабатываем результаты
    foreach($ga->getResults() as $result) {
    $browser = $result->getbrowser(); //страна
    $visits = $result->getVisits(); //кол-во посещений
	
    #нот сет переводим на русский
    $browser = str_replace("(not set)", "не определено", $browser);	

    #формируем строку
    $output .= $browser.";".$visits."\n";
    }

    #пишем в файл
    $fp = fopen($path.$browserCSV,"w");
    fputs($fp, trim($output));
    fclose($fp);	
?>