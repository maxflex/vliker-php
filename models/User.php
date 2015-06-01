<?php
	class User extends Model
	{
		const SALT 					= "32dg9823dldfg2o001-2134>?erj&*(&(*^";	// Для генерации кук
		
		/*====================================== ПЕРЕМЕННЫЕ И КОНСТАНТЫ ======================================*/

		public static $mysql_table	= "users";
		
		/*====================================== СИСТЕМНЫЕ ФУНКЦИИ ======================================*/
		
		
		/*====================================== СТАТИЧЕСКИЕ ФУНКЦИИ ======================================*/
		
		
		
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
					$User = self::add();
					
					// Пушим пользователя в куки и сессию
					$User->toSession(true);
					$User->toCookie();
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
			
			// Получаем пользователя по ID (чтобы из его параметров генерировать хэш)
			$User = User::findById($cookie_user);
			
			// Если пользователь найден
			if ($User) {
				// Пытаемся найти пользователя
				$RememberMeUser = self::find(array(
					"condition"	=> "id=".$cookie_user,
				));
				
				// Если пользователь найден
				if ($RememberMeUser) {
					// Логинимся (и создаем сессию)
					$RememberMeUser->toSession(true);
					
					return true;
				} else {
					return false;
				}
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
				$User = User::findById($_SESSION["user"]->id);
				$User->toSession();
			} else {
				// Получаем пользователя из СЕССИИ
				$User = $_SESSION["user"];
			}
						
			// Возвращаем пользователя
			return $User;
		}	

		/*====================================== ФУНКЦИИ КЛАССА ======================================*/
		
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
		 * Обновить ID последней просмотренной задачи.
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
		 * Получить новую задачу
		 * 
		 */
		public function getNewTask()
		{
			// Получаем новую задачу
			$NewTask = Task::find([
				"condition"	=> "id > {$this->id_last_seen_task} AND id_user != {$this->id} AND active=1"
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
				"condition"	=> "id > {$this->id_last_seen_task} AND id_user != {$this->id} AND active=1",
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
			$this->token = md5(self::SALT . $this->id  . self::SALT);
			
			// Remember me token в КУКУ
			$cookie_time = time() + 3600 * 24 * 30 * 12 * 2; // час - сутки - месяц * 3 * 2 = КУКА на 2 года
			setcookie("vtoken", $this->token . $this->id, $cookie_time);	// КУКА ТОКЕНА (первые 16 символов - токен, последние - id_user)
		}
		

	}