<?php
class Licey_Widget extends WP_Widget {
 
	public function Licey_Widget() {
		$widget_ops = array( 'classname' => 'licey_widget', 'description' => 'Меню управления журналом ученика и его учетной записью.' );
		$control_ops = array( 'width' => 200, 'height' => 250, 'id_base' => 'liceywidget' );
		parent::__construct( 'liceywidget', 'Виджет журнала', $widget_ops, $control_ops );
	}
 
	public function form($instance) {
		?>
		<p>
			<label for="<?php echo $this->get_field_id('licey'); ?>"><?php _e('Title'); ?>:</label>
			<input id="<?php echo $this->get_field_id('licey'); ?>" name="<?php echo $this->get_field_name('title'); ?>" value="<?php echo $instance['title']; ?>">
		</p>
		<?php
	}
 
	public function update($new_instance, $old_instance) {
		return $new_instance;
	}
 
	public function widget($args, $instance) {
		echo $args['before_widget'] . $args['before_title'];
		echo ($instance['title']) ? $instance['title'] : 'Журнал лицея №153';
		echo $args['after_title'];
		$cur_student = get_current_student();
		if(!$cur_student && !is_user_logged_in()) {
			echo "<h2 class='greetings'>Авторизуйтесь, чтобы пользоваться журналом</h2>";
		} else if(!$cur_student) {
			echo "
				<h2 class='greetings'>Подтвердите, что вы являетесь учеником (учителем), чтобы пользоваться журналом:</h2>
				<ul class='licey_widget_menu'>
					<li><a href='#'>Подтвердить аккаунт ученика</a></li>
				</ul>
			";
		} else {
			echo "
				<h2 class='greetings'>Добро пожаловать, " . licey_shortname($cur_student->fio) . "!</h2>
				<ul class='licey_widget_menu'>
					<li><a href='" . get_option('licey_journal_student_url') . "'>Ваши оценки</a></li>
					<li><a href='" . get_option('licey_schedule_url') . "'>Расписание</a></li>
				</ul>
			";
		}
		echo $args['after_widget'];
	}
}
 
// register HelloWorld widget
add_action('widgets_init', create_function('', 'return register_widget("Licey_Widget");') );
?>