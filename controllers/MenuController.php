<?php	// Контроллер	class MenuController extends Controller	{		public $defaultAction = "test";				// Папка вьюх		protected $_viewsFolder	= "menu";								public function actionWall()		{			$this->renderClean("wall");			}				public function actionConstruct()		{			$this->renderClean("construct");		}				##################################################		###################### AJAX ######################		##################################################							}