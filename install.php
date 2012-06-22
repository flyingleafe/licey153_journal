<?php
	
global $wpdb;
//определяем имена будущих таблиц
$table_marks = $wpdb->prefix.liceyjournal_marks;
$table_students = $wpdb->prefix.liceyjournal_students;
$table_schedule = $wpdb->prefix.liceyjournal_schedule;
$table_subjects = $wpdb->prefix.liceyjournal_subjects;
$table_teachers = $wpdb->prefix.liceyjournal_teachers;

/****определяем настройки*****/
add_option('licey_completed_settings', false, '', 'yes');

add_option('licey_canicular_dates', json_encode( array(
	array(
		licey_get_study_year('05-31').'-05-31',
		licey_get_study_year('09-01').'-09-01'
	),
	array(
		licey_get_study_year('10-31').'-10-31',
		licey_get_study_year('11-06').'-11-06'
	),
	array(
		licey_get_study_year('12-30').'-12-30',
		licey_get_study_year('01-13').'-01-13'
	),
	array(
		licey_get_study_year('03-12').'-03-12',
		licey_get_study_year('04-01').'-04-01'
	)
) ), '', 'yes');

add_option('licey_single-stud_replace_string', '[singlestudent]', '', 'yes');
add_option('licey_subj-form_replace_string', '[subj-form]', '', 'yes');

$the_page_title = "Журнал ученика";

$the_page = get_page_by_title( $the_page_title );

if ( !$the_page ) {

    // Create post object
    $_p = array();
    $_p['post_title'] = $the_page_title;
    $_p['post_content'] = "[singlestudent]";
    $_p['post_status'] = 'publish';
    $_p['post_type'] = 'page';
    $_p['comment_status'] = 'closed';
    $_p['ping_status'] = 'closed';
    $_p['post_category'] = array(1); // the default 'Uncatrgorised'

    // Insert the post into the database
    $the_page_id = wp_insert_post( $_p );

    $the_page = get_post( $the_page_id );

} else {
    // the plugin may have been previously active and the page may just be trashed...

    $the_page_id = $the_page->ID;

    //make sure the page is not trashed...
    $the_page->post_status = 'publish';
    $the_page_id = wp_update_post( $the_page );

}
//записываем путь к странице журнала в настройку
add_option('licey_journal_student_url', $the_page->guid);

/****создаем таблицы****/

//таблица оценок
$sql1 =
"
	CREATE TABLE IF NOT EXISTS `".$table_marks."` (
	`id` int auto_increment,
	`subject` varchar(16) not null,
	`student` varchar(32) not null,
	`date` date not null,
	`mark` tinyint not null,
	PRIMARY KEY (id)
	) ENGINE=InnoDB DEFAULT CHARSET=utf8;
";
$wpdb->query($sql1);

//таблица учеников
$sql2 = 
"
	CREATE TABLE IF NOT EXISTS `".$table_students."` (
	`id` int auto_increment,
	`username` varchar(32) not null,
	`student_name` varchar(60) not null,
	`wp_user` varchar(60),
	`form` varchar(3) not null,
	`phone` varchar(11) not null,
	`confirmed` boolean,
	PRIMARY KEY (id)
	) ENGINE=InnoDB DEFAULT CHARSET=utf8;
";
$wpdb->query($sql2);

//таблица расписания
$sql3 = 
"
	CREATE TABLE IF NOT EXISTS `".$table_schedule."` (
	`id` int auto_increment,
	`form` varchar(3) not null,
	`mon` varchar(256) not null,
	`tue` varchar(256) not null,
	`wed` varchar(256) not null,
	`thu` varchar(256) not null,
	`fri` varchar(256) not null,
	`sat` varchar(256) not null,
	PRIMARY KEY (id)
	) ENGINE=InnoDB DEFAULT CHARSET=utf8;
";
$wpdb->query($sql3);

//таблица учителей по предметам
$sql4 = 
"
	CREATE TABLE IF NOT EXISTS `".$table_subjects."` (
	`id` int auto_increment,
	`form` varchar(3) not null,
	`formmaster` varchar(256) not null,
	`algebra` varchar(256) not null,
	`geometry` varchar(256) not null,
	`physics` varchar(256) not null,
	`russian` varchar(256) not null,
	`english` varchar(256) not null,
	`inform` varchar(256) not null,
	`chemistry` varchar(256) not null,
	`biology` varchar(256) not null,
	`geography` varchar(256) not null,
	`history` varchar(256) not null,
	`sociology` varchar(256) not null,
	`economy` varchar(256) default '',
	`fizra` varchar(256) not null,
	`drawing` varchar(256) default '',
	`bashkort` varchar(256) default '',
	`obj` varchar(256) default '',
	PRIMARY KEY (id)
	) ENGINE=InnoDB DEFAULT CHARSET=utf8;
";
$wpdb->query($sql4);

//таблица учителей
$sql5 = 
"
	CREATE TABLE IF NOT EXISTS `".$table_teachers."` (
	`id` int auto_increment,
	`username` varchar(32) not null,
	`fio` varchar(60) not null,
	`wp_user` varchar(60),
	`subjects` varchar(128),
	PRIMARY KEY (id)
	) ENGINE=InnoDB DEFAULT CHARSET=utf8;
";
$wpdb->query($sql5);

?>