<?php
class TableMarks_SingleStudent extends TableMarks
{
	private $subject_dates;

	private $student;

	public function __construct($settings='')
	{
		global $wpdb;

		if( $settings || isset($_POST['choose_settings']) ) {
			
			//извлекаем параметры
			if($settings && is_string($settings)) $settings = licey_unpack($settings);
			$this->settings = array_merge( $settings, (array)$_POST['choose_settings'] );

			$student = $this->settings['student'];
			$month = $this->settings['month'];
	
			$this->show_class = 'licey_show_marks_table';
			$this->edit_class = 'licey_edit_marks_table';

			$this->student = new Student($student);

			$this->row_params = licey_getStudyDates( array('month' => $month) );
			$this->col_params = array_splice(array_keys( $wpdb->get_row("SELECT * FROM `".JOURNAL_DB_SUBJECTS."`;", ARRAY_A) ), 3);

			foreach ($this->col_params as $subject) {
				$this->table_content[] = $this->student->get_marks( $subject, array('month' => $month) );
				$this->subject_dates[$subject] = licey_getStudyDates( array('month' => $month, 'subject' => $subject, 'form' => $this->student->form) );
			}

			$this->labels['corner'] = "Дата:";
			$this->labels['submit'] = 'Сохранить';
			$this->labels['incompleted'] = 'Выберите ученика и месяц';

			$this->complete = true;
		} else $this->complete = false;
	}

	public function view_access_given() {

		$current_user = wp_get_current_user();

		if( $current_user->has_cap('edit_dashboard') ) return true;
		
		if( $this->student->wp_user === $current_user->user_login ) return true;

		return false;
	}

	protected function col_view($col_param)
	{
		return licey_subject_translate($col_param);
	}

	protected function content_edit_filter($date, $subject, $mark)
	{	
		$disabled = ( in_array($date, $this->subject_dates[$subject]) )? '' : ' disabled';
		$data = array(
			'before' => "<input type='text' name='marks[".$subject."][".$date."]' value='",
			'value' => $this->content_filter($date, '', $mark),
			'after' => "'" . $disabled . ">"
		);
		return $data;
	}

	public function update()
	{
		$marks = $_POST['marks'];
		$student = $this->settings['student'];

		foreach($marks as $subject => $date_marks) {
			foreach ($date_marks as $date => $mark) {
				$new_mark = new Mark( array('student' => $student, 'subject' => $subject, 'date' => $date) );
				$new_mark->update($mark);
			}
		}
		$this->report = "Оценки ученика сохранены";
	}

	public function choose_settings()
	{
	
		$presentation.= "
			<form name='choose_table' method='post' action='" . licey_cur_uri(false) . "'>
		";
		if(is_admin() && !isset($_REQUEST['student-edit']) && !isset($_POST['students_edit_btn']) ) {
			global $wpdb;
			$ids = $wpdb->get_results("SELECT id FROM ".JOURNAL_DB_STUDENTS.";");

			$presentation.= "<select name='choose_settings[student]'>";
			foreach( $ids as $id ) {
				$student = new Student($id->id);
				$presentation.= "<option value=" . $student->username . ">" . licey_shortname($student->fio) . "</option>";
			}
			$presentation.= "</select>";
		}
		$presentation.= "
				<select name='choose_settings[month]'>
		";
		for($i=9; $i!=6; $i++) {
			if($i>12) $i = 1;
			$selected = ($i == $this->settings['month'])? 'selected':'';
			$presentation.= "<option ".$selected." value=".$i.">".licey_month_translate($i)."</option>";
		}
		$presentation.= "
				</select>
				<input type='submit' value='Редактировать'>
			</form>
		";
		return $presentation;
	}
}
?>