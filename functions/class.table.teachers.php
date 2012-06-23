<?php
/**
 * Класс таблицы учителей
 * @package Classes/Table
 */
class TableTeachers extends Table
{
	protected $choosable = false;
	protected $complete = true;

	protected $show_class = 'licey_show_teachers_table';
	protected $edit_class = 'licey_edit_teachers_table';

	public function __construct() {
		global $wpdb, $table_teachers;

		$this->row_params = array('Полное имя', 'Имя пользователя', 'Привязанный пользователь WP', 'Предметы', 'Удалить');

		$this->col_params = get_teachers();
		for ($i=0; $i<count($this->col_params); $i++) {
			$this->table_content[$i][0] = $this->col_params[$i]->fio;
			$this->table_content[$i][1] = $this->col_params[$i]->username;
			$this->table_content[$i][2] = $this->col_params[$i]->wp_user;
			$this->table_content[$i][3] = $this->col_params[$i]->subjects;
		}

		$this->labels['corner'] = 'Имя';
		$this->labels['submit'] = 'Сохранить';
		$this->labels['access_denied'] = 'Вы не имеете доступа к списку учителей.';
	}

	protected function col_view($teacher) {
		return licey_shortname( $teacher->fio );
	}

	protected function content_filter($row_param, $teacher, $item)
	{

		switch($row_param) {
			case 'Предметы' :
				$list = '';
				foreach($teacher->subjects as $subject) {
					$list.= licey_subject_translate($subject) . ', ';
				}
				return substr($list, 0, strlen($list) - 2);
			case 'Удалить' :
				return '';
			default : 
				return $item;
		}
	}

	protected function content_edit_filter($row_param, $teacher, $item)
	{
		switch($row_param) {
			case 'Полное имя' :
				return array(
					'before' => "<input type='text' class='input-fio' name='teacher-fio[" . $teacher->username . "]' value='", 
					'value' => $teacher->fio,
					'after' => "'>"
				);
			case 'Имя пользователя' : 
				return array(
					'before' => "<input type='text' class='input-username' name='teacher-username[" . $teacher->username . "]' value='", 
					'value' => $teacher->username,
					'after' => "'>"
				);

			case 'Привязанный пользователь WP' : 
				$list = "<select class='input-wp_user' name='teacher-wp_user[" . $teacher->username . "]'>
							<option value=''>--//--</option>
				";
				foreach(get_users() as $user) {
					$selected = ( $user->user_login === $teacher->wp_user )? ' selected' : '';
					$list.= "<option" . $selected . ">" . $user->user_login . "</option>";
				}
				$list.= "</select>";
				return array(
					'before' => '',
					'value' => $list,
					'after' => ''
				);

			case 'Предметы' :
				$list = "<select class='input-subjects' name='teacher-subjects[" . $teacher->username . "][]' multiple>";
				foreach(get_subjects_list() as $subject) {
					$selected = ( in_array($subject, $teacher->subjects) ) ? ' selected' : '';
					$list.= "<option value='" . $subject . "'". $selected . ">" . licey_subject_translate($subject) . "</option>";
				}
				$list.= "</select>";
				return array(
					'before' => '',
					'value' => $list,
					'after' => ''
				);
			case 'Удалить' :
				return array(
					'before' => '',
					'value' => "<a class='delete-teacher' href='" . licey_cur_uri() . "teacher-del=" . $teacher->id . "'>X</a>",
					'after' => ''
				);
		}
	}
	

	public function update() {
		$fios = $_POST['teacher-fio'];
		$usernames = $_POST['teacher-username'];
		$wp_users = $_POST['teacher-wp_user'];
		$subjects = $_POST['teacher-subjects'];

		foreach($this->col_params as $teacher) {
			$teacher->set_fio($fios[$teacher->username]);
			$teacher->set_username($usernames[$teacher->username]);
			$teacher->link_wp_user($wp_users[$teacher->username]);
			$teacher->set_subjects($subjects[$teacher->username]);
		}

		$this->report = 'Данные учителей успешно сохранены';
	}

}

?>