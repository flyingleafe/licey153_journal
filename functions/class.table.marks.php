<?php
class TableMarks extends Table 
{
	public function __construct($settings = '')
	{

		if( ( $settings['form'] && $settings['subject'] ) || isset($_POST['choose_settings']) ) {

			//извлекаем параметры
			if($settings && is_string($settings)) $settings = licey_unpack($settings);
			$this->settings = array_merge( (array)$settings, (array)$_POST['choose_settings'] );

			$subject = $this->settings['subject'];
			$month = $this->settings['month'];
			$form = $this->settings['form'];

			//прописываем классы
			$this->show_class = 'licey_show_marks_table';
			$this->edit_class = 'licey_edit_marks_table';

			$this->row_params = licey_getStudyDates( array( 'month' => $month, 'subject' => $subject, 'form' => $form ) );

			$form = new Form($form);
			$this->col_params = $form->get_students();

			foreach($this->col_params as $student)
				$this->table_content[] = $student->get_marks($subject, array( 'month' => $month ));

			$this->labels['corner'] = 'Дата:';
			$this->labels['submit'] = 'Сохранить';
			$this->labels['access_denied'] = 'Вы не можете просматривать журнал этого класса';

			$this->complete = true;

		} else {
			$this->complete = false;
			$this->labels['incompleted'] = 'Выберите класс, предмет и месяц';
		}

	}

	protected function row_view($row_param)
	{
		return licey_transform_date($row_param);
	}

	public function view_access_given() {
		global $wpdb, $table_students;
		$current_user = wp_get_current_user();

		if( $current_user->has_cap('edit_dashboard') ) return true;
		
		foreach($this->col_params as $student) {
			if( $current_user->user_login === $student->wp_user ) return true;
		}

		return false;
	}

	protected function col_view($col_param)
	{
		return licey_shortname($col_param->fio);
	}

	protected function content_filter($date, $student = '', $mark)
	{
		if ($mark->date === $date) return __mark($mark->mark);
		return '';
	}

	protected function content_edit_filter($date, $student = '', $mark)
	{
		$data = array(
			'before' => "<input type='text' name='marks[".$student->username."][".$date."]' value='",
			'value' => $this->content_filter($date, $student, $mark),
			'after' => "'>"
		);
		return $data;
	}

	public function update()
	{
		
		$marks = $_POST['marks'];
		$subject = $this->settings['subject'];

		foreach($marks as $student => $date_marks) {
			foreach ($date_marks as $date => $mark) {
				$new_mark = new Mark( array('student' => $student, 'subject' => $subject, 'date' => $date) );
				$new_mark->update($mark);
			}
		}

		$this->report = "Оценки учеников сохранены";
	}

	public function choose_settings()
	{
		global $wpdb, $table_schedule, $table_subjects;

		$forms = $wpdb->get_col("SELECT * FROM `".$table_schedule."`", 1);
		usort($forms, 'licey_sort_forms');
		$subjects = array_keys($wpdb->get_row("SELECT * FROM `".$table_subjects."`;", ARRAY_A));
		array_splice($subjects, 0, 3);

		$presentation = "
			<form name='choose_table' method='post' action='" . licey_cur_uri(false) . "'>
		";
		if(is_admin()) {
			$presentation.= "
				<select name='choose_settings[form]' size=1>
			";
			foreach ($forms as $form) {
				$selected = ($form === $this->settings['form'])? ' selected':'';
				$presentation.= "<option".$selected.">".$form."</option>";
			}
		} else {
			$cur_student = get_current_student();
			if($cur_student) $presentation.= "<input type='hidden' name='choose_settings[form]' value='" . $cur_student->form . "'>";
		}
		$presentation.= "
			</select>
			<select name='choose_settings[subject]' size=1>
		";
		foreach ($subjects as $subj) {
			$selected = ($subj === $this->settings['subject'])? 'selected':'';
			$presentation.= "<option ".$selected." value='".$subj."'>".licey_subject_translate($subj)."</option>";
		}
		$presentation.= "
			</select>
			<select name='choose_settings[month]' size=1>
		";
		for($i=9; $i!=6; $i++) {
			if($i>12) $i = 1;
			$selected = ($i == $this->settings['month'])? 'selected':'';
			$presentation.= "<option ".$selected." value=".$i.">".licey_month_translate($i)."</option>";
		}
		$presentation.= "
			</select>
			<input type='submit' value='Ок'>
			</form>
		";
		return $presentation;
	}
}

class TableMarks_SingleStudent extends TableMarks
{
	private $subject_dates;

	private $student;

	public function __construct($settings='')
	{
		global $wpdb, $table_subjects;

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
			$this->col_params = array_splice(array_keys( $wpdb->get_row("SELECT * FROM `".$table_subjects."`;", ARRAY_A) ), 3);

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

		global $wpdb, $table_students;
		$current_user = wp_get_current_user();

		if( in_array('Administrator', $current_user->roles) ) return true;
		
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
			global $wpdb, $table_students;
			$ids = $wpdb->get_results("SELECT id FROM $table_students;");

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