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

		$this->row_params = array('Имя пользователя', 'Привязанный пользователь WP', 'Предметы');

		$teachers_ids = $wpdb->get_col("SELECT id FROM $table_teachers;");
		for ($i=0; $i<count($teachers_ids); $i++) {
			$this->col_params[$i] = new Teacher($teachers_ids[$i]);
			$this->table_content[$i][0] = $this->col_params[$i]->username;
			$this->table_content[$i][1] = $this->col_params[$i]->wp_user;
			$this->table_content[$i][2] = $this->col_params[$i]->subjects;
		}

		$this->labels['corner'] = 'Имя:';
		$this->labels['submit'] = 'Сохранить';
		$this->labels['access_denied'] = 'Вы не имеете доступа к списку учителей.';
	}

	protected function col_view($teacher) {
		return licey_shortname( $teacher->fio );
	}

	protected function content_filter($row_param, $teacher, $item)
	{

		switch($row_param) {
			case 'Имя пользователя' :
			case 'Привязанный пользователь WP' : 
				return $item;
			case 'Предметы' :
				$list = '';
				foreach($teacher->subjects as $subject) {
					$list.= licey_subject_translate($subject) . ', ';
				}
				return substr($list, 0, strlen($list) - 2);
		}
	}

	protected function content_edit_filter($row_param, $teacher, $item)
	{
		switch($row_param) {
			case 'Имя пользователя' : 
				return array(
					'before' => "<input type='text' name='teacher-username[" . $teacher->username . "]' value='", 
					'value' => $teacher->username,
					'after' => "'>"
				);

			case 'Привязанный пользователь WP' : 
				$list = "<select name='teacher-wp_user[" . $teacher->username . "]'>
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
				$list = "<select name='teacher-subjects[" . $teacher->username . "][]' multiple>";
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
		}
	}

	public function update() {
		$usernames = $_POST['teacher-username'];
		$wp_users = $_POST['teacher-wp_user'];
		$subjects = $_POST['teacher-subjects'];

		foreach($this->col_params as $teacher) {
			$teacher->set_username($usernames[$teacher->username]);
			$teacher->link_wp_user($wp_users[$teacher->username]);
			$teacher->set_subjects($subjects[$teacher->username]);
		}

		$this->report = 'Данные учителей успешно сохранены';
	}
}

?>