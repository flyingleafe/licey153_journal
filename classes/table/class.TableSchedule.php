<?php
class TableSchedule extends Table
{
	private $subjects_list;

	public function __construct($settings = '')
	{
		global $wpdb;
		//настроек пока никаких нет
		$this->settings = array();
		$this->subjects_list = array('--//--') + array_splice(array_keys($wpdb->get_row("SELECT * FROM ".JOURNAL_DB_SUBJECTS.";", ARRAY_A)), 2);

		$forms = $wpdb->get_col("SELECT form FROM `" . JOURNAL_DB_SCHEDULE . "`;");
		usort($forms, 'licey_sort_forms');

		foreach($forms as $form)
			$this->row_params[] = new Form($form);

		$this->col_params = array('mon', 'tue', 'wed', 'thu', 'fri', 'sat');

		for($i=0; $i<count($this->col_params); $i++) {
			foreach($this->row_params as $form) {
				$this->table_content[$i][] = $form->schedule->{$this->col_params[$i]};
			}
		}

		$this->show_class = "licey_schedule_table";
		$this->edit_class = "licey_edit_schedule_table";
		$this->labels = array('corner' => '&nbsp;', 'submit' => 'Сохранить');
		$this->labels['access_denied'] = 'Вы не можете просматривать расписание. Зайдите как учитель или ученик.';
		$this->complete = true;
	}

	protected function row_view($row_param)
	{
		return "<h3>" . $row_param->form . "</h3>";
	}

	protected function col_view($col_param)
	{
		return licey_day_translate($col_param);
	}

	protected function content_filter($form, $day, $schedule)
	{
		$input = "<table>";
		$i = 1;
		foreach ($schedule as $subject) {
			$input.= "<tr>";
			if($i % 2) $input.= "<td rowspan=2>" . floor($i/2 + 1) . " пара</td>";
			$input.= "<td>" . licey_subject_translate($subject) . "</td></tr>";
			$i++;
		}
		$input.= "</table>";
		return $input;
	}

	public function view_access_given() {
		$current_user = wp_get_current_user();

		if(is_admin() || $current_user->has_cap('edit_dashboard') || get_current_student() || get_current_teacher() ) return true;
	}

	protected function content_edit_filter($form, $day, $schedule)
	{
		$input = "<table>";
		$i = 1;
		foreach ($schedule as $subject) {
			$input.= "<tr>";
			if($i % 2) $input.= "<td rowspan=2>" . floor($i/2 + 1) . " пара</td>";
			$input.= "<th><select name='schedule[" . $form->form . "][" . $day . "][" . $i . "]' size=1>";
			foreach($this->subjects_list as $item) {
				$selected = ($item === $subject)? ' selected' : '';
				$input.= "<option value='" . $item . "'" . $selected . ">" . licey_subject_translate($item) . "</option>";
			}
			$input.= "</select></th></tr>";
			$i++;
		}
		$input.= "</table>";
		return array('before' => '', 'value' => $input, 'after' => '');
	}

	public function update()
	{
		$schedule = $_POST['schedule'];
		foreach($schedule as $form_name => $days) {
			$form = new Form($form_name);
			foreach($days as $day => $sched)
				$form->updateDaySchedule($day, $sched);
		}
		$this->report = "Расписание успешно изменено";
	}

	public function choose_settings()
	{
		return '';
	}
}
?>