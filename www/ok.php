<?

require_once("include/bittorrent.php");

dbconn();

if (!mkglobal("type"))
	die();

if ($type == "signup" && mkglobal("email")) {
	if (!validemail($email))
		stderr($lang['error'], "Это не похоже на реальный email адрес.");
	stdhead($lang['signup_successful']);
        stdmsg($lang['signup_successful'],($use_email_act ? sprintf($lang['confirmation_mail_sent'], htmlspecialchars($email)) : sprintf($lang['thanks_for_registering'], $SITENAME)));
	stdfoot();
	}
elseif ($type == "confirmed") {
	stdhead($lang['account_activated']);
	begin_main_frame();
	stdmsg($lang['account_activated'], $lang['this_account_activated']);
	end_main_frame();
	stdfoot();
}
elseif ($type == "confirm") {
	if (isset($CURUSER)) {
		stdhead("Подтверждение регистрации");
			begin_main_frame();

		print("<h1>Ваш аккаунт успешно подтвержден!</h1>\n");
		print("<p>Ваш аккаунт теперь активирован! Вы автоматически вошли. Теперь вы можете <a href=\"$DEFAULTBASEURL/\"><b>перейти на главную</b></a> и начать использовать ваш уккаунт.</p>\n");
		print("<p>Презжде чем начать использовать $SITENAME мы рекомендуем вам прочитать <a href=\"/rules.php\"><b>правила</b></a> и <a href=\"/faq.php\"><b>ЧаВо</b></a>.</p>\n");
			end_main_frame();
		stdfoot();
	}
	else {
		stdhead("Signup confirmation");
			begin_main_frame();

		print("<h1>Account successfully confirmed!</h1>\n");
		print("<p>Your account has been activated! However, it appears that you could not be logged in automatically. A possible reason is that you disabled cookies in your browser. You have to enable cookies to use your account. Please do that and then <a href=\"login.php\">log in</a> and try again.</p>\n");
			end_main_frame();
		stdfoot();
	}
}
else
	die();

?>