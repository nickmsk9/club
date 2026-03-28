<?php
require "include/bittorrent.php";
dbconn();
GLOBAl $mcache , $CURUSER;
 $mcache->delete_value('users_'.$user_id);
stdhead("Операция прошла успешно");
begin_main_frame();
begin_frame("Результат платежа",true);

	echo "Операция прошла успешно <br />";
	echo "<br><p>Вернуться - <a href='vip_shop.php'>Назад</a></p>";

end_frame();
end_main_frame();
stdfoot();

?>


