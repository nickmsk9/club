<?php

require_once 'include/bittorrent.php';
dbconn();
getlang();
header("Content-Type: text/html; charset=".$lang['language_charset']);


if($_SERVER['HTTP_X_REQUESTED_WITH'] == 'XMLHttpRequest')
{
	// Get variables //
	$do = ( isset($_REQUEST['do']) ? strip_tags(htmlspecialchars_uni($_REQUEST['do'])) : '' );
	$commentid = (isset($_REQUEST['cid']) && is_numeric($_REQUEST['cid'])) ? intval($_REQUEST['cid']) : 0;
	$blogid = ( is_numeric($_REQUEST['bid']) ? intval($_REQUEST['bid']) : 0 );
	switch($do)
	{
/*
/////////////////////////////////////////////////////////////////////////////////////
//
// Действие - Редактирование комментария.
//
////////////////////////////////////////////////////////////////////////////////////
*/
		case 'edit_comment' :
		  // Если id комментария и id торрента не равны 0, тогда продолжаем
		  if($commentid != 0 && $blogid != 0) {
			  // Выбираем из таблиц comments и users необходимые данные
			  $sql = "SELECT blog_comments.id AS post_id, blog_comments.bid, blog_comments.user AS author_id, blog_comments.text, users.class FROM blog_comments LEFT JOIN users ON users.id = blog_comments.user WHERE blog_comments.id = $commentid AND blog_comments.bid = $blogid LIMIT 1";
			  $res = sql_query($sql) or sqlerr(__FILE__,__LINE__);
			  // Если данные успешно выбраны продолжаем дальше
			  if($res && mysql_num_rows($res) > 0) {
			  // Извлекаем данные в ассоциативный массив	  
			  $comment = mysql_fetch_assoc($res);
			  // Проверяем права доступа 
			  if((get_user_class() >= UC_MODERATOR && $CURUSER['class'] > $comment['class']) || $CURUSER['id'] == $comment['author_id']) {
			  // Генерируем текстовое поле с текстом комментария	  				  
			  echo "<textarea name=\"edit_post\" id=\"edit_post\" style=\"width:100%;height:100px;\">".convertEncoding($comment['text'],'UTF-8','UTF-8')."</textarea>";
			  echo "<br /><div style=\"float:left;margin-top:5px;\"><a href=\"javascript:;\" onClick=\"BL_SaveComment('".intval($comment['post_id'])."','".intval($comment['bid'])."')\" style=\"font-size:11px;color:#000000;\">Сохранить</a>&nbsp;|&nbsp;<a href=\"javascript:;\" onClick=\"BL_CommentCancel('".intval($comment['post_id'])."','".intval($comment['bid'])."')\" style=\"font-size:11px;color:#000000;\">Отменить</a></div>";
				  }
			  }
		  }
		break;
/*
/////////////////////////////////////////////////////////////////////////////////////
//
// Действие - Сохранение отредактированного комментария
//
////////////////////////////////////////////////////////////////////////////////////
*/
		case 'save_comment' :
		  // Получаем из глобального массива измененный текст комментария и преобразуем его
		  // в нужную кодировку - UTF-8
		  $text = convertEncoding($_POST['text'],'UTF-8','UTF-8');
		  // Удаляем возможные теги из текста и преобразуем HTML теги в их сущности
		  //$text = strip_tags(htmlspecialchars($text));
		  // Проверяем чтобы текст не был пустым, так же проверяем чтобы id комментария и
		  // id торрента не были равны нулю
		  if(!empty($text) && $text != '' && $commentid != 0 && $blogid != 0) {
			  // Подгатавливаем запрос, который извлечет из базы нужный нам текст комментария
			  // он будет записан в ячейку таблицы с оригинальным текстом комментария т.е. еще
			  // не отредактированным
			  $sql = "SELECT text FROM blog_comments WHERE id = $commentid AND bid = $blogid LIMIT 1";
			  // Выполняем запрос
			  $ori_text = mysql_fetch_assoc(sql_query($sql)) or sqlerr(__FILE__,__LINE__);
			  // Подготавливаем запрос, кторый обновит текущий комментарий
			  $sql = "UPDATE blog_comments SET text = ".sqlesc($text).", ori_text = ".sqlesc($ori_text['text']).", editedat = ".sqlesc(time()).", editedby = ".intval($CURUSER['id'])." WHERE id = $commentid AND bid = $blogid";
			  // Выполняем запрос
			  $row = sql_query($sql) or sqlerr(__FILE__,__LINE__);
			  // Если запрос был удачно выполнен, подгатавливаем еще один запрос который извлечет              // уже измененный текст комментария
			  if($row) {
				  $sql = "SELECT text, bid FROM blog_comments WHERE id = $commentid AND bid = $blogid LIMIT 1";
				  // Выполняем запрос
				  $res = sql_query($sql) or sqlerr(__FILE__,__LINE__);
				  // Если запрос был удачно выполнен выводим измененный текст, иначе старый текст
				  if($res && mysql_num_rows($res) > 0) {
					  $comment = mysql_fetch_assoc($res);
					  // Вывод нового текста
					  echo format_comment($comment['text']);
				  }
			  } else {
				  // Если был неудачный запрос выведется старый текст
				  echo format_comment($orig_text['orig_text']);
			  }
		  }
		break;

/*
/////////////////////////////////////////////////////////////////////////////////////
//
// Действие - Отмена редактирования комментария
//
////////////////////////////////////////////////////////////////////////////////////
*/
		case 'save_cancel' :
		   // Проверяем чтобы не были равны нулю id комментария и id торрента
		   if($commentid != 0 && $blogid != 0) {
			 // Подгатавливаем запрос на выборку текста комментария  
			 $sql = "SELECT text, bid FROM blog_comments WHERE id = $commentid AND bid = $blogid LIMIT 1";
			 // Выполняем запрос
			 $res = sql_query($sql) or sqlerr(__FILE__,__LINE__);
			 // Если запрос был выполнен удачно
			 if($res && mysql_num_rows($res) > 0) {
				// Извлекаем данные в массив 
				$row = mysql_fetch_assoc($res);
				// Выводим неизмененный текст комментария
				echo format_comment($row['text']);
			 }
		   }
		break;
		
/*
/////////////////////////////////////////////////////////////////////////////////////
//
// Действие - Цитата комментария
//
////////////////////////////////////////////////////////////////////////////////////
*/		
		case 'comment_quote' :
		  // Получаем текущее содержание формы комментария и преобразуем в нужную кодировку
		  $text = convertEncoding($_POST['text'],'UTF-8','UTF-8');
		  // Извлекаем опысные символы
		//  $text = strip_tags(htmlspecialchars($text));
		  // Прверям id комментария и торрента а так же на то что цитирует зарегистрированный          // пользователь
		  if($commentid != 0 && $blogid != 0 && $CURUSER) {
			  // Подготавливаем запрос
			  $sql = "SELECT blog_comments.user,blog_comments.text,users.username, blog_comments.bid FROM blog_comments LEFT JOIN users ON users.id = blog_comments.user WHERE blog_comments.id = $commentid AND blog_comments.bid = $blogid LIMIT 1";
			  // Выполняем запрос
			  $res = sql_query($sql) or sqlerr(__FILE__,__LINE__);
			  // Если удачный запрос
			  if($res && mysql_num_rows($res) > 0) {
				 // Извлекем данные в массив 
				 $comment = mysql_fetch_assoc($res);
				 $username = htmlspecialchars($comment['username']);
				 $old_text = convertEncoding($comment['text'],'UTF-8','UTF-8');
				 // Гененрируем новое содержание формы комментария
				 $new_text = "{$text}[quote={$username}]{$old_text}[/quote]";
				 // Выводим
				 echo $new_text;
			  }
		  }
		break;

/*
/////////////////////////////////////////////////////////////////////////////////////
//
// Действие - Добавление нового комментария
//
////////////////////////////////////////////////////////////////////////////////////
*/
		case 'add_comment' :
		
global $memcache_obj;

		
		  // Получаем текст комментария и преобразеум в нужную кодировку
		  $text = convertEncoding($_POST['text'],'UTF-8','UTF-8');
		  // Удаляем опасные символы
		 // $text = strip_tags(htmlspecialchars($text));
		  // Кол-во комментариев на страницу
		  $limited = 10;
		  // Прверяем
		  if($blogid != 0 && !empty($text) && $text != '' && $CURUSER) {
		  // Добавляем в базу новый комментарий	  
		  $res = sql_query("INSERT INTO blog_comments (`user`,`bid`,`added`,`text`,`ori_text`,`ip`) VALUES ('".intval($CURUSER['id'])."','{$blogid}',".sqlesc(time()).",".sqlesc($text).",".sqlesc($text).",".sqlesc(getip()).")") or sqlerr(__FILE__,__LINE__);
			 // id последнего комментария
			 $ID = mysql_insert_id();
			 // Обновление счетчика комментариев
			 sql_query("UPDATE blogs SET comments = comments + 1 WHERE bid = $blogid");

		$res1 = sql_query("SELECT subject, uid FROM blogs WHERE bid = $blogid") or sqlerr(__FILE__,__LINE__);
		$arr = mysql_fetch_array($res1);
		$name = $arr['subject'];
		$uid = $arr['uid'];
			 	$subject = sqlesc("Новый комментарий");
	$msg = sqlesc("Для записи в Вашем блоге - [url=blog.php?bid=$blogid]".$name."[/url] добавился новый комментарий от пользователя ".$CURUSER["username"]."");
	sql_query("INSERT INTO messages (sender, receiver, added, msg, poster, subject) SELECT 0, uid, ".sqlesc(date('Y-m-d H:i:s'))." , $msg, 0, $subject FROM blogs WHERE bid = $blogid AND uid != $CURUSER[id]") or sqlerr(__FILE__,__LINE__);
		$memcache_obj->delete('messages_'.$uid,1);

			 	
			 /*
			 //////////////////////////////////////////////////////////////////////////////////
			 // Слежение за комментариями
			 // Для работы раскомментируйте строки ниже (+2 лишних запроса => лишняя нагрузка)
			 //////////////////////////////////////////////////////////////////////////////////
			 */
			 /*
			 $commentName = @mysql_fetch_assoc(sql_query("SELECT name FROM torrents WHERE id = $torrentid"));
			 $subject = sqlesc('Новый комментарий');
			 $msg = sqlesc("Для торрента [url=details/id{$torrentid}&viewcomm={$ID}#comm{$ID}]".htmlspecialchars($commentName['name'])."[/url] добавился новый комментарий.");
			 sql_query("INSERT INTO messages (sender, receiver, added, msg, poster, subject) SELECT 0, userid, NOW(), $msg, 0, $subject FROM checkcomm WHERE checkid = $torrentid AND torrent = 1 AND userid != $CURUSER[id]") or sqlerr(__FILE__,__LINE__);
			 */
			 /*
			 /////////////////////////////////////////////////////////////////////////////////
			 // Конец слежению за комментариями
			 /////////////////////////////////////////////////////////////////////////////////
			 */
			 
			 			 
			 // Если успешно добавлен
			 if($res) {

				 // Получаем кол-во комментариев к текущему торренту
				 list($count) = mysql_fetch_row(sql_query("SELECT COUNT(*) FROM blog_comments WHERE bid = $blogid LIMIT 1")) or sqlerr(__FILE__,__LINE__);
				 // Генерируем постраничную навигацию
				 list($pagertop, $pagerbottom, $limit) = pager($limited, $count, "blog.php?bid={$blogid}&amp;", array(lastpagedefault => 1));
				 // Выполняем запрос на извлечение всех комментариев к текущему торренту
				 // + еще некоторая необходимая информация
				 $subres = sql_query("SELECT c.id, c.bid AS blogid, c.ip, c.text, c.user, c.added, c.editedby , c.editedat, u.avatar, u.warned, ".
                  "u.username, u.title, u.class, u.donor, u.gender, u.last_access, e.username AS editedbyname 
				  FROM blog_comments AS c LEFT JOIN users AS u ON c.user = u.id LEFT JOIN users AS e ON c.editedby = e.id WHERE bid = " .
                  "$blogid ORDER BY c.id $limit") or sqlerr(__FILE__, __LINE__);
				 // Подгатавляваем массив
				 $allrows = array();
                 while ($subrow = mysql_fetch_array($subres))
                        $allrows[] = $subrow;
                 
		 // Выводим список комментариев к текущему торренту
		 print("<table class=main cellspacing=\"0\" cellPadding=\"5\" width=\"100%\" >");
         print("<tr><td class=\"brd\">");
         print($pagertop);
         print("</td></tr>");
         print("<tr><td class=\"brd\">");
         blogtable_ajax($allrows);
         print("</td></tr>");
         print("<tr><td class=\"brd\">");
         print($pagerbottom);
         print("</td></tr>");
         print("</table>");
                  print("<table style=\"margin-top: 2px;\" cellpadding=\"5\" width=\"100%\">");
                  print("<tr><td align=\"center\" class=\"brd\">");
                  print("<form name=\"comment\" id=\"comment\">");
                  print("<div>");
				  print("<div align=\"center\">". commbbcode("comment","text","", 1) ."</div>");
                  print("</div></form>");
                  print("</td></tr><tr><td  align=\"center\" colspan=\"2\" class=\"brd\">");
				  print("<input type=\"button\" class=btn value=\"Разместить комментарий\" onClick=\"BL_SendComment('{$blogid}')\" id=\"send_comment\" />
		 <input type=\"button\" class=btn value=\"Смайлы\" onClick=\"javascript:winop()\" />
		 <input type=\"button\" class=btn value=\"Смайлы2\" onClick=\"javascript:winop2()\" />");
                  print("</td></tr></table>");		
			 }
		  }
		break;
		
/*
/////////////////////////////////////////////////////////////////////////////////////
//
// Действие - Удаление комментария
//
////////////////////////////////////////////////////////////////////////////////////
*/		
		case 'delete_comment' :
		  // Кол-во комментариев на страницу
		  $limited = 10;
		  if($commentid != 0 && $blogid != 0) {
		  // Подгатавливаем запрос	  
		  $sql = "SELECT blog_comments.user AS user_id, users.class FROM blog_comments LEFT JOIN users ON users.id = blog_comments.user WHERE blog_comments.id = $commentid AND blog_comments.bid = $blogid";
		  $res = sql_query($sql) or sqlerr(__FILE__,__LINE__);
		  $row = mysql_fetch_assoc($res);
		  
		  if((get_user_class() >= UC_MODERATOR) || ($CURUSER['id'] == $row['user_id'])) { 
		  
			  sql_query("DELETE FROM blog_comments WHERE id = $commentid AND bid = $blogid");
			  // Получаем кол-во комментариев к торренту
			  list($count) = mysql_fetch_row(sql_query("SELECT COUNT(*) FROM blog_comments WHERE bid = $blogid LIMIT 1")) or sqlerr(__FILE__,__LINE__);
			  // Генерируем постраничную навигацию
			  sql_query("UPDATE blogs SET comments = comments - 1 WHERE bid = $blogid");

			  list($pagertop, $pagerbottom, $limit) = pager($limited, $count, "blog.php?bid={$blogid}&amp;", array(lastpagedefault => 1));
			  
			  // Если нет комментаривем к текущему торренту, просто выводим форму добавления
			  // комментария
			  if(!$count) {
                  print("<table style=\"margin-top: 2px;\" cellpadding=\"5\" width=\"100%\">");
                  print("<tr><td align=\"center\" class=\"brd\">");
                  print("<form name=\"comment\" id=\"comment\">");
                  print("<div>");
				  print("<div align=\"center\">". commbbcode("comment","text","", 1) ."</div>");
                  print("</div></form>");
                  print("</td></tr><tr><td  align=\"center\" colspan=\"2\" class=\"brd\">");
				  print("<input type=\"button\" class=btn value=\"Разместить комментарий\" onClick=\"BL_SendComment('{$blogid}')\" id=\"send_comment\" />
		 <input type=\"button\" class=btn value=\"Смайлы\" onClick=\"javascript:winop()\" />
		 <input type=\"button\" class=btn value=\"Смайлы2\" onClick=\"javascript:winop2()\" />");
                  print("</td></tr></table>");					  
			  } else {
			  // Получаем список торрентов
			  $subres = sql_query("SELECT c.id, c.bid AS blogid, c.ip, c.text, c.user, c.added, c.editedby, c.editedat, u.avatar, u.warned, ".
                  "u.username, u.title, u.class, u.donor, u.downloaded, u.uploaded, u.gender, u.last_access, e.username AS editedbyname FROM blog_comments AS c LEFT JOIN users AS u ON c.user = u.id LEFT JOIN users AS e ON c.editedby = e.id WHERE bid = " .
                  "$blogid ORDER BY c.id $limit") or sqlerr(__FILE__, __LINE__);
			  $allrows = array();
                 while ($subrow = mysql_fetch_array($subres))
                        $allrows[] = $subrow;
                 
		 // Выводим список комментариев к текущему торренту
		 print("<table class=main cellspacing=\"0\" cellPadding=\"5\" width=\"100%\" >");
         print("<tr><td class=\"brd\">");
         print($pagertop);
         print("</td></tr>");
         print("<tr><td class=\"brd\"");
         blogtable_ajax($allrows);
         print("</td></tr>");
         print("<tr><td class=\"brd\">");
         print($pagerbottom);
         print("</td></tr>");
         print("</table>");
                  print("<table style=\"margin-top: 2px;\" cellpadding=\"5\" width=\"100%\">");
                  print("<tr><td align=\"center\" class=\"brd\">");
                  print("<form name=\"comment\" id=\"comment\">");
                  print("<div>");
				  print("<div align=\"center\">". commbbcode("comment","text","", 1) ."</div>");
                  print("</div></form>");
                  print("</td></tr><tr><td  align=\"center\" colspan=\"2\" class=\"brd\">");
				  print("<input type=\"button\" class=btn value=\"Разместить комментарий\" onClick=\"BL_SendComment('{$blogid}')\" id=\"send_comment\" />
		 <input type=\"button\" class=btn value=\"Смайлы\" onClick=\"javascript:winop()\" />
		 <input type=\"button\" class=btn value=\"Смайлы2\" onClick=\"javascript:winop2()\" />");
                  print("</td></tr></table>");		
			  }
		    } 
		  } 
		break;
		
/*
/////////////////////////////////////////////////////////////////////////////////////
//
// Действие - Просмотр оригинального текста комментария
//
////////////////////////////////////////////////////////////////////////////////////
*/			
		
		case 'view_original' :
		   if($blogid != 0 && $commentid != 0) {
			   $sql = "SELECT text,ori_text FROM blog_comments WHERE id = $commentid AND bid = $blogid";
			   $res = sql_query( $sql ) or sqlerr(__FILE__,__LINE__);
		       if($res && mysql_num_rows($res) > 0) {
				  $comment = mysql_fetch_assoc($res); 
				  
				  $content .= "<div style=\"border:1px dashed #ccc;padding:5px;\"><b>Оригинальный текст:</b></span><br />".format_comment($comment['ori_text'])."</div><br />\n";
				  $content .= "<div style=\"border:1px dashed #ccc;padding:5px;\"><b>Текущий текст:</b><br />".format_comment($comment['text'])."</div><br />\n";
				  $content .= "<div style=\"float:right;\"><a href=\"javascript:;\" style=\"font-size:9px;color:#999999;\" onClick=\"BL_RecoverOriginal('{$commentid}','{$blogid}')\">Восстановить оригинал</a>&nbsp;|&nbsp;<a href=\"javascript:;\" style=\"font-size:9px;color:#999999;\" onClick=\"BL_CommentCancel('{$commentid}','{$blogid}')\">Отменить</a>";
				  
				  echo $content;
				  
			   }
		   }
		break;
		
/*
/////////////////////////////////////////////////////////////////////////////////////
//
// Действие - Восстановление текста комментария
//
////////////////////////////////////////////////////////////////////////////////////
*/			
		
		case 'recover_original' :
           if($commentid != 0 && $blogid != 0) {
			   $sql = "SELECT ori_text FROM blog_comments WHERE id = $commentid AND bid = $blogid";
			   $res = sql_query( $sql ) or sqlerr(__FILE__,__LINE__);
			   if($res && mysql_num_rows($res) > 0) {
				   $row = mysql_fetch_assoc($res);
				   
				   if(sql_query("UPDATE blog_comments SET text = ".sqlesc($row['ori_text'])." WHERE id = $commentid AND bid = $blogid")) {
					  echo format_comment($row['ori_text']);					   
				   }
			   }
		   }
		break;
	}
}

?>