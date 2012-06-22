<?php
/**
 * Класс таблицы учителей
 * @package Classes/Table
 */
class TableTeachers extends Table
{
	protected $choosable = false;

	public function __construct() {
		global $wpdb, $table_teachers;

		$this->row_params = array('Имя пользователя', 'Привязанный пользователь WP', 'Предметы');

		$teachers_ids = $wbdb->get_col("SELECT id, fio FROM $table_teachers;");
		for ($i=0; $i<count($teachers_ids); $i++) {
			$this->col_params[$i] = new Teacher($teachers_ids[$i]);
			$this->table_content[$i][0] = $this->col_params[$i]->username;
			$this->table_content[$i][1] = $this->col_params[$i]->wp_user;
			$this->table_content[$i][2] = $this->col_params[$i]->subjects;
		}


	}

	public function update() {

	}
}

?>