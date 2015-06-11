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
			return memcached()->get(realIp());
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
		 * 
		 */
		public function ban()
		{
			// добавить IP в бан на сутки
			memcached()->set(realIp(), true, 60 * 60 * 24);
			
			// начисляем кол-во банов пользователю
			$this->banned++;
			$this->save("banned");
			
			exit();
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
			$cookie_time = time() + 3600 * 24 * 30 * 12 * 2; // час - сутки - месяц * 3 * 2 = КУКА на 2 года
			setcookie("vtoken", $this->token . $this->getId(), $cookie_time, "/");	// КУКА ТОКЕНА (первые 16 символов - токен, последние - id_user)
		}
		

	}