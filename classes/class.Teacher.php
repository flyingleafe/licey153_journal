<?php
/**
 * Класс учителя
 * @package Classes
 */
class Teacher
{
	//ID
	public $id;

	//Хэндл
	public $username;

	//ФИО
	public $fio;

	//Привязка к WP-пользователю
	public $wp_user;

	//Предметы учителя
	public $subjects;

	//Оповещение о состоянии
	public $report;

	public function __construct($handle='', $wp_user = false)
	{
		global $wpdb;
		$current_user = wp_get_current_user();

		if($handle) {
			//определяем параметр запроса
			if( is_numeric($handle) ) $way = 'id';
			else {
				if(!$wp_user) $way = 'username';
				else $way = 'wp_user';
			}

			//делаем запрос к БД
			$data = $wpdb->get_row("SELECT * FROM `".JOURNAL_DB_TEACHERS."` WHERE `".$way."`='".$handle."';");

		} else if($current_user->ID) 
			$data = $wpdb->get_row("SELECT * FROM `".JOURNAL_DB_TEACHERS."` WHERE `wp_user`='".$current_user->user_login."';");

		if($data) {
			$this->id = $data->id;
			$this->username = $data->username;
			$this->fio = $data->fio;
			$this->wp_user = $data->wp_user;
			$this->subjects = json_decode($data->subjects, true);
		}
	}

	public function set_username($username)
	{
		if(!$username) return false;

		global $wpdb;

		$wpdb->update(JOURNAL_DB_TEACHERS, array( 'username' => $username ), array( 'id' => $this->id ), array('%s'), array('%d') );

		foreach ($this->subjects as $subject) {
			$wpdb->update(JOURNAL_DB_SUBJECTS, array( $subject => $username ), array( $subject => $this->username ), array('%s'), array('%s') );
		}

		$this->username = $username;
		return true;
	}

	public function set_fio($fio)
	{
		if(!$fio) return false;

		global $wpdb;

		$this->fio = $fio;
		$wpdb->update(JOURNAL_DB_TEACHERS, array( 'fio' => $fio ), array( 'id' => $this->id ), array('%s'), array('%d') );
		return true;
	}

	public function set_subjects($subjects)
	{
		if(!$subjects) return false;

		if(!in_array('formmaster', $subjects) ) $subjects = array_merge($subjects, array('formmaster') );

		global $wpdb;

		$wpdb->update(JOURNAL_DB_TEACHERS, array( 'subjects' => json_encode($subjects) ), array( 'id' => $this->id ), array('%s'), array('%d') );

		foreach ($this->subjects as $subject) {
			if(!in_array($subject, $subjects)) {
				$wpdb->update(JOURNAL_DB_SUBJECTS, array( $subject => NULL ), array( $subject => $this->username ), array('%s'), array('%d') );
			}
		}

		$this->subjects = $subjects;
		return true;
	}

	public function link_wp_user( $user )
	{
		if(!$user) return false;

		require_once(ABSPATH . WPINC . '/registration.php');
		try {
			if(!username_exists($user)) {
				throw new Exception('Такого пользователя не существует.');
			}

			global $wpdb;

			$alreadyLinked = $wpdb->get_results("SELECT id, wp_user FROM ".JOURNAL_DB_TEACHERS." WHERE wp_user=$user AND id<>" . $this->id . ";");

			if($alreadyLinked) {
				throw new Exception('Другой учитель уже привязан к этому пользователю!');
			}

			$this->wp_user = $user;
			$wpdb->update(JOURNAL_DB_TEACHERS, array( 'wp_user' => $user ), array( 'id' => $this->id ), array('%s'), array('%d') );
			return true;

		} catch( Exception $e ) {
			$this->report = $e->getMessage();
		}
	}

	public function add_new($username, $fio, $subjects = array() )
	{
		global $wpdb;
		
		try {
			//проверям на наличие учителя с таким логином
			$exist_teachers = $wpdb->get_results("SELECT username FROM `" . JOURNAL_DB_TEACHERS . "` WHERE username='" . $username . "';");
			
			//если есть, бросаем исключение
			if($exist_teachers) {
				throw new Exception('Учитель с таким именем пользователя уже существует!');
			}

			if(!in_array('formmaster', $subjects) ) $subjects = array_merge($subjects, array('formmaster') );

			//все ок - добавляем
			$wpdb->insert(JOURNAL_DB_TEACHERS, array('username' => $username, 'fio' => $fio, 'subjects' => json_encode($subjects) ), array('%s','%s','%s') );
			
			//применяем только что полученные настройки к данному обьекту
			$this->id = $wpdb->insert_id;
			$this->username = $username;
			$this->fio = $fio;
			$this->subjects = $subjects;
			
			$this->report = "Новый учитель уcпешно добавлен";
			
		} catch( Exception $e ) {
			//ловим исключение, выводим ошибку
			$this->report = $e->getMessage();
		}
		
		//возвращаем готовый объект
		return $this;
	}

	public function get_forms()
	{
		global $wpdb;

		$data = array();

		foreach($this->subjects as $subject) {
			$forms = $wpdb->get_col("SELECT * FROM `".JOURNAL_DB_SUBJECTS."` WHERE `$subject`=`$this->username`;", 1);
			if($forms) $data[$subject] = $forms;
		}

		return $data;
	}

	public function delete()
	{
		global $wpdb;

		$wpdb->query("DELETE FROM `" . JOURNAL_DB_TEACHERS . "` WHERE `id`=" . $this->id . ";");
		$this->report = "Учитель успешно удален";

		return true;
	}
}