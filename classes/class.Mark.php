<?php
/**
 * Класс оценки. Маленький-премаленький
 */
class Mark
{
	public $id;
	public $mark;
	public $student;
	public $subject;
	public $date;

	public function __construct($param) {
		global $wpdb;

		if(is_numeric($param)) {
			$mark = $wpdb->get_row("SELECT * FROM `".JOURNAL_DB_MARKS."` WHERE id='".$param."';");

			$this->id = $param;
			$this->mark = $mark->mark;
			$this->date = $mark->date;
			$this->student = $mark->student;
			$this->subject = $mark->subject;

		} else if(is_array($param)) {	
			
			$this->date = $param['date'];
			$this->student = $param['student'];
			$this->subject = $param['subject'];

			$others = $wpdb->get_row("SELECT * FROM `".JOURNAL_DB_MARKS."` WHERE date='".$this->date."' AND student='".$this->student."' AND subject='".$this->subject."';");
			$this->mark = $others->mark;
			$this->id = $others->id;
		}

	}

	public function update($new_mark) {
		global $wpdb;

		$new_mark = __mark($new_mark);
		if($new_mark == $this->mark) return;

		if($this->mark) {
			if ($new_mark) {
				$wpdb->update(JOURNAL_DB_MARKS, array('mark' => $new_mark), array('date' => $this->date, 'student' => $this->student, 'subject' => $this->subject), array('%d'), array('%s', '%s', '%s'));
			} else {
				$wpdb->query("DELETE FROM `".JOURNAL_DB_MARKS."` WHERE date='".$this->date."' AND student='".$this->student."' AND subject='".$this->subject."';");
			}

		} else {
			$wpdb->insert(JOURNAL_DB_MARKS, array('mark' => $new_mark, 'date' => $this->date, 'student' => $this->student, 'subject' => $this->subject), array('%d', '%s', '%s', '%s'));
		}
	}
}