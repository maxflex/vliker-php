<?php
	class Task extends Model
	{
	
		/*====================================== ПЕРЕМЕННЫЕ И КОНСТАНТЫ ======================================*/

		public static $mysql_table	= "tasks";
		
		
		// Провайдер скриншотов
		const THUMBNAIL_SERVICE = "http://mini.s-shot.ru/?";
		
		/*====================================== СИСТЕМНЫЕ ФУНКЦИИ ======================================*/
		
		public function __construct($array = false)
		{
			parent::__construct($array);
			
			// Сокращаем url сразу и запоминаем оригинальный
			$this->url_original = $this->url;
			$this->url = self::shortenUrl($this->url);
		}
		
		
		/*====================================== СТАТИЧЕСКИЕ ФУНКЦИИ ======================================*/
		
		/**
		 * Проверка URL на валидность
		 * 
		 */
		public static function checkUrl($url)
		{	
			if (($url=="http://vk.com/photo236886_332")||($url=="http://vk.com/wall123123552")) {
				return "Это пример. Введите адрес своей страницы, куда хотите накрутить сердечки";
			}
			
			if ($url=="") {
				return "Введите адрес страницы, куда хотите накрутить сердечки!";
			}
			
			if (strpos($url," ")) {
				return "Некорректная ссылка";
			}
			
			if (!preg_match("#https?://(m.)?vk.com/#", $url))  {
				return "Неверный адрес. Ссылка должна начинаться с http://vk.com/";
			}
			
			
			if (((!(strpos($url,"photo")))&&(!(strpos($url,"video")))&&(!(strpos($url,"wall"))))||(!(strpos($url,"_")))) {
				return "Укажите точный адрес фотографии, видео, записи или комментария";
			}
			
			return true;
		}
		
		
		/**
		 * Сократить ссылку.
		 * 
		 */
		public static function shortenUrl($url){
		  //preg_match("~[\S]*((photo|video|wall)[-]?[0-9]+[_][0-9]+)([\?]reply=[0-9]+)?[\S]*~",$lnk,$m);
		   	preg_match("#((photo|video|wall)[-]?[0-9]+[_][0-9]+)([\?]reply=[0-9]+)?#", $url, $m);
		    return "http://vk.com/".$m[1].$m[3];
		}
		
		
		/**
		 * Получить последнюю задачу накрутки.
		 * 
		 */
		public static function lastTask()
		{
			$last_task = $_COOKIE["last_task"];
			
			if ($last_task) {
				return $last_task;
			} else {
				return false;
			}
		}
		
		
		/**
		 * Найти задачу по URL.
		 *
		 */
		public static function findByUrl($url)
		{
			// сокращаем ссылку
			$url = self::shortenUrl($url);
			
			// ищем задачу по ссылке
			return self::find([
				"condition" => "url='$url'"
			]);
		}
		
		
		
		/**
		 * Функция проверяет массив с лайками на валидность.
		 * 
		 * $task_data – данные по задаче, включая её ID, события, проверки, время, потраченное на простановку лайков и тд. 
		 * @return array с ID задач, куда надо поставить лайки
		 */
		public static function verifyLikes($task_data)
		{
			// Количество замечаний (подозрительных лайков)
			$warnings = [];
			
			foreach ($task_data as $task) {
				// Блокируем пользователя, если есть X и более замечаний
				if (count($warnings) >= 5) {
					User::fromSession()->ban($warnings);	
				}
				
				// Проверяем события лайка
				// Должен быть сначала ME => MD => WB => WF
				// как на картинке: https://pp.vk.me/c621829/v621829117/2c6cb/B7W1Exkpw28.jpg
				// (только MC заменен на MD – Mouse Down)
				$events_string = implode(" => ", $task["an"]);
				
				if ($events_string != "ME => MD => WB => WF") {
					$warnings[] = ["Неверная последовательность событий" => $events_string];
					continue;
				}
				
				// Проверяем время, затраченное на простановку лайка
				// (время между событиями window.blur и window.focus)
				// Разрешенное время – от 1 секунды до 2х минут
				$time = floor($task["ts"] / 1000);
				
				if ($time < 1 || $time > (2 * 60)) {
					$warnings[] = ["Неверное время простановки лайка" => ($task["ts"] / 1000)];
					$warnings++;
					continue;	
				}
				
				// Проверяем, был ли MOUSEMOVE, двигал ли пользователь мышью вообще?
				// если двигал, то строка будет начинаться с 3x
				if (strpos($task["ce"], "3x") !== 0) {
					$warnings[] = ["Движение мышью отсутствует" => $task["ce"]];
					continue;	
				}
				
				// Задача прошла все проверки, добавляем ее в валидные
				$valid_task_ids[] = $task["id"];
			}
			
			return $valid_task_ids;	
		}
		
		/**
		 * Поставить лайк задачам.
		 *
		 */
		public static function like($task_ids)
		{
			// Ставим лайки задачам
			static::dbConnection()->query("UPDATE ".static::$mysql_table." SET likes=(likes + 1) WHERE id IN (". implode(",", $task_ids) .")");
			
			// Завершаем выполненные задачи
			static::dbConnection()->query("UPDATE ".static::$mysql_table." SET active=0 WHERE id IN (". implode(",", $task_ids) .") AND likes>=needed");
			
			// Сохраняем ID последней просмотренной в БД
			User::fromSession()->saveLastSeenTask();
		}
				
		/**
		 * Начислить жалобы задачам.
		 * 
		 */
		public static function report($task_report_ids)
		{
			// Ставим репорт задачам
			static::dbConnection()->query("UPDATE ".static::$mysql_table." SET reports=(reports + 1) WHERE id IN (". implode(",", $task_report_ids) .")");
			
			// Завершаем задачи, где репортов больше трёх
			static::dbConnection()->query("UPDATE ".static::$mysql_table." SET active=0 WHERE id IN (". implode(",", $task_report_ids) .") AND reports>=3");
		}

		
		/**
		 * Возвращает пустую задачу (когда задач больше нет).
		 * 
		 */
		public static function nullTask() {
			return new self([
				"id" => null
			]);
		}
				
		/**
		 * Перезаписываем метод FIND, добавляем к основному функционалу возврат пустой задачи,
		 * если больше задач не найдено
		 *
		 * $return_null - возвратить NULL-задачу вместо false, если ничего не найдено
		 */
		public static function find($params = array(), $return_null = false) {
			$result = parent::find($params);
			
			if (!$result && $return_null) {
				return self::nullTask();
			} else {
				return $result;
			}
		}
			
		/*====================================== ФУНКЦИИ КЛАССА ======================================*/
		
		public function beforeSave()
		{
			// добавляем доп. данные для новой задачи
			if ($this->isNewRecord) {
				$this->date_created = now();
				$this->ip 			= realIp();
			}
		}
		
		/**
		 * Добавить лайки текущей задаче.
		 * 
		 */
		public function addLikes($count) {
			// Увеличиваем кол-во лайков
			$this->needed = $this->needed + $count;
			
			// Устанавливаем дату последней активности задачи
			$this->date_active = now();
			
			// Если накручено больше 3х лайков, то активируем и сохраняем задачу
			if ($this->needed >= 3) {
				$this->active = 1;
				$this->save();
				
				// Сохраняем задачу последней накрутки, чтобы предлагать ее вместо примера
				setcookie("last_task", $this->url, cookieTime(), "/");
			}
		}
		
		/**
		 * Отобразить задачу.
		 * 
		 */
		public function display()
		{
			// Если пустая задача
			if ($this->isNull()) {
				echo '
				<div class="card">
				  <figure class="front">
				  </figure>
				  <figure class="back">
				    Новых задач нет
				  </figure>
				</div>';
				echo "<div class='null-task'></div>";
			} else {
				// Какие данные будут использоваться во FRONT-END?
				$task_json = json_encode($this->dbData(["id", "url"]));
				
				echo "
					<img src='".self::THUMBNAIL_SERVICE."{$this->url}' class='thumbnail' onmousedown='clickTask(this, "
						. $task_json .")'>
				";
			}
		}
		
		
		/**
		 * Задача является пустой (задач больше нет).
		 * 
		 */
		public function isNull()
		{
			return $this->id === null;
		}
		
		/**
		 * Получить тип ссылки.
		 * 
		 * @return string photo|video|wall|reply
		 */
		public function linktype(){
			preg_match_all("#photo|video|wall|reply#", $this->url, $m);
			
			// Если есть комментарий
			if (in_array("reply", $m[0])) {
				$m[0][0] = "reply";
			}
			
			return $m[0][0];
		}
		
		/*
		 * Картинка по типу ссылки
		 */
		public function linkTypeImg()
		{
			switch ($this->linktype()) {
				case 'photo': {return "img/i_photo.png";}
				case 'video': {return "img/i_video.png";}
				case 'wall' : {return "img/i_wall.png";}
				case 'reply': {return "img/i_resp.png";}  
				default		: {return "img/i_sub.png";}  
			}
		}
		
	}