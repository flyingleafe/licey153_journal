<?php
/********* Функция получения текущего и прошлого года *******/
function licey_get_study_year($date)
{
	$today = date('z');
	if($today < 244) {
		if($date >= '09-01') $year = date('Y')-1;
		else $year = date('Y');
	} else {
		if($date >= '09-01') $year = date('Y');
		else $year = date('Y')+1;
	}
	return $year;
}


/********* Функция перевода названия предмета *********/
function licey_subject_translate($word)
{
	$eng = array("formmaster", "algebra", "geometry", "physics", "russian", "literature", "english", "inform", "chemistry", "biology", "geography", "history", "sociology", "economy", "fizra", "drawing", "bashkort", "obj");
	$rus = array("Кл. рук.", "Алгебра", "Геометрия", "Физика", "Русский язык", "Литература", "Английский язык", "Информатика", "Химия", "Биология", "География", "История", "Обществознание", "Экономика", "Физкультура", "Черчение", "Башкирский язык", "ОБЖ");
	
	for($i=0; $i<count($eng); $i++) {
		if($word === $eng[$i]) $word = $rus[$i];
	}
	return($word);
}

/********* Функция перевода дня *****************/
function licey_day_translate($word)
{
	$eng = array("mon", "tue", "wed", "thu", "fri", "sat");
	$rus = array("Понедельник", "Вторник", "Среда", "Четверг", "Пятница", "Суббота");
	for($i=0; $i<count($eng); $i++) {
		if($word === $eng[$i]) $word = $rus[$i];
	}
	return($word);
}

/********* Функция перевода месяца **************/
function licey_month_translate($word)
{
	$eng = array("January", "February", "March", "April", "May", "June", "July", "August", "September", "October", "November", "December");
	$num = array(1, 2, 3, 4, 5, 6, 7, 8, 9, 10, 11, 12);
	$rus = array("Январь", "Февраль", "Март", "Апрель", "Май", "Июнь", "Июль", "Август", "Сентябрь", "Октябрь", "Ноябрь", "Декабрь");
	
	for($i=0; $i<count($eng); $i++) {
		if(($word === $eng[$i]) || ($word == $num[$i])) $word = $rus[$i];
	}
	return($word);
}


/********* Функция сокращения имени ученика/учителя *********/
function licey_shortname($name)
{
	$fio = explode(' ', $name);
	$shortname = $fio[0].' '.substr($fio[1], 0, 2).'. '.substr($fio[2], 0, 2).'.';
	return $shortname;
}
	
/********* Функция сортировки массива по датам *********/
function licey_sortbydate($a, $b)
{
	if ($a->date == $b->date) return 0;
	else if ($a->date > $b->date) return 1;
	else return -1;
}

/********* Функция сортировки учеников по алфавиту *********/
function licey_sort_students($a, $b)
{
	if(get_class($a) === 'Student') {
		$name1 = $a->fio; $name2 = $b->fio;
	} else {
		$name1 = $a->student_name; $name2 = $b->student_name;
	}

	if(strlen($name1) > strlen($name2)) $name1 = substr($name1, 0, strlen($name2));
	else if (strlen($name1) < strlen($name2)) $name2 = substr($name2, 0, strlen($name1));
	
	if ($name1 == $name2) return 0;
	else if ($name1 > $name2) return 1;
	else return -1;
}

/********* Функция сортировки класов *********/
function licey_sort_forms($a, $b)
{
	$number_a = (int)substr($a, 0, strlen($a)-1);
	$number_b = (int)substr($b, 0, strlen($b)-1);
	
	$letter_a = substr($a, -1);
	$letter_b = substr($b, -1);
	
	if($number_a > $number_b) return 1;
	else if($number_a < $number_b) return -1;
	else {
		if($letter_a == $letter_b) return 0;
		else if($letter_a > $letter_b) return 1;
		else return -1;
	}
}

/********* Функция получения массива дат без повторяющихся *********/
function licey_get_dates($marks)
{
	$dates = array();
	array_push($dates, $marks[0]->date);
	foreach ($marks as $mark) {
		if($dates[count($dates)-1] != $mark->date) $dates[] = $mark->date;
	}
	return $dates;
}

/********* Функция переименования дат из ДБ для вывода на экран ******/
function licey_transform_date($date)
{
	switch($date) {
		case 'I_pol': $date = '<strong>I пол.</strong>'; break;
		case 'II_pol': $date = '<strong>II пол.</strong>'; break;
		case 'god': $date = '<strong>Год</strong>'; break;
		case 'exam': $date = '<strong>Экзам.</strong>'; break;
		case 'itog': $date = '<strong>Итог</strong>'; break;
		case 'I': 
		case 'II':
		case 'III':
		case 'IV': $date = '<strong>'.$date.'<strong>'; break;
		default: $date = substr($date, -2); break;
	}
	return $date;
}

/********* Функция преобразования 0 и -1 в * и н (в оценках) *********/
function __mark($mark)
{
	switch ($mark) {
		case -1: return '*';
		case -2: return 'н';
		case '*': return -1;
		case 'н': return -2;
		default: return $mark;
	}
}

/************ Функция записи значений в ДБ ************/
function licey_update()
{
	//инфа о ДБ
	global $wpdb;
	
	$report = "Изменения сохранены";
	$stop = false;
	
	//добавление класса
	if(isset($_POST['add_form_number']) && isset($_POST['add_form_letter']))
	{
		if(function_exists('current_user_can') && !current_user_can('manage_options')) die('Доступ запрещен');
		if(function_exists('check_admin_referer')) check_admin_referer('add_new_form');
		$newform = $_POST['add_form_number'] . $_POST['add_form_letter'];
				
		$form = new Form;
		$form = $form->add_new($newform);
		$report = $form->report;
	}
	
	//удаление класса
	if(isset( $_POST['form-del'] )) {
		$del = $_POST['form-id'];
		$form = new Form($del);
		$form->delete();
		$report = $form->report;
	}
	
	//добавление ученика
	if(isset($_POST['add_student_btn']))
	{
		if(function_exists('current_user_can') && !current_user_can('manage_options')) die('Доступ запрещен');
		if(function_exists('check_admin_referer')) check_admin_referer('add_new_student');
		
		//определение переменных
		$student_name = $_POST['add_student_name'];
		$username = $_POST['add_student_username'];
		$form = $_POST['add_student_form'];
		
		//добавление
		$student = new Student();
		$student = $student->add_new($username, $student_name, $form);
		$report = $student->report;
	}
	
	//удаление ученика
	if(isset($_REQUEST['student-del']))
	{
		$id = $_REQUEST['student-id'];
		$student = new Student($id);
		$student->delete();
		$report = $student->report;
	}

	//удаление учителя 
	if(isset($_REQUEST['teacher-del']))
	{
		$id = $_REQUEST['teacher-del'];
		if(!is_numeric($id)) $id = $_REQUEST['teacher-id'];
		$teacher = new Teacher($id);
		$teacher->delete();
		$report = $teacher->report;
	}

	//обновление ученика
	if(isset($_POST['student_update'])) {
		$student = new Student( $_POST['student-edit']);
		$student->update( array(
			'username' => $_POST['student-username'],
			'fio' => $_POST['student-fio'],
			'wp_user' => $_POST['student-wp_user'],
			'form' => $_POST['student-form'],
			'phone' => $_POST['student-phone'],
			'confirmed' => isset($_POST['student-confirmed'])
		));
		$report = $student->report;
	}
	
	if(isset($_POST['canicular_dates_btn']))
	{
		if(function_exists('current_user_can') && !current_user_can('manage_options')) die('Доступ запрещен');
		if(function_exists('check_admin_referer')) check_admin_referer('edit_dates');
		
		$dates = array();
		for($i = 0; $i < 4; $i++) {
			for($j = 0; $j < 2; $j++) {
				$day = str_pad($_POST['canicular_dates_day'][$i][$j], 2, 0, STR_PAD_LEFT);
				$month = str_pad($_POST['canicular_dates_month'][$i][$j], 2, 0, STR_PAD_LEFT);
				$dates[$i][$j] = licey_get_study_year($month . "-" . $day) . "-" . $month . "-" . $day;
			}
		}

		update_option('licey_canicular_dates', json_encode( $dates ));
	}

	if(isset($_POST['edit_strings_btn']))
	{
		if(function_exists('current_user_can') && !current_user_can('manage_options')) die('Доступ запрещен');
		if(function_exists('check_admin_referer')) check_admin_referer('edit_strings');
		
		$subjform = $_POST['edit_subj-form_hook'];
		$singlestud = $_POST['edit_singlestud_hook'];
		
		update_option('licey_single-stud_replace_string', $singlestud);
		update_option('licey_subj-form_replace_string', $subjform);
	}

	if(isset($_POST['pages_options_submit']))
	{
		update_option('licey_journal_student_url', $_POST['licey_journal_student_url']);
		update_option('licey_journal_forms_url', $_POST['licey_journal_forms_url']);
		update_option('licey_schedule_url', $_POST['licey_schedule_url']);
	}

	if(isset($_POST['add_teacher-submit']))
	{
		$teacher = new Teacher;
		$teacher->add_new($_POST['add_teacher-username'], $_POST['add_teacher-fio']);
		$report = $teacher->report;
	}

	if(isset($_POST['table_update_btn']))
	{
		$class = $_POST['table_classname'];
		$settings = $_POST['table_settings'];

		$table = new $class($settings);
		$stop = $table->update();

		$report = $table->report;
	}
	
	//вывешиваем синий прямоугольничек с надписью
	if( isset($_GET['updated']) && $report ) echo "<div id='updated'><span id='updated'>" . $report . "</span></div>";
	return $stop;
}


/**
 * Функция календаря - возвращает массив дат, соответствующих расписанию
 * @param array params
 * @return array
 */
function licey_getStudyDates($params)
{
	global $wpdb;

	$default = array('month' => date('n'), 'subject' => '', 'form' => '10В' );

	$month = ( $params['month'] )? $params['month'] : $default['month'];
	$subject = ( $params['subject'] )? $params['subject'] : $default['subject'];
	$form = ( $params['form'] )? $params['form'] : $default['form'];

	$canicular_dates = $dates = json_decode( get_option('licey_canicular_dates'), true);

	$days = $wpdb->get_row("SELECT * FROM `" . JOURNAL_DB_SCHEDULE . "` WHERE form='" . $form . "';", ARRAY_A);

	if($subject) {
		foreach($days as $day => $subjects) {
			if (strpos($subjects, $subject) === false) unset($days[$day]);
		}
	}
	$days = array_keys($days);

	$year = licey_get_study_year( str_pad($month, 2, 0, STR_PAD_LEFT).'-'.str_pad($i, 2, 0, STR_PAD_LEFT));

	$dates = array();

	//прогон по всем дням месяца
	for($i = 1; $i <= 31; $i++) {
		
		//сразу проверяем корректность даты
		if( checkdate($month, $i, $year) ) {
			
			$timestamp = mktime(0, 0, 0, $month, $i, $year);
			$date = date('Y-m-d', $timestamp);
			
			//начинаем составлять условие
			$check = ( in_array( strtolower(date('D', $timestamp)), $days ) );
			
			foreach ($canicular_dates as $c_date)
				if($check) $check = !( ( $date >= $c_date[0] ) && ( $date <= $c_date[1] ) );

			//если прошли все проверки, добавляем
			if($check) $dates[] = $date;
		}
	}

	//добавляем псевдо-даты четвертных и итоговых оценок
	switch ($month) {
		case 10:
			array_push($dates, 'I');
			break;

		case 12:
			array_push($dates, 'II', 'I_pol');
			break;
		
		case 3:
			array_push($dates, 'III');
			break;

		case 5:
			array_push($dates, "IV", "II_pol", "god", "exam", "itog");
			break;
	}

	return $dates;
}


function licey_pack($data)
{
	return urlencode(json_encode($data));
}

function licey_unpack($str)
{
	return json_decode(urldecode($str), true);
}

function licey_cur_uri( $query = true ) {
	$request = $_SERVER['REQUEST_URI'];
	if(strpos($request, 'updated=true')) $request = substr($request, 0, strlen($request) - 13);
	$sign = ($request)? "&amp;" : "?";
	if(!$query) $sign = '';
	return "http://". $_SERVER['HTTP_HOST'] . $request . $sign;
}

function get_subjects_list($formmaster = false) {
	global $wpdb;
	$splice = ( $formmaster )? 2 : 3;
	return array_splice(array_keys( $wpdb->get_row("SELECT * FROM `".JOURNAL_DB_SUBJECTS."`;", ARRAY_A) ), $splice);
}
function get_current_student() {
	$current_user = wp_get_current_user();

	if(!$current_user->ID) return false;

	$student = new Student( $current_user->user_login, true);
	if(!$student->id) return false;

	return $student;
}

function get_current_teacher() {
	$current_user = wp_get_current_user();

	if(!$current_user->ID) return false;

	$teacher = new Teacher( $current_user->user_login, true);
	if(!$student->id) return false;

	return $student;
}

function get_forms_list() {
	global $wpdb;
	$forms = $wpdb->get_col("SELECT form FROM ".JOURNAL_DB_SUBJECTS.";");
	usort($forms, 'licey_sort_forms');

	return $forms;
}

function get_teachers() {
	global $wpdb;

	$ids = $wpdb->get_col("SELECT id, fio FROM ".JOURNAL_DB_TEACHERS." ORDER BY fio;");
	$teachers = array();

	foreach($ids as $id) {
		$teachers[] = new Teacher($id);
	}
	return $teachers;
}

function get_students($form = '') {
	global $wpdb;

	if(!$form) $q = "SELECT id FROM ".JOURNAL_DB_STUDENTS.";";
	else $q = $wpdb->prepare("SELECT id, form FROM %s WHERE 'form'=%s", JOURNAL_DB_STUDENTS, $form);

	$ids = $wpdb->get_col($q);
	$students = array();

	foreach($ids as $id) $students[] = new Student($id);

	return $students;
}
?>