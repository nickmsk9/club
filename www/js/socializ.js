function socializ(u, t) {

	var m1 = 390; // расстояние от начала страницы до плавающей панели
	var m2 = 50;  // расстояние от верха видимой области страницы до панели
	var f = 'pic/social/'; // путь к папке с изображениями

	document.write('<div id="socializ"></div>');
	var s = jQuery('#socializ');
	s.css({ top: m1 });

	function margin() {
		var top = jQuery(window).scrollTop();
		if (top + m2 < m1) {
			s.css({ top: m1 - top });
		} else {
			s.css({ top: m2 });
		}
	}
	jQuery(window).scroll(function () { margin(); });

	s.append(
		'<a href="http://twitter.com/home?status=RT @AnimeClub.Lv ' + t + ' - ' + u + '" title="Добавить в Twitter" rel="nofollow"><img src="' + f + 'twitter.png" alt="" /></a>' +
		'<a href="http://www.google.com/reader/link?url=' + u + '&title=' + t + '&srcURL=http://animeclub.lv/" title="Добавить в Google Buzz" rel="nofollow"><img src="' + f + 'google-buzz.png" alt="" /></a>' +
		'<a href="http://www.friendfeed.com/share?title=' + t + ' - ' + u + '" title="Добавить в FriendFeed" rel="nofollow"><img src="' + f + 'friendfeed.png" alt="" /></a>' +
		'<a href="http://www.facebook.com/sharer.php?u=' + u + '" title="Поделиться в Facebook" rel="nofollow"><img src="' + f + 'facebook.png" alt="" /></a>' +
		'<a href="http://vkontakte.ru/share.php?url=' + u + '" title="Поделиться ВКонтакте" rel="nofollow"><img src="' + f + 'vkontakte.png" alt="" /></a>' +
		'<a href="http://connect.mail.ru/share?share_url=' + u + '" title="Поделиться в Моем Мире" rel="nofollow"><img src="' + f + 'moy-mir.png" alt="" /></a>' +
		'<a href="http://www.livejournal.com/update.bml?event=' + u + '&subject=' + t + '" title="Опубликовать в livejournal.com" rel="nofollow"><img src="' + f + 'livejournal.png" alt="" /></a>' +
		'<a href="http://delicious.com/save?url=' + u + '&title=' + t + '" title="Сохранить в Delicious" rel="nofollow"><img src="' + f + 'delicious.png" alt="" /></a>' +
		'<a href="http://www.google.com/bookmarks/mark?op=edit&output=popup&bkmk=' + u + '&title=' + t + '" title="Сохранить в Google" rel="nofollow"><img src="' + f + 'google.png" alt="" /></a>' +
		'<a href="http://bobrdobr.ru/add.html?url=' + u + '&title=' + t + '" title="Забобрить" rel="nofollow"><img src="' + f + 'bobrdobr.png" alt="" /></a>' +
		'<a href="http://memori.ru/link/?sm=1&u_data[url]=' + u + '&u_data[name]=' + t + '" title="В Memori.ru" rel="nofollow"><img src="' + f + 'memori.png" alt="" /></a>' +
		'<a href="http://www.mister-wong.ru/index.php?action=addurl&bm_url=' + u + '&bm_description=' + t + '" title="В Мистер Вонг" rel="nofollow"><img src="' + f + 'mister-wong.png" alt="" /></a>'
	);

	s.find('a').attr({ target: '_blank' }).css({ opacity: 0.5 }).hover(
		function () { jQuery(this).css({ opacity: 1 }); },
		function () { jQuery(this).css({ opacity: 0.7 }); }
	);
	s.hover(
		function () { jQuery(this).find('a').css({ opacity: 0.7 }); },
		function () { jQuery(this).find('a').css({ opacity: 0.5 }); }
	);
}