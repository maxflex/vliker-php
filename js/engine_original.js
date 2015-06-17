	
	// ID задач, куда были поставлены лайки
	var task_data = []
	
	// ID задач, куда надо оставить репорт
	var task_report_ids = []
	
	// Анимация – это кодовое слово для хранения условий засчёта лайка (чтобы не было подозрений у тех, кто смотрит код)
	// Лайк засчитывается по следующей схеме: https://pp.vk.me/c621829/v621829117/2c6cb/B7W1Exkpw28.jpg
	// @explain:  внутри массива есть 6 пустых массивов – в них хранятся события по каждому блоку
	// Лайк засчитывается только в том случае, если все события по блоку были выполнены в правильном порядке
	var animations = [[],[],[],[],[],[]]

	// Сами события: 
	// ME – Mouse Enter
	// MD - Mouse Down (раньше было Mouse Click)
	// WB - Window Blur
	// WF - Window Focus
	// MM - Mouse Move (не используется. вместо этого CORE_ENGINE)
	var STATES = {
		ME : 'ME',
		MD : 'MD',
		WB : 'WB',
		WF : 'WF',
		MM : 'MM'
	}
	
	// Это говорит о том, что было движение мышки вообще в целом. Очень важный аспект безопасности
	// ВАЖНО: если CORE_ENGINE отличен от 3xXXX - лайк не засчитан (XXX - любые 3 числа)
	var CORE_ENGINE = '0x000'
	
	// Время нажатия на ссылку (TIME START)
	// нужно для подсчета времени, потраченного для проставления лайка
	// (время между событиями window.blur и window.focus)
	var TS;
	
	// Последняя нажатая задача
	var current_task = null;
	// Последний нажатый блок (его ID)
	var id_current_block = null;
	
	/**
	 * После появления квадратиков.
	 * 
	 */
	function bindAfterLoad() {

		$(window)
			// Событие WF, засчитывание лайка, разблокировка дива
			.on("focus", function() {
				// Если есть текущая задача (получается после клика, соответственно
				// при возврате назад на окно она уже должна быть
				// и нажатый блок тоже должен быть обязательно
				if (current_task != null && id_current_block != null) {
					// Если событие еще не было добавлено 
					if ($.inArray(STATES.WF, animations[id_current_block]) === -1) {
						// Добавляем событие
						animations[id_current_block].push(STATES.WF)
					}
					
					// Запоминаем все ID нажатых задач, чтобы поставить туда лайки
					task_data.push({
						"id" : current_task.id,
						"an" : animations[id_current_block],
						"ce" : CORE_ENGINE,
						"ts" : new Date().getTime() - TS
					})
					
					// Очищаем сорщик событий по этому диву
					animations[id_current_block] = []
					
					// Очистка данных
					// current_task = null – нельзя обнулять, иначе не будет работать репорт
					id_current_block = null
					CORE_ENGINE = '0x000'
					
					// Разблокируем окно
					unblockDiv()
				}
			})
			// Событие WB
			.on("blur", function() {
				// если уже был нажат блок
				if (id_current_block != null) {
					console.log(id_current_block)
					// Если событие еще не было добавлено 
					if ($.inArray(STATES.WB, animations[id_current_block]) === -1) {
						// Добавляем событие
						animations[id_current_block].push(STATES.WB)
						
						// Запоминаем время 
						TS = new Date().getTime();
					}
				}	
			})
			// Событие MM
			.on("mousemove", function() {
				// Если CORE_ENGINE не установлен
				if (CORE_ENGINE == '0x000') {
					// генерируем его по маске 3xXXX (где XXX - три случайные цифры)
					CORE_ENGINE = (1.5 * 2) + 'x' + Math.round(100 + Math.random() * (9 * 100 - 100))
				}
			})
		
		$(".vliker-block")
			// Учитываем событие ME
			.on("mouseenter", function() {
				// Получаем ID блока (-1, потому что потом будем использовать для обращения в массиве)
				id_block = $(this).attr("data-block-id") - 1
				
				// Если событие еще не было добавлено 
				if ($.inArray(STATES.ME, animations[id_block]) === -1) {
					// Добавляем событие
					animations[id_block].push(STATES.ME)
				}
			})
	}
	
	
	
	/**
	 * Залочить/разлочить DIV квадратиками.
	 * 
	 */
	function blockDiv() {
		$("#div-blocker").show()
		$("#hint-header").html("<span class='text-error'><span class='glyphicon glyphicon-remove'></span> Поставьте лайк в открывшейся вкладке, чтобы продолжить накрутку</span>")
	}
	function unblockDiv() {
		$("#div-blocker").hide()
		$("#hint-header").html("Жмите «мне нравится» на страницах ниже")
	}
	
	
	/**
	 * Добавить лайк (отображение в HTML).
	 * 
	 */
	function addLikeHtml() {
		// общее кол-вол лайков
		total_likes = parseInt($("#likes-count").html())
		
		// если не установлено, то 0
		if (isNaN(total_likes)) {
			total_likes = 0
		}
		
		// начисляем
		$("#likes-count").html(total_likes + 1)
	}
	

	/**
	 * Фукнция нажатия на задачу.
	 * 
	 */
	function clickTask(img, task) {
		// Элемент DIV, внутри которого задача
		div = $(img).parent()
		
		// Получаем ID блока (-1, потому что потом будем использовать для обращения в массиве)
		id_block = div.attr("data-block-id") - 1
		
		// Запоминаем ID последнего нажатого блока
		id_current_block = id_block
		
		// Учитываем событие MC
		// Если событие еще не было добавлено 
		if ($.inArray(STATES.MD, animations[id_block]) === -1) {
			// Добавляем событие
			animations[id_block].push(STATES.MD)
		}
		
		// Запоминаем текущую задачу
		current_task = task
		
		// Открываем по ссылке в новой вкладке
		openInNewTab(task.url)
		
		// Добавить лайк
		addLikeHtml()
		
		// Блокируем DIV
		blockDiv()
		
		// Делаем анфокус дива (потому что глючит анимация и форсируется mouseenter после возвращения в окно)
		div.trigger("mouseout")
		
		// Загружаем новую задачу в блок
		loadNewTask(id_block + 1)
	}
	
	
	/**
	 * Репорт задачи.
	 * 
	 */
	function reportTask()
	{
		// если уже есть нажатая задача
		if (current_task != null) {
			// если жалоба уже была оставлена
			if ($.inArray(current_task.id, task_report_ids) !== -1) {
				notifySuccess("Вы уже оставили жалобу на эту ссылку")
			} else {
				// Запоминаем ID всех задач, которые нужно зарепортить
				task_report_ids.push(current_task.id)
				
				// Удаляем последнюю задачу, ей лайк ставить не надо – она зарепорчена
				task_data.pop()
				
				// Выдаем сообщение, мол, зарепорили, спасибо
				notifySuccess("Жалоба оставлена!")
			}
		} else {
			notifyError("Невозможно оставить жалобу – вы еще ничего не лайкнули")
		}
	}
	
	/**
	 * Грузим новую задачу.
	 * 
	 */
	function loadNewTask(id_block) {
		ajaxStart();
		$.post("task/getNew", {}, function(response) {
			ajaxEnd()
			$("#block-" + id_block).html(response)
		})
	}
	
	
	
	/**
	 * Нажатие на кнопку стоп, подтверждение.
	 * 
	 */
	function stopConfirm()
	{
		// Количество накрученных лайков
		// т.к из основного массива поставленных лайков вычетаются репорты,
		// мы прибавляем к общему количеству количество репортов, чтобы получить реальное число лайков
		likes_count = task_data.length + task_report_ids.length;
		
		// Если прокликано задач меньше 3х
		if (likes_count < 3) {
			bootbox.alert(getIcon("caution") + "Поставьте хотя бы 3 лайка, чтобы завершить накрутку")
			return
		}
		
		bootbox.confirm({
			message: getIcon("heart") + "Вам будет накручено <span class='text-success'><b>+" 
				+ likes_count + "</b>" + glyphIcon('heart glyphicon-middle') + "</span>",
			buttons: {
				confirm: {
					label: "Завершить"
				},
				cancel: {
					label: "Продолжить накрутку",
					className: "btn-default opacity-hover high-opacity pull-left"
				}
			},
			callback: function(result) {
				// если нажали "завершить", то накручиваем лайки
				if (result === true) {
					stop()
				}
			}   
		})
	}
	
	/**
	 * Остановить накрутку и накрутить накрученные.
	 * 
	 */
	function stop() {
		ajaxStart()
		// ставим лайки задачам
		$.post("task/stop", {
			"task_data" 			: task_data,
			"task_report_ids"	: task_report_ids
		}, function(response) {
			ajaxEnd()
			
			// Обнуляем задачи и репорты
			task_data 		= []
			task_report_ids	= []

			// Возвращаем VLiker в исходное состояние
			stopAnimation()
		})
	}