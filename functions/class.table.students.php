<?php
class TableStudents extends Table
{
	public function __construct( $settings = array() )
	{
		global $wpdb, $table_schedule;
		$this->settings = $settings;

		$forms = $wpdb->get_col("SELECT form FROM `" . $table_schedule . "`;");
		usort($forms, 'licey_sort_forms');

		for($i = 0; $i<count($forms); $i++) {
			$this->row_params[$i] = new Form($forms[$i]);
			$this->table_content[$i] = $this->row_params[$i]->get_students();
		}

		$this->show_class = 'licey_sched_table';
		$this->edit_class = 'licey_edit_sched_table';
		$this->labels['submit'] = 'Cохранить';
		$this->labels['delete'] = 'Удалить';
		$this->labels['edit'] = 'Ред.';
	}

	public function show($return = false)
	{
		$presentation = "<div class=" . $this->show_class . ">";

		$i = 0;
		foreach($this->row_params as $form) {
			$presentation.= "<div><h1>" . $form->form . "</h1><ol>";

			foreach ($this->table_content[$i] as $student)
				$presentation.= "<li>" . $student->fio . "</li>";

			$presentation.= "</ol></div>";
			$i++;
		}

		$presentation.= "</div>";

		if($return) return $presentation;
		echo $presentation;
	}

	public function show_edit($return = false)
	{
		if(isset($_REQUEST['students_edit_btn'])) {
			foreach($_POST['checked_students'] as $id) {
				$student = new Student($id);
				$student->show_edit();
			}
			return true;
		}

		if(isset($_REQUEST['student-edit'])) {
			$id = $_REQUEST['student-edit'];
			if( !is_numeric($id) ) $id = $_REQUEST['student-id'];
			$student = new Student($id);
			$student->show_edit();
			return true;
		}

		$presentation = $this->showAddFields(true);

		$presentation.= $this->getHeader();

		$presentation.= "<div class=" . $this->edit_class . "><form></form>";

		$i = 0;
		foreach($this->row_params as $form) {
			$presentation.= "
				<div>
					<input type='checkbox' name='checked_forms[] value='" . $form->id .  "'>
					<h1>" . $form->form . "</h1>
					<form method='post' name='form-options-" . $form->id .  "' action=''>
						<input type='hidden' name='form-id' value='" . $form->id . "'>
						<input type='submit' name='form-edit' value='" . $this->labels['edit'] . "'>
						<input type='submit' name='form-del' value='" . $this->labels['delete'] . "'>
					</form>
					<ol>
			";

			foreach ($this->table_content[$i] as $student) 
				$presentation.= "
					<li>
						<input type='checkbox' name='checked_students[]' value='" . $student->id . "'>
						<a href='" . licey_cur_uri() . "student-edit=" . $student->id . "'>" . $student->fio . "</a>
						<form method='post' name='student-options-" . $student->id . "' action=''>
							<input type='hidden' name='student-id' value='" . $student->id . "'>
							<input type='submit' name='student-edit' value='" . $this->labels['edit'] . "'>
							<input type='submit' name='student-del' value='" . $this->labels['delete'] . "'>
						</form>
					</li>
				";

			$presentation.= "</ol></div>";
			$i++;
		}

		$presentation.= $this->getFooter();

		if($return) return $presentation;
		echo $presentation;
	}

	public function showAddFields($return = false)
	{
		global $wpdb, $table_schedule;

		$forms = $wpdb->get_col("SELECT form FROM `".$table_schedule."`;");
		usort($forms, 'licey_sort_forms');
		
		$presentation = "
			Добавить класс: <form name='add_new_form' method='post' action='" .  $_SERVER['PHP_SELF'] . "?page=forms-edit&amp;updated=true'>
				<select name='add_form_number' size=1>
		";
		for ($i=9; $i<12; $i++) $presentation.= "<option>" . $i . "</option>";
		$presentation.= "
				</select>
				<input type='text' name='add_form_letter' maxlength=1 size=1 value=''>
				<input type='submit' value='Добавить'>
			</form><br>
			Добавить ученика: <form name='add_new_student' method='post' action='" . $_SERVER['PHP_SELF'] . "?page=forms-edit&amp;updated=true'>
				ФИО: <input type='text' name='add_student_name' value=''>
				Имя пользователя: <input type='text' name='add_student_username' value=''>
				Класс: <select name='add_student_form' size=1>
		";
		foreach ($forms as $form) $presentation.= "<option>" . $form . "</option>";
		
		$presentation.= "		
				</select>
				<input type='submit' name='add_student_btn' value='Добавить'>
			</form>
		";
		if($return) return $presentation;
		echo $presentation;
	}

	public function getFooter()
	{
		$btns = "
			<input type='hidden' name='table_update_btn' value=''>
			<p>
			С выбранными классами:
			<input class='" . $this->submit_btn_class . "' type='submit' value='" . $this->labels['edit'] . "' name='forms_edit_btn'>
			<input class='" . $this->submit_btn_class . "' type='submit' value='" . $this->labels['delete'] . "'' name='forms_delete_btn'>
			</p><p>
			С выбранными учениками:
			<input class='" . $this->submit_btn_class . "' type='submit' value='" . $this->labels['edit'] . "' name='students_edit_btn'>
			<input class='" . $this->submit_btn_class . "' type='submit' value='" . $this->labels['delete'] . "'' name='students_delete_btn'>
			</p>
			</div>
			</form>
		";
		return $btns;
	}

	public function update() {
		
		$stud_ids = $_POST['checked_students'];
		$form_ids = $_POST['checked_forms'];

		if(isset($_POST['students_delete_btn'])) {
			foreach($stud_ids as $id) {
				$student = new Student($id);
				$student->delete();
			}
			$this->report = 'Ученики успешно удалены';
		}
	}

	public function choose_settings()
	{
		return '';
	}
}
?>