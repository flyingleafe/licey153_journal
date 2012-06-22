<?php
function licey_edit_marks() 
{
	$table = new TableMarks();
	licey_update();

	?><h1>Выставление оценок</h1><?php
	
	$table->show_edit();
}

function licey_edit_schedule()
{	
	licey_update();
	
	?><h1>Редактирование расписания</h1><?php
	
	$table = new TableSchedule();
	$table->show_edit();
}	

function licey_journal_options()
{
	licey_update();
	
	$js_class = 'licey-dates-edit';
	$cans = array( 'Летние', 'Осенние', 'Зимние', 'Весенние' );
	$dates = json_decode( get_option('licey_canicular_dates'), true);

	?><h1>Основные настройки журнала</h1><br>
	
	<h2>Даты начала и конца каникул.</h2><?php echo "<form name='edit_dates' method='post' action='" . licey_cur_uri() . "updated=true'>";
	if(function_exists('wp_nonce_field')) wp_nonce_field('edit_dates');

	$c = 0;
	foreach( $cans as $name ) {
		echo $name . " каникулы: с ";
		for($j=0; $j<2; $j++) {
			if($j == 1) echo " по ";
			echo "
				<input class='" . $js_class . "'' type='number' name='canicular_dates_day[" . $c . "][" . $j . "]' value='" . substr($dates[$c][$j], -2) . "' maxlength=2 size=1>
				<select name='canicular_dates_month[" . $c . "][" . $j . "]' size=1>
			";
			for($i=9; $i!=6; $i++) {
				if($i>12) $i = 1;
				$selected = ($i == (int)substr($dates[$c][$j], 5, 2) ) ? 'selected' : '';
				echo "<option " . $selected . " value=" . $i . ">" . licey_month_translate($i) . "</option>";
			}

			echo "</select>";
		}
		echo "<br>";
		$c++;
	}
	
	echo "<input type='submit' name='canicular_dates_btn' value='Сохранить'></form>";
	
	echo "<h2>Настройки отображения.</h2><form name='edit_strings' method='post' action='".$_SERVER['PHP_SELF']."?page=main-options&amp;updated=true'>";
	if(function_exists('wp_nonce_field')) wp_nonce_field('edit_strings');
	
	echo "Ключ для отображения журнала по предметам и классам: <input type='text' name='edit_subj-form_hook' value='".get_option('licey_subj-form_replace_string')."'><br>";
	echo "Ключ для отображения оценок отдельного ученика: <input type='text' name='edit_singlestud_hook' value='".get_option('licey_single-stud_replace_string')."'><br>";
	echo "<input type='submit' name='edit_strings_btn' value='Сохранить'></form>";
}

function licey_edit_forms()
{
	if(licey_update()) return;
	
	?><h1>Ученический состав</h1><?php

	$table = new TableStudents();
	$table->show_edit();
}

function licey_edit_teachers()
{
	echo "right here... right now...";
}

function licey_test()
{
	licey_update();

	echo "right here... right now...";

	$table = new TableMarks();
	$table->show();
}
?>