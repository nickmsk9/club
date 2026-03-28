-- phpMyAdmin SQL Dump
-- version 5.2.2
-- https://www.phpmyadmin.net/
--
-- Хост: db
-- Время создания: Июл 14 2025 г., 17:11
-- Версия сервера: 8.0.42
-- Версия PHP: 8.2.27

SET SQL_MODE = "NO_AUTO_VALUE_ON_ZERO";
START TRANSACTION;
SET time_zone = "+00:00";


/*!40101 SET @OLD_CHARACTER_SET_CLIENT=@@CHARACTER_SET_CLIENT */;
/*!40101 SET @OLD_CHARACTER_SET_RESULTS=@@CHARACTER_SET_RESULTS */;
/*!40101 SET @OLD_COLLATION_CONNECTION=@@COLLATION_CONNECTION */;
/*!40101 SET NAMES utf8mb4 */;

--
-- База данных: `555`
--

-- --------------------------------------------------------

--
-- Структура таблицы `album`
--

CREATE TABLE `album` (
  `id` int NOT NULL,
  `userid` int NOT NULL,
  `link` text NOT NULL,
  `text` text,
  `type` int NOT NULL
) ENGINE=MyISAM DEFAULT CHARSET=utf8mb3;

-- --------------------------------------------------------

--
-- Структура таблицы `albumcat`
--

CREATE TABLE `albumcat` (
  `id` int NOT NULL,
  `name` text NOT NULL,
  `number` int NOT NULL
) ENGINE=MyISAM DEFAULT CHARSET=utf8mb3;

--
-- Дамп данных таблицы `albumcat`
--

INSERT INTO `albumcat` (`id`, `name`, `number`) VALUES
(1, 'Разное', 1),
(2, 'Компьютерные', 2),
(3, 'Собственные рисунки', 3),
(4, 'Фотошоп', 4),
(5, 'Анимации', 5),
(6, 'Юмор', 6),
(7, 'Фотографии', 7),
(8, 'Косплей', 8),
(9, 'Фан-Арт', 9),
(10, 'Обои', 10);

-- --------------------------------------------------------

--
-- Структура таблицы `anews`
--

CREATE TABLE `anews` (
  `id` int UNSIGNED NOT NULL,
  `userid` int NOT NULL DEFAULT '0',
  `added` datetime NOT NULL DEFAULT '0000-00-00 00:00:00',
  `body` text NOT NULL,
  `poster` text NOT NULL,
  `screen` text NOT NULL,
  `screen2` text NOT NULL,
  `screen3` text NOT NULL,
  `subject` text NOT NULL
) ENGINE=MyISAM DEFAULT CHARSET=utf8mb3;

-- --------------------------------------------------------

--
-- Структура таблицы `articles`
--

CREATE TABLE `articles` (
  `id` int UNSIGNED NOT NULL,
  `name` varchar(255) NOT NULL DEFAULT '',
  `text` text NOT NULL,
  `images` text NOT NULL,
  `categories` varchar(100) NOT NULL,
  `added` datetime NOT NULL DEFAULT '0000-00-00 00:00:00',
  `views` int UNSIGNED NOT NULL DEFAULT '0',
  `userid` int UNSIGNED NOT NULL DEFAULT '0',
  `brandnew` enum('yes','no') NOT NULL DEFAULT 'no',
  `mainimage` varchar(200) NOT NULL
) ENGINE=MyISAM DEFAULT CHARSET=utf8mb3;

-- --------------------------------------------------------

--
-- Структура таблицы `article_categories`
--

CREATE TABLE `article_categories` (
  `id` int UNSIGNED NOT NULL,
  `enname` varchar(50) NOT NULL DEFAULT '',
  `name` varchar(50) NOT NULL DEFAULT '',
  `image` varchar(250) NOT NULL DEFAULT ''
) ENGINE=MyISAM DEFAULT CHARSET=utf8mb3;

--
-- Дамп данных таблицы `article_categories`
--

INSERT INTO `article_categories` (`id`, `enname`, `name`, `image`) VALUES
(1, '', 'Япония', ''),
(2, '', 'Искусство', ''),
(3, '', 'Юмор', ''),
(4, '', 'Разное', ''),
(5, '', 'Анонсы', ''),
(6, '', 'Аниме Новости', '');

-- --------------------------------------------------------

--
-- Структура таблицы `article_comments`
--

CREATE TABLE `article_comments` (
  `id` int UNSIGNED NOT NULL,
  `userid` int UNSIGNED NOT NULL DEFAULT '0',
  `articleid` int UNSIGNED NOT NULL DEFAULT '0',
  `added` datetime NOT NULL DEFAULT '0000-00-00 00:00:00',
  `text` text NOT NULL
) ENGINE=MyISAM DEFAULT CHARSET=utf8mb3;

-- --------------------------------------------------------

--
-- Структура таблицы `avps`
--

CREATE TABLE `avps` (
  `arg` varchar(20) NOT NULL,
  `value_s` text NOT NULL,
  `value_i` int NOT NULL DEFAULT '0',
  `value_u` int UNSIGNED NOT NULL DEFAULT '0'
) ENGINE=MyISAM DEFAULT CHARSET=utf8mb3;

--
-- Дамп данных таблицы `avps`
--

INSERT INTO `avps` (`arg`, `value_s`, `value_i`, `value_u`) VALUES
('lastcleantime', '', 0, 1752512347),
('bonusup', '', 0, 1279883465),
('lasttorretnclean', '', 0, 0);

-- --------------------------------------------------------

--
-- Структура таблицы `bans`
--

CREATE TABLE `bans` (
  `id` int UNSIGNED NOT NULL,
  `added` datetime NOT NULL DEFAULT '0000-00-00 00:00:00',
  `addedby` int UNSIGNED NOT NULL DEFAULT '0',
  `comment` varchar(255) NOT NULL,
  `first` bigint DEFAULT NULL,
  `last` bigint DEFAULT NULL,
  `until` timestamp NOT NULL DEFAULT '0000-00-00 00:00:00'
) ENGINE=MyISAM DEFAULT CHARSET=utf8mb3;

-- --------------------------------------------------------

--
-- Структура таблицы `blocks`
--

CREATE TABLE `blocks` (
  `id` int UNSIGNED NOT NULL,
  `userid` int UNSIGNED NOT NULL DEFAULT '0',
  `blockid` int UNSIGNED NOT NULL DEFAULT '0'
) ENGINE=MyISAM DEFAULT CHARSET=utf8mb3;

-- --------------------------------------------------------

--
-- Структура таблицы `blogs`
--

CREATE TABLE `blogs` (
  `bid` int UNSIGNED NOT NULL,
  `uid` int UNSIGNED NOT NULL,
  `privat` enum('yes','no') NOT NULL DEFAULT 'no',
  `comment` enum('yes','no') NOT NULL DEFAULT 'yes',
  `comments` int UNSIGNED NOT NULL,
  `views` int UNSIGNED NOT NULL,
  `p_added` int NOT NULL,
  `subject` varchar(255) NOT NULL,
  `txt` text NOT NULL,
  `tags` text NOT NULL,
  `up` int NOT NULL,
  `down` int NOT NULL
) ENGINE=MyISAM DEFAULT CHARSET=utf8mb3;

--
-- Дамп данных таблицы `blogs`
--

INSERT INTO `blogs` (`bid`, `uid`, `privat`, `comment`, `comments`, `views`, `p_added`, `subject`, `txt`, `tags`, `up`, `down`) VALUES
(1, 1, 'no', 'yes', 0, 0, 1751803787, '', '', '', 0, 0);

-- --------------------------------------------------------

--
-- Структура таблицы `blogtags`
--

CREATE TABLE `blogtags` (
  `id` int UNSIGNED NOT NULL,
  `name` varchar(30) NOT NULL,
  `howmuch` int NOT NULL DEFAULT '0'
) ENGINE=MyISAM DEFAULT CHARSET=utf8mb3;

-- --------------------------------------------------------

--
-- Структура таблицы `blog_comments`
--

CREATE TABLE `blog_comments` (
  `id` int UNSIGNED NOT NULL,
  `bid` int UNSIGNED NOT NULL,
  `user` int UNSIGNED NOT NULL DEFAULT '0',
  `added` int UNSIGNED NOT NULL,
  `text` text NOT NULL,
  `ori_text` text NOT NULL,
  `editedby` int UNSIGNED NOT NULL DEFAULT '0',
  `editedat` int UNSIGNED NOT NULL,
  `ip` varchar(15) NOT NULL,
  `karma` int DEFAULT '0'
) ENGINE=MyISAM DEFAULT CHARSET=utf8mb3;

-- --------------------------------------------------------

--
-- Структура таблицы `bonus`
--

CREATE TABLE `bonus` (
  `id` int NOT NULL,
  `name` varchar(50) NOT NULL,
  `points` decimal(5,2) NOT NULL DEFAULT '0.00',
  `description` text NOT NULL,
  `type` varchar(10) NOT NULL DEFAULT 'traffic',
  `quanity` bigint UNSIGNED NOT NULL DEFAULT '0'
) ENGINE=MyISAM DEFAULT CHARSET=utf8mb3;

--
-- Дамп данных таблицы `bonus`
--

INSERT INTO `bonus` (`id`, `name`, `points`, `description`, `type`, `quanity`) VALUES
(1, '1GB Аплоуда', 30.00, 'С достаточным числом Ваших заработанных бонусов, Вы можете обменять на 1 Gb (гигабайт) аплоада (закачки). После того, как Вы обменяете свои бонусы на 1 Гб аплоада - число бонусов, соответствующих стоимости будет списано с Вашего аккаунта, а ваш рейтинг увеличится на 1 Гб розданной информации.', 'traffic', 1073741824),
(2, '5GB Аплоуда', 75.00, 'С достаточным числом Ваших заработанных бонусов, Вы можете обменять на 5 Gb (гигабайт) аплоада (закачки). После того, как Вы обменяете свои бонусы на 5 Гб аплоада - число бонусов, соответствующих стоимости будет списано с Вашего аккаунта, а ваш рейтинг увеличится на 5 Гб розданной информации.', 'traffic', 5368709120),
(3, '10GB Аплоуда', 150.00, 'С достаточным числом Ваших заработанных бонусов, Вы можете обменять на 10 Gb (гигабайт) аплоада (закачки). После того, как Вы обменяете свои бонусы на 10 Гб аплоада - число бонусов, соответствующих стоимости будет списано с Вашего аккаунта, а ваш рейтинг увеличится на 10 Гб розданной информации.', 'traffic', 10737418240),
(4, '25GB Аплоуда', 250.00, 'С достаточным числом Ваших заработанных бонусов, Вы можете обменять на 25 Gb (гигабайт) аплоада (закачки). После того, как Вы обменяете свои бонусы на 25 Гб аплоада - число бонусов, соответствующих стоимости будет списано с Вашего аккаунта, а ваш рейтинг увеличится на 25 Гб розданной информации.', 'traffic', 26843545600);

-- --------------------------------------------------------

--
-- Структура таблицы `bookmarks`
--

CREATE TABLE `bookmarks` (
  `id` int UNSIGNED NOT NULL,
  `userid` int UNSIGNED NOT NULL DEFAULT '0',
  `torrentid` int UNSIGNED NOT NULL DEFAULT '0'
) ENGINE=MyISAM DEFAULT CHARSET=utf8mb3;

-- --------------------------------------------------------

--
-- Структура таблицы `categories`
--

CREATE TABLE `categories` (
  `id` int UNSIGNED NOT NULL,
  `sort` int NOT NULL DEFAULT '0',
  `name` varchar(30) NOT NULL,
  `image` varchar(255) NOT NULL
) ENGINE=MyISAM DEFAULT CHARSET=utf8mb3;

--
-- Дамп данных таблицы `categories`
--

INSERT INTO `categories` (`id`, `sort`, `name`, `image`) VALUES
(1, 10, 'AMV', '1.gif'),
(21, 180, 'Images', '12.gif'),
(3, 30, 'DVD', '4.gif'),
(20, 170, 'TV', '9.gif'),
(5, 50, 'Games', '7.gif'),
(6, 60, 'Hentai', '10.gif'),
(7, 70, 'J-Music', '13.gif'),
(8, 80, 'Live Action', '15.gif'),
(9, 90, 'Manga', '2.gif'),
(10, 100, 'Mobile', '5.gif'),
(11, 110, 'Movie', '8.gif'),
(12, 120, 'OST', '11.gif'),
(13, 130, 'OVA', '14.gif'),
(19, 160, 'Misc', '6.gif'),
(18, 150, 'Subtitles', '3.gif'),
(22, 190, 'Ongoing', '17.gif'),
(23, 200, 'Anthology', '18.gif');

-- --------------------------------------------------------

--
-- Структура таблицы `cheaters`
--

CREATE TABLE `cheaters` (
  `id` int UNSIGNED NOT NULL,
  `added` int NOT NULL,
  `userid` int NOT NULL DEFAULT '0',
  `torrentid` int NOT NULL DEFAULT '0',
  `client` varchar(255) NOT NULL,
  `rate` varchar(255) NOT NULL,
  `beforeup` varchar(255) NOT NULL,
  `upthis` varchar(255) NOT NULL,
  `timediff` varchar(255) NOT NULL,
  `userip` varchar(15) NOT NULL DEFAULT '0'
) ENGINE=MyISAM DEFAULT CHARSET=utf8mb3;

--
-- Дамп данных таблицы `cheaters`
--

INSERT INTO `cheaters` (`id`, `added`, `userid`, `torrentid`, `client`, `rate`, `beforeup`, `upthis`, `timediff`, `userip`) VALUES
(9261, 1343070666, 54597, 4890, 'uTorrent/3130(27207)', '4220005.6183206', '672706538140', '1105641472', '261', '80.232.241.11'),
(9262, 1343071126, 54597, 4890, 'uTorrent/3130(27207)', '2527846.4', '673812179612', '404455424', '159', '80.232.241.11'),
(9263, 1343071519, 48379, 5066, 'uTorrent/1850(17414)', '2104662.8470067', '5927718110419', '3796811776', '1803', '176.36.14.32'),
(9264, 1343075179, 48379, 5066, 'uTorrent/1850(17414)', '2361108.789925', '5934645371485', '4405829002', '1865', '176.36.14.32'),
(9265, 1343084185, 48379, 5066, 'uTorrent/1850(17414)', '2161632.1422222', '5940715838833', '3890937856', '1799', '176.36.14.32');

-- --------------------------------------------------------

--
-- Структура таблицы `comments`
--

CREATE TABLE `comments` (
  `id` int UNSIGNED NOT NULL,
  `user` int UNSIGNED NOT NULL DEFAULT '0',
  `request` int UNSIGNED NOT NULL DEFAULT '0',
  `torrent` int UNSIGNED NOT NULL DEFAULT '0',
  `anews` int UNSIGNED NOT NULL DEFAULT '0',
  `added` datetime NOT NULL DEFAULT '0000-00-00 00:00:00',
  `text` text NOT NULL,
  `ori_text` text NOT NULL,
  `editedby` int UNSIGNED NOT NULL DEFAULT '0',
  `editedat` datetime NOT NULL DEFAULT '0000-00-00 00:00:00',
  `ip` varchar(15) NOT NULL,
  `galary` int NOT NULL
) ENGINE=MyISAM DEFAULT CHARSET=utf8mb3;

--
-- Дамп данных таблицы `comments`
--

INSERT INTO `comments` (`id`, `user`, `request`, `torrent`, `anews`, `added`, `text`, `ori_text`, `editedby`, `editedat`, `ip`, `galary`) VALUES
(7, 1, 0, 1, 0, '2025-07-06 12:45:09', 'ячсячся', 'ячсячся', 0, '0000-00-00 00:00:00', '192.168.97.1', 0),
(2, 2, 0, 1, 0, '2025-07-06 07:36:06', 'zxczxczxczxc', 'zxczxczxczxc', 0, '0000-00-00 00:00:00', '192.168.97.1', 0),
(4, 2, 0, 1, 0, '2025-07-06 07:39:33', '[color=maroon]ЯЧЯчЯчЯчячЯч[center]ЯЧЯчЯчЯ[/center][/color] *35*', '[color=maroon]ЯЧЯчЯчЯчячЯч[center]ЯЧЯчЯчЯ[/center][/color] *35*', 0, '0000-00-00 00:00:00', '192.168.97.1', 0),
(5, 2, 0, 1, 0, '2025-07-06 07:40:02', 'ячсячсячсячсячсячсячсячсяч *57*', 'ячсячсячсячсячсячсячсячсяч *57*', 0, '0000-00-00 00:00:00', '192.168.97.1', 0),
(6, 1, 0, 1, 0, '2025-07-06 09:17:23', '*28*', '*28*', 0, '0000-00-00 00:00:00', '192.168.97.1', 0),
(8, 1, 0, 1, 0, '2025-07-06 12:45:12', 'чясячсячсячся', 'чясячсячсячся', 0, '0000-00-00 00:00:00', '192.168.97.1', 0),
(9, 1, 0, 1, 0, '2025-07-06 12:45:15', 'чсмчсмячс', 'чсмчсмячс', 0, '0000-00-00 00:00:00', '192.168.97.1', 0),
(10, 1, 0, 1, 0, '2025-07-06 12:45:18', 'тестируем!', 'ячсячсЯЧсЯЧ', 1, '2025-07-06 16:41:51', '192.168.97.1', 0),
(12, 1, 0, 1, 0, '2025-07-07 14:53:38', 'привет', 'привет', 0, '0000-00-00 00:00:00', '192.168.97.1', 0);

-- --------------------------------------------------------

--
-- Структура таблицы `countries`
--

CREATE TABLE `countries` (
  `id` int UNSIGNED NOT NULL,
  `name` varchar(50) DEFAULT NULL,
  `flagpic` varchar(50) DEFAULT NULL
) ENGINE=MyISAM DEFAULT CHARSET=utf8mb3;

--
-- Дамп данных таблицы `countries`
--

INSERT INTO `countries` (`id`, `name`, `flagpic`) VALUES
(87, 'Антигуа и Барбуда', 'antiguabarbuda.gif'),
(10, 'Дания', 'denmark.gif'),
(91, 'Сенегал', 'senegal.gif'),
(76, 'Тринидад и Тобаго', 'trinidadandtobago.gif'),
(20, 'Австралия', 'australia.gif'),
(36, 'Австрия', 'austria.gif'),
(27, 'Албания', 'albania.gif'),
(34, 'Алжир', 'algeria.gif'),
(12, 'Англия', 'uk.gif'),
(35, 'Ангола', 'angola.gif'),
(66, 'Андора', 'andorra.gif'),
(19, 'Аргентина', 'argentina.gif'),
(53, 'Афганистан', 'afghanistan.gif'),
(80, 'Багамы', 'bahamas.gif'),
(83, 'Барбадос', 'barbados.gif'),
(16, 'Бельгия', 'belgium.gif'),
(84, 'Бенгладеш', 'bangladesh.gif'),
(101, 'Болгария', 'bulgaria.gif'),
(65, 'Босния', 'bosniaherzegovina.gif'),
(18, 'Бразилия', 'brazil.gif'),
(74, 'Вануату', 'vanuatu.gif'),
(72, 'Венгрия', 'hungary.gif'),
(71, 'Венесуела', 'venezuela.gif'),
(75, 'Вьетнам', 'vietnam.gif'),
(7, 'Германия', 'germany.gif'),
(77, 'Гондурас', 'honduras.gif'),
(32, 'Гонк Конг', 'hongkong.gif'),
(41, 'Греция', 'greece.gif'),
(42, 'Гуатемала', 'guatemala.gif'),
(40, 'Доминиканская Республика', 'dominicanrep.gif'),
(100, 'Египт', 'egypt.gif'),
(43, 'Израиль', 'israel.gif'),
(26, 'Индия', 'india.gif'),
(13, 'Ирландия', 'ireland.gif'),
(61, 'Ирландия', 'iceland.gif'),
(102, 'Исла де Муерто', 'jollyroger.gif'),
(22, 'Испания', 'spain.gif'),
(9, 'Италия', 'italy.gif'),
(82, 'Камбоджа', 'cambodia.gif'),
(5, 'Канада', 'canada.gif'),
(78, 'Киргистан', 'kyrgyzstan.gif'),
(57, 'Кирибати', 'kiribati.gif'),
(8, 'Китай', 'china.gif'),
(52, 'Кного', 'congo.gif'),
(96, 'Колумбия', 'colombia.gif'),
(99, 'Коста Рика', 'costarica.gif'),
(51, 'Куба', 'cuba.gif'),
(85, 'Лаос', 'laos.gif'),
(98, 'Латвия', 'latvia.gif'),
(97, 'Леванон', 'lebanon.gif'),
(67, 'Литва', 'lithuania.gif'),
(31, 'Люксембург', 'luxembourg.gif'),
(68, 'Македония', 'macedonia.gif'),
(39, 'Малайзия', 'malaysia.gif'),
(24, 'Мексика', 'mexico.gif'),
(62, 'Науру', 'nauru.gif'),
(60, 'Нигерия', 'nigeria.gif'),
(69, 'Нидерландские Антиллы', 'nethantilles.gif'),
(15, 'Нидерланды', 'netherlands.gif'),
(21, 'Новая Зеландия', 'newzealand.gif'),
(11, 'Норвегия', 'norway.gif'),
(44, 'Пакистан', 'pakistan.gif'),
(88, 'Парагвая', 'paraguay.gif'),
(81, 'Перу', 'peru.gif'),
(14, 'Польша', 'poland.gif'),
(23, 'Португалия', 'portugal.gif'),
(49, 'Пуерто Рико', 'puertorico.gif'),
(3, 'Россия', 'russia.gif'),
(73, 'Румуния', 'romania.gif'),
(93, 'Северная Корея', 'northkorea.gif'),
(47, 'Сейшельские Острова', 'seychelles.gif'),
(46, 'Сербия', 'serbia.gif'),
(25, 'Сингапур', 'singapore.gif'),
(63, 'Словакия', 'slovenia.gif'),
(90, 'СССР', 'ussr.gif'),
(2, 'США', 'usa.gif'),
(48, 'Тайвань', 'taiwan.gif'),
(89, 'Тайланд', 'thailand.gif'),
(92, 'Того', 'togo.gif'),
(64, 'Туркменистан', 'turkmenistan.gif'),
(54, 'Турция', 'turkey.gif'),
(55, 'Узбекистан', 'uzbekistan.gif'),
(70, 'Украина', 'ukraine.gif'),
(86, 'Уругвай', 'uruguay.gif'),
(58, 'Филиппины', 'philippines.gif'),
(4, 'Финляндия', 'finland.gif'),
(6, 'Франция', 'france.gif'),
(94, 'Хорватия', 'croatia.gif'),
(45, 'Чехия', 'czechrep.gif'),
(50, 'Чили', 'chile.gif'),
(56, 'Швейцария', 'switzerland.gif'),
(1, 'Швеция', 'sweden.gif'),
(79, 'Эквадор', 'ecuador.gif'),
(95, 'Эстония', 'estonia.gif'),
(37, 'Югославия', 'yugoslavia.gif'),
(28, 'Южная Африка', 'southafrica.gif'),
(29, 'Южная Корея', 'southkorea.gif'),
(38, 'Южные Самоа', 'westernsamoa.gif'),
(30, 'Ямайка', 'jamaica.gif'),
(17, 'Япония', 'japan.gif'),
(103, 'Казахстан', 'kazakhstan.gif'),
(104, 'Белоруссия', 'belarus.gif'),
(105, 'Молдова', 'moldova.gif');

-- --------------------------------------------------------

--
-- Структура таблицы `donatedelux`
--

CREATE TABLE `donatedelux` (
  `id` int NOT NULL,
  `date` datetime NOT NULL,
  `msg` varchar(255) NOT NULL,
  `operator` varchar(40) NOT NULL,
  `phone` int NOT NULL,
  `smtext` varchar(255) NOT NULL,
  `smsid` int NOT NULL,
  `num` int NOT NULL,
  `country` varchar(40) NOT NULL,
  `cost` varchar(40) NOT NULL,
  `currency` varchar(40) NOT NULL,
  `profit` int NOT NULL,
  `dollarcost` int NOT NULL,
  `test` int NOT NULL
) ENGINE=MyISAM DEFAULT CHARSET=utf8mb3;

-- --------------------------------------------------------

--
-- Структура таблицы `files`
--

CREATE TABLE `files` (
  `id` int UNSIGNED NOT NULL,
  `torrent` int UNSIGNED NOT NULL DEFAULT '0',
  `filename` varchar(255) NOT NULL,
  `size` bigint UNSIGNED NOT NULL DEFAULT '0'
) ENGINE=MyISAM DEFAULT CHARSET=utf8mb3;

--
-- Дамп данных таблицы `files`
--

INSERT INTO `files` (`id`, `torrent`, `filename`, `size`) VALUES
(1, 1, 'Досье «Чёрная канарейка».2024.BDRemux.1080p.R.G. Goldenshara.mkv', 20063598757);

-- --------------------------------------------------------

--
-- Структура таблицы `forums`
--

CREATE TABLE `forums` (
  `sort` tinyint UNSIGNED NOT NULL DEFAULT '0',
  `id` int UNSIGNED NOT NULL,
  `name` varchar(60) NOT NULL DEFAULT '',
  `description` varchar(200) DEFAULT NULL,
  `postcount` int UNSIGNED NOT NULL DEFAULT '0',
  `topiccount` int UNSIGNED NOT NULL DEFAULT '0',
  `forid` tinyint DEFAULT '0'
) ENGINE=MyISAM DEFAULT CHARSET=utf8mb3;

--
-- Дамп данных таблицы `forums`
--

INSERT INTO `forums` (`sort`, `id`, `name`, `description`, `postcount`, `topiccount`, `forid`) VALUES
(0, 26, 'Новости', 'Новости проекта. ', 0, 0, 5),
(0, 27, 'Тех. Поддержка', 'Вопросы, связанные с работой трекера и сайта, публикуем сюда. ', 0, 0, 7),
(1, 28, 'Идеи и Предложения', 'Есть идеи как улучшить проект? Пишем сюда. ', 0, 0, 5),
(0, 29, 'Ошибки Трекера и  Форума', 'Ошибки и глюки трекера и форума. ', 0, 0, 7),
(0, 30, 'Уголок Отаку', 'Делимся друг с другом всем, что связано с Аниме! ', 0, 0, 8),
(2, 31, 'Мероприятия', 'Что, Где, Когда?! ', 0, 0, 5),
(15, 32, 'AnimeClub.Lv', 'Хочешь поучаствовать в создании сайта? Тогда тебе - сюда! ', 0, 0, 5),
(1, 33, 'AMV', '', 0, 0, 6),
(7, 34, 'Games', '', 0, 0, 6),
(5, 35, 'Hentai', '', 0, 0, 6),
(6, 36, 'J-Music / OST', '', 0, 0, 6),
(4, 37, 'Manga', '', 0, 0, 6),
(2, 38, 'TV', '', 0, 0, 6),
(3, 39, 'Ongoing', '', 0, 0, 6),
(15, 40, 'Юмор', 'Тебе смешно, мы тоже хотим посмеяться. Делись! ', 0, 0, 8),
(21, 41, 'Флуд', 'Всё обо всём. ', 0, 0, 8),
(21, 42, 'Корзина', 'Весь мусор идёт сюда. ', 0, 0, 7),
(10, 43, 'Наши хобби ', 'Рассказываем своим соплеменникам о своих увлечениях !', 0, 0, 8),
(0, 44, 'Фотки', 'Получился удачный кадр или засняли что-то интересное? Постим здесь !', 0, 0, 8),
(0, 47, 'Мануалы', 'Вам не понятно что и как ? Возможно, Вы найдете ответы здесь .', 0, 0, 7),
(4, 48, 'Конкурсы', '', 0, 0, 5),
(0, 49, 'Запросы', 'Не можете найти нужное Аниме ? Вам сюда .', 0, 0, 6);

-- --------------------------------------------------------

--
-- Структура таблицы `friends`
--

CREATE TABLE `friends` (
  `id` int UNSIGNED NOT NULL,
  `userid` int UNSIGNED NOT NULL DEFAULT '0',
  `friendid` int UNSIGNED NOT NULL DEFAULT '0',
  `status` enum('yes','no','pending') NOT NULL DEFAULT 'pending'
) ENGINE=MyISAM DEFAULT CHARSET=utf8mb3;

--
-- Дамп данных таблицы `friends`
--

INSERT INTO `friends` (`id`, `userid`, `friendid`, `status`) VALUES
(3, 3, 1, 'yes'),
(4, 1, 3, 'yes'),
(5, 1, 2, 'yes'),
(6, 2, 1, 'yes');

-- --------------------------------------------------------

--
-- Структура таблицы `groups`
--

CREATE TABLE `groups` (
  `id` int NOT NULL,
  `name` varchar(1000) NOT NULL,
  `descr` text NOT NULL,
  `priv` enum('yes','no') NOT NULL DEFAULT 'no',
  `rating` int NOT NULL DEFAULT '0',
  `owner` int NOT NULL,
  `users` int NOT NULL,
  `news` int NOT NULL,
  `comm` int NOT NULL,
  `topic` int NOT NULL,
  `added` int NOT NULL
) ENGINE=MyISAM DEFAULT CHARSET=utf8mb3;

--
-- Дамп данных таблицы `groups`
--

INSERT INTO `groups` (`id`, `name`, `descr`, `priv`, `rating`, `owner`, `users`, `news`, `comm`, `topic`, `added`) VALUES
(1, 'Администрация', 'Група создана для Администрации и относящемся к ним пользователей ', 'yes', 0, 22, 0, 0, 0, 0, 1310027844);

-- --------------------------------------------------------

--
-- Структура таблицы `guests`
--

CREATE TABLE `guests` (
  `id` int UNSIGNED NOT NULL,
  `ip` varchar(15) NOT NULL DEFAULT '',
  `time_accessed` int NOT NULL,
  `browser` varchar(255) NOT NULL,
  `loc` text NOT NULL
) ENGINE=MyISAM DEFAULT CHARSET=utf8mb3;

--
-- Дамп данных таблицы `guests`
--

INSERT INTO `guests` (`id`, `ip`, `time_accessed`, `browser`, `loc`) VALUES
(1513093, '192.168.97.1', 1752512349, '48ffa9bed7def1918b7c019ef4790bd1', 'http://localhost:8080/apple-touch-icon-precomposed.png');

-- --------------------------------------------------------

--
-- Структура таблицы `hackers`
--

CREATE TABLE `hackers` (
  `id` int NOT NULL,
  `added` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP,
  `ip` varchar(15) NOT NULL,
  `system` varchar(255) NOT NULL,
  `event` text NOT NULL
) ENGINE=MyISAM DEFAULT CHARSET=utf8mb3;

-- --------------------------------------------------------

--
-- Структура таблицы `karma`
--

CREATE TABLE `karma` (
  `id` int NOT NULL,
  `userid` int NOT NULL,
  `fromid` int NOT NULL,
  `added` int NOT NULL,
  `type` varchar(10) NOT NULL,
  `descr` varchar(250) NOT NULL,
  `old` enum('yes','no') NOT NULL DEFAULT 'no'
) ENGINE=MyISAM DEFAULT CHARSET=utf8mb3;

--
-- Дамп данных таблицы `karma`
--

INSERT INTO `karma` (`id`, `userid`, `fromid`, `added`, `type`, `descr`, `old`) VALUES
(1, 2, 1, 1751791464, 'plus', 'чсячс', 'no'),
(2, 3, 1, 1752162721, 'plus', 'топ', 'no');

-- --------------------------------------------------------

--
-- Структура таблицы `konkurs`
--

CREATE TABLE `konkurs` (
  `id` int NOT NULL,
  `uid` int NOT NULL,
  `name` varchar(40) NOT NULL,
  `age` int NOT NULL,
  `from` varchar(100) NOT NULL,
  `eyes` varchar(100) NOT NULL,
  `hair` varchar(100) NOT NULL,
  `body` varchar(100) NOT NULL,
  `life` text NOT NULL,
  `dream` varchar(255) NOT NULL,
  `deviz` varchar(255) NOT NULL,
  `anime` text NOT NULL,
  `photo1` varchar(255) NOT NULL,
  `photo2` varchar(255) NOT NULL,
  `photo3` varchar(255) NOT NULL
) ENGINE=MyISAM DEFAULT CHARSET=utf8mb3;

-- --------------------------------------------------------

--
-- Структура таблицы `messages`
--

CREATE TABLE `messages` (
  `id` int UNSIGNED NOT NULL,
  `sender` int UNSIGNED NOT NULL DEFAULT '0',
  `receiver` int UNSIGNED NOT NULL DEFAULT '0',
  `added` datetime DEFAULT NULL,
  `subject` varchar(255) NOT NULL,
  `msg` text,
  `unread` enum('yes','no') NOT NULL DEFAULT 'yes',
  `poster` int UNSIGNED NOT NULL DEFAULT '0',
  `location` tinyint(1) NOT NULL DEFAULT '1',
  `saved` enum('no','yes') NOT NULL DEFAULT 'no',
  `spam` int NOT NULL
) ENGINE=MyISAM DEFAULT CHARSET=utf8mb3;

--
-- Дамп данных таблицы `messages`
--

INSERT INTO `messages` (`id`, `sender`, `receiver`, `added`, `subject`, `msg`, `unread`, `poster`, `location`, `saved`, `spam`) VALUES
(7, 1, 3, '2025-07-06 16:18:21', 'Предложение дружбы.', 'Пользователь [url=user/id1]webnet[/url] желает добавить Вас в друзья. [url=friends.php?act=accept&id=1&user=1]Принять[/url] [url=friends.php?act=surrender&id=1&user=1]Отклонить[/url]', 'no', 0, 0, 'no', 0),
(2, 1, 2, '2025-07-06 08:48:48', 'привет', 'тестируем!!!!!! *22*', 'no', 1, 1, 'yes', 1751791728),
(3, 1, 2, '2025-07-06 08:49:22', 'привет', 'тестируем!!!!!! *22*', 'no', 1, 1, 'yes', 1751791762),
(4, 1, 2, '2025-07-06 08:49:37', 'привет', 'тестируем!!!!!! *22*', 'no', 1, 1, 'yes', 1751791777),
(6, 2, 1, '2025-07-06 15:26:14', 'ЧЯЧ', 'ФЫФы', 'no', 2, 1, 'no', 1751815574),
(8, 3, 1, '2025-07-06 16:21:38', 'Отмена дружбы.', 'Пользователь [url=user/id3]demon[/url] удалил Вас из друзей.', 'no', 0, 0, 'no', 0),
(9, 3, 1, '2025-07-06 16:21:48', 'Предложение дружбы.', 'Пользователь [url=user/id3]demon[/url] желает добавить Вас в друзья. [url=friends.php?act=accept&id=3&user=3]Принять[/url] [url=friends.php?act=surrender&id=3&user=3]Отклонить[/url]', 'no', 0, 0, 'no', 0),
(10, 3, 1, '2025-07-06 16:21:59', 'фывфы', 'привет *28*', 'no', 3, 1, 'no', 1751818919),
(11, 1, 2, '2025-07-06 16:34:48', 'Предложение дружбы.', 'Пользователь [url=user/id1]webnet[/url] желает добавить Вас в друзья. [url=friends.php?act=accept&id=5&user=1]Принять[/url] [url=friends.php?act=surrender&id=5&user=1]Отклонить[/url]', 'no', 0, 0, 'no', 0),
(12, 2, 1, '2025-07-10 15:53:27', '\'Ответ на предложение дружбы.\'', '\'Пользователь [url=user/id2]merdox[/url] согласился на дружбу.\'', 'no', 0, 1, 'no', 0);

-- --------------------------------------------------------

--
-- Структура таблицы `news`
--

CREATE TABLE `news` (
  `id` int UNSIGNED NOT NULL,
  `userid` int NOT NULL DEFAULT '0',
  `added` datetime NOT NULL DEFAULT '0000-00-00 00:00:00',
  `body` text NOT NULL,
  `subject` text NOT NULL
) ENGINE=MyISAM DEFAULT CHARSET=utf8mb3;

--
-- Дамп данных таблицы `news`
--

INSERT INTO `news` (`id`, `userid`, `added`, `body`, `subject`) VALUES
(1, 1, '2025-07-06 04:27:59', 'фывфывфывфвф', 'тестируем');

-- --------------------------------------------------------

--
-- Структура таблицы `notconnectablepmlog`
--

CREATE TABLE `notconnectablepmlog` (
  `id` int UNSIGNED NOT NULL,
  `user` int UNSIGNED NOT NULL DEFAULT '0',
  `date` datetime DEFAULT NULL
) ENGINE=MyISAM DEFAULT CHARSET=utf8mb3;

-- --------------------------------------------------------

--
-- Структура таблицы `options`
--

CREATE TABLE `options` (
  `option_id` bigint UNSIGNED NOT NULL,
  `blog_id` int NOT NULL DEFAULT '0',
  `option_name` varchar(64) NOT NULL DEFAULT '',
  `option_value` longtext NOT NULL,
  `autoload` varchar(20) NOT NULL DEFAULT 'yes'
) ENGINE=MyISAM DEFAULT CHARSET=utf8mb3;

-- --------------------------------------------------------

--
-- Структура таблицы `orbital_blocks`
--

CREATE TABLE `orbital_blocks` (
  `bid` int NOT NULL,
  `bkey` varchar(15) NOT NULL,
  `title` varchar(60) NOT NULL,
  `content` text NOT NULL,
  `bposition` char(1) NOT NULL,
  `weight` int NOT NULL DEFAULT '1',
  `active` int NOT NULL DEFAULT '1',
  `time` varchar(14) NOT NULL DEFAULT '0',
  `blockfile` varchar(255) NOT NULL,
  `view` int NOT NULL DEFAULT '0',
  `expire` varchar(14) NOT NULL DEFAULT '0',
  `action` char(1) NOT NULL,
  `which` varchar(255) NOT NULL,
  `allow_hide` enum('yes','no') NOT NULL
) ENGINE=MyISAM DEFAULT CHARSET=utf8mb3;

--
-- Дамп данных таблицы `orbital_blocks`
--

INSERT INTO `orbital_blocks` (`bid`, `bkey`, `title`, `content`, `bposition`, `weight`, `active`, `time`, `blockfile`, `view`, `expire`, `action`, `which`, `allow_hide`) VALUES
(35, '', 'Администрация', '', 'c', 1, 1, '', 'block-adminka.php', 2, '0', 'd', 'all', 'yes'),
(16, '', 'Чат', '', 'c', 2, 1, '', 'block-shoutbox.php', 1, '0', 'd', 'ihome,', 'yes'),
(2, '', 'Новости', '', 'c', 4, 1, '', 'block-news.php', 3, '0', 'd', 'ihome,', 'yes'),
(6, '', 'Релизы', '', 'd', 1, 1, '', 'block-releases.php', 3, '0', 'd', 'ihome,', 'yes'),
(20, '', 'Онлайн', '', 'd', 5, 1, '', 'block-online.php', 0, '0', 'd', 'ihome,', 'yes'),
(40, '', 'Меню ', '', 'd', 2, 1, '', 'block-main.php', 1, '0', 'd', 'ihome,', 'yes'),
(38, '', 'Нуждаются в раздающих', '', 'd', 3, 1, '', 'block-help.php', 0, '0', 'd', 'browse,', 'yes'),
(45, '', 'Афиша', '', 'c', 5, 1, '', 'block-anews.php', 3, '0', 'd', 'afisha,', 'yes'),
(46, '', 'Релизы', '', 'c', 6, 1, '', 'block-releases2.php', 0, '0', 'd', 'releases,', 'yes'),
(47, '', 'Новое на Форуме', '', 'd', 4, 1, '', 'block-forum.php', 3, '0', 'd', 'ihome,', 'yes'),
(51, '', 'Опрос', '', 'c', 7, 1, '', 'block-poll.php', 1, '0', 'd', 'ihome,', 'yes'),
(62, '', 'Вконтакте', '', 'd', 6, 1, '', 'block-vkontakte.php', 0, '0', 'd', 'ihome,', 'yes'),
(63, '', 'shoutbox mem', '', 'd', 7, 1, '', 'block-shoutbox_mem.php', 1, '0', 'd', 'index2,', 'yes'),
(64, '', 'Онлайн игра с элементами аниме', '', 'c', 3, 0, '', 'block-reklama.php', 0, '0', 'd', 'all', 'no');

-- --------------------------------------------------------

--
-- Структура таблицы `overforums`
--

CREATE TABLE `overforums` (
  `id` int UNSIGNED NOT NULL,
  `name` varchar(60) NOT NULL DEFAULT '',
  `description` varchar(200) DEFAULT NULL,
  `forid` tinyint UNSIGNED NOT NULL DEFAULT '1',
  `sort` tinyint UNSIGNED NOT NULL DEFAULT '0'
) ENGINE=MyISAM DEFAULT CHARSET=utf8mb3;

--
-- Дамп данных таблицы `overforums`
--

INSERT INTO `overforums` (`id`, `name`, `description`, `forid`, `sort`) VALUES
(5, 'АнимеКлаб.Lv', '', 0, 0),
(6, ' Тематические категории (>_<)', '', 0, 2),
(7, 'Технический раздел', '', 0, 4),
(8, 'Общение', '', 0, 1);

-- --------------------------------------------------------

--
-- Структура таблицы `pay`
--

CREATE TABLE `pay` (
  `id` int NOT NULL,
  `order_num` varchar(255) NOT NULL,
  `order_summ` int NOT NULL,
  `order_date` varchar(255) NOT NULL,
  `order_uid` int NOT NULL,
  `order_format` enum('plus','minus') NOT NULL,
  `order_desc` varchar(255) NOT NULL
) ENGINE=MyISAM DEFAULT CHARSET=utf8mb3;

-- --------------------------------------------------------

--
-- Структура таблицы `pay_bonus`
--

CREATE TABLE `pay_bonus` (
  `id` int NOT NULL,
  `name` varchar(50) NOT NULL,
  `points` decimal(5,2) NOT NULL DEFAULT '0.00',
  `description` text NOT NULL,
  `type` varchar(10) NOT NULL DEFAULT 'traffic',
  `quanity` bigint UNSIGNED NOT NULL DEFAULT '0'
) ENGINE=MyISAM DEFAULT CHARSET=utf8mb3;

--
-- Дамп данных таблицы `pay_bonus`
--

INSERT INTO `pay_bonus` (`id`, `name`, `points`, `description`, `type`, `quanity`) VALUES
(1, '1GB Аплоуда', 10.00, 'С достаточным числом Ваших рублей, Вы можете обменять на 1Гб (гигабайт) аплоада (закачки). После того, как Вы обменяете свои рубли на 1Гб аплоада - число рублей, соответствующих стоимости будет списано с Вашего аккаунта, а ваш рейтинг увеличится на 1Гб розданной информации.', 'traffic', 1073741824),
(2, '5GB Аплоуда', 40.00, 'С достаточным числом Ваших рублей, Вы можете обменять на 5Гб (гигабайт) аплоада (закачки). После того, как Вы обменяете свои рубли на 5Гб аплоада - число рублей, соответствующих стоимости будет списано с Вашего аккаунта, а ваш рейтинг увеличится на 5Гб розданной информации.', 'traffic', 5368709120),
(3, '10GB Аплоуда', 70.00, 'С достаточным числом Ваших рублей, Вы можете обменять на 10Гб (гигабайт) аплоада (закачки). После того, как Вы обменяете свои рубли на 10Гб аплоада - число рублей, соответствующих стоимости будет списано с Вашего аккаунта, а ваш рейтинг увеличится на 10Гб розданной информации.', 'traffic', 10737418240),
(4, '25GB Аплоуда', 150.00, 'С достаточным числом Ваших рублей, Вы можете обменять на 25Гб (гигабайт) аплоада (закачки). После того, как Вы обменяете свои рубли на 25Гб аплоада - число рублей, соответствующих стоимости будет списано с Вашего аккаунта, а ваш рейтинг увеличится на 25Гб розданной информации.', 'traffic', 26843545600),
(11, 'VIP 7 дней', 25.00, 'Уважаемые пользователи , данная функция позволяет Вам приобрести VIP аккаунт и качать любимое аниме без ограничений .\r\nПри наличие VIP аккаунта , система не будет учитывать скачаное , только розданое , что способствует быстрому росту рейтинга .', 'vip', 7),
(12, 'VIP 14 дней', 50.00, 'Уважаемые пользователи , данная функция позволяет Вам приобрести VIP аккаунт и качать любимое аниме без ограничений .\r\nПри наличие VIP аккаунта , система не будет учитывать скачаное , только розданое , что способствует быстрому росту рейтинга .', 'vip', 14),
(13, 'VIP 30 дней', 110.00, 'Уважаемые пользователи , данная функция позволяет Вам приобрести VIP аккаунт и качать любимое аниме без ограничений .\r\nПри наличие VIP аккаунта , система не будет учитывать скачаное , только розданое , что способствует быстрому росту рейтинга .', 'vip', 30),
(10, 'VIP 1 днень', 5.00, 'Уважаемые пользователи , данная функция позволяет Вам приобрести VIP аккаунт и качать любимое аниме без ограничений .\r\nПри наличие VIP аккаунта , система не будет учитывать скачаное , только розданое , что способствует быстрому росту рейтинга .', 'vip', 1);

-- --------------------------------------------------------

--
-- Структура таблицы `peers`
--

CREATE TABLE `peers` (
  `id` int UNSIGNED NOT NULL,
  `torrent` mediumint UNSIGNED NOT NULL DEFAULT '0',
  `peer_id` binary(20) NOT NULL,
  `ip` varchar(64) NOT NULL DEFAULT '',
  `ipv6` varchar(128) NOT NULL,
  `port` smallint UNSIGNED NOT NULL DEFAULT '0',
  `uploaded` bigint UNSIGNED NOT NULL DEFAULT '0',
  `downloaded` bigint UNSIGNED NOT NULL DEFAULT '0',
  `to_go` bigint UNSIGNED NOT NULL DEFAULT '0',
  `seeder` enum('yes','no') NOT NULL DEFAULT 'no',
  `started` int NOT NULL DEFAULT '0',
  `last_action` int NOT NULL DEFAULT '0',
  `prev_action` int NOT NULL DEFAULT '0',
  `connectable` enum('yes','no') NOT NULL DEFAULT 'yes',
  `userid` int UNSIGNED NOT NULL DEFAULT '0',
  `agent` varchar(60) NOT NULL DEFAULT '',
  `finishedat` int UNSIGNED NOT NULL DEFAULT '0',
  `downloadoffset` bigint UNSIGNED NOT NULL DEFAULT '0',
  `uploadoffset` bigint UNSIGNED NOT NULL DEFAULT '0',
  `passkey` char(32) NOT NULL DEFAULT ''
) ENGINE=MyISAM DEFAULT CHARSET=utf8mb3 ROW_FORMAT=FIXED;

-- --------------------------------------------------------

--
-- Структура таблицы `photos`
--

CREATE TABLE `photos` (
  `pid` int UNSIGNED NOT NULL,
  `album_id` int NOT NULL DEFAULT '0',
  `user_id` int NOT NULL DEFAULT '0',
  `photo_descr` text NOT NULL,
  `photo_rating` int NOT NULL DEFAULT '0',
  `photo_added` datetime NOT NULL DEFAULT '0000-00-00 00:00:00',
  `photo_name` varchar(100) NOT NULL,
  `views` int NOT NULL DEFAULT '0',
  `comments` int NOT NULL DEFAULT '0',
  `fullsize` tinyint(1) NOT NULL DEFAULT '0',
  `fullsize_size` varchar(50) NOT NULL
) ENGINE=MyISAM DEFAULT CHARSET=utf8mb3;

-- --------------------------------------------------------

--
-- Структура таблицы `photos_comments`
--

CREATE TABLE `photos_comments` (
  `cid` int UNSIGNED NOT NULL,
  `album_id` int NOT NULL DEFAULT '0',
  `photo_id` int NOT NULL DEFAULT '0',
  `user_id` int NOT NULL DEFAULT '0',
  `comment_title` varchar(200) NOT NULL,
  `comment_text` text NOT NULL,
  `comment_added` datetime NOT NULL DEFAULT '0000-00-00 00:00:00',
  `parent` int NOT NULL DEFAULT '0'
) ENGINE=MyISAM DEFAULT CHARSET=utf8mb3;

-- --------------------------------------------------------

--
-- Структура таблицы `photo_albums`
--

CREATE TABLE `photo_albums` (
  `aid` int UNSIGNED NOT NULL,
  `created_by` int NOT NULL DEFAULT '0',
  `creation_date` datetime NOT NULL DEFAULT '0000-00-00 00:00:00',
  `updated_date` datetime NOT NULL DEFAULT '0000-00-00 00:00:00',
  `album_title` varchar(70) NOT NULL,
  `album_descr` text NOT NULL,
  `photos_count` int DEFAULT '0',
  `album_view_access` enum('all','friends','only_me') DEFAULT 'all',
  `album_comment_access` enum('all','friends','only_me') NOT NULL DEFAULT 'all',
  `album_preview` text
) ENGINE=MyISAM DEFAULT CHARSET=utf8mb3;

-- --------------------------------------------------------

--
-- Структура таблицы `podarki`
--

CREATE TABLE `podarki` (
  `id` smallint NOT NULL,
  `pic` text,
  `bonus` int UNSIGNED NOT NULL
) ENGINE=MyISAM DEFAULT CHARSET=utf8mb3;

--
-- Дамп данных таблицы `podarki`
--

INSERT INTO `podarki` (`id`, `pic`, `bonus`) VALUES
(1, '/pic/podarki/21.gif', 50),
(2, '/pic/podarki/35.gif', 50),
(3, '/pic/podarki/108.gif', 50),
(4, '/pic/podarki/120.gif', 50),
(5, '/pic/podarki/134.gif', 50),
(6, '/pic/podarki/135.gif', 50),
(7, '/pic/podarki/121.gif', 50),
(8, '/pic/podarki/109.gif', 50),
(9, '/pic/podarki/34.gif', 50),
(10, '/pic/podarki/20.gif', 50),
(11, '/pic/podarki/36.gif', 50),
(12, '/pic/podarki/22.gif', 50),
(13, '/pic/podarki/137.gif', 50),
(14, '/pic/podarki/123.gif', 50),
(15, '/pic/podarki/122.gif', 50),
(16, '/pic/podarki/136.gif', 50),
(17, '/pic/podarki/23.gif', 50),
(18, '/pic/podarki/37.gif', 50),
(19, '/pic/podarki/33.gif', 50),
(20, '/pic/podarki/27.gif', 50),
(21, '/pic/podarki/132.gif', 50),
(22, '/pic/podarki/126.gif', 50),
(23, '/pic/podarki/127.gif', 50),
(24, '/pic/podarki/133.gif', 50),
(25, '/pic/podarki/26.gif', 50),
(26, '/pic/podarki/32.gif', 50),
(27, '/pic/podarki/18.gif', 50),
(28, '/pic/podarki/24.gif', 50),
(29, '/pic/podarki/30.gif', 50),
(30, '/pic/podarki/125.gif', 50),
(31, '/pic/podarki/131.gif', 50),
(32, '/pic/podarki/119.gif', 50),
(33, '/pic/podarki/118.gif', 50),
(34, '/pic/podarki/130.gif', 50),
(35, '/pic/podarki/124.gif', 50),
(36, '/pic/podarki/31.gif', 50),
(37, '/pic/podarki/25.gif', 50),
(38, '/pic/podarki/19.gif', 50),
(39, '/pic/podarki/42.gif', 50),
(40, '/pic/podarki/56.gif', 50),
(41, '/pic/podarki/4.gif', 50),
(42, '/pic/podarki/180.gif', 50),
(43, '/pic/podarki/81.gif', 50),
(44, '/pic/podarki/95.gif', 50),
(45, '/pic/podarki/143.gif', 50),
(46, '/pic/podarki/157.gif', 50),
(47, '/pic/podarki/156.gif', 50),
(48, '/pic/podarki/142.gif', 50),
(49, '/pic/podarki/94.gif', 50),
(50, '/pic/podarki/80.gif', 50),
(51, '/pic/podarki/181.gif', 50),
(52, '/pic/podarki/57.gif', 50),
(53, '/pic/podarki/5.gif', 50),
(54, '/pic/podarki/43.gif', 50),
(55, '/pic/podarki/7.gif', 50),
(56, '/pic/podarki/55.gif', 50),
(57, '/pic/podarki/41.gif', 50),
(58, '/pic/podarki/69.gif', 50),
(59, '/pic/podarki/183.gif', 50),
(60, '/pic/podarki/168.gif', 50),
(61, '/pic/podarki/96.gif', 50),
(62, '/pic/podarki/82.gif', 50),
(63, '/pic/podarki/154.gif', 50),
(64, '/pic/podarki/140.gif', 50),
(65, '/pic/podarki/141.gif', 50),
(66, '/pic/podarki/155.gif', 50),
(67, '/pic/podarki/83.gif', 50),
(68, '/pic/podarki/169.gif', 50),
(69, '/pic/podarki/97.gif', 50),
(70, '/pic/podarki/182.gif', 50),
(71, '/pic/podarki/68.gif', 50),
(72, '/pic/podarki/40.gif', 50),
(73, '/pic/podarki/6.gif', 50),
(74, '/pic/podarki/54.gif', 50),
(75, '/pic/podarki/186.gif', 50),
(76, '/pic/podarki/78.gif', 50),
(77, '/pic/podarki/50.gif', 50),
(78, '/pic/podarki/2.gif', 50),
(79, '/pic/podarki/44.gif', 50),
(80, '/pic/podarki/151.gif', 50),
(81, '/pic/podarki/145.gif', 50),
(82, '/pic/podarki/93.gif', 50),
(83, '/pic/podarki/179.gif', 50),
(84, '/pic/podarki/87.gif', 50),
(85, '/pic/podarki/178.gif', 50),
(86, '/pic/podarki/86.gif', 50),
(87, '/pic/podarki/92.gif', 50),
(88, '/pic/podarki/144.gif', 50),
(89, '/pic/podarki/150.gif', 50),
(90, '/pic/podarki/45.gif', 50),
(91, '/pic/podarki/51.gif', 50),
(92, '/pic/podarki/3.gif', 50),
(93, '/pic/podarki/187.gif', 50),
(94, '/pic/podarki/79.gif', 50),
(95, '/pic/podarki/185.gif', 50),
(96, '/pic/podarki/47.gif', 50),
(97, '/pic/podarki/1.gif', 50),
(98, '/pic/podarki/53.gif', 50),
(99, '/pic/podarki/146.gif', 50),
(100, '/pic/podarki/152.gif', 50),
(101, '/pic/podarki/84.gif', 50),
(102, '/pic/podarki/90.gif', 50),
(103, '/pic/podarki/91.gif', 50),
(104, '/pic/podarki/85.gif', 50),
(105, '/pic/podarki/153.gif', 50),
(106, '/pic/podarki/147.gif', 50),
(107, '/pic/podarki/52.gif', 50),
(108, '/pic/podarki/46.gif', 50),
(109, '/pic/podarki/184.gif', 50),
(110, '/pic/podarki/63.gif', 50),
(111, '/pic/podarki/77.gif', 50),
(112, '/pic/podarki/189.gif', 50),
(113, '/pic/podarki/162.gif', 50),
(114, '/pic/podarki/88.gif', 50),
(115, '/pic/podarki/176.gif', 50),
(116, '/pic/podarki/89.gif', 50),
(117, '/pic/podarki/177.gif', 50),
(118, '/pic/podarki/163.gif', 50),
(119, '/pic/podarki/76.gif', 50),
(120, '/pic/podarki/188.gif', 50),
(121, '/pic/podarki/62.gif', 50),
(122, '/pic/podarki/74.gif', 50),
(123, '/pic/podarki/60.gif', 50),
(124, '/pic/podarki/48.gif', 50),
(125, '/pic/podarki/149.gif', 50),
(126, '/pic/podarki/175.gif', 50),
(127, '/pic/podarki/161.gif', 50),
(128, '/pic/podarki/160.gif', 50),
(129, '/pic/podarki/174.gif', 50),
(130, '/pic/podarki/148.gif', 50),
(131, '/pic/podarki/49.gif', 50),
(132, '/pic/podarki/61.gif', 50),
(133, '/pic/podarki/75.gif', 50),
(134, '/pic/podarki/59.gif', 50),
(135, '/pic/podarki/71.gif', 50),
(136, '/pic/podarki/65.gif', 50),
(137, '/pic/podarki/170.gif', 50),
(138, '/pic/podarki/164.gif', 50),
(139, '/pic/podarki/158.gif', 50),
(140, '/pic/podarki/159.gif', 50),
(141, '/pic/podarki/165.gif', 50),
(142, '/pic/podarki/171.gif', 50),
(143, '/pic/podarki/64.gif', 50),
(144, '/pic/podarki/70.gif', 50),
(145, '/pic/podarki/58.gif', 50),
(146, '/pic/podarki/8.gif', 50),
(147, '/pic/podarki/66.gif', 50),
(148, '/pic/podarki/72.gif', 50),
(149, '/pic/podarki/99.gif', 50),
(150, '/pic/podarki/167.gif', 50),
(151, '/pic/podarki/173.gif', 50),
(152, '/pic/podarki/172.gif', 50),
(153, '/pic/podarki/98.gif', 50),
(154, '/pic/podarki/166.gif', 50),
(155, '/pic/podarki/73.gif', 50),
(156, '/pic/podarki/67.gif', 50),
(157, '/pic/podarki/9.gif', 50),
(158, '/pic/podarki/14.gif', 50),
(159, '/pic/podarki/28.gif', 50),
(160, '/pic/podarki/129.gif', 50),
(161, '/pic/podarki/101.gif', 50),
(162, '/pic/podarki/115.gif', 50),
(163, '/pic/podarki/114.gif', 50),
(164, '/pic/podarki/100.gif', 50),
(165, '/pic/podarki/128.gif', 50),
(166, '/pic/podarki/29.gif', 50),
(167, '/pic/podarki/15.gif', 50),
(168, '/pic/podarki/17.gif', 50),
(169, '/pic/podarki/116.gif', 50),
(170, '/pic/podarki/102.gif', 50),
(171, '/pic/podarki/103.gif', 50),
(172, '/pic/podarki/117.gif', 50),
(173, '/pic/podarki/16.gif', 50),
(174, '/pic/podarki/12.gif', 50),
(175, '/pic/podarki/113.gif', 50),
(176, '/pic/podarki/107.gif', 50),
(177, '/pic/podarki/106.gif', 50),
(178, '/pic/podarki/112.gif', 50),
(179, '/pic/podarki/13.gif', 50),
(180, '/pic/podarki/39.gif', 50),
(181, '/pic/podarki/11.gif', 50),
(182, '/pic/podarki/104.gif', 50),
(183, '/pic/podarki/110.gif', 50),
(184, '/pic/podarki/138.gif', 50),
(185, '/pic/podarki/139.gif', 50),
(186, '/pic/podarki/111.gif', 50),
(187, '/pic/podarki/105.gif', 50),
(188, '/pic/podarki/10.gif', 50),
(189, '/pic/podarki/38.gif', 50);

-- --------------------------------------------------------

--
-- Структура таблицы `podarok`
--

CREATE TABLE `podarok` (
  `id` int NOT NULL,
  `podarokid` int NOT NULL DEFAULT '1',
  `userid` int NOT NULL DEFAULT '0',
  `useradd` int NOT NULL DEFAULT '0',
  `date` datetime NOT NULL DEFAULT '0000-00-00 00:00:00',
  `text` text NOT NULL
) ENGINE=MyISAM DEFAULT CHARSET=utf8mb3;

-- --------------------------------------------------------

--
-- Структура таблицы `pollanswers`
--

CREATE TABLE `pollanswers` (
  `id` int UNSIGNED NOT NULL,
  `pollid` int UNSIGNED NOT NULL DEFAULT '0',
  `userid` int UNSIGNED NOT NULL DEFAULT '0',
  `selection` tinyint UNSIGNED NOT NULL DEFAULT '0'
) ENGINE=MyISAM DEFAULT CHARSET=utf8mb3;

--
-- Дамп данных таблицы `pollanswers`
--

INSERT INTO `pollanswers` (`id`, `pollid`, `userid`, `selection`) VALUES
(1, 1, 1, 2),
(2, 1, 2, 3),
(3, 1, 3, 1);

-- --------------------------------------------------------

--
-- Структура таблицы `polls`
--

CREATE TABLE `polls` (
  `id` int UNSIGNED NOT NULL,
  `added` datetime NOT NULL DEFAULT '0000-00-00 00:00:00',
  `question` varchar(255) NOT NULL,
  `option0` varchar(255) NOT NULL,
  `option1` varchar(255) NOT NULL,
  `option2` varchar(255) NOT NULL,
  `option3` varchar(255) NOT NULL,
  `option4` varchar(255) NOT NULL,
  `option5` varchar(255) NOT NULL,
  `option6` varchar(255) NOT NULL,
  `option7` varchar(255) NOT NULL,
  `option8` varchar(255) NOT NULL,
  `sort` enum('yes','no') NOT NULL DEFAULT 'yes'
) ENGINE=MyISAM DEFAULT CHARSET=utf8mb3;

--
-- Дамп данных таблицы `polls`
--

INSERT INTO `polls` (`id`, `added`, `question`, `option0`, `option1`, `option2`, `option3`, `option4`, `option5`, `option6`, `option7`, `option8`, `sort`) VALUES
(1, '2025-07-06 04:37:55', 'ыфвф', 'выв', 'фывф', 'фывф', 'фев', '', '', '', '', '', 'yes');

-- --------------------------------------------------------

--
-- Структура таблицы `posts`
--

CREATE TABLE `posts` (
  `id` int UNSIGNED NOT NULL,
  `topicid` int UNSIGNED NOT NULL DEFAULT '0',
  `userid` int UNSIGNED NOT NULL DEFAULT '0',
  `added` datetime DEFAULT NULL,
  `body` text,
  `editedby` int UNSIGNED NOT NULL DEFAULT '0',
  `editedat` datetime NOT NULL DEFAULT '0000-00-00 00:00:00'
) ENGINE=MyISAM DEFAULT CHARSET=utf8mb3;

-- --------------------------------------------------------

--
-- Структура таблицы `prof_guest`
--

CREATE TABLE `prof_guest` (
  `id` int NOT NULL,
  `uid` bigint NOT NULL,
  `profid` bigint NOT NULL,
  `time` int NOT NULL
) ENGINE=MyISAM DEFAULT CHARSET=utf8mb3;

--
-- Дамп данных таблицы `prof_guest`
--

INSERT INTO `prof_guest` (`id`, `uid`, `profid`, `time`) VALUES
(1, 1, 2, 1752165429),
(2, 2, 1, 1752165726),
(3, 1, 3, 1752162778),
(4, 3, 1, 1751818905);

-- --------------------------------------------------------

--
-- Структура таблицы `ratetorrents`
--

CREATE TABLE `ratetorrents` (
  `id` int NOT NULL,
  `rating_id` int NOT NULL,
  `rating_num` int NOT NULL,
  `userid` int NOT NULL
) ENGINE=MyISAM DEFAULT CHARSET=utf8mb3;

--
-- Дамп данных таблицы `ratetorrents`
--

INSERT INTO `ratetorrents` (`id`, `rating_id`, `rating_num`, `userid`) VALUES
(1, 1, 4, 2);

-- --------------------------------------------------------

--
-- Структура таблицы `rating`
--

CREATE TABLE `rating` (
  `id` int NOT NULL,
  `topic` int NOT NULL DEFAULT '0',
  `torrent` int NOT NULL DEFAULT '0',
  `rating` int NOT NULL DEFAULT '0',
  `user` int NOT NULL DEFAULT '0'
) ENGINE=MyISAM DEFAULT CHARSET=utf8mb3;

-- --------------------------------------------------------

--
-- Структура таблицы `readposts`
--

CREATE TABLE `readposts` (
  `id` int UNSIGNED NOT NULL,
  `userid` int UNSIGNED NOT NULL DEFAULT '0',
  `topicid` int UNSIGNED NOT NULL DEFAULT '0',
  `lastpostread` int UNSIGNED NOT NULL DEFAULT '0'
) ENGINE=MyISAM DEFAULT CHARSET=utf8mb3;

-- --------------------------------------------------------

--
-- Структура таблицы `referals`
--

CREATE TABLE `referals` (
  `id` int UNSIGNED NOT NULL,
  `ref` int NOT NULL,
  `ip` varchar(15) NOT NULL,
  `from_url` varchar(255) NOT NULL DEFAULT 'Не установлено ',
  `added` datetime NOT NULL DEFAULT '0000-00-00 00:00:00'
) ENGINE=MyISAM DEFAULT CHARSET=utf8mb3;

-- --------------------------------------------------------

--
-- Структура таблицы `shoutbox`
--

CREATE TABLE `shoutbox` (
  `id` int NOT NULL,
  `userid` int NOT NULL DEFAULT '0',
  `class` int NOT NULL DEFAULT '0',
  `username` varchar(25) NOT NULL,
  `date` int NOT NULL DEFAULT '0',
  `text` text NOT NULL,
  `warned` enum('yes','no') NOT NULL DEFAULT 'no',
  `donor` enum('yes','no') NOT NULL DEFAULT 'no',
  `gender` enum('1','2','3') NOT NULL DEFAULT '1',
  `enabled` enum('yes','no') NOT NULL DEFAULT 'yes',
  `parked` enum('yes','no') NOT NULL DEFAULT 'no'
) ENGINE=MyISAM DEFAULT CHARSET=utf8mb3;

--
-- Дамп данных таблицы `shoutbox`
--

INSERT INTO `shoutbox` (`id`, `userid`, `class`, `username`, `date`, `text`, `warned`, `donor`, `gender`, `enabled`, `parked`) VALUES
(357174, 1, 70, 'webnet', 1751721598, 'РРКККСДњZњАР     ', 'no', 'no', '1', 'yes', 'no'),
(357173, 1, 70, 'webnet', 1751721595, 'привет', 'no', 'no', '1', 'yes', 'no'),
(357175, 1, 70, 'webnet', 1751721604, 'фывф', 'no', 'no', '1', 'yes', 'no'),
(357176, 1, 70, 'webnet', 1751721606, 'привет', 'no', 'no', '1', 'yes', 'no'),
(357177, 1, 70, 'webnet', 1751721609, 'РРКККСДњZњАР     ', 'no', 'no', '1', 'yes', 'no'),
(357178, 1, 70, 'webnet', 1751721659, 'привет', 'no', 'no', '1', 'yes', 'no'),
(357179, 1, 70, 'webnet', 1751721661, 'тест!', 'no', 'no', '1', 'yes', 'no'),
(357180, 1, 70, 'webnet', 1751721664, 'хм!', 'no', 'no', '1', 'yes', 'no'),
(357181, 1, 70, 'webnet', 1751721692, 'привет!', 'no', 'no', '1', 'yes', 'no'),
(357182, 1, 70, 'webnet', 1751721694, 'как твои дела?', 'no', 'no', '1', 'yes', 'no'),
(357183, 1, 70, 'webnet', 1751721814, ' <img border=\"0px\" src=\"http://localhost:8080/pic/smilies/1.gif\" /> ', 'no', 'no', '1', 'yes', 'no'),
(357184, 1, 70, 'webnet', 1751721903, ' <img border=\"0px\" src=\"http://localhost:8080/pic/smilies/221.gif\" /> ', 'no', 'no', '1', 'yes', 'no'),
(357185, 1, 70, 'webnet', 1751731098, ' <img border=\"0px\" src=\"http://localhost:8080/pic/smilies/28.gif\" /> ', 'no', 'no', '1', 'yes', 'no'),
(357186, 1, 70, 'webnet', 1751743219, ' <img border=\"0px\" src=\"http://localhost:8080/pic/smilies/45.gif\" /> ', 'no', 'no', '1', 'yes', 'no'),
(357187, 1, 70, 'webnet', 1751776937, 'привет', 'no', 'no', '1', 'yes', 'no'),
(357188, 1, 70, 'webnet', 1751776940, 'РРКККСДњZњАР     ', 'no', 'no', '1', 'yes', 'no'),
(357189, 1, 70, 'webnet', 1751777327, 'привет!', 'no', 'no', '1', 'yes', 'no'),
(357190, 1, 70, 'webnet', 1751777333, 'РРКККСВњZZПАМОӑКРРК', 'no', 'no', '1', 'yes', 'no'),
(357191, 1, 70, 'webnet', 1751777553, 'привет', 'no', 'no', '1', 'yes', 'no'),
(357192, 1, 70, 'webnet', 1751777701, 'привет!', 'no', 'no', '1', 'yes', 'no'),
(357193, 1, 70, 'webnet', 1751777705, 'интереA=&gt;', 'no', 'no', '1', 'yes', 'no'),
(357194, 1, 70, 'webnet', 1751777710, 'ывы', 'no', 'no', '1', 'yes', 'no'),
(357195, 1, 70, 'webnet', 1751777712, 'фывDK', 'no', 'no', '1', 'yes', 'no'),
(357196, 1, 70, 'webnet', 1751777713, 'фывфывф', 'no', 'no', '1', 'yes', 'no'),
(357197, 1, 70, 'webnet', 1751777715, 'ыфывфывф', 'no', 'no', '1', 'yes', 'no'),
(357198, 1, 70, 'webnet', 1751777716, 'ывфывф', 'no', 'no', '1', 'yes', 'no'),
(357199, 1, 70, 'webnet', 1751777718, 'фывфывф', 'no', 'no', '1', 'yes', 'no'),
(357200, 1, 70, 'webnet', 1751777719, 'фывфывф', 'no', 'no', '1', 'yes', 'no'),
(357201, 1, 70, 'webnet', 1751777761, 'ысвы', 'no', 'no', '1', 'yes', 'no'),
(357202, 1, 70, 'webnet', 1751777762, 'ячсп', 'no', 'no', '1', 'yes', 'no'),
(357203, 1, 70, 'webnet', 1751777764, 'привет!', 'no', 'no', '1', 'yes', 'no'),
(357204, 1, 70, 'webnet', 1751777765, 'РРКККСВњZZ', 'no', 'no', '1', 'yes', 'no'),
(357205, 1, 70, 'webnet', 1751777862, 'привет', 'no', 'no', '1', 'yes', 'no'),
(357206, 1, 70, 'webnet', 1751777863, 'рабоатет?', 'no', 'no', '1', 'yes', 'no'),
(357207, 1, 70, 'webnet', 1751777865, 'интересно?', 'no', 'no', '1', 'yes', 'no'),
(357208, 1, 70, 'webnet', 1751777866, 'фывDK', 'no', 'no', '1', 'yes', 'no'),
(357209, 1, 70, 'webnet', 1751777866, 'фывDK', 'no', 'no', '1', 'yes', 'no'),
(357210, 1, 70, 'webnet', 1751777866, 'в', 'no', 'no', '1', 'yes', 'no'),
(357211, 1, 70, 'webnet', 1751777867, 'фыв', 'no', 'no', '1', 'yes', 'no'),
(357212, 1, 70, 'webnet', 1751777867, 'фы', 'no', 'no', '1', 'yes', 'no'),
(357213, 1, 70, 'webnet', 1751777867, 'вф', 'no', 'no', '1', 'yes', 'no'),
(357214, 1, 70, 'webnet', 1751779251, 'привет', 'no', 'no', '1', 'yes', 'no'),
(357215, 1, 70, 'webnet', 1751779253, 'провер85&lt;', 'no', 'no', '1', 'yes', 'no'),
(357216, 1, 70, 'webnet', 1751779254, 'ваы', 'no', 'no', '1', 'yes', 'no'),
(357217, 1, 70, 'webnet', 1751779255, 'ыва', 'no', 'no', '1', 'yes', 'no'),
(357218, 1, 70, 'webnet', 1751779256, 'привет', 'no', 'no', '1', 'yes', 'no'),
(357219, 1, 70, 'webnet', 1751779272, 'проверка чата', 'no', 'no', '1', 'yes', 'no'),
(357220, 1, 70, 'webnet', 1751779274, 'привет!', 'no', 'no', '1', 'yes', 'no'),
(357221, 1, 70, 'webnet', 1751779278, 'как твои дела?', 'no', 'no', '1', 'yes', 'no'),
(357222, 1, 70, 'webnet', 1751779281, 'во работает!', 'no', 'no', '1', 'yes', 'no'),
(357223, 1, 70, 'webnet', 1751779282, 'вывDK', 'no', 'no', '1', 'yes', 'no'),
(357224, 1, 70, 'webnet', 1751779282, 'вфыв', 'no', 'no', '1', 'yes', 'no'),
(357225, 1, 70, 'webnet', 1751779283, 'ф', 'no', 'no', '1', 'yes', 'no'),
(357226, 1, 70, 'webnet', 1751779283, 'ываы', 'no', 'no', '1', 'yes', 'no'),
(357227, 1, 70, 'webnet', 1751779284, 'ываы', 'no', 'no', '1', 'yes', 'no'),
(357228, 1, 70, 'webnet', 1751779411, 'привет', 'no', 'no', '1', 'yes', 'no'),
(357229, 1, 70, 'webnet', 1751779415, 'как твои дела? что нового?', 'no', 'no', '1', 'yes', 'no'),
(357230, 1, 70, 'webnet', 1751779416, 'ыфвDK', 'no', 'no', '1', 'yes', 'no'),
(357231, 1, 70, 'webnet', 1751779417, 'вфыв', 'no', 'no', '1', 'yes', 'no'),
(357232, 1, 70, 'webnet', 1751779417, 'фыв', 'no', 'no', '1', 'yes', 'no'),
(357233, 1, 70, 'webnet', 1751779417, 'фывф', 'no', 'no', '1', 'yes', 'no'),
(357234, 1, 70, 'webnet', 1751779417, 'ыв', 'no', 'no', '1', 'yes', 'no'),
(357235, 1, 70, 'webnet', 1751779418, 'ыа', 'no', 'no', '1', 'yes', 'no'),
(357236, 1, 70, 'webnet', 1751779418, 'ыв', 'no', 'no', '1', 'yes', 'no'),
(357237, 1, 70, 'webnet', 1751779418, 'а', 'no', 'no', '1', 'yes', 'no'),
(357238, 1, 70, 'webnet', 1751779418, 'ыва', 'no', 'no', '1', 'yes', 'no'),
(357239, 1, 70, 'webnet', 1751779418, 'фыв', 'no', 'no', '1', 'yes', 'no'),
(357240, 1, 70, 'webnet', 1751779418, 'фыв', 'no', 'no', '1', 'yes', 'no'),
(357241, 1, 70, 'webnet', 1751779419, 'а', 'no', 'no', '1', 'yes', 'no'),
(357242, 1, 70, 'webnet', 1751779419, 'ва', 'no', 'no', '1', 'yes', 'no'),
(357243, 1, 70, 'webnet', 1751779423, 'ы <img border=\"0px\" src=\"http://localhost:8080/pic/smilies/23.gif\" /> ', 'no', 'no', '1', 'yes', 'no'),
(357244, 1, 70, 'webnet', 1751779609, ' <img border=\"0px\" src=\"http://localhost:8080/pic/smilies/30.gif\" /> ', 'no', 'no', '1', 'yes', 'no'),
(357245, 2, 0, 'merdox', 1751784540, ' <img border=\"0px\" src=\"http://localhost:8080/pic/smilies/28.gif\" /> ', 'no', 'no', '2', 'yes', 'no'),
(357246, 2, 0, 'merdox', 1751784546, 'не понял', 'no', 'no', '2', 'yes', 'no'),
(357247, 2, 0, 'merdox', 1751784551, 'не работате!!!', 'no', 'no', '2', 'yes', 'no'),
(357248, 1, 70, 'webnet', 1751806090, ' <img border=\"0px\" src=\"http://localhost:8080/pic/smilies/33.gif\" /> ', 'no', 'no', '1', 'yes', 'no'),
(357249, 1, 70, 'webnet', 1751806096, ' <img border=\"0px\" src=\"http://localhost:8080/pic/smilies/93.gif\" /> ', 'no', 'no', '1', 'yes', 'no'),
(357250, 1, 70, 'webnet', 1751810583, 'РРКККСВњZZ', 'no', 'no', '1', 'yes', 'no'),
(357251, 1, 70, 'webnet', 1751810586, 'php 8!', 'no', 'no', '1', 'yes', 'no'),
(357252, 1, 70, 'webnet', 1751810590, 'work???', 'no', 'no', '1', 'yes', 'no'),
(357253, 1, 70, 'webnet', 1751810593, 'РРКККСВњZZ', 'no', 'no', '1', 'yes', 'no'),
(357254, 1, 70, 'webnet', 1751810601, 'опять Кириллу не воспринимает', 'no', 'no', '1', 'yes', 'no'),
(357255, 1, 70, 'webnet', 1751900034, ' <img border=\"0px\" src=\"http://localhost:8080/pic/smilies/15.gif\" /> ', 'no', 'no', '1', 'yes', 'no'),
(357256, 1, 70, 'webnet', 1751995146, 'тестирC5&lt;', 'no', 'no', '1', 'yes', 'no'),
(357257, 1, 70, 'webnet', 1751995150, 'теструем', 'no', 'no', '1', 'yes', 'no'),
(357258, 1, 70, 'webnet', 1751995556, 'тесB8', 'no', 'no', '1', 'yes', 'no'),
(357259, 1, 70, 'webnet', 1751995558, 'привет', 'no', 'no', '1', 'yes', 'no'),
(357260, 1, 70, 'webnet', 1751995562, 'РРКККСВњZZ', 'no', 'no', '1', 'yes', 'no'),
(357261, 1, 70, 'webnet', 1751995850, 'ĀāĀĀĀāāĀāĀĀĀ', 'no', 'no', '1', 'yes', 'no'),
(357262, 1, 70, 'webnet', 1751998208, 'hello ', 'no', 'no', '1', 'yes', 'no'),
(357263, 1, 70, 'webnet', 1751998209, 'привет', 'no', 'no', '1', 'yes', 'no'),
(357264, 1, 70, 'webnet', 1751998214, ' <img border=\"0px\" src=\"http://localhost:8080/pic/smilies/26.gif\" /> ', 'no', 'no', '1', 'yes', 'no'),
(357265, 1, 70, 'webnet', 1752345850, ' <img border=\"0px\" src=\"http://localhost:8080/pic/smilies/29.gif\" /> ', 'no', 'no', '1', 'yes', 'no'),
(357266, 1, 70, 'webnet', 1752345870, '<img class=\"linked-image\" src=\"https://upload.wikimedia.org/wikipedia/ru/b/bc/Poster_Inception_film_2010.jpg\" border=\"0\" alt=\"https://upload.wikimedia.org/wikipedia/ru/b/bc/Poster_Inception_film_2010.jpg\" title=\"https://upload.wikimedia.org/wikipedia/ru/b/bc/Poster_Inception_film_2010.jpg\"/>', 'no', 'no', '1', 'yes', 'no');

-- --------------------------------------------------------

--
-- Структура таблицы `sitelog`
--

CREATE TABLE `sitelog` (
  `id` int UNSIGNED NOT NULL,
  `added` datetime DEFAULT NULL,
  `color` varchar(11) NOT NULL DEFAULT 'transparent',
  `txt` text,
  `type` varchar(8) NOT NULL DEFAULT 'tracker'
) ENGINE=MyISAM DEFAULT CHARSET=utf8mb3;

--
-- Дамп данных таблицы `sitelog`
--

INSERT INTO `sitelog` (`id`, `added`, `color`, `txt`, `type`) VALUES
(9, '2025-07-05 12:23:47', 'transparent', '=== Начало login ===', 'tracker'),
(10, '2025-07-05 12:24:34', 'transparent', '=== Начало login ===', 'tracker'),
(11, '2025-07-05 12:24:34', 'transparent', 'Попытка логина: webnet', 'tracker'),
(12, '2025-07-05 12:24:34', 'transparent', 'Успешный вход для webnet, редирект...', 'tracker'),
(13, '2025-07-05 19:45:19', 'FFFFFF', 'Зарегистрирован новый пользователь merdox', 'tracker'),
(14, '2025-07-06 05:58:32', '5DDB6E', 'Торрент #1 (Canary Black / Досье «Чёрная канарейка» (2025) DVDRip) был загружен пользователем [url=http://localhost:8080/user/id1]webnet[/url]', 'torrent'),
(15, '2025-07-06 06:22:30', 'f99c57', 'Торрент \'Canary Black / Досье «Чёрная канарейка» (2025) DVDRip\' был отредактирован пользователем webnet', 'torrent'),
(16, '2025-07-06 07:46:50', 'f99c57', 'Торрент \'Canary Black / Досье «Чёрная канарейка» (2025) DVDRip\' был отредактирован пользователем webnet', 'torrent'),
(17, '2025-07-06 08:09:06', 'f99c57', 'Торрент \'Canary Black / Досье «Чёрная канарейка» (2025) DVDRip\' был отредактирован пользователем webnet', 'torrent'),
(18, '2025-07-08 16:33:07', 'green', 'Очистка системы была успешно произведена @ July 8, 2025, 4:33 pm', 'system'),
(19, '2025-07-08 17:17:21', 'green', 'Очистка системы была успешно произведена @ July 8, 2025, 5:17 pm', 'system'),
(20, '2025-07-08 17:48:14', 'green', 'Очистка системы была успешно произведена @ July 8, 2025, 5:48 pm', 'system');

-- --------------------------------------------------------

--
-- Структура таблицы `snatched`
--

CREATE TABLE `snatched` (
  `id` int NOT NULL,
  `userid` int DEFAULT '0',
  `torrent` int UNSIGNED NOT NULL DEFAULT '0',
  `port` smallint UNSIGNED NOT NULL DEFAULT '0',
  `uploaded` bigint UNSIGNED NOT NULL DEFAULT '0',
  `downloaded` bigint UNSIGNED NOT NULL DEFAULT '0',
  `seeder` enum('yes','no') NOT NULL DEFAULT 'no',
  `connectable` enum('yes','no') NOT NULL DEFAULT 'yes',
  `finished` enum('yes','no') NOT NULL DEFAULT 'no'
) ENGINE=MyISAM DEFAULT CHARSET=utf8mb3;

-- --------------------------------------------------------

--
-- Структура таблицы `stylesheets`
--

CREATE TABLE `stylesheets` (
  `id` int UNSIGNED NOT NULL,
  `uri` varchar(255) NOT NULL,
  `name` varchar(64) NOT NULL
) ENGINE=MyISAM DEFAULT CHARSET=utf8mb3;

--
-- Дамп данных таблицы `stylesheets`
--

INSERT INTO `stylesheets` (`id`, `uri`, `name`) VALUES
(3, 'Anime', 'Anime');

-- --------------------------------------------------------

--
-- Структура таблицы `subscribe`
--

CREATE TABLE `subscribe` (
  `id` int NOT NULL,
  `userid` bigint NOT NULL,
  `torid` int NOT NULL
) ENGINE=MyISAM DEFAULT CHARSET=utf8mb3;

--
-- Дамп данных таблицы `subscribe`
--

INSERT INTO `subscribe` (`id`, `userid`, `torid`) VALUES
(1, 1, 1);

-- --------------------------------------------------------

--
-- Структура таблицы `tags`
--

CREATE TABLE `tags` (
  `id` int UNSIGNED NOT NULL,
  `category` int NOT NULL DEFAULT '0',
  `name` varchar(30) NOT NULL,
  `howmuch` int NOT NULL DEFAULT '0'
) ENGINE=MyISAM DEFAULT CHARSET=utf8mb3;

--
-- Дамп данных таблицы `tags`
--

INSERT INTO `tags` (`id`, `category`, `name`, `howmuch`) VALUES
(1, 6, 'пишем теги', 1),
(2, 6, 'еще немного тэгов', 1);

-- --------------------------------------------------------

--
-- Структура таблицы `thanks`
--

CREATE TABLE `thanks` (
  `id` int NOT NULL,
  `torrentid` int NOT NULL DEFAULT '0',
  `userid` int NOT NULL DEFAULT '0',
  `added` datetime NOT NULL DEFAULT '0000-00-00 00:00:00',
  `touserid` int UNSIGNED NOT NULL DEFAULT '0'
) ENGINE=MyISAM DEFAULT CHARSET=utf8mb3;

--
-- Дамп данных таблицы `thanks`
--

INSERT INTO `thanks` (`id`, `torrentid`, `userid`, `added`, `touserid`) VALUES
(1, 1, 2, '2025-07-06 06:59:32', 1);

-- --------------------------------------------------------

--
-- Структура таблицы `topics`
--

CREATE TABLE `topics` (
  `id` int UNSIGNED NOT NULL,
  `userid` int UNSIGNED NOT NULL DEFAULT '0',
  `subject` varchar(255) CHARACTER SET utf8mb3 COLLATE utf8mb3_bin DEFAULT NULL,
  `locked` enum('yes','no') CHARACTER SET utf8mb3 COLLATE utf8mb3_bin NOT NULL DEFAULT 'no',
  `forumid` int UNSIGNED NOT NULL DEFAULT '0',
  `lastpost` int UNSIGNED NOT NULL DEFAULT '0',
  `sticky` enum('yes','no') CHARACTER SET utf8mb3 COLLATE utf8mb3_bin NOT NULL DEFAULT 'no',
  `views` int UNSIGNED NOT NULL DEFAULT '0',
  `ratingsum` int NOT NULL DEFAULT '0',
  `numratings` int NOT NULL DEFAULT '0'
) ENGINE=MyISAM DEFAULT CHARSET=utf8mb3;

-- --------------------------------------------------------

--
-- Структура таблицы `torrents`
--

CREATE TABLE `torrents` (
  `id` int UNSIGNED NOT NULL,
  `info_hash` varbinary(40) NOT NULL DEFAULT '',
  `name` varchar(255) NOT NULL,
  `filename` varchar(255) NOT NULL,
  `save_as` varchar(255) NOT NULL,
  `search_text` text NOT NULL,
  `descr` text NOT NULL,
  `ori_descr` text NOT NULL,
  `image1` text CHARACTER SET utf8mb3 COLLATE utf8mb3_bin NOT NULL,
  `category` int UNSIGNED NOT NULL DEFAULT '0',
  `size` bigint UNSIGNED NOT NULL DEFAULT '0',
  `added` int NOT NULL DEFAULT '0',
  `type` enum('single','multi') NOT NULL DEFAULT 'single',
  `numfiles` int UNSIGNED NOT NULL DEFAULT '0',
  `comments` int UNSIGNED NOT NULL DEFAULT '0',
  `ratio` int UNSIGNED NOT NULL DEFAULT '0',
  `checkcomm` enum('yes','no') NOT NULL,
  `views` int UNSIGNED NOT NULL DEFAULT '0',
  `hits` int UNSIGNED NOT NULL DEFAULT '0',
  `times_completed` int UNSIGNED NOT NULL DEFAULT '0',
  `leechers` int UNSIGNED NOT NULL DEFAULT '0',
  `seeders` int UNSIGNED NOT NULL DEFAULT '0',
  `last_action` int NOT NULL DEFAULT '0',
  `last_reseed` int NOT NULL DEFAULT '0',
  `visible` enum('yes','no') NOT NULL DEFAULT 'yes',
  `banned` enum('yes','no') NOT NULL DEFAULT 'no',
  `free` int UNSIGNED NOT NULL,
  `owner` int UNSIGNED NOT NULL DEFAULT '0',
  `owner_name` varchar(40) NOT NULL,
  `owner_class` int NOT NULL,
  `sticky` enum('yes','no') NOT NULL DEFAULT 'no',
  `tags` text NOT NULL,
  `modded` enum('yes','no') NOT NULL DEFAULT 'no',
  `modby` int UNSIGNED DEFAULT '0',
  `modname` text NOT NULL,
  `modtime` datetime NOT NULL DEFAULT '0000-00-00 00:00:00',
  `screen1` text NOT NULL,
  `screen2` text NOT NULL,
  `screen3` text NOT NULL,
  `karma` int DEFAULT '0'
) ENGINE=MyISAM DEFAULT CHARSET=utf8mb3;

--
-- Дамп данных таблицы `torrents`
--

INSERT INTO `torrents` (`id`, `info_hash`, `name`, `filename`, `save_as`, `search_text`, `descr`, `ori_descr`, `image1`, `category`, `size`, `added`, `type`, `numfiles`, `comments`, `ratio`, `checkcomm`, `views`, `hits`, `times_completed`, `leechers`, `seeders`, `last_action`, `last_reseed`, `visible`, `banned`, `free`, `owner`, `owner_name`, `owner_class`, `sticky`, `tags`, `modded`, `modby`, `modname`, `modtime`, `screen1`, `screen2`, `screen3`, `karma`) VALUES
(1, 0x35393162363364333931346363393631353863663166313763313861346234393532343639623431, 'Canary Black / Досье «Чёрная канарейка» (2025) DVDRip', '[kinozal.tv]id2097627.torrent', 'Досье «Чёрная канарейка».2024.BDRemux.1080p.R.G. Goldenshara.mkv', '[kinozal.tv]id2097627 Досье «Чёрная канарейка».2024.BDRemux.1080p.R.G. Goldenshara.mkv', '[b]Оригинальное название:[/b] {название}\r\n[b]Английское название:[/b] {название}\r\n[b]Русское название:[/b] {название}\r\n\r\n[b]Год выпуска:[/b] {год}\r\n[b]Жанр:[/b] {жанры}\r\n[b]Продолжительность:[/b] {xx эпизодов по yy мин.}\r\n[b]Студия:[/b] {студия}\r\n[b]Режиссёр:[/b] {режиссёр}\r\n[b]Дорожки[/b] {список пиктограмм дорожек}\r\n[b]Описание:[/b] {описание}\r\n[b]Автор оригинала:[/b] {опционально}\r\n[b]Снято по манге[/b]: {опционально}\r\n[b]Ссылка на AniDB:[/b] {ссылка}\r\n[b]Ссылка на World Art:[/b] {опционально}\r\n   \r\n[u][b]Техданные:[/b][/u]\r\n[b]Контейнер:[/b] {AVI|MKV|OGM|MP4|VOB|ISO|BIN}\r\n[b]Видео:[/b] {кодек, разрешение, частота кадров, битрейт}\r\n[b]Аудио 1:[/b] {RU|EN|JP}: {VO|MVO|DUB, кодек, каналы, частота, битрейт}\r\n[b]Аудио 2:[/b] {RU|EN|JP}: {VO|MVO|DUB, кодек, каналы, частота, битрейт}\r\n[b]Субтитры 1:[/b] {RU|EN|JP}: {soft|hard}, {SRT|ASS|SUB|...}\r\n[b]Субтитры 2:[/b] {RU|EN|JP}: {soft|hard}, {SRT|ASS|SUB|...}\r\n\r\n[b]Авторы и участники:[/b]\r\n[b]Рип:[/b] {автор или группа}\r\n[b]Озвучка:[/b] {автор или группа}\r\n[b]Субтитры:[/b] {автор или группа}\r\n\r\n[b]Источник:[/b] {источник}\r\n\r\n[b]Дополнительная информация:[/b] {опционально}\r\n\r\n\r\n\r\n[b]Личная оценка: [/b]10 из 10', '[b]Оригинальное название:[/b] {название}\r\n[b]Английское название:[/b] {название}\r\n[b]Русское название:[/b] {название}\r\n\r\n[b]Год выпуска:[/b] {год}\r\n[b]Жанр:[/b] {жанры}\r\n[b]Продолжительность:[/b] {xx эпизодов по yy мин.}\r\n[b]Студия:[/b] {студия}\r\n[b]Режиссёр:[/b] {режиссёр}\r\n[b]Дорожки[/b] {список пиктограмм дорожек}\r\n[b]Описание:[/b] {описание}\r\n[b]Автор оригинала:[/b] {опционально}\r\n[b]Снято по манге[/b]: {опционально}\r\n[b]Ссылка на AniDB:[/b] {ссылка}\r\n[b]Ссылка на World Art:[/b] {опционально}\r\n   \r\n[u][b]Техданные:[/b][/u]\r\n[b]Контейнер:[/b] {AVI|MKV|OGM|MP4|VOB|ISO|BIN}\r\n[b]Видео:[/b] {кодек, разрешение, частота кадров, битрейт}\r\n[b]Аудио 1:[/b] {RU|EN|JP}: {VO|MVO|DUB, кодек, каналы, частота, битрейт}\r\n[b]Аудио 2:[/b] {RU|EN|JP}: {VO|MVO|DUB, кодек, каналы, частота, битрейт}\r\n[b]Субтитры 1:[/b] {RU|EN|JP}: {soft|hard}, {SRT|ASS|SUB|...}\r\n[b]Субтитры 2:[/b] {RU|EN|JP}: {soft|hard}, {SRT|ASS|SUB|...}\r\n\r\n[b]Авторы и участники:[/b]\r\n[b]Рип:[/b] {автор или группа}\r\n[b]Озвучка:[/b] {автор или группа}\r\n[b]Субтитры:[/b] {автор или группа}\r\n\r\n[b]Источник:[/b] {источник}\r\n\r\n[b]Дополнительная информация:[/b] {опционально}\r\n\r\n\r\n\r\n[b]Личная оценка: [/b]10 из 10', 'https://avatars.mds.yandex.net/get-kinopoisk-image/4716873/630eacd0-a995-4e3b-a32b-2fff434ca900/300x450', 6, 20063598757, 1751789346, 'single', 1, 9, 80, 'yes', 124, 2, 0, 0, 0, 1751781512, 0, 'yes', 'no', 0, 1, 'webnet', 70, 'no', 'пишем теги,еще немного тэгов', 'yes', 1, 'webnet', '2025-07-06 06:24:22', '', '', '', 0);

-- --------------------------------------------------------

--
-- Структура таблицы `torrents_parsed`
--

CREATE TABLE `torrents_parsed` (
  `torrent` int NOT NULL,
  `descr_parsed` text NOT NULL,
  `hash` varchar(32) NOT NULL
) ENGINE=MyISAM DEFAULT CHARSET=utf8mb3;

-- --------------------------------------------------------

--
-- Структура таблицы `uploadapp`
--

CREATE TABLE `uploadapp` (
  `id` int UNSIGNED NOT NULL,
  `active` tinyint(1) NOT NULL DEFAULT '1',
  `userid` int NOT NULL DEFAULT '0',
  `applied` datetime NOT NULL DEFAULT '0000-00-00 00:00:00',
  `grpacct` tinyint(1) NOT NULL DEFAULT '0',
  `grpname` varchar(100) NOT NULL,
  `grpdes` varchar(4) NOT NULL,
  `content` varchar(1000) NOT NULL,
  `img` varchar(100) NOT NULL,
  `comment` varchar(100) NOT NULL,
  `seeding` tinyint(1) NOT NULL DEFAULT '0',
  `othergrps` tinyint(1) NOT NULL DEFAULT '0',
  `seedtime` varchar(100) NOT NULL,
  `votes` text NOT NULL
) ENGINE=MyISAM DEFAULT CHARSET=utf8mb3;

-- --------------------------------------------------------

--
-- Структура таблицы `users`
--

CREATE TABLE `users` (
  `id` bigint UNSIGNED NOT NULL,
  `username` varchar(40) NOT NULL,
  `old_password` varchar(40) NOT NULL,
  `passhash` varchar(32) NOT NULL,
  `secret` varchar(20) NOT NULL,
  `email` varchar(80) NOT NULL,
  `status` enum('pending','confirmed') NOT NULL DEFAULT 'pending',
  `added` datetime NOT NULL DEFAULT '0000-00-00 00:00:00',
  `last_login` datetime NOT NULL DEFAULT '0000-00-00 00:00:00',
  `last_access` datetime NOT NULL DEFAULT '0000-00-00 00:00:00',
  `forum_access` datetime NOT NULL DEFAULT '0000-00-00 00:00:00',
  `last_upload` datetime NOT NULL DEFAULT '0000-00-00 00:00:00',
  `editsecret` varchar(20) NOT NULL,
  `privacy` enum('strong','normal','low') NOT NULL DEFAULT 'normal',
  `hiden` enum('yes','no') NOT NULL DEFAULT 'no',
  `stylesheet` int DEFAULT '3',
  `page` text,
  `info` varchar(500) DEFAULT NULL,
  `acceptpms` enum('yes','friends','no') NOT NULL DEFAULT 'yes',
  `ip` varchar(15) NOT NULL,
  `class` tinyint UNSIGNED NOT NULL DEFAULT '0',
  `override_class` tinyint UNSIGNED NOT NULL DEFAULT '255',
  `avatar` varchar(100) NOT NULL,
  `photo` varchar(100) NOT NULL DEFAULT '0',
  `icq` varchar(255) NOT NULL,
  `skype` varchar(255) NOT NULL,
  `uploaded` bigint UNSIGNED NOT NULL DEFAULT '0',
  `downloaded` bigint UNSIGNED NOT NULL DEFAULT '0',
  `bonus` decimal(7,2) NOT NULL DEFAULT '0.00',
  `title` varchar(30) NOT NULL,
  `country` int UNSIGNED NOT NULL DEFAULT '0',
  `notifs` varchar(100) NOT NULL,
  `modcomment` text,
  `enabled` enum('yes','no') NOT NULL DEFAULT 'yes',
  `parked` enum('yes','no') NOT NULL DEFAULT 'no',
  `avatars` enum('yes','no') NOT NULL DEFAULT 'yes',
  `donor` enum('yes','no') NOT NULL DEFAULT 'no',
  `warned` enum('yes','no') NOT NULL DEFAULT 'no',
  `chat_ban` enum('yes','no') NOT NULL DEFAULT 'no',
  `chat_ban_until` datetime NOT NULL DEFAULT '0000-00-00 00:00:00',
  `upload` enum('yes','no') NOT NULL DEFAULT 'yes',
  `warneduntil` datetime NOT NULL DEFAULT '0000-00-00 00:00:00',
  `leechwarn` enum('yes','no') NOT NULL DEFAULT 'no',
  `leechwarnuntil` datetime NOT NULL DEFAULT '0000-00-00 00:00:00',
  `lastwarned` datetime NOT NULL,
  `deletepms` enum('yes','no') NOT NULL DEFAULT 'yes',
  `savepms` enum('yes','no') NOT NULL DEFAULT 'no',
  `gender` enum('1','2','3') NOT NULL DEFAULT '1',
  `birthday` date DEFAULT '0000-00-00',
  `passkey` varchar(32) NOT NULL,
  `language` varchar(255) NOT NULL DEFAULT 'russian',
  `passkey_ip` varchar(15) NOT NULL,
  `moderated` int NOT NULL DEFAULT '0',
  `viewfriends` enum('yes','no') NOT NULL DEFAULT 'yes',
  `timezone` smallint NOT NULL DEFAULT '0',
  `dst` tinyint NOT NULL DEFAULT '-60',
  `vipuntil` datetime NOT NULL DEFAULT '0000-00-00 00:00:00',
  `oldclass` int NOT NULL DEFAULT '0',
  `karma` int NOT NULL DEFAULT '0',
  `bal` int UNSIGNED NOT NULL DEFAULT '0',
  `animedna_bonus` date NOT NULL DEFAULT '0000-00-00'
) ENGINE=MyISAM DEFAULT CHARSET=utf8mb3;

--
-- Дамп данных таблицы `users`
--

INSERT INTO `users` (`id`, `username`, `old_password`, `passhash`, `secret`, `email`, `status`, `added`, `last_login`, `last_access`, `forum_access`, `last_upload`, `editsecret`, `privacy`, `hiden`, `stylesheet`, `page`, `info`, `acceptpms`, `ip`, `class`, `override_class`, `avatar`, `photo`, `icq`, `skype`, `uploaded`, `downloaded`, `bonus`, `title`, `country`, `notifs`, `modcomment`, `enabled`, `parked`, `avatars`, `donor`, `warned`, `chat_ban`, `chat_ban_until`, `upload`, `warneduntil`, `leechwarn`, `leechwarnuntil`, `lastwarned`, `deletepms`, `savepms`, `gender`, `birthday`, `passkey`, `language`, `passkey_ip`, `moderated`, `viewfriends`, `timezone`, `dst`, `vipuntil`, `oldclass`, `karma`, `bal`, `animedna_bonus`) VALUES
(1, 'webnet', '', '18efc4e509d0671fc6b3d27d54972f4e', 'CGzukFNOja82hplMZadh', 'webnetbt@gmail.com', 'confirmed', '2018-03-28 12:21:51', '2025-07-12 02:44:30', '2025-07-14 17:11:41', '2025-07-12 13:05:39', '0000-00-00 00:00:00', '', 'normal', 'yes', 3, NULL, '[url=http://localhost:8080][img]http://localhost:8080/torrentbar/bar.php?id=1[/img][/url]\r\n\r\n[b]\r\nтест[/b]', 'yes', '192.168.97.1', 70, 255, 'http://localhost:8080/pic/king.jpg', '__2025_07_09__190110.jpg', '0', '', 0, 0, 0.00, '', 53, '', NULL, 'yes', 'no', 'yes', 'no', 'no', 'no', '0000-00-00 00:00:00', 'yes', '0000-00-00 00:00:00', 'no', '0000-00-00 00:00:00', '0000-00-00 00:00:00', 'yes', 'no', '1', '1922-04-05', 'db3b347bb61b6f68cfbaa72f0769c70c', 'russian', '', 1, 'yes', 180, 0, '0000-00-00 00:00:00', 0, 0, 0, '0000-00-00'),
(2, 'merdox', 'f5c0ec9ba650ec08bb45f9cfa04a396b', 'f5c0ec9ba650ec08bb45f9cfa04a396b', '8QPuQbr9ll8DSsga8P7n', 'mer@mail.ru', 'confirmed', '2025-07-05 19:45:19', '2025-07-10 16:37:15', '2025-07-12 02:44:19', '0000-00-00 00:00:00', '1970-01-01 00:00:01', '', 'normal', 'no', 3, NULL, '[url=http://localhost:8080][img]http://localhost:8080/torrentbar/bar.php?id=2[/img][/url]', 'yes', '192.168.97.1', 50, 255, '', '0', '0', '', 476741369856, 4194304, 555.00, '', 39, '', '', 'yes', 'no', 'yes', 'yes', 'no', 'no', '0000-00-00 00:00:00', 'yes', '0000-00-00 00:00:00', 'no', '0000-00-00 00:00:00', '2025-07-05 19:45:19', 'yes', 'no', '2', '1923-02-04', '8f4a2f0c6e482729f71b075842e37351', 'russian', '192.168.97.1', 0, 'yes', 180, 60, '1970-01-01 00:00:01', 0, 1, 0, '0000-00-00'),
(3, 'demon', '', 'c1e0999cb9282cb4fb07a49c4104ed0b', 'vi7jhXpSRtlUNtZtTsmc', '1', 'confirmed', '2025-07-06 15:32:17', '2025-07-06 16:18:38', '2025-07-06 16:21:28', '0000-00-00 00:00:00', '0000-00-00 00:00:00', '', 'normal', 'no', 3, NULL, NULL, 'yes', '192.168.97.1', 0, 255, '', '0', '', '', 0, 0, 0.00, '', 0, '', NULL, 'yes', 'no', 'yes', 'no', 'no', 'no', '0000-00-00 00:00:00', 'yes', '0000-00-00 00:00:00', 'no', '0000-00-00 00:00:00', '0000-00-00 00:00:00', 'yes', 'no', '1', '0000-00-00', '', 'russian', '', 0, 'yes', 0, -60, '0000-00-00 00:00:00', 0, 1, 0, '0000-00-00');

-- --------------------------------------------------------

--
-- Структура таблицы `users_data`
--

CREATE TABLE `users_data` (
  `id` bigint UNSIGNED NOT NULL,
  `userid` bigint UNSIGNED NOT NULL,
  `downloaded` int NOT NULL,
  `down` enum('yes','no') NOT NULL DEFAULT 'yes'
) ENGINE=MyISAM DEFAULT CHARSET=utf8mb3;

-- --------------------------------------------------------

--
-- Структура таблицы `user_uploads`
--

CREATE TABLE `user_uploads` (
  `id` int NOT NULL,
  `image_path` varchar(30) DEFAULT NULL,
  `uid_fk` int DEFAULT NULL
) ENGINE=MyISAM DEFAULT CHARSET=utf8mb3;

-- --------------------------------------------------------

--
-- Структура таблицы `visitor_history`
--

CREATE TABLE `visitor_history` (
  `id` int NOT NULL,
  `url` varchar(200) NOT NULL,
  `uid` int NOT NULL,
  `uname` varchar(200) NOT NULL,
  `uclass` int NOT NULL,
  `ip` varchar(15) NOT NULL,
  `time` int NOT NULL
) ENGINE=MyISAM DEFAULT CHARSET=utf8mb3;

--
-- Дамп данных таблицы `visitor_history`
--

INSERT INTO `visitor_history` (`id`, `url`, `uid`, `uname`, `uclass`, `ip`, `time`) VALUES
(23, '/user/id2?2', 2, 'merdox', 50, '192.168.97.1', 1752165726),
(4, '/blog.php?bid=1?1', 1, 'webnet', 70, '192.168.97.1', 1751804542),
(16, '/user/id3?3', 1, 'webnet', 70, '192.168.97.1', 1752162778),
(25, '/user/id1?1', 1, 'webnet', 70, '192.168.97.1', 1752327010),
(22, '/user/id2?2', 1, 'webnet', 70, '192.168.97.1', 1752165429);

-- --------------------------------------------------------

--
-- Структура таблицы `vote`
--

CREATE TABLE `vote` (
  `id` int NOT NULL,
  `userid` int NOT NULL,
  `anketid` int NOT NULL
) ENGINE=MyISAM DEFAULT CHARSET=utf8mb3;

-- --------------------------------------------------------

--
-- Структура таблицы `voting_id`
--

CREATE TABLE `voting_id` (
  `id` int NOT NULL,
  `blog_id` int DEFAULT NULL,
  `ip_add` varchar(40) DEFAULT NULL,
  `userid` int DEFAULT NULL,
  `act` varchar(10) NOT NULL
) ENGINE=MyISAM DEFAULT CHARSET=utf8mb3;

-- --------------------------------------------------------

--
-- Структура таблицы `wcomments`
--

CREATE TABLE `wcomments` (
  `com_id` int NOT NULL,
  `comment` varchar(200) DEFAULT NULL,
  `msg_id_fk` int DEFAULT NULL,
  `uid_fk` int DEFAULT NULL,
  `ip` varchar(30) DEFAULT NULL,
  `created` int DEFAULT '1269249260'
) ENGINE=MyISAM DEFAULT CHARSET=utf8mb3;

-- --------------------------------------------------------

--
-- Структура таблицы `wmessages`
--

CREATE TABLE `wmessages` (
  `msg_id` int NOT NULL,
  `message` varchar(200) DEFAULT NULL,
  `uid_fk` int DEFAULT NULL,
  `ip` varchar(30) DEFAULT NULL,
  `created` int DEFAULT '1269249260',
  `uploads` varchar(30) DEFAULT NULL
) ENGINE=MyISAM DEFAULT CHARSET=utf8mb3;

--
-- Индексы сохранённых таблиц
--

--
-- Индексы таблицы `album`
--
ALTER TABLE `album`
  ADD PRIMARY KEY (`id`);

--
-- Индексы таблицы `albumcat`
--
ALTER TABLE `albumcat`
  ADD PRIMARY KEY (`id`);

--
-- Индексы таблицы `anews`
--
ALTER TABLE `anews`
  ADD PRIMARY KEY (`id`),
  ADD KEY `added` (`added`);

--
-- Индексы таблицы `articles`
--
ALTER TABLE `articles`
  ADD PRIMARY KEY (`id`),
  ADD KEY `added` (`added`),
  ADD KEY `categories` (`categories`),
  ADD KEY `id_2` (`categories`,`userid`);

--
-- Индексы таблицы `article_categories`
--
ALTER TABLE `article_categories`
  ADD PRIMARY KEY (`id`),
  ADD KEY `name` (`name`);

--
-- Индексы таблицы `article_comments`
--
ALTER TABLE `article_comments`
  ADD PRIMARY KEY (`id`),
  ADD KEY `userid` (`userid`,`articleid`),
  ADD KEY `articleid` (`articleid`);

--
-- Индексы таблицы `avps`
--
ALTER TABLE `avps`
  ADD PRIMARY KEY (`arg`);

--
-- Индексы таблицы `bans`
--
ALTER TABLE `bans`
  ADD PRIMARY KEY (`id`),
  ADD KEY `first_last` (`first`,`last`),
  ADD KEY `id` (`id`,`first`,`last`);

--
-- Индексы таблицы `blocks`
--
ALTER TABLE `blocks`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `userfriend` (`userid`,`blockid`);

--
-- Индексы таблицы `blogs`
--
ALTER TABLE `blogs`
  ADD PRIMARY KEY (`bid`),
  ADD KEY `bid` (`uid`,`comments`,`views`,`p_added`,`subject`),
  ADD KEY `privat` (`privat`),
  ADD KEY `p_added` (`p_added`),
  ADD KEY `uid` (`uid`);

--
-- Индексы таблицы `blogtags`
--
ALTER TABLE `blogtags`
  ADD PRIMARY KEY (`id`),
  ADD KEY `id` (`id`,`name`,`howmuch`);

--
-- Индексы таблицы `blog_comments`
--
ALTER TABLE `blog_comments`
  ADD PRIMARY KEY (`id`),
  ADD KEY `id` (`id`,`bid`,`user`,`added`,`editedby`,`editedat`,`karma`);

--
-- Индексы таблицы `bonus`
--
ALTER TABLE `bonus`
  ADD PRIMARY KEY (`id`);

--
-- Индексы таблицы `bookmarks`
--
ALTER TABLE `bookmarks`
  ADD PRIMARY KEY (`id`),
  ADD KEY `userid` (`userid`,`torrentid`);

--
-- Индексы таблицы `categories`
--
ALTER TABLE `categories`
  ADD PRIMARY KEY (`id`);

--
-- Индексы таблицы `cheaters`
--
ALTER TABLE `cheaters`
  ADD PRIMARY KEY (`id`);

--
-- Индексы таблицы `comments`
--
ALTER TABLE `comments`
  ADD PRIMARY KEY (`id`),
  ADD KEY `user` (`user`),
  ADD KEY `torrent` (`torrent`),
  ADD KEY `id` (`id`,`user`,`request`,`torrent`,`anews`,`editedby`,`ip`,`galary`);

--
-- Индексы таблицы `countries`
--
ALTER TABLE `countries`
  ADD PRIMARY KEY (`id`),
  ADD KEY `name` (`name`,`flagpic`);

--
-- Индексы таблицы `donatedelux`
--
ALTER TABLE `donatedelux`
  ADD PRIMARY KEY (`id`);

--
-- Индексы таблицы `files`
--
ALTER TABLE `files`
  ADD PRIMARY KEY (`id`),
  ADD KEY `torrent` (`torrent`);

--
-- Индексы таблицы `forums`
--
ALTER TABLE `forums`
  ADD PRIMARY KEY (`id`),
  ADD KEY `forid` (`forid`);

--
-- Индексы таблицы `friends`
--
ALTER TABLE `friends`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `userfriend` (`userid`,`friendid`);

--
-- Индексы таблицы `groups`
--
ALTER TABLE `groups`
  ADD PRIMARY KEY (`id`);

--
-- Индексы таблицы `guests`
--
ALTER TABLE `guests`
  ADD PRIMARY KEY (`id`),
  ADD KEY `ip` (`ip`,`time_accessed`);

--
-- Индексы таблицы `hackers`
--
ALTER TABLE `hackers`
  ADD PRIMARY KEY (`id`);

--
-- Индексы таблицы `karma`
--
ALTER TABLE `karma`
  ADD PRIMARY KEY (`id`),
  ADD KEY `userid` (`userid`,`fromid`);

--
-- Индексы таблицы `konkurs`
--
ALTER TABLE `konkurs`
  ADD PRIMARY KEY (`id`);

--
-- Индексы таблицы `messages`
--
ALTER TABLE `messages`
  ADD PRIMARY KEY (`id`),
  ADD KEY `sender` (`sender`,`receiver`),
  ADD KEY `receiver` (`receiver`,`unread`);

--
-- Индексы таблицы `news`
--
ALTER TABLE `news`
  ADD PRIMARY KEY (`id`),
  ADD KEY `added` (`added`);

--
-- Индексы таблицы `notconnectablepmlog`
--
ALTER TABLE `notconnectablepmlog`
  ADD PRIMARY KEY (`id`);

--
-- Индексы таблицы `options`
--
ALTER TABLE `options`
  ADD PRIMARY KEY (`option_id`),
  ADD UNIQUE KEY `option_name` (`option_name`);

--
-- Индексы таблицы `orbital_blocks`
--
ALTER TABLE `orbital_blocks`
  ADD PRIMARY KEY (`bid`),
  ADD KEY `title` (`title`),
  ADD KEY `weight` (`weight`),
  ADD KEY `active` (`active`);

--
-- Индексы таблицы `overforums`
--
ALTER TABLE `overforums`
  ADD PRIMARY KEY (`id`);

--
-- Индексы таблицы `pay`
--
ALTER TABLE `pay`
  ADD PRIMARY KEY (`id`),
  ADD KEY `order_uid` (`order_uid`);

--
-- Индексы таблицы `pay_bonus`
--
ALTER TABLE `pay_bonus`
  ADD PRIMARY KEY (`id`);

--
-- Индексы таблицы `peers`
--
ALTER TABLE `peers`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `torrent` (`torrent`,`userid`),
  ADD KEY `userid` (`userid`),
  ADD KEY `seeder` (`seeder`,`userid`),
  ADD KEY `peer_id` (`peer_id`,`ip`,`port`,`uploaded`,`downloaded`,`seeder`,`last_action`,`prev_action`,`userid`);
ALTER TABLE `peers` ADD FULLTEXT KEY `ip` (`ip`);

--
-- Индексы таблицы `photos`
--
ALTER TABLE `photos`
  ADD PRIMARY KEY (`pid`);

--
-- Индексы таблицы `photos_comments`
--
ALTER TABLE `photos_comments`
  ADD PRIMARY KEY (`cid`),
  ADD KEY `album_id_2` (`album_id`,`photo_id`,`user_id`);

--
-- Индексы таблицы `photo_albums`
--
ALTER TABLE `photo_albums`
  ADD PRIMARY KEY (`aid`),
  ADD KEY `photos_count` (`photos_count`,`album_view_access`),
  ADD KEY `aid` (`aid`,`created_by`),
  ADD KEY `created_by` (`created_by`);

--
-- Индексы таблицы `podarki`
--
ALTER TABLE `podarki`
  ADD PRIMARY KEY (`id`);

--
-- Индексы таблицы `podarok`
--
ALTER TABLE `podarok`
  ADD PRIMARY KEY (`id`);

--
-- Индексы таблицы `pollanswers`
--
ALTER TABLE `pollanswers`
  ADD PRIMARY KEY (`id`),
  ADD KEY `pollid` (`pollid`),
  ADD KEY `selection` (`selection`),
  ADD KEY `userid` (`userid`),
  ADD KEY `pollid_2` (`pollid`,`userid`,`selection`);

--
-- Индексы таблицы `polls`
--
ALTER TABLE `polls`
  ADD PRIMARY KEY (`id`);

--
-- Индексы таблицы `posts`
--
ALTER TABLE `posts`
  ADD PRIMARY KEY (`id`),
  ADD KEY `topicid` (`topicid`),
  ADD KEY `userid` (`userid`);
ALTER TABLE `posts` ADD FULLTEXT KEY `body` (`body`);

--
-- Индексы таблицы `prof_guest`
--
ALTER TABLE `prof_guest`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `uid` (`uid`,`profid`),
  ADD KEY `time` (`time`);

--
-- Индексы таблицы `ratetorrents`
--
ALTER TABLE `ratetorrents`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `id` (`rating_id`,`userid`),
  ADD KEY `rating_id` (`rating_id`);

--
-- Индексы таблицы `rating`
--
ALTER TABLE `rating`
  ADD PRIMARY KEY (`id`),
  ADD KEY `id` (`id`,`topic`,`user`),
  ADD KEY `rating` (`rating`),
  ADD KEY `topic` (`topic`);

--
-- Индексы таблицы `readposts`
--
ALTER TABLE `readposts`
  ADD PRIMARY KEY (`id`),
  ADD KEY `topicid` (`topicid`);

--
-- Индексы таблицы `referals`
--
ALTER TABLE `referals`
  ADD PRIMARY KEY (`id`),
  ADD KEY `id` (`id`,`ref`,`ip`,`from_url`,`added`);

--
-- Индексы таблицы `shoutbox`
--
ALTER TABLE `shoutbox`
  ADD PRIMARY KEY (`id`),
  ADD KEY `date` (`date`);
ALTER TABLE `shoutbox` ADD FULLTEXT KEY `text` (`text`);

--
-- Индексы таблицы `sitelog`
--
ALTER TABLE `sitelog`
  ADD PRIMARY KEY (`id`),
  ADD KEY `added` (`added`);

--
-- Индексы таблицы `snatched`
--
ALTER TABLE `snatched`
  ADD PRIMARY KEY (`id`),
  ADD KEY `snatch` (`torrent`,`userid`),
  ADD KEY `torrent` (`torrent`,`uploaded`,`downloaded`,`seeder`);

--
-- Индексы таблицы `stylesheets`
--
ALTER TABLE `stylesheets`
  ADD PRIMARY KEY (`id`),
  ADD KEY `uri` (`uri`);

--
-- Индексы таблицы `subscribe`
--
ALTER TABLE `subscribe`
  ADD PRIMARY KEY (`id`),
  ADD KEY `userid` (`userid`,`torid`);

--
-- Индексы таблицы `tags`
--
ALTER TABLE `tags`
  ADD PRIMARY KEY (`id`),
  ADD KEY `id` (`id`,`name`,`howmuch`);

--
-- Индексы таблицы `thanks`
--
ALTER TABLE `thanks`
  ADD PRIMARY KEY (`id`),
  ADD KEY `torrentid` (`torrentid`,`userid`),
  ADD KEY `torrentid_2` (`torrentid`),
  ADD KEY `userid` (`userid`);

--
-- Индексы таблицы `topics`
--
ALTER TABLE `topics`
  ADD PRIMARY KEY (`id`),
  ADD KEY `userid` (`userid`),
  ADD KEY `subject` (`subject`),
  ADD KEY `lastpost` (`lastpost`),
  ADD KEY `forumid` (`forumid`);

--
-- Индексы таблицы `torrents`
--
ALTER TABLE `torrents`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `info_hash` (`info_hash`),
  ADD KEY `owner` (`owner`),
  ADD KEY `category` (`category`),
  ADD KEY `modded` (`modded`),
  ADD KEY `info_hash_2` (`info_hash`,`times_completed`,`leechers`,`seeders`),
  ADD KEY `added` (`added`);

--
-- Индексы таблицы `torrents_parsed`
--
ALTER TABLE `torrents_parsed`
  ADD UNIQUE KEY `torrent` (`torrent`);

--
-- Индексы таблицы `uploadapp`
--
ALTER TABLE `uploadapp`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `users` (`userid`);

--
-- Индексы таблицы `users`
--
ALTER TABLE `users`
  ADD PRIMARY KEY (`id`),
  ADD KEY `ip` (`ip`),
  ADD KEY `country` (`country`),
  ADD KEY `enabled` (`enabled`),
  ADD KEY `warned` (`warned`),
  ADD KEY `id` (`id`,`ip`,`passkey`,`passkey_ip`),
  ADD KEY `id_2` (`id`,`bonus`),
  ADD KEY `id_3` (`id`,`class`,`uploaded`,`downloaded`,`enabled`,`parked`,`passkey`,`passkey_ip`),
  ADD KEY `photo` (`photo`);

--
-- Индексы таблицы `users_data`
--
ALTER TABLE `users_data`
  ADD PRIMARY KEY (`id`);

--
-- Индексы таблицы `user_uploads`
--
ALTER TABLE `user_uploads`
  ADD PRIMARY KEY (`id`),
  ADD KEY `uid_fk` (`uid_fk`);

--
-- Индексы таблицы `visitor_history`
--
ALTER TABLE `visitor_history`
  ADD PRIMARY KEY (`id`),
  ADD KEY `url` (`url`,`ip`);

--
-- Индексы таблицы `vote`
--
ALTER TABLE `vote`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `userid` (`userid`,`anketid`);

--
-- Индексы таблицы `voting_id`
--
ALTER TABLE `voting_id`
  ADD PRIMARY KEY (`id`),
  ADD KEY `mes_id_fk` (`blog_id`);

--
-- Индексы таблицы `wcomments`
--
ALTER TABLE `wcomments`
  ADD PRIMARY KEY (`com_id`),
  ADD KEY `msg_id_fk` (`msg_id_fk`),
  ADD KEY `uid_fk` (`uid_fk`);

--
-- Индексы таблицы `wmessages`
--
ALTER TABLE `wmessages`
  ADD PRIMARY KEY (`msg_id`),
  ADD KEY `uid_fk` (`uid_fk`);

--
-- AUTO_INCREMENT для сохранённых таблиц
--

--
-- AUTO_INCREMENT для таблицы `album`
--
ALTER TABLE `album`
  MODIFY `id` int NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT для таблицы `albumcat`
--
ALTER TABLE `albumcat`
  MODIFY `id` int NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=11;

--
-- AUTO_INCREMENT для таблицы `anews`
--
ALTER TABLE `anews`
  MODIFY `id` int UNSIGNED NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT для таблицы `articles`
--
ALTER TABLE `articles`
  MODIFY `id` int UNSIGNED NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT для таблицы `article_categories`
--
ALTER TABLE `article_categories`
  MODIFY `id` int UNSIGNED NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=7;

--
-- AUTO_INCREMENT для таблицы `article_comments`
--
ALTER TABLE `article_comments`
  MODIFY `id` int UNSIGNED NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT для таблицы `bans`
--
ALTER TABLE `bans`
  MODIFY `id` int UNSIGNED NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT для таблицы `blocks`
--
ALTER TABLE `blocks`
  MODIFY `id` int UNSIGNED NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=4;

--
-- AUTO_INCREMENT для таблицы `blogs`
--
ALTER TABLE `blogs`
  MODIFY `bid` int UNSIGNED NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=2;

--
-- AUTO_INCREMENT для таблицы `blogtags`
--
ALTER TABLE `blogtags`
  MODIFY `id` int UNSIGNED NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT для таблицы `blog_comments`
--
ALTER TABLE `blog_comments`
  MODIFY `id` int UNSIGNED NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT для таблицы `bonus`
--
ALTER TABLE `bonus`
  MODIFY `id` int NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=5;

--
-- AUTO_INCREMENT для таблицы `bookmarks`
--
ALTER TABLE `bookmarks`
  MODIFY `id` int UNSIGNED NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=2;

--
-- AUTO_INCREMENT для таблицы `categories`
--
ALTER TABLE `categories`
  MODIFY `id` int UNSIGNED NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=25;

--
-- AUTO_INCREMENT для таблицы `cheaters`
--
ALTER TABLE `cheaters`
  MODIFY `id` int UNSIGNED NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=9266;

--
-- AUTO_INCREMENT для таблицы `comments`
--
ALTER TABLE `comments`
  MODIFY `id` int UNSIGNED NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=13;

--
-- AUTO_INCREMENT для таблицы `countries`
--
ALTER TABLE `countries`
  MODIFY `id` int UNSIGNED NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=106;

--
-- AUTO_INCREMENT для таблицы `donatedelux`
--
ALTER TABLE `donatedelux`
  MODIFY `id` int NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT для таблицы `files`
--
ALTER TABLE `files`
  MODIFY `id` int UNSIGNED NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=2;

--
-- AUTO_INCREMENT для таблицы `forums`
--
ALTER TABLE `forums`
  MODIFY `id` int UNSIGNED NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=50;

--
-- AUTO_INCREMENT для таблицы `friends`
--
ALTER TABLE `friends`
  MODIFY `id` int UNSIGNED NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=7;

--
-- AUTO_INCREMENT для таблицы `groups`
--
ALTER TABLE `groups`
  MODIFY `id` int NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=3;

--
-- AUTO_INCREMENT для таблицы `guests`
--
ALTER TABLE `guests`
  MODIFY `id` int UNSIGNED NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=1513094;

--
-- AUTO_INCREMENT для таблицы `hackers`
--
ALTER TABLE `hackers`
  MODIFY `id` int NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=215;

--
-- AUTO_INCREMENT для таблицы `karma`
--
ALTER TABLE `karma`
  MODIFY `id` int NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=3;

--
-- AUTO_INCREMENT для таблицы `konkurs`
--
ALTER TABLE `konkurs`
  MODIFY `id` int NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT для таблицы `messages`
--
ALTER TABLE `messages`
  MODIFY `id` int UNSIGNED NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=13;

--
-- AUTO_INCREMENT для таблицы `news`
--
ALTER TABLE `news`
  MODIFY `id` int UNSIGNED NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=2;

--
-- AUTO_INCREMENT для таблицы `notconnectablepmlog`
--
ALTER TABLE `notconnectablepmlog`
  MODIFY `id` int UNSIGNED NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT для таблицы `options`
--
ALTER TABLE `options`
  MODIFY `option_id` bigint UNSIGNED NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT для таблицы `orbital_blocks`
--
ALTER TABLE `orbital_blocks`
  MODIFY `bid` int NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=65;

--
-- AUTO_INCREMENT для таблицы `overforums`
--
ALTER TABLE `overforums`
  MODIFY `id` int UNSIGNED NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=9;

--
-- AUTO_INCREMENT для таблицы `pay`
--
ALTER TABLE `pay`
  MODIFY `id` int NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT для таблицы `pay_bonus`
--
ALTER TABLE `pay_bonus`
  MODIFY `id` int NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=14;

--
-- AUTO_INCREMENT для таблицы `peers`
--
ALTER TABLE `peers`
  MODIFY `id` int UNSIGNED NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=7005904;

--
-- AUTO_INCREMENT для таблицы `photos`
--
ALTER TABLE `photos`
  MODIFY `pid` int UNSIGNED NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT для таблицы `photos_comments`
--
ALTER TABLE `photos_comments`
  MODIFY `cid` int UNSIGNED NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT для таблицы `photo_albums`
--
ALTER TABLE `photo_albums`
  MODIFY `aid` int UNSIGNED NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT для таблицы `podarki`
--
ALTER TABLE `podarki`
  MODIFY `id` smallint NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=190;

--
-- AUTO_INCREMENT для таблицы `podarok`
--
ALTER TABLE `podarok`
  MODIFY `id` int NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT для таблицы `pollanswers`
--
ALTER TABLE `pollanswers`
  MODIFY `id` int UNSIGNED NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=4;

--
-- AUTO_INCREMENT для таблицы `polls`
--
ALTER TABLE `polls`
  MODIFY `id` int UNSIGNED NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=2;

--
-- AUTO_INCREMENT для таблицы `posts`
--
ALTER TABLE `posts`
  MODIFY `id` int UNSIGNED NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT для таблицы `prof_guest`
--
ALTER TABLE `prof_guest`
  MODIFY `id` int NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=5;

--
-- AUTO_INCREMENT для таблицы `ratetorrents`
--
ALTER TABLE `ratetorrents`
  MODIFY `id` int NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=2;

--
-- AUTO_INCREMENT для таблицы `rating`
--
ALTER TABLE `rating`
  MODIFY `id` int NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT для таблицы `readposts`
--
ALTER TABLE `readposts`
  MODIFY `id` int UNSIGNED NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT для таблицы `referals`
--
ALTER TABLE `referals`
  MODIFY `id` int UNSIGNED NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT для таблицы `shoutbox`
--
ALTER TABLE `shoutbox`
  MODIFY `id` int NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=357267;

--
-- AUTO_INCREMENT для таблицы `sitelog`
--
ALTER TABLE `sitelog`
  MODIFY `id` int UNSIGNED NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=21;

--
-- AUTO_INCREMENT для таблицы `snatched`
--
ALTER TABLE `snatched`
  MODIFY `id` int NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT для таблицы `stylesheets`
--
ALTER TABLE `stylesheets`
  MODIFY `id` int UNSIGNED NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=6;

--
-- AUTO_INCREMENT для таблицы `subscribe`
--
ALTER TABLE `subscribe`
  MODIFY `id` int NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=2;

--
-- AUTO_INCREMENT для таблицы `tags`
--
ALTER TABLE `tags`
  MODIFY `id` int UNSIGNED NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=3;

--
-- AUTO_INCREMENT для таблицы `thanks`
--
ALTER TABLE `thanks`
  MODIFY `id` int NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=2;

--
-- AUTO_INCREMENT для таблицы `topics`
--
ALTER TABLE `topics`
  MODIFY `id` int UNSIGNED NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT для таблицы `torrents`
--
ALTER TABLE `torrents`
  MODIFY `id` int UNSIGNED NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=2;

--
-- AUTO_INCREMENT для таблицы `uploadapp`
--
ALTER TABLE `uploadapp`
  MODIFY `id` int UNSIGNED NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT для таблицы `users`
--
ALTER TABLE `users`
  MODIFY `id` bigint UNSIGNED NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=4;

--
-- AUTO_INCREMENT для таблицы `users_data`
--
ALTER TABLE `users_data`
  MODIFY `id` bigint UNSIGNED NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT для таблицы `user_uploads`
--
ALTER TABLE `user_uploads`
  MODIFY `id` int NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT для таблицы `visitor_history`
--
ALTER TABLE `visitor_history`
  MODIFY `id` int NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=26;

--
-- AUTO_INCREMENT для таблицы `vote`
--
ALTER TABLE `vote`
  MODIFY `id` int NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT для таблицы `voting_id`
--
ALTER TABLE `voting_id`
  MODIFY `id` int NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT для таблицы `wcomments`
--
ALTER TABLE `wcomments`
  MODIFY `com_id` int NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT для таблицы `wmessages`
--
ALTER TABLE `wmessages`
  MODIFY `msg_id` int NOT NULL AUTO_INCREMENT;
COMMIT;

/*!40101 SET CHARACTER_SET_CLIENT=@OLD_CHARACTER_SET_CLIENT */;
/*!40101 SET CHARACTER_SET_RESULTS=@OLD_CHARACTER_SET_RESULTS */;
/*!40101 SET COLLATION_CONNECTION=@OLD_COLLATION_CONNECTION */;
