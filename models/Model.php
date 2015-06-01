<?php

	// Скелет модели
	class Model
	{
		// Таблица модели
		public static $mysql_table = NULL;
		
		// Переменные из таблицы (которые надо сохранять и тд)
		public $mysql_vars = array();
		
		// Переменные из таблицы MySQL, которые сохранять не надо
		protected $_exclude_vars = array("id");
		
		// Если есть сериализованные данные в БД, то указать здесь для авто сериализации/ансериализации
		protected $_serialized = array();
		
		// Числа через запятую, для хранения чекбоксов и других значений ( строка "1, 2, 5" => array (1, 2, 5))
		protected $_inline_data = array();
		
		// Переменные
		public $isNewRecord = true;			// Новая запись
		
		// Конструктор
		public function __construct($array = false)
		{	
			// Запрос к текущей БД на показ столбцов
			$Query = static::dbConnection()->query("SHOW COLUMNS FROM ".static::$mysql_table);
						
			// Динамически создаем переменные на основе таблицы	
			while ($data = $Query->fetch_assoc())
			{
				$this->mysql_vars[] = $data["Field"];
			}
						
			// Если создаем по массиву
			if (is_array($array))
			{
				foreach ($array as $key => $val)
				{	
					$this->{$key} = $val;
				}
				
				// Если есть ID - он уже не новая запись
				if ($this->id)
				{
					$this->isNewRecord = false;	
				}
			}
			
			// Если есть сериализованные данные – делаем ансериалайз для доступности (потом перед сохранением назад в сериалайз)
			if (count($this->_serialized)) {
				foreach ($this->_serialized as $serialized_field) {
					// При создании нового объекта может передаваться уже нормальный unserialized массив,
					// если создавать объект класса вручную через new ClassName(array(social => array(...))
					// Поэтому если тип $this->{$serialized_field} уже массив, то ничего разселиализовывать не нужно
					if (!is_array($this->{$serialized_field})) {
						$this->{$serialized_field} = unserialize($this->{$serialized_field});
					}
				}
			}
			
			// Если есть inline-данные
			if (count($this->_inline_data)) {
				foreach ($this->_inline_data as $inline_data_field) {
					// Читать описание выше, для сериализованных данных
					if (!is_array($this->{$inline_data_field})) {
						$this->{$inline_data_field} = explode(",", $this->{$inline_data_field});
					}
				} 
			}
		}
		
		/*
		 * Получаем все записи
		 * $params - дополнительные параметры (condition - дополнительное условие, order - параметры сортировки)
		 */
		public static function findAll($params = array())
		{
			// Получаем все данные из таблицы + доп условие, если есть
			$result = static::dbConnection()->query("
				SELECT * FROM ".static::$mysql_table." 
				WHERE true ".(!empty($params["condition"]) ? " AND ".$params["condition"] : "") // Если есть дополнительное условие выборки
				.(!empty($params["order"]) ? " ORDER BY ".$params["order"] : "")				// Если есть условие сортировки
				.(!empty($params["limit"]) ? " LIMIT ".$params["limit"] : "")					// Если есть условие лимита
			);
							
			// Если успешно получили и что-то есть
			if ($result && $result->num_rows) {
				// Получаем имя текущего класса
				$class_name = get_called_class();
				
				// Создаем массив объектов
				while ($array = $result->fetch_assoc()) {
					$return[] = new $class_name($array);
				}
				
				// Возвращаем массив объектов
				return $return;
			}
			else {
				return false;
			}
		}
		
		/*
		 * Получаем одну запись
		 * $params - дополнительные параметры (condition - дополнительное условие, order - параметры сортировки)
		 */
		public static function find($params = array())
		{
			// Получаем все данные из таблицы + доп условие, если есть
			$result = static::dbConnection()->query("
				SELECT * FROM ".static::$mysql_table."
				WHERE true ".(!empty($params["condition"]) ? " AND ".$params["condition"] : "") // Если есть дополнительное условие выборки
				.(!empty($params["order"]) ? " ORDER BY ".$params["order"] : "")				// Если есть условие сортировки
				." LIMIT 1");
	
			// Если запрос без ошибок и что-то нашлось
			if ($result->num_rows)
			{
				// Создаем объект
				$array = $result->fetch_assoc();
				
				// Получаем название класса
				$class_name = get_called_class();
				
				// Возвращаем объект
				return new $class_name($array);
			}
			else
			{
				return false;
			}
		}
		
		/* 
		 * Загрузка записи по ID
		 */
		public static function findById($id)
		{
			// Получаем все данные из таблицы
			$result = static::dbConnection()->query("SELECT * FROM ".static::$mysql_table." WHERE id=".$id);
					
			// Если запрос без ошибок и что-то нашлось
			if ($result->num_rows)
			{
				// Создаем объект
				$array = $result->fetch_assoc();
				
				// Получаем название класса
				$class_name = get_called_class();
				
				// Возвращаем объект
				return new $class_name($array);	
			}
			else
			{
				return false;
			}
		}
		
		
		/**
		 * Подсчитать количество записей.
		 * 
		 */
		public static function count($params = array())
		{
			// Получаем количество из условия
			$result = static::dbConnection()->query("SELECT COUNT(*) as c FROM ".static::$mysql_table."
				WHERE true ".(!empty($params["condition"]) ? " AND ".$params["condition"] : "") // Если есть дополнительное условие выборки
			);
			
			// Возвращаем кол-во
			return $result->fetch_object()->c;
		}
		
		
		/*
		 * Получаем ID последней записи
		 */
		public static function lastId()
		{
		 	return static::dbConnection()
		 		->query("SELECT * FROM ".static::$mysql_table." ORDER BY id DESC LIMIT 1")
		 		->fetch_assoc()["id"];
		}
		
		
		/**
		 * Получить только ID объектов по условию.
		 * 
		 */
		public static function getIds($params)
		{
			// Получаем все данные из таблицы + доп условие, если есть
			$result = static::dbConnection()->query("
				SELECT id FROM ".static::$mysql_table." 
				WHERE true ".(!empty($params["condition"]) ? " AND ".$params["condition"] : "") // Если есть дополнительное условие выборки
				.(!empty($params["order"]) ? " ORDER BY ".$params["order"] : "")				// Если есть условие сортировки
				.(!empty($params["limit"]) ? " LIMIT ".$params["limit"] : "")					// Если есть условие лимита
				);
			
			// Если запрос без ошибок и что-то нашлось
			if ($result->num_rows) {
				// Создаем массив айдишников
				while ($array = $result->fetch_assoc()) {
					$ids[] = $array["id"];
				}
				
				return $ids;
			} else {
				return false;
			}
		}
		 
		/*
		 * Функция определяет соединение БД
		 */
		public static function dbConnection()
		{
			return dbConnection();	// По умолчанию возвращает подключение к бд Settings
		}
		
		
		/**
		 * Подогнать данные из массива в объект.
		 * @example Object $A = {level: 7, name: "Bloodseeker"} и $_POST[a] = array ('level' => 7, name => 'Bloodseeker') 
		 *
		 * $save - сохранять сразу полсе обновления?  
		 */
		public function update(array $data, $save = true)
		{
			// Если в массиве нет никаких данных
			if (is_array($data) && !hasValues($data)) {
				return false;
			}
			foreach ($data as $key => $value) {
				// Обновление обычных данных
				$this->{$key} = $value;
			}
			
			// Если надо сразу сохранить
			if ($save) {
				return $this->save();
			} else {
				return true;
			}
		}
		
		
		/**
		 * Обновить по ID и сохранить.
		 * 
		 */
		public static function updateById($id, $data)
		{
			$Object = self::findById($id); // находим объект
			
			// если найден, сохраняем
			if ($Object) {
				$Object->update($data);
			} else {
				return false;
			}
		}
		
		/*
		 * Сохранение
		 * $single_field – если изменять надо только одно поле в БД (чтобы не напрягать БД)
		 */
		 public function save($single_field = false)
		 {
		 	// Перед сохранением
		 	if (method_exists($this, "beforeSave")) {
			 	$this->beforeSave();
		 	}
		 	
		 	
		 	// Проверяем есть ли в бд шидзе с таким ID
			if ($this->isNewRecord)
			{
				// Составляем запрос на добавление новой записи
			 	foreach($this->mysql_vars as $field)
			 	{
			 		if (in_array($field, $this->_exclude_vars)) // Пропускаем поля, которые не надо сохранять
			 			continue;
			 		
			 		// Если значение установлено, будем его сохранять
			 		if (isset($this->{$field}))
			 		{
				 		$into[]		= $field;
				 		
				 		// Если текущее поле в формате serialize
				 		if (in_array($field, $this->_serialized)) {
					 		$values[]	= "'".serialize($this->{$field})."'";		// Сериализуем значение обратно
				 		} else if (in_array($field, $this->_inline_data) && is_array($this->{$field})) {
					 		$values[]	= "'".implode(",", $this->{$field})."'";		// inline-данные назад в строку
					 	} else {
					 		$values[]	= "'".$this->{$field}."'";					// Оборачиваем значение в кавычки		
				 		}				 		
			 		}
			 	}

				$result = static::dbConnection()->query("INSERT INTO ".static::$mysql_table." (".implode(",", $into).") VALUES (".implode(",", $values).")");
				// echo "The query was: "."INSERT INTO ".static::$mysql_table." (".implode(",", $into).") VALUES (".implode(",", $values).")";
				if ($result) {
					$this->id = static::dbConnection()->insert_id; 	// Получаем ID
					$this->isNewRecord = false;						// Уже не новая запись
					
					// После сохранения 
					if (method_exists($this, "afterSave")) {
						$this->afterSave();								// После сохранения
					}
					
					return $this->id;
				} else {
					return false;
				}
			}	
			else
			{
				// Если изменять только одно поле в БД 
				if ($single_field) {
					$query[] = $single_field." = '".$this->{$single_field}."'";
				} else {
				// Иначе сохраняем все
					// Составляем запрос на сохранение всего
				 	foreach($this->mysql_vars as $field)
				 	{
				 		if (in_array($field, $this->_exclude_vars)) // Пропускаем поля, которые не надо сохранять
				 			continue;
				 		
				 		// Если текущее поле в формате serialize
					 	if (in_array($field, $this->_serialized)) {
					 		$query[] = $field." = '".serialize($this->{$field})."'";	// Сериализуем значение
					 	} else if (in_array($field, $this->_inline_data) && is_array($this->{$field})) {
					 		$query[] = $field." = '".implode(",", $this->{$field})."'";	// Превращаем в строку
					 	} else {
						 	$query[] = $field." = '".$this->{$field}."'";
					 	}
				 	}
				}
				
							 					
				$result = static::dbConnection()->query("UPDATE ".static::$mysql_table." SET ".implode(",", $query)." WHERE id=".$this->id);
				
				if ($result) {
					$this->afterSave();	// После сохранения
					return $this->id;
				} else {
					return false;
				}
			}	
		 }
		 
		 /*
		  * Перед сохранением
		  */
		 public function beforeSave()
		 {
			 // Будет переопределяться в child-классах
		 }
		 
		 /*
		  * После сохранения
		  */
		 public function afterSave()
		 {
			 // Будет переопределяться в child-классах
		 }
		 
		 /*
		  * Полностью удалить модель
		  */
		 public function delete()
		 {
		 	// Удаляем из БД
			static::dbConnection()->query("DELETE FROM ".static::$mysql_table." WHERE id=".$this->id);
			
			// Удаляем объект
			unset($this);
		 }
		 
		 /**
		 * Удалить модель по ID.
		 * 
		 */
		public static function deleteById($id)
		{
			// Удаляем из БД
			static::dbConnection()->query("DELETE FROM ".static::$mysql_table." WHERE id=".$id);
		}
		
		/*
		 * Удалить несколько записей
		 */
		public static function deleteAll($params)
		{
			// Удаляем из БД
			static::dbConnection()->query("DELETE FROM ".static::$mysql_table." WHERE ".$params["condition"]);	
		}
		 
		 /*
		 * Возвращает массив с сохраняемой в БД информацией 
		 * $select –  выбрать конкретные поля
		 */
		public function dbData($select = false)
		{
			// Возвращаем только те данные, которые берутся из БД
			foreach ($this->mysql_vars as $var) {
				if ($select && !in_array($var, $select)) { 	// Если нужно выбрать конкретные поля
					continue;								// то пропускаем ненужные
				}
				$return[$var] = $this->{$var};
			}
			
			// Если в БД еще есть сериализованные данные
			if (count($_serialized)) {
				foreach ($_serialized as $var) {
					if ($select && !in_array($var, $select)) { 	// Если нужно выбрать конкретные поля
						continue;								// то пропускаем ненужные
					}
					$return[$var] = $this->{$var};
				}
			}
			
			return $return;
		}
		
		
		/**
		 * Создать и сохранить объект текущего класса + сохранить.
		 * 
		 */
		public static function add($array = false) {
			// Если передан массив и значений у массива нет – выйти
			if (is_array($array) && !hasValues($array)) {
				return false;
			}
			
			// Получаем название класса, из которого была вызвана функция
			$calledClassName = get_called_class(); 
			
			// Создаем объект этого класса
			$Object = new $calledClassName($array);
			
			// Сохраняем объект
			$Object->save();
			
			// Возвращаем объект
			return $Object;
		}
		
		
		/**
		 * Добавить взаимосвязь с другим объектом.
		 * 
		 * $name – имя связи (будет доступен по $InitialObject->Name)
		 * $save - сохранить ссылку на взаимосвязь в БД?
		 */
		public function addRelation($name, $Object, $save = false)
		{
			// Преобразуем название в нижний регистр
			$name = strtolower($name);
			
			// Добавляем взаимосвязь с объектом (название переменной с большой буквы)
			$this->{ucfirst($name)} = $Object;
			
			// Добавляем ID взаимосвязи
			$this->{"id_" . $name} = $Object->id;
			
			// если нужно сохранить
			if ($save) {
				$this->save("id_" . $name);
			} 
		}
		
		
		/**
		 * Найти и добавить связь с другой таблицей, если она не указана.
		 * 
		 */
		public function getRelation($ClassName)
		{
			// Название добавляемого поля поля (id_request, например)
			$id_string = "id_" . strtolower($ClassName);
			$id_current = "id_" . strtolower(get_called_class());
			
			$result = static::dbConnection()->query("SELECT id FROM ".$ClassName::$mysql_table." WHERE $id_current=".$this->id);
			
			if ($result->num_rows) {
				$this->{$id_string} = $result->fetch_row()[0];	
			}
		}
	}

?>