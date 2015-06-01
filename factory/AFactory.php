<?php
	
	
	/**
	 * Класс статической коллекции. Типа классов, филиалов, статусов заявки.
	 */
	class Factory {
		
		// Все записи коллекции
		static $all = false;
		
		// Заголовок коллекции
		static $title = false;

		/**
		 * Построить селектор из всех записей.
		 * $selcted - что выбрать по умолчанию
		 * $name 	– имя селектора, по умолчанию имя класса
		 * $attrs	– остальные атрибуты
		 * 
		 */
		public static function buildSelector($selcted = false, $name = false, $attrs = false)
		{
			$class_name = strtolower(get_called_class());
			echo "<select class='form-control' id='".$class_name."-select' name='".($name ? $name : $class_name)."' ".Html::generateAttrs($attrs).">";
			if (static::$title) {
				echo "<option selected disabled>". static::$title ."</option>";
				echo "<option disabled>──────────────</option>";
			}
			foreach (static::$all as $id => $value) {
				echo "<option value='$id' ".($id == $selcted ? "selected" : "").">$value</option>";
			}
			echo "</select>";
		}
		
		
		/**
		 * Создать для ng-options ануляра.
		 * 
		 */
		public static function angInit()
		{
			return angInit(strtolower(get_called_class()), static::$all);
		}
		
	}