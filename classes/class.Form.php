<?php
/***************************
 * Вспомогательный класс расписания
***************************/

class Schedule
{
	//дни недели	
	public $mon = array();
	public $tue = array();
	public $wed = array();
	public $thu = array();
	public $fri = array();
	public $sat = array();
}

/***************************
 * Класс класса :D
***************************/

class Form
{
	//ID в БД
	public $id;
	
	//Имя класса
	public $form;
	
	//Расписание
	public $schedule;
	
	//Сообщение об успехе/ошибке
	public $report;
	
	/**
	 * Функция сборки
	 * @param string $handler 
	 */
	public function __construct( $handler = '' ) {
		global $wpdb;
		
		$this->schedule = new Schedule;
		
		if( $handler ) {
			if(is_numeric($handler)) $way = 'id';
			else $way = 'form';
			$data = $wpdb->get_row("SELECT * FROM `".JOURNAL_DB_SCHEDULE."` WHERE ".$way."='".$handler."';");
			$this->id = 	$data->id;
			$this->form = 	$data->form;
			
			$days = array('mon', 'tue', 'wed', 'thu', 'fri', 'sat');
			
			foreach ( $days as $day )
				$this->schedule->$day = json_decode($data->$day);				
		}
	}
	
	
	/**
	 * Функция добавления нового класса
	 * @param string $form
	 */
	public function add_new( $form ) {
		global $wpdb;
		
		try {
			//проверяем наличие класса
			$exist_form = $wpdb->get_results("SELECT * FROM `".JOURNAL_DB_SCHEDULE."` WHERE form='".$form."';");
						
			if($exist_form) {
				throw new Exception('Такой класс уже существует!');
			}
			
			//составляем массив данных
			$arr = $this->empty_schedule();
			$arr = array('form' => $form) + $arr;
			
			//запрос к БД
			$wpdb->insert(JOURNAL_DB_SCHEDULE, $arr, array('%s','%s','%s','%s','%s','%s','%s','%s') );
			$wpdb->insert(JOURNAL_DB_SUBJECTS, array('form' => $form), array('%s') );	
			
			//собираем готовый объект
			$this->__construct($form);	
			
			$this->report = "Новый класс успешно создан";
			
		} catch (Exception $e) {
			//ловим исключение
			$this->report = $e->getMessage();
		}
		
		//возвращаем готовый объект
		return $this;
	}
	
	/**
	 * Функция удаления класса
	 * @param string $way
	 */
	public function delete($way = 'id') {
		global $wpdb;
		
		//определяем метод выбора класса
		if($way = 'id') $handler = $this->id;
		else if($way = 'form') $handler = $this->form;
		
		//если метод задан криво, умираем
		else {
			$this->report = "Неверно задан параметр функции";
			return false;
		}
		
		$wpdb->query("DELETE FROM `".JOURNAL_DB_SCHEDULE."` WHERE `".$way."`=".$handler.";");
		$wpdb->query("DELETE FROM `".JOURNAL_DB_SCHEDULE."` WHERE `form`=".$this->form.";");
		$this->report = "Класс успешно удален";
		
		return true;
	}
	
	/**
	 * Функция возвращает пустое расписание
	 * @param string $res
	 * @return mixed
	 */	
	private function empty_schedule($res = 'ARRAY_S') {
		//записываем обозначение пустого урока и точку с запятой в переменные		
		$emp = "--//--";
		$sep = ";";
		
		//массив дней, чтоб по нему прогонять цикл
		$days = array('mon', 'tue', 'wed', 'thu', 'fri', 'sat');
		$arr = new Schedule;
		
		//заполняем объект пустотой
		foreach ( $days as $day ) {
			$sub = array();
			for($i=0; $i<10; $i++) {
				$sub[] = $emp;
			}
			$arr->$day = $sub;	 
		}
		
		//проверяем параметр, который определяет тип возвращаемых данных
		//(по умолчанию ARRAY_S)
		if( !strpos($res, 'ARRAY') ) {
			//приводим объект к массиву			
			$arr = (array) $arr;
			
			if( $res === 'ARRAY_S') {
				//упаковываем подмассивы в строку, возвращаем одномерный массив			
				foreach ($days as $day)  
					$arr[$day] = json_encode( $arr[$day] );
				return $arr;
				
			} else if ( $res === 'ARRAY_A' )
				//возвращаем двумерный массив				
				return $arr;
		}
		
		//иначе возвращаем объект
		return $arr;
	}
	
	/**
	 *	Получает список учеников класса
	 *	@param void
	 *	@return array 
	 */
	public function get_students() {
		global $wpdb;

		$students = array();
		//получаем из дб только id 
		$stud_ids = $wpdb->get_col("SELECT * FROM `" . JOURNAL_DB_STUDENTS . "` WHERE form='" . $this->form . "' ORDER BY student_name;");
		
		//заполняем массив объектами
		foreach ($stud_ids as $id) {
			$student = new Student($id);
			$students[] = $student;
		}

		return $students;
	}

	public function updateDaySchedule($day, $schedule)
	{
		global $wpdb;

		if(!$schedule) {
			$schedule = $this->empty_schedule('OBJ');
			$schedule = $schedule->$day;
		}
		$wpdb->update( JOURNAL_DB_SCHEDULE, array( $day => json_encode($schedule) ), array( 'form' => $this->form ), array('%s'), array('%s') );
	}

	public function show($return = false)
	{
		$presentation = "<h1>" . $this->form . "</h1><ol>";
		$students = "<h1>";
	}
}
?>