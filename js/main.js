	var animation_speed = 300 // Скорость анимаций
	
	// Влайкер. Автор: Массим.
	$(document).ready(function() {
	
	})
	
	
	
	/**
	 * Анимация перевода вверх.
	 * 
	 */
	function topAnimation() {
/*
		$("#animation-block").addClass("animated fadeOutUpBig")
			.one('webkitAnimationEnd mozAnimationEnd MSAnimationEnd oanimationend animationend', function() {
				console.log("ANIMATION END")
				$(this).hide()
				
			}
		)
*/		
		$("#animation-block, #likes-iframe").slideUp(animation_speed)
		$("body").css({'overflow' : 'visible'})
		//$("#row-buttons").delay(50).animate({"top" : "-375px"}, 350)

	}
	
	
	/**
	 * Анимация начала накрутки.
	 * 
	 */
	function startAnimation()
	{
		$("#row-ads, #row-logo, #row-example, #row-menu, #likes-iframe").slideUp(animation_speed)
	}
	
	/**
	 * Анимация конца накрутки.
	 * 
	 */
	function stopAnimation()
	{
		$("#row-ads, #row-logo, #row-example, #row-menu, #likes-iframe").slideDown(animation_speed)
		hideContent()
	}
	
	
	
	/**
	 * Анимация загрузки (начало и конец).
	 * 
	 * @access public
	 * @return void
	 */
	function ajaxStart() {
		NProgress.start()
	}
	
	function ajaxEnd() {
		NProgress.done()
	}
	
	
	/**
	 * Открыть ссылку в новой вкладке.
	 * 
	 */
	function openInNewTab(url) {
		var win = window.open(url, '_blank');
		win.focus();
	}
	
	
	/**
	 * Показать контент.
	 * 
	 */
	function showContent(content) {
		$("#row-content").show().addClass("fadeInDown")
		$("#content").html(content)
	}
	
	/**
	 * Скрыть контент.
	 * 
	 */
	function hideContent() {
		$("#row-content").removeClass("fadeInDown").fadeOut(animation_speed)
		$("#content").html("")
	}
	
	/**
	 * Является ли переменная валидным JSON-объектом?
	 * 
	 */
	function toJSON(object) {
		var IS_JSON = true;
		try 
		{
			return $.parseJSON(object);
		}
		catch(err)
		{
			return false;
		}             
	}