<?php
/**
 * Класс таблицы предметов
 * @package Classes/Tables
 */

class TableSubjects extends Table
{
	protected $choosable = false;
	protected $complete = true;

	protected $show_class = 'licey_show_table_subjects';
	protected $edit_class = 'licey_edit_table_subjects';

	private $teachers = array();

	public function __construct()
	{
		global $wpdb;

		$data = $wpdb->get_results("SELECT * FROM ".JOURNAL_DB_SUBJECTS.";", ARRAY_A);
		
		$this->col_params = get_forms_list();
		$this->row_params = get_subjects_list(true);
		$this->teachers = get_teachers();

		for($i=0; $i<count($this->col_params); $i++) {
			foreach( $data as $row ) {
				if( $row['form'] == $this->col_params[$i] ) {
					for( $j=0; $j<count($this->row_params); $j++ ) {
						$teachers = (array)json_decode($row[$this->row_params[$j]], true);
						$this->table_content[$i][$j] = array();
						foreach ($teachers as $teacher) {
							$this->table_content[$i][$j][] = new Teacher($teacher);
						}
					}
				}
			}
		}

		$this->labels['corner'] = "Класс";
		$this->labels['submit'] = "Сохранить";
		$this->labels['access_denied'] = "Вы не имеете доступа к списку учителей по предметам.";
	}

	public function row_view($subject) {
		return licey_subject_translate($subject);
	}

	public function content_filter($subject, $form, $teachers) {
		if($teachers) {
			$list = '';
			foreach ($teachers as $teacher)
				$list.= licey_shortname($teacher->fio) . ', ';

			return substr($list, 0, -2);
		}
		return '';
	}

	public function content_edit_filter($subject, $form, $cur_teachers)
	{
		$list = "
			<select name='subject-teacher[" . $form . "][" . $subject . "][]' class='input-subject-teacher' multiple>
				<option value=''>--//--</option>
		";
		foreach($this->teachers as $teacher) {
			if( in_array($subject, $teacher->subjects) ) {
				$selected = ( in_array($teacher, $cur_teachers ) )? ' selected' : '';
				$list.= "<option value='" . $teacher->username . "'" . $selected . ">" . licey_shortname($teacher->fio) . "</option>";
			}
		}
		$list.= "</select>";

		return array('before' => '', 'value' => $list, 'after' => '');
	}

	public function update() {
		global $wpdb;

		foreach($_POST['subject-teacher'] as $form => $subject) {
			foreach ($subject as $subj => $teachers) {
				$wpdb->update(JOURNAL_DB_SUBJECTS, array( $subj => json_encode($teachers) ), array( 'form' => $form ), array('%s'), array('%s') );
			}
		}
	}
}