<?php
/**
 * Абстрактный старший класс таблицы
 * @package Classes/Table
 */
abstract class Table
{
	//составлена ли таблица
	protected $complete;

	//составные части таблицы
	protected $table_content;
	protected $row_params;
	protected $col_params;

	//дополнительные параметры, кот. необходимо передать
	protected $settings;
	
	//некоторое текстовое наполнение
	protected $labels = array();

	//классы CSS
	protected $show_class = 'licey_show_table';
	protected $edit_class = 'licey_edit_table';
	protected $submit_btn_class = 'licey_table_update_btn';

	//сообщение о состоянии
	public $report;

	abstract public function __construct($settings);
	public function choose_settings() {}

	public function view_access_given() {
		return true;
	}
	
	protected function row_view($row_param) {
		return $row_param;
	}
	protected function col_view($col_param) {
		return $col_param;
	}
	protected function content_filter($row_param, $col_param, $item) {
		return $item;
	}
	protected function content_edit_filter($row_param, $col_param, $item) {
		return array('before' => '', 'value' => $item, 'after' => '');
	}
	
	/**
	 * Функция отображения таблицы для просмотра
	 */
	public function show($return = false)
	{
		$presentation = $this->choose_settings();

		//готова ли таблица?
		if($this->complete) {

			$presentation.= "<table class='" . $this->show_class . "'>";
			if($this->labels['corner']) $presentation.= "<tr><td>" . $this->labels['corner'] . "</td>";

			foreach($this->row_params as $date) 
				$presentation.= "<td>" . $this->row_view($date) . "</td>";

			$presentation.= "</tr>";
				
			for ( $i=0; $i < count($this->table_content); $i++ ) {
				$presentation.= "<tr>";
				$presentation.= "<td><span>" . $this->col_view($this->col_params[$i]) . "</span></td>";
				
				$j = 0;
				foreach ($this->row_params as $row_param) {
					$input = $this->content_filter($row_param, $this->col_params[$i], $this->table_content[$i][$j]);
					if($input) $j++;
					$presentation.= "<td>" . $input . "</td>";
				}
				
				$presentation.= "</tr>";
			}

			$presentation.= "</table>";

		} else $presentation.= "<div>" . $this->labels['incompleted'] . "</div>";

		if($return) return $presentation;
		echo $presentation;
	}


	/**
	 * Функция отображения таблицы для редактирования
	 */
	public function show_edit($return = false)
	{
		$presentation = $this->choose_settings();

		//готова ли таблица?
		if($this->complete) {
			if(!$this->view_access_given()) {
				echo $this->labels['access_denied'];
				return false;
			}

			$presentation.= $this->getHeader();

			$presentation.= "<table class='" . $this->edit_class . "'>";
			$presentation.= "<tr><td>" . $this->labels['corner'] . "</td>";

			foreach ($this->row_params as $date) 
				$presentation.= "<td>" . $this->row_view($date) . "</td>";

			$presentation.= "</tr>";

			for ( $i=0; $i < count($this->table_content); $i++ ) {
				$presentation.= "<tr>";
				$presentation.= "<td><span>" . $this->col_view($this->col_params[$i]) . "</span></td>";
				
				$j = 0;
				foreach ($this->row_params as $row_param) {
					$input = $this->content_edit_filter($row_param, $this->col_params[$i], $this->table_content[$i][$j]);
					if($input['value']) $j++;
					$presentation.= "<td>". $input['before'] . $input['value'] . $input['after'] . "</td>";
				}
					
				$presentation.= "</tr>";
			}

			$presentation.= "</table>";

			$presentation.= $this->getFooter();

		} else $presentation.= "<div>" . $this->labels['incompleted'] . "</div>";

		if($return) return $presentation;
		echo $presentation;
	}

	abstract public function update();

	public function getHeader()
	{
		$header = "
			<form name='licey_edit_table' method='post' action='" . licey_cur_uri() . "updated=true'>
			<input type='hidden' name='table_classname' value='" . get_class($this) . "'>
			<input type='hidden' name='table_settings' value='" . licey_pack( $this->settings ) . "'>
		";
		return $header;
	}

	public function getFooter()
	{
		$btn = "
			<input class='button-primary " . $this->submit_btn_class . "' type='submit' value='" . $this->labels['submit'] . "'' name='table_update_btn'>
			</form>
		";
		return $btn;
	}
}


$path = WP_PLUGIN_DIR.'/'.str_replace(basename(__FILE__), "", plugin_basename(__FILE__));
require_once($path.'class.table.marks.php');
require_once($path.'class.table.schedule.php');
require_once($path.'class.table.students.php');
require_once($path.'class.table.teachers.php');
?>