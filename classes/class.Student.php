<?php
/***************************
 * Класс ученика
***************************/

class Student
{
	//ID в базе данных
	public $id;
	
	//логин ученика
	public $username;
	
	//логин пользователя WP
	public $wp_user;

	//ФИО ученика
	public $fio;
	
	//Класс
	public $form;

	//Номер телефона
	public $phone;

	//Флаг валидации
	public $confirmed = false;
	
	//Сообщение об ошибке
	public $report = 0;

	
	/**
	 * Функция построения объекта
	 * @param mixed token
	 */
	public function __construct($token = 0, $wp_user = false) {
		global $wpdb;
		
		if($token) {
			//определяем параметр запроса
			if( is_numeric($token) ) $way = 'id';
			else {
				if(!$wp_user) $way = 'username';
				else $way = 'wp_user';
			}
			
			//делаем запрос к БД
			$data = $wpdb->get_row("SELECT * FROM `".JOURNAL_DB_STUDENTS."` WHERE `".$way."`='".$token."';");
			
			if(!$data) return false;
			
			//задаем данные
			$this->id = $data->id;
			$this->username = $data->username;
			$this->wp_user = $data->wp_user;
			$this->fio = $data->student_name;
			$this->form = $data->form;
			$this->phone = $data->phone;
			$this->confirmed = $data->confirmed;
		}
	}
	
	/**
	 * Функция добавления нового ученика
	 * @param string username
	 * 
	 */
	public function add_new($username, $fio, $form) {
		global $wpdb;
		
		try {
			//проверям на наличие ученика с таким логином
			$exist_students = $wpdb->get_results("SELECT username FROM	`".JOURNAL_DB_STUDENTS."` WHERE username='".$username."';");
			
			//если есть, бросаем исключение
			if($exist_students) {
				throw new Exception('Ученик с таким именем пользователя уже существует!');
			}
			
			//то же самое с наличием класса
			$exist_form = $wpdb->get_results("SELECT form FROM `".JOURNAL_DB_SCHEDULE."` WHERE form='".$form."';");
			
			if(!$exist_form) {
				throw new Exception('Выбранного класса не существует!');
			}
			
			//все ок - добавляем
			$wpdb->insert(JOURNAL_DB_STUDENTS, array('username' => $username, 'student_name' => $fio, 'form' => $form), array('%s','%s','%s'));
			
			//применяем только что полученные настройки к данному обьекту
			$this->__construct($username);
			
			$this->report = "Новый ученик уcпешно добавлен";
			
		} catch( Exception $e ) {
			//ловим исключение, выводим ошибку
			$this->report = $e->getMessage();
		}
		
		//возвращаем готовый объект
		return $this;
	}

	/**
	 * Изменяет имя ученика
	 * @param string fio
	 */
	public function set_fio( $fio )
	{
		if(!$fio) return false;

		global $wpdb;

		$this->fio = $fio;
		$wpdb->update(JOURNAL_DB_STUDENTS, array( 'student_name' => $fio ), array( 'id' => $this->id ), array('%s'), array('%d') );
		return true;
	}

	/**
	 * Привязывает ученика к заданному пользователю WordPress
	 * @param string user
	 */
	public function link_wp_user( $user )
	{
		if(!$user) return false;

		require_once(ABSPATH . WPINC . '/registration.php');
		try {
			if(!username_exists($user)) throw new Exception('Такого пользователя не существует.');

			global $wpdb;

			$alreadyLinked = $wpdb->get_results("SELECT id, wp_user FROM ".JOURNAL_DB_STUDENTS." WHERE wp_user=$user AND id<>" . $this->id . ";");
			if($alreadyLinked) throw new Exception('Другой ученик уже привязан к этому пользователю!');

			$this->wp_user = $user;
			$wpdb->update(JOURNAL_DB_STUDENTS, array( 'wp_user' => $user ), array( 'id' => $this->id ), array('%s'), array('%d') );
			return true;

		} catch( Exception $e ) {
			$this->report = $e->getMessage();
		}
	}

	/**
	 * Изменяет класс, в кот. числится ученик
	 * @param string form
	 */
	public function set_form( $form )
	{
		if(!$form) return false;

		global $wpdb;

		$this->form = $form;
		$wpdb->update(JOURNAL_DB_STUDENTS, array( 'form' => $form ), array( 'id' => $this->id ), array('%s'), array('%d') );
		return true;
	}

	/**
	 * Изменяет имя пользователя ученика
	 * @param string username
	 */
	public function set_username( $username )
	{
		if(!$username) return false;

		global $wpdb;

		$wpdb->update(JOURNAL_DB_STUDENTS, array( 'username' => $username ), array( 'id' => $this->id ), array('%s'), array('%d') );
		$wpdb->update(JOURNAL_DB_MARKS, array( 'student' => $username ), array( 'student' => $this->username ), array('%s'), array('%s') );

		$this->username = $username;
		return true;
	}

	/**
	 * Изменяет телефон ученика
	 * @param string phone
	 */
	public function set_phone( $phone ) {
		if(!$phone) return false;

		global $wpdb;

		$this->phone = $phone;
		$wpdb->update(JOURNAL_DB_STUDENTS, array( 'phone' => $phone ), array( 'id' => $this->id ), array('%s'), array('%d') );
		return true;
	}

	/**
	 * Подтверждает или не подтверждает валидацию ученика
	 * @param bool flag
	 */
	public function confirm( $flag = true ) {
		global $wpdb;

		$this->confirmed = $flag;
		$wpdb->update(JOURNAL_DB_STUDENTS, array( 'confirmed' => $flag ), array( 'id' => $this->id ), array('%s'), array('%d') );
		return true;
	}

	public function unconfirm()
	{
		return $this->confirm( false );
	}
	
	/**
	 * Удаляет ученика из базы данных
	 * @param string way
	 */
	public function delete() {
		global $wpdb;

		$wpdb->query("DELETE FROM `" . JOURNAL_DB_STUDENTS . "` WHERE `id`=" . $this->id . ";");
		$this->report = "Ученик успешно удален";

		return true;
	}

	/**
	 * Функция обновления данных об ученике
	 */
	public function update( $args = array() ) {

		$this->set_fio( $args['fio'] );
		$this->set_username( $args['username'] );
		$this->set_form( $args['form'] );
		$this->set_phone( $args['phone'] );
		$this->confirm( $args['confirmed'] );
		$this->link_wp_user( $args['wp_user']);

		if(!$this->report) $this->report = 'Данные ученика изменены';
		echo $this->report;
	}

	public function get_marks( $subject = '', $date_settings = array() )
	{
		global $wpdb;

		$marks = array();
		$q = "SELECT * FROM ".JOURNAL_DB_MARKS." WHERE student='" . $this->username . "'";
			
		if($subject) $q.= " AND subject='" . $subject ."'";

		if(isset($date_settings['month'])) {
			$month = '-' . str_pad($date_settings['month'], 2, 0, STR_PAD_LEFT) . '-';
			$q.= " AND (date LIKE '%" . $month . "%' OR date IN ('I', 'II', 'III', 'IV', 'I_pol', 'II_pol', 'god', 'exam', 'itog'))";
		}
		
		$q.= " ORDER BY date;";

		$marks_ids = $wpdb->get_col($q);

		foreach($marks_ids as $id) {
			$mark = new Mark($id);
			$marks[] = $mark;
		}
		
		return $marks;
	}

	public function show_edit( $return = false, $marks = true)
	{
		global $wpdb;

		$forms = $wpdb->get_col("SELECT form FROM `" . JOURNAL_DB_SCHEDULE . "`");
		$wp_users = get_users();

		$presentation = "
			<div class='licey_student_edit'>	
				<h2>" . $this->fio . "</h1>
				<form name='single_student_edit_" . $this->id . "' method='post' action='" . licey_cur_uri( false ) . "'>
					<input type='hidden' name='student-edit' value='" . $this->id . "'>
					<p>Логин ученика: <input name='student-username' type='text' value='" . $this->username . "'></p>
					<p>Имя ученика: <input name='student-fio' type='text' value='" . $this->fio . "'></p>
					<p>Пользователь WordPress, принадлежащий ученику: <select name='student-wp_user'>
						<option value=0>-- Не привязан --</option>
		";
		foreach( $wp_users as $user ) {
			$selected = ( $user->user_login === $this->wp_user )? ' selected' : '';
			$presentation.= "<option" . $selected . ">" . $user->user_login . "</option>";
		}

		$presentation.= "
						</select>
						<span class='description'>Не рекомендуется менять этот параметр вручную. Подразумевается, что ученики самостоятельно привяжут пользователя при помощи SMS-валидации.</span>
					</p>
					<p>Класс ученика: <select name='student-form'>
		";

		foreach ($forms as $form) {
			$selected = ( $form === $this->form )? ' selected' : '';
			$presentation.= "<option" . $selected . ">" . $form . "</option>";
		}

		$presentation.= "
						</select>
					</p>
					<p>Телефон ученика: <input name='student-phone' type='text' maxlength='12' value='" . $this->phone . "'></p>
					<p>Привязка пользователя к ученику подтверждена: <input name='student-confirmed' type='checkbox'" . (($this->confirmed) ? ' checked' : '') . "></p>
					<input type='submit' name='student_update' value='Сохранить'>
				</form>
		";

		if( $marks ) {

			$presentation.= "	
					<h2 class='edit_marks'>Редактировать оценки ученика: </h2>
					<div class='edit_marks'>
			";

			$table = new TableMarks_SingleStudent( array('student' => $this->username) );
			$presentation.= $table->show_edit( true ) . "</div>";
		}

		$presentation.= "</div>";
		if($return) return $presentation;
		echo $presentation;
	}
}
?>