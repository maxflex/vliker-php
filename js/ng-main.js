	angular.module("VLiker", ["ngAnimate"])
		.filter('reverse', function() {
			return function(items) {
				return items.slice().reverse();
			};
		})
		/*
			Основной контроллер
		*/
		.controller("MainCtrl", function($scope) {
			// Примеры ссылок
			$scope.example_links = ["http://vk.com/photo236886_332", "http://vk.com/wall123123552"]
			
			// Показать случайный пример
			$scope.example_link = $scope.example_links[Math.floor(Math.random() * $scope.example_links.length)];
			
			// Перейти к примеру
			$scope.goExample = function() {
				$scope.example_clicked = true
				$scope.url = $scope.example_link
			}
			
			// Перейти по пункту меню
			$scope.goMenu = function(id_menu) {
				// Если меню не было выбрано, показываем анимацию
				if (!$scope.id_menu) {
					// Анимация перевода вверх
					topAnimation();	
				}
				
				// Выбираем меню
				if ($scope.id_menu != id_menu) {
					$scope.id_menu = id_menu	
				}
				
				// Начинаем загрузку
				ajaxStart();
				
				switch (id_menu) {
					// Стена
					case 1: {
						$scope.show_wall = false
						$("#content").load("menu/wall", function(response) {
							ajaxEnd()
							$("#row-content").show().addClass("fadeInDown")
							console.log(response);
						});
						break
					}
					// Статистика
					case 2: {
						$scope.show_stats = false
						break
					}
					// Маджазин
					case 3: {
						$scope.show_store = false
						break
					}
					// Инструкции (как пользоваться)
					case 4: {
						$scope.show_instr = false
						break
					}
				}
				$scope.$apply()
			}
			
			// Главная функция. Старт влайкера
			$scope.start = function() {
				ajaxStart()
				$.post("task/blocks", {"url" : $scope.url}, function(response) {
					ajaxEnd()
					// Пытаемся распарсить JSON
					object = toJSON(response)
					
					// Если ответ не в JSON - то это HTML, его просто вывести надо будет
					if (object === false) {
						startAnimation()
					    showContent(response)
					} else {
						$scope.url_error = object.error;
						$scope.$apply()	
					}
				})
			}
		})