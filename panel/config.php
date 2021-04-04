<?php
   define('DB_SERVER', 'localhost');
   define('DB_USERNAME', 'root');
   define('DB_PASSWORD', 'p6qr9a2GDma');
   define('DB_DATABASEFORUM', 'loader');
   $dbforum = mysqli_connect(DB_SERVER,DB_USERNAME,DB_PASSWORD,DB_DATABASEFORUM);
   
   class Logger {
		private
			$file,
			$timestamp;
	
		public function __construct($filename) {
			$this->file = $filename;
		}
	
		public function setTimestamp($format) {
			$this->timestamp = date($format);
		}
	
		public function putLog($op, $insert) {
			if (isset($this->timestamp)) {
				file_put_contents($this->file, $op."[".$this->timestamp."]".$insert, FILE_APPEND);
			} else {
				trigger_error("Timestamp not set", E_USER_ERROR);
			}
		}
	
		public function getLog() {
			$content = @file_get_contents($this->file);
			return $content;
		}
	
	}

	function containsWord($str, $word) {
		return !!preg_match('#\\b' . preg_quote($word, '#') . '\\b#i', $str);
	}
?>