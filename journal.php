<?php
/**
 * @package FlyingLeafe
 * @version 0.1
 */
/*
Plugin Name: Онлайн-журнал лицея №153
Plugin URI: http://burningweb.ru
Description: Успеваемость учеников онлайн.
Armstrong: My Plugin
Author: Flying Leafe
Version: 0.1 alpha
Author URI: http://burningweb.ru
*/

//Определение ДБ
global $wpdb;
$table_marks = $wpdb->prefix.liceyjournal_marks;
$table_students = $wpdb->prefix.liceyjournal_students;
$table_schedule = $wpdb->prefix.liceyjournal_schedule;
$table_subjects = $wpdb->prefix.liceyjournal_subjects;
$table_teachers = $wpdb->prefix.liceyjournal_teachers;

//Подключение файла функций
require_once(WP_PLUGIN_DIR.'/'.str_replace(basename(__FILE__), "", plugin_basename(__FILE__)).'functions/functions.php');

//Подключение классов
require_once(WP_PLUGIN_DIR.'/'.str_replace(basename(__FILE__), "", plugin_basename(__FILE__)).'functions/classes.php');

//Подключение виджета 
require_once(WP_PLUGIN_DIR.'/'.str_replace(basename(__FILE__), "", plugin_basename(__FILE__)).'widget.php');


/**
 * Функция установки плагина
 * Создает базу данных и инициализирует настройки
**/
function journal_install()
{
	//Подключаем установочный скрипт
	$path = WP_PLUGIN_DIR.'/'.str_replace(basename(__FILE__), "", plugin_basename(__FILE__)).'install.php';
	require_once($path);
}

/**
 * Функция инициализации админ-меню
**/
function journal_admin_page() 
{
	//Добавляем страницу настроек
	$main = WP_PLUGIN_DIR.'/'.str_replace(basename(__FILE__), "", plugin_basename(__FILE__)).'options.php';
	require_once($main);
	add_menu_page('Онлайн-журнал', 'Журнал', 8, 'main-options', 'licey_journal_options');
	//добавим субменю
	//Эта строка закомментирована для того, чтобы в ближайшем будущем начать писать мастер установки журнала
	//$settings_completed = get_option('licey_completed_settings');
	$settings_completed = true;
	if( $settings_completed ) {
		add_submenu_page('main-options', 'Онлайн-журнал', 'Выставление оценок', 8, 'marks-edit', 'licey_edit_marks');
		add_submenu_page('main-options', 'Онлайн-журнал', 'Расписание', 8, 'schedule-edit', 'licey_edit_schedule');
		add_submenu_page('main-options', 'Онлайн-журнал', 'Список учеников', 8, 'forms-edit', 'licey_edit_forms');
		add_submenu_page('main-options', 'Онлайн-журнал', 'Список учителей', 8, 'teachers-edit', 'licey_edit_teachers');
		add_submenu_page('main-options', 'Онлайн-журнал', 'Тестовая страница', 8, 'test', 'licey_test');
	}
}

function show_singlestudent_marks( $settings ) 
{
	$cur_student = get_current_student();
	if(!$cur_student && !is_admin()) return "Вы не можете просматривать журнал. Зайдите под своим пользователем-учеником, или же осуществите СМС-валидацию ученика.";

	$settings = shortcode_atts( array(
		'student' => $cur_student->username,
		'month' => '',
	), $settings );

	$table = new TableMarks_SingleStudent( $settings );
	return "<h1>" . $cur_student->fio . "</h1>" . $table->show(true);
}

function show_schedule( $attr )
{
	$table = new TableSchedule;
	return $table->show(true);
}

/**
 * Функция инициализации шорткода
 * Создает шорткод, который выводит таблицу с оценками с заданными параметрами
 */
function show_subjform_marks( $settings )
{
	$cur_student = get_current_student();
	if(!$cur_student && !is_admin()) return "Вы не можете просматривать журнал. Зайдите под своим пользователем-учеником, или же осуществите СМС-валидацию ученика.";

	$settings = shortcode_atts( array(
		'form' => $cur_student->form,
		'month' => '',
		'subject' => ''
	), $settings );

	$table = new TableMarks( $settings );
	return $table->show(true);
}

function licey_styles()
{
	echo "<link rel='stylesheet' type='text/css' href='" . plugins_url('styles/style.css', __FILE__) . "'>";
}

function licey_admin_styles_and_js()
{
	echo "<link rel='stylesheet' type='text/css' href='" . plugins_url('styles/admin.css', __FILE__) . "'>";
	echo "<script type='text/javascript' src='" . plugins_url('js/admin.js', __FILE__) . "'></script>";
}

register_activation_hook(__FILE__, 'journal_install');
add_shortcode('singlestudent', 'show_singlestudent_marks');
add_shortcode('subj-form', 'show_subjform_marks');
add_shortcode('licey_schedule', 'show_schedule');
add_action('wp_head', 'licey_styles');
add_action('admin_head', 'licey_admin_styles_and_js');
add_action('admin_menu', 'journal_admin_page');
?>
