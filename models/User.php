<?php
	class User extends Model
	{
		const SALT 					= "32dg9823dldfg2o001-2134>?erj&*(&(*^";	// Для генерации кук
		
		/*====================================== ПЕРЕМЕННЫЕ И КОНСТАНТЫ ======================================*/

		public static $mysql_table	= "users";
		
		/*====================================== СИСТЕМНЫЕ ФУНКЦИИ ======================================*/
		

		/*====================================== СТАТИЧЕСКИЕ ФУНКЦИИ ======================================*/
		
		
		/**
		 * Пользователь забанен?
		 * 
		 */
		public static function ipBanned()
		{
//			return (memcached()->get(realIp()) !== false || memcached()->getResultCode() != Memcached::RES_NOTFOUND);
			return (memcached()->get(realIp()) !== false);
		}
		
		
		/**
		 * На сколько заморожен пользователь.
		 * 
		 * @return строка – от "10 минут" до "сутки"
		 */
		public static function bannedTimeText()
		{
			if (self::ipBanned()) {
				$ban_info =  memcached()->get(realIp());
				
				return $ban_info["details"]["text"];
			}
		}
		
		/**
		 * Инфа о бане по ID пользователя.
		 * если установлен $params[ip] – поиск по IP
		 * если установлен $params[id] – поиск по ID
		 * 
		 */
		public static function banInfo($params)
		{
			extract($params);
			
			// Если установлен IP
			if (isset($ip)) {
				$ban_info = memcached()->get($ip);
			} else {
				$User = User::findById($id);
				$ban_info = memcached()->get($User->ip);
			}
			
			preType($ban_info);
		}
		
		/**
		 * Создаем пользователя и логинимся в сессию, если надо.
		 * 
		 */
		public static function createAndLogin()
		{
			// Если пользователь не залогинен
			if (!self::loggedIn()) {
				// Если пользователь не существует 
				// (в функции проверки выполняется вход автоматически)
				if (!self::fromCookie()) {
					// Добавляем  пользователя
					$User = new self();
					
					// Устанавливаем значения по умолчанию
					$User->setDefaults();
					
					// Пушим пользователя в сессию
					$User->toSession(true);
				}
			}	
		}
		
		/*
		 * Автовход по Remember-me
		 */
		public static function fromCookie()
		{
			// Если кука не установлена
			if (!$_COOKIE["vtoken"]) {
				return false;
			}
			// Кука токена хранится в виде: 
			// 1) Первые 16 символов MD5-хэш
			// 2) Остальные символы – id_user (код пользователя)
			// $cookie_hash = mb_strimwidth($_COOKIE["ratie_token"], 0, 32); // Нам не надо получать хэш из кук -- мы создаем новый здесь для сравнения
			$cookie_user = substr($_COOKIE["vtoken"], 32);
			
			// Получаем пользователя по ID
			$User = User::findById($cookie_user);
			
			// Если пользователь найден
			if ($User) {
				$User->toSession(true);
				return true;
			} else {
				return false;
			}
		}
		
		/*
		 * Проверяем, залогинен ли пользователь
		 */
		public static function loggedIn()
		{
			return isset($_SESSION["user"]);
		}
		
		
		/**
		 * Проверяем, существует ли пользователь.
		 * 
		 */
		public static function exists()
		{
			return isset($_COOKIE["vtoken"]);
		}
		
		/*
		 * Пользователь из сессии
		 * @boolean $update – обновлять данные из БД
		 */
		public static function fromSession($upadte = false)
		{
			// Если обновить данные из БД, то загружаем пользователя
			if ($upadte) {
				$User = User::findById($_SESSION["user"]->getId());
				$User->toSession();
			} else {
				// Получаем пользователя из СЕССИИ
				$User = $_SESSION["user"];
			}
						
			// Возвращаем пользователя
			return $User;
		}	

		/*====================================== ФУНКЦИИ КЛАССА ======================================*/
		
		
		/**
		 * Бан пользователя на сутки по MEMCACHED.
		 * $ban_info – массив с инфой по бану
		 */
		public function ban($warnings)
		{
			$ban_info = [
				"id_user"	=> $this->id,
				"warnings"	=> $warnings,
				"time"		=> now(),
				"browser"	=> $_SERVER['HTTP_USER_AGENT'],
				"task"		=> $this->Task->dbData(["url", "url_original"]), 
			];
			
			// начисляем кол-во банов пользователю
			$this->banned++;
			$this->save("banned");
			
			// Устанавливаем время бана в зависимости от того, в какой раз банится пользователь
			switch ($this->banned) {
				case 1: {
					$ban_text = "10 минут";
					$ban_time = 60 * 10;	// 10 минут
					break;
				}
				case 2: {
					$ban_text = "30 минут";
					$ban_time = 60 * 30;	// 30 минут
					break;
				}
				case 3: {
					$ban_text = "один час";
					$ban_time = 60 * 60;	// 1 час
					break;
				}
				case 4: {
					$ban_text = "три часа";
					$ban_time = 60 * 60 * 3; // 3 часа
					break;
				}
				case 5: {
					$ban_text = "6 часов";
					$ban_time = 60 * 60 * 6; // 6 часов
					break;
				}
				case 6:
				case 7:
				case 8:
				case 9: {
					$ban_text = "12 часов";
					$ban_time = 60 * 60 * 12; // 12 часов
					break;
				}
				// 10 банов и более – на сутки
				default: {
					$ban_text = "сутки";
					$ban_time = 60 * 60 * 24; // сутки
				}
			}
			
			// Информация по времени и количеству банов
			$ban_info["details"] = [
				"time"	=> ($ban_time / 3600), // время бана в часах
				"count"	=> $this->banned,
				"text"	=> $ban_text,
			];
			
			// добавить IP в бан на сутки
			memcached()->set(realIp(), $ban_info, $ban_time);
			
			exit();
		}
		
		
		/**
		 * Разбанить пользователя.
		 *
		 */
		public function unban()
		{
			memcached()->delete(realIp());
		}  
		
		/**
		 * Получить ID пользователя. Если ID не установлен, то надо создать пользователя в БД
		 * 
		 */
		public function getId()
		{
			// если ID не установлен
			if (!$this->id) {
				// сохраняем в БД и получаем ID оттуда
				$this->save();
				
				// т.к. уже есть ID, добавляем в куки
				$this->toCookie();
			}
			
			return $this->id;
		}
		
		public function beforeSave()
		{
			// если впервые сохраняем пользователя
			if ($this->isNewRecord) {
				// запоминаем его данные
				$this->ip 			= realIp();
				$this->date_created = now();
			}
		}
		
		
		/**
		 * Значения по умолчанию.
		 * 
		 */
		public function setDefaults()
		{
			$this->id_last_seen_task = 0;
		}
		
		/**
		 * Обновить ID последней просмотренной задачи в СЕССИИ.
		 * 
		 */
		public function updateLastSeenTask($id_task)
		{
			if ($this->id_last_seen_task < $id_task) {
				$this->id_last_seen_task = $id_task;
				$this->toSession();
			}
		}
		
		
		/**
		 * Обновить ID последней просмотренной задачи в БД.
		 * 
		 */
		public function saveLastSeenTask()
		{
			$this->save("id_last_seen_task");
		}
		
		/**
		 * Получить новую задачу
		 * 
		 */
		public function getNewTask()
		{
			// Получаем новую задачу (true  – если ничего не найдено, возвратить NULL-задачу)
			$NewTask = Task::find([
				"condition"	=> "id > {$this->id_last_seen_task} AND id_user != ". $this->getId() . " AND active=1"
			], true);
			
			// Обновить ID последней просмотренной задачи
			$this->updateLastSeenTask($NewTask->id);
			
			return $NewTask;
		}
		
		/**
		 * Получить новые задачи
		 * $count -  количество задач
		 * 
		 */
		public function getNewTasks($count)
		{
			// Получаем новые задачи
			$NewTasks = Task::findAll([
				"condition"	=> "id > {$this->id_last_seen_task} AND id_user != {$this->getId()} AND active=1",
				"limit"		=> $count
			]);
			
			// Заполняем пустыми задачами, если недостаточно для отображения
			for ($i=0; $i < $count; $i++) {
				if (!$NewTasks[$i]) {
					$NewTasks[$i] = Task::nullTask();
				}
			}
			
			// Обновляем last seen task
			foreach ($NewTasks as $NewTask) {
				$this->updateLastSeenTask($NewTask->id);
			}
			
			return $NewTasks;
		}
		
		/*
		 * Вход/запись пользователя в сессию
		 * $start_session – стартовать ли сессию?
		 */
		public function toSession($start_session = false)
		{
			// Если стартовать сессию
			if ($start_session) {
				session_set_cookie_params(3600 * 24,"/"); // PHP сессия на сутки
				session_start();
			}
			
			$_SESSION["user"] = $this;
		}
		
		
		/**
		 * Установить задачу в сиссию.
		 * 
		 */
		public function setTask($Task)
		{
			$this->Task = $Task;
			$this->toSession();
		}
		
		 /*
		  * Добавляем ID пользователя в куки
		  */
		public function toCookie()
		{
			$this->token = md5(self::SALT . $this->getId()  . self::SALT);
			
			// Remember me token в КУКУ
			setcookie("vtoken", $this->token . $this->getId(), cookieTime(), "/");	// КУКА ТОКЕНА (первые 16 символов - токен, последние - id_user)
		}
		

	}