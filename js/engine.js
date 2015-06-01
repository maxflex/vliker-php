	
	// ID задач, куда были поставлены лайки
	var task_ids = []
	
	$(document).ready(function() {
	})
	
	$(window).on("focus", function() {
		unblockDiv()
	})
	
	function blockDiv() {
		$("#div-blocker").show()
		$("#hint-header").html("<span class='text-error'><span class='glyphicon glyphicon-remove'></span> Поставьте лайк в открывшейся вкладке, чтобы продолжить накрутку</span>")
	}
	function unblockDiv() {
		$("#div-blocker").hide()
		$("#hint-header").html("Жмите «мне нравится» на страницах ниже")
	}
	
	function taskClick(img, task) {
		openInNewTab(task.url)
		
		task_ids.push(task.id)
		
		total_likes = parseInt($("#likes-count").html())
		if (isNaN(total_likes)) {
			total_likes = 0
		}
		$("#likes-count").html(total_likes + 1)
		
		blockDiv()
		
		id_block = $(img).parent().attr("data-block-id");
		loadNewTask(id_block)
	}
	
	
	function loadNewTask(id_block) {
		ajaxStart();
		$.post("task/getNew", {}, function(response) {
			ajaxEnd()
			$("#block-" + id_block).html(response)
		})
	}
	
	function stop() {
		// Количество накрученных лайков
		likes_count = task_ids.length;
		
		// Если прокликано задач меньше 3х
		if (likes_count < 3) {
			bootbox.alert("Поставьте хотя бы 3 лайка, чтобы завершить накрутку")
			return
		}
				
		ajaxStart()
		// ставим лайки задачам
		$.post("task/stop", {task_ids}, function(response) {
			ajaxEnd()
			
			// Обнуляем задачи, которым нужно накрутить лайки при нажатии "Стоп"
			task_ids = [] 
			
			bootbox.alert("Вам будет накручено <span class='text-success'><b>+" 
				+ likes_count + "</b><span class='glyphicon glyphicon-heart glyphicon-middle'></span></span>", function() {
				stopAnimation()	
			})
		})
	}