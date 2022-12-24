--
-- Структура таблицы `activity_history`
--

CREATE TABLE `activity_history` (
  `id` int(11) NOT NULL,
  `discord_id` varchar(50) NOT NULL COMMENT 'Id пользователя в дискорде',
  `date` date NOT NULL COMMENT 'Дата активности',
  `voice_active` tinyint(1) NOT NULL DEFAULT '0' COMMENT 'Активности в голосовых чатах',
  `message_active` tinyint(1) NOT NULL DEFAULT '0' COMMENT 'Активность в сообщениях',
  `like_active` tinyint(1) NOT NULL DEFAULT '0' COMMENT 'Активность пожертвования',
  `mem_active` tinyint(1) NOT NULL DEFAULT '0' COMMENT 'Активность в хороших мемах',
  `reaction_active` tinyint(1) NOT NULL DEFAULT '0' COMMENT 'Активности реакции',
  `music_active` tinyint(1) NOT NULL DEFAULT '0' COMMENT 'Музыкальная активность',
  `always_active` tinyint(1) NOT NULL DEFAULT '1' COMMENT 'Активность нахождения в канале'
) ENGINE=InnoDB DEFAULT CHARSET=utf8;

-- --------------------------------------------------------

--
-- Структура таблицы `dailies`
--

CREATE TABLE `dailies` (
  `id` int(11) NOT NULL,
  `date` date NOT NULL,
  `active1` varchar(100) NOT NULL,
  `active2` varchar(100) NOT NULL,
  `active3` varchar(100) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8;

--
-- Дамп данных таблицы `dailies`
--

INSERT INTO `dailies` (`id`, `date`, `active1`, `active2`, `active3`) VALUES
(1, '2022-12-24', 'voice_active', 'like_active', 'reaction_active');

-- --------------------------------------------------------

--
-- Структура таблицы `level`
--

CREATE TABLE `level` (
  `id` int(11) NOT NULL,
  `name` varchar(100) NOT NULL,
  `description` varchar(255) NOT NULL,
  `cost` int(11) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8;

--
-- Дамп данных таблицы `level`
--

INSERT INTO `level` (`id`, `name`, `description`, `cost`) VALUES
(1, '1', '', 10),
(2, '2', '', 20),
(3, '3', '', 40),
(4, '4', '', 80),
(5, '5', '', 100),
(6, '6', '', 150);

-- --------------------------------------------------------

--
-- Структура таблицы `users`
--

CREATE TABLE `users` (
  `id` int(11) NOT NULL,
  `discord_id` varchar(50) NOT NULL COMMENT 'Id пользователя в дискорде',
  `username` varchar(255) NOT NULL COMMENT 'Имя пользователя',
  `tag` varchar(5) NOT NULL COMMENT 'Тег пользователя',
  `level` int(11) NOT NULL DEFAULT '0' COMMENT 'Уровень пользователя',
  `balance` int(11) NOT NULL DEFAULT '0' COMMENT 'Баланс пользователя',
  `created_at` int(11) NOT NULL COMMENT 'Дата создания пользователя'
) ENGINE=InnoDB DEFAULT CHARSET=utf8;

--
-- Индексы таблицы `activity_history`
--
ALTER TABLE `activity_history`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `discord_id` (`discord_id`,`date`);

--
-- Индексы таблицы `dailies`
--
ALTER TABLE `dailies`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `date` (`date`);

--
-- Индексы таблицы `level`
--
ALTER TABLE `level`
  ADD PRIMARY KEY (`id`);

--
-- Индексы таблицы `users`
--
ALTER TABLE `users`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `discord_id` (`discord_id`);

--
-- AUTO_INCREMENT для сохранённых таблиц
--

--
-- AUTO_INCREMENT для таблицы `activity_history`
--
ALTER TABLE `activity_history`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=3;

--
-- AUTO_INCREMENT для таблицы `dailies`
--
ALTER TABLE `dailies`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=2;

--
-- AUTO_INCREMENT для таблицы `level`
--
ALTER TABLE `level`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=7;

--
-- AUTO_INCREMENT для таблицы `users`
--
ALTER TABLE `users`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=3;
COMMIT;
