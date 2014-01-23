<?

	class tinyCms {
		
		var $categories = array(
			'News' => array(
				'Titel:text',
				'Nachricht:textarea'
			),
			'Rezepte' => array(
				'Titel:text',
				'Backdauer (h):number',
				'Nachricht:textarea'
			),
		);
		
		const dbFilename = 'tinyCms.db';

		var $sqliteHandle = null;
		
		public function __construct() {
			$this->initDb();
			$this->showCategory('Rezepte');
		}
		
		public function showGui($category) {
			
		}
		
		/***********************************************************************
		 * Private functions for creating the gui for administrative users.
		 **********************************************************************/
		
		/**
		 * This functions processes an overview that contains all available
		 * categories.
		 */ 
		private function showCategoryOverview() {
			
			$content = array();
			$content[] = '<h1>Kategorie-Übersicht</h1>';	
				
			foreach ($this->categories as $category => $fields) {
				$content[] = 
					'<p><a href="' . $_SERVER['SCRIPT_NAME'] . '?category=' .
					urlencode($category) . '">' .$category . '</a></p>';		
			}
			
			echo implode(PHP_EOL, $content);
		}
		
		/**
		 * This function processes an overview that contains all news items
		 * related to this category.
		 * 
		 * @param string Name of the category.
		 */
		private function showCategory($category) {
			
			if (!isset($this->categories[$category]))
				die('Kategorie " ' .$category. ' "existiert nicht.');
			
			$content = array();
			$content[] = '<h1>Kategorie: ' . $category . '</h1>';
				
			foreach ($this->categories as $category => $fields) {
				$content[] = 
					'<p><a href="' . $_SERVER['SCRIPT_NAME'] . '?category=' .
					urlencode($category) . '">' .$category . '</a></p>';		
			}
			
			echo implode(PHP_EOL, $content);
		}
		 
		private function showInputForm($category) {
			
			if (!isset($this->categories[$category]))
				die('Kategorie " ' .$category. ' "existiert nicht.');
			
			$content = array();
			$content[] = '<h1>Kategorie: ' . $category . '</h1>';
			$content[] = '<form>';
			
			foreach ($this->categories[$category] as $field) {
				$field = explode(':', $field);
				$fieldName = self::convertText($field[0]);
				
				$content[] = '<div>' . $field[0] . ':<div>';
				
				if ($field[1] == 'text')
					$content[] = '<div><input type="text" name="' . $fieldName . '" value="" /></div>';
				if ($field[1] == 'number')
					$content[] = '<div><input type="number" name="' . $fieldName . '" value="" /></div>';
				if ($field[1] == 'textarea')
					$content[] = '<div><textarea rows="4" cols="20" name="' . $fieldName . '"></textarea></div>';
				
			} 
			
			$content[] = '<div>Datum:<div>';
			$content[] = '<div><input type="date" name="item_date" value="" /></div>';
			
			$content[] = '<input type="submit" value="absenden" />';
			$content[] = '</form>';

			echo implode(PHP_EOL, $content);
		}
		 
		/***********************************************************************
		 * Private functions for maintenance.
		 **********************************************************************/
		
		private static function convertText($string) {
			$string = strtolower($string);
			$string = preg_replace('#[^\da-z]#', ' ', $string);
			$string = trim($string);
			$string = str_replace(' ', '_', $string);
			return $string;
		}

		private function initDb() {
		
			try {
				$this->sqliteHandle = new SQLite3( self::dbFilename );
			} catch (Exception $e) {
				die('Die Datenbank kann nicht geladen/erstellt werden. Schreibrechte gesetzt?');	
			}
			$this->sqliteHandle->query(
				'CREATE TABLE IF NOT EXISTS
					`items` (
						`item_id` INTEGER PRIMARY KEY AUTOINCREMENT,
						`item_data` TEXT NOT NULL,
						`item_date` DATETIME NOT NULL
					);');
		}
		
	}

	header('Content-Type: text/html; charset=utf-8');
	$cms = new tinyCms();

?>