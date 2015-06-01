<?php	
	// Если не установлен контроллер, то главная страница
	/*if (empty($_GET["controller"])) {
		include_once("main.php");
		exit();	
	}*/
	
	// Подключаем файл конфигураций
	include_once("config.php");
	
	// Если сессия уже когда-то была начата (если пользователь залогинен), то возобновляем ее
	/*if(isset($_COOKIE["PHPSESSID"])) {
	  session_start();
	}*/
	session_start();
	
	// Получаем названия контроллеров и экшена	
	$_controller	 = $_GET["controller"];	// Получаем название контроллера
	$_action		 = $_GET["action"];		// Получаем название экшена
	
	/* // Проверка на аякс-запрос
	if (strtolower(mb_strimwidth($_action, 0, 4)) == "ajax") {
		
		$_ajax_request = true;
		
		// Это аякс-запрос, к скрипту можно обращаться только через AJAX
		if (strtolower($_SERVER['HTTP_X_REQUESTED_WITH']) != 'xmlhttprequest') {
			die("SECURITY RESTRICTION: THIS PAGE ACCEPTS AJAX REQUESTS ONLY (poshel nahuj)");	// Выводим мега-сообщение
		}
	} else {
		$_ajax_request = false;
	} */
	
	// Если контроллеры пустые, то MAIN controller
	if (!$_controller && !$_action) {
		$_controller = "main";
	}
	
	/* Основные действия */	
	$_controllerName = ucfirst(strtolower($_controller))."Controller";	// Преобразуем название контроллера в NameController
	$_actionName	 = "action".ucfirst(strtolower($_action));			// Преобразуем название экшена в actionName
	
	
	$IndexController = new $_controllerName;	// Создаем объект контроллера
	
	// Запускаем BeforeAction, если существует
	if (method_exists($IndexController, "beforeAction")) {
		$IndexController->beforeAction();
	}
	
	// Если указанный _actionName существует – запускаем его
	if (method_exists($IndexController, $_actionName))
	{
		$IndexController->$_actionName();			// Запускаем нужное действие
	} // иначе запускаем метод по умолчанию
	else
	{
		$IndexController->{"action".$IndexController->defaultAction}();
	}
	
	// Когда понадобится AfterAction – раскомментировать
	/* // Запускаем afterAction, если существует
	if (method_exists($IndexController, "afterAction")) {
		$IndexController->afterAction();
	} */
	
	/*********************/
?>