<?php
	// Глобальные функции сайта
	
	/*
	 * Пре-тайп
	 */
	function preType($anything, $exit = false)
	{
		echo "<pre>";
		print_r($anything);
		echo "</pre>";
		
		if ($exit)
		{
			exit();
		}
	}
	
	/*
	 * Возвращает соединение DB_SETTINGS
	 */
	function dbConnection()
	{
		global $db_connection;
		return $db_connection;
	}
	
	
	/**
	 * Возвращает соединение MemcacheD.
	 * 
	 */
	function memcached() 
	{
		global $memcached;
		return $memcached;
	}
	
	/*
	 * Создаем подключение к БД user_x
	 */
	function initUserConnection($id_user)
	{
		global $db_user; 

		// Открываем соединение с основной БД		
		$db_user = new mysqli(DB_HOST, DB_LOGIN, DB_PASSWORD, DB_PREFIX."user_{$id_user}");
		
		// Установлено ли соединение
		if (mysqli_connect_errno($db_user))
		{
			die("Failed to connect to USER {$id_user} MySQL: " . mysqli_connect_error());
		}
		
		// Устанавливаем кодировку
		$db_user->set_charset("utf8");		
	}
	
	/*
	 * Показываем к какой таблице пользователя подключены (бд user_x)
	 */
	function showDbUser()
	{
		global $db_user;
		echo $db_user->query("SELECT DATABASE()")->fetch_array()[0];
	}
	
	/*
	 * Получает текущее время
	 */
	function now()
	{
		return date("Y-m-d H:i:s");
	}
	
	/*
	 * Обрезает пробелы и извлекает теги
	 */
	function secureString($string)
	{
		return trim(strip_tags($string));
	}
	
	/*
	 * Настоящий IP пользователя
	 */
	function realIp()
	{
	    $client  = @$_SERVER['HTTP_CLIENT_IP'];
	    $forward = @$_SERVER['HTTP_X_FORWARDED_FOR'];
	    $remote  = $_SERVER['REMOTE_ADDR'];
	
	    if(filter_var($client, FILTER_VALIDATE_IP))
	    {
	        $ip = $client;
	    }
	    elseif(filter_var($forward, FILTER_VALIDATE_IP))
	    {
	        $ip = $forward;
	    }
	    else
	    {
	        $ip = $remote;
	    }
	
	    return $ip;
	}
	
	/*
	 * Включить PARTIAL
	 * $string	– название включаемого файла
	 * $vars	– переменные, которые будут доступны в файле
	 */
	function partial($string, $vars = array())
	{
		// Если передаем переменные в инклуд, то объявляем их здесь (иначе будут недоступны)
		if (!empty($vars)) {
			// Объявляем переменные, соответсвующие элементам массива
			foreach ($vars as $key => $value) {
				$$key = $value;
			}
		}
			
		$called_dir = dirname(debug_backtrace()[0]["file"]);	// Получаем путь к директории, откуда была вызвана функция
		
		include_once($called_dir."/_".$string.".php");
	}
	
	
	/*
	 * Включить глобальный PARTIAL
	 * $string	– название включаемого файла
	 * $vars	– переменные, которые будут доступны в файле
	 */
	function globalPartial($string, $vars = array())
	{
		// Если передаем переменные в инклуд, то объявляем их здесь (иначе будут недоступны)
		if (!empty($vars)) {
			// Объявляем переменные, соответсвующие элементам массива
			foreach ($vars as $key => $value) {
				$$key = $value;
			}
		}
					
		include_once(BASE_ROOT."/views/_partials/_".$string.".php");
	}
	
	/*
	 * В формат ангуляра
	 */
	function angInit($name, $Object)
	{
		return $name." = ".htmlspecialchars(json_encode($Object, JSON_NUMERIC_CHECK));
	}
	
	/*
	 * Преобразование true/false в 1/0 для сохранения в БД
	 */
	function trueFalseConvert(&$array)
	{
		foreach ($array as $key => $val)
		{
			if ($val === "true") {
				$array[$key] = true;
			} elseif ($val === "false") {
				$array[$key] = false;
			}
		}
	}
	
	/*
	 * Возвратить значение, если оно установлено
	 * $value 	- проверяемое значение
	 * $pre		- если значение установлено, добавить при выводе
	 */ 
	function ifSet($value, $pre = "")
	{
		return (isset($value) ? $pre.$value : "");
	}
	
	/*
	 * Создать URL
	 * $params = array (controller, action, text, 
	 * params - массив, дополнительные параметры, будут переданы в GET 
	 * htmlOptions - массив, аттрибуты HTML элемента)
	 */
	function createUrl($params)
	{
		// Если есть опции HTML (атрибуты)
		if (isset($params["htmlOptions"])) {
			foreach ($params["htmlOptions"] as $option => $value) {
				$htmlOptions .= $option."='$value' ";
			}
		}
		
		echo "<a $htmlOptions href='".$params['controller']
			 	.ifSet($params["action"])
			 	.(isset($params["params"]) ? "&".http_build_query($params["params"]) : "")."'>"
			 	.$params["text"]."</a>";
	}
	
	/*
	 * Проверяет активен ли пункт меню
	 * $controller	– контроллер, при котором пункт меню активен
	 * $action		– экшн, при котором пункт меню становится активен
	 * $paramsNotEqual (array) - дополнительные параметры для сравнения, которые должны быть не равны 	[ВАЖНО: параметр берется из $_GET]
	 * $paramsEqual (array) – дополнитльные параметры для сравнения, которые должны быть равны 			[ВАЖНО: параметр берется из $_GET]
	 */
	function menuActive($controller, $action = null, $paramsEqual = array(), $paramsNotEqual = array())
	{
		// Проверяем контроллер
		if ($_GET["controller"] != $controller) {
			return;
		}
		
		// Проверяем экшн
		if (isset($action) && $_GET["action"] != $action) {
			return;
		}
		
		// Проверяем дополнительные параметры НЕРАВЕНСТВА
		foreach ($paramsNotEqual as $param_name => $param_val) {
			if ($param_val == $_GET[$param_name]) {
				return;
			}
		}
		
		// Проверяем дополнительные параметры РАВЕНСТВА
		foreach ($paramsEqual as $param_name => $param_val) {
			if ($param_val != $_GET[$param_name]) {
				return;
			}
		}
		
		// Если все проверки пройдены, возвращаем активный класс
		return "class='active'";
	}
	
	/*
	 * Удаляем куку
	 * $cookie_name – какую куку удаляем
	 * $domain – где удаляется кука (это нужно было для очистки куки PHPSESSID, она удаляется с домена «/», а ratie_token с пустого только)
	 */
	function removeCookie($cookie_name, $domain = "") 
	{
		unset($_COOKIE[$cookie_name]);
		setcookie($cookie_name, "", time() - 3600, $domain);
	}
	
	/*
	 * Функция возвращает настройки $GLOBALS['settings']
	 */
	function settings()
	{
		return $GLOBALS["settings"];
	}
	
	/*
	 * Функция просто отображает через H1 (для тестирования)
	 */
	function h1($text)
	{
		echo "<h1 class='text-white'>$text</h1><br>";
	}
	
	/*
	 * Функция выводит дату в относительном формате
	 * $dont_format - не переводить время из DateTime
	 */
	function relativeDate($date, $dont_format = false) // $date --> время в формате Unix time
	{
		if ($date == "0000-00-00 00:00:00" || $date == null || $date == 0) {
			return "Время неизвестно";
		}
		
		// Если нужно форматировать дату
		if (!$dont_format) {
			$date = strtotime($date);	
		}
		
	    $stf = 0;
	    $cur_time = time();
	    $diff = $cur_time - $date;
	 
	    $seconds = array('секунда', 'секунды', 'секунд');
	    $minutes = array('минута', 'минуты', 'минут');
	    $hours = array('час', 'часа', 'часов');
	    $days = array('день', 'дня', 'дней');
	    $weeks = array('неделя', 'недели', 'недель');
	    $months = array('месяц', 'месяца', 'месяцев');
	    $years = array('год', 'года', 'лет');
	    $decades = array('десятилетие', 'десятилетия', 'десятилетий');
	 
	    $phrase = array($seconds, $minutes, $hours, $days, $weeks, $months, $years, $decades);
	    $length = array(1, 60, 3600, 86400, 604800, 2630880, 31570560, 315705600);
	 
	    for ($i = sizeof($length) - 1; ($i >= 0) && (($no = $diff / $length[$i]) <= 1); $i--) ;
	    if ($i < 0) $i = 0;
	    $_time = $cur_time - ($diff % $length[$i]);
	    $no = floor($no);
	    $value = sprintf("%d %s ", $no, getPhrase($no, $phrase[$i]));
	 
	    if (($stf == 1) && ($i >= 1) && (($cur_time - $_time) > 0)) $value .= time_ago($_time);
	 
	    return $value . ' назад';
	}
	function getPhrase($number, $titles)
	{
	    $cases = array (2, 0, 1, 1, 1, 2);
	    return $titles[ ($number%100>4 && $number%100<20)? 2 : $cases[min($number%10, 5)] ];
	}
	
	/*
	 * Перобразовать в строку
	 */
	function toString($str)
	{
		return "'".$str."'";
	}
	
	/*
	 * Строка в цвет
	 */
	function stringToColor($str) {
	  $code = dechex(crc32($str));
	  $code = substr($code, 0, 6);
	  return $code;
	}
	
	/*
	 * JSON-ответ
	 */
	function toJson($response)
	{
		echo json_encode($response);
	}
	
	/*
	 * JSON-ответ
	 */
	function jsonResponse($response)
	{
		exit(json_encode($response));
	}
	
	/**
	 * Проверить есть ли хотя бы одно значение в массиве.
	 * 
	 */
	function hasValues($array)
	{
		return (count(array_filter($array)) > 0);
	}
?>