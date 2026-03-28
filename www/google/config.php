<?php
    #учетная запись GA
    $u = "brazer@inbox.lv";
    $p = "qwqwqwqw";
    $id = "16147421";

    #текущая дата
    $currentdate = date("Ymd");
    #дата, начиная с которой необходимо получить данные из GA для отчета. Формат YYYY-MM-DD
    $datestart = "2010-01-30";
    #дата, заканчивая которой
    #$datefinish="";
	
    #или вычисляем дату - конец предыдущего месяца
    $currentday = date("d"); 
	$currentmonth = date("m"); 
	$currentyear = date("Y");
    $datefinish = date("Y-m-d", mktime(0,0,0, $currentmonth, 0, $currentyear));

    #дата 3 месяца назад
    $date3MonthStart =  date("Y-m-d", mktime(0,0,0, $currentmonth-3, $currentday-2, $currentyear));
    $date3MonthFinish = date("Y-m-d", mktime(0,0,0, $currentmonth, $currentday-2, $currentyear));

    #дата месяц назад
    $date1MonthStart =  date("Y-m-d", mktime(0,0,0, $currentmonth-1, $currentday-1, $currentyear));
    $date1MonthFinish = date("Y-m-d", mktime(0,0,0, $currentmonth, $currentday-1, $currentyear));
	
    #количество стран
    $countryRows = 9;
	#количество городов
    $cityRows = 6;
	#количество броузеров
	$browserRows = 6;
    #количество ОС
    $osRows = 6;
	#разрешения экрана
    $resolutionRows = 7;

    #csv-файл для отчета Посетители
    $visitorsCSV = "csv/visitors.csv";
    #csv-файл для отчета Посетители за посл. 3 месяца
    $visitors3CSV = "csv/visitors_3.csv";
    #csv-файл для отчета Посетители по часам
    //$hourCSV = "csv/hour.csv";
    #csv-файл для отчета География по странам
    $countryCSV = "csv/country.csv";
	#csv-файл для отчета География по городам
    $cityCSV = "csv/city.csv";
	#csv-файл для отчета Источники
    $referrersCSV = "csv/referrers.csv";
    #csv-файл для отчета Броузеры
    $browserCSV = "csv/browser.csv";
    #csv-файл для отчета ОС
    $osCSV = "csv/os.csv";
	#csv-файл для отчета Разрешения экрана
	$resolutionCSV = "csv/resolution.csv";	
	
    #файл со статистикой до начала использования GA. Формат: дата;посетители;просмотры
    #$addFile = "csv/default.csv";
    $addFile = false;

    #полный пусть к директории со скриптом (слэш в конце обязателен!)
    $path = "/google/";
?>