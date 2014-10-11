<?php

class tinyCms {
    
    private $categories = array(
        'News' => array(
            'Nachricht:textarea'
        ),
        'Unsere "Rezepte"' => array(
            'Kurzbeschreibung:text',
            'Backdauer (h):number',
            'Nachricht:textarea'
        ),
    );
    
    const dbFilename = 'tinyCms.db';

    private $sqliteHandle = null;
    
    public function __construct()
    {
        $this->initDb();
    }
    
    public function getOverview($category)
    {  
        $category = SQLite3::escapeString($category);
      
        $result = $this->sqliteHandle->query("SELECT
                                                    *
                                                FROM
                                                    `items`
                                                WHERE
                                                    `item_category` = '" . $category . "'
                                                ORDER BY
                                                    `item_date` DESC");
        $buffer = array();
        while($row = $result->fetchArray()) {
            $temp = array(
                'item_id' => $row['item_id'],
                'item_title' => $row['item_title'],
                'item_date' => $row['item_date']
            );
            $temp = array_merge($temp, json_decode($row['item_data'], true));
            $buffer[] = new tinyCmsItem($temp);
        }
      
        return $buffer;
      
    }
    
    public function getItem($item_id)
    {  
        $item_id = (int) $item_id;
      
        $result = $this->sqliteHandle->query("SELECT
                                                    *
                                                FROM
                                                    `items`
                                                WHERE
                                                    `item_id` = " . $item_id . "
                                                LIMIT 1");
        $row = $result->fetchArray();
      
        $temp = array(
            'item_id' => $row['item_id'],
            'item_title' => $row['item_title'],
            'item_date' => $row['item_date']
        );
        $temp = array_merge($temp, json_decode($row['item_data'], true));
      
        return new tinyCmsItem($temp);
    }
    
    public function showGui()
    {  
        if (isset($_POST['save'])) {
            $this->saveForm($_POST);
        } else if (isset($_GET['delete']) and 0 <= (int) $_GET['delete'] and isset($_GET['category']) and !empty($_GET['category'])) {
            $this->deleteItem($_GET['delete'], $_GET['category']);
        } else if (isset($_GET['item']) and 0 <= (int) $_GET['item'] and isset($_GET['category']) and !empty($_GET['category'])) {
            $this->showInputForm($_GET['item'], $_GET['category']);
        } else if (isset($_GET['category']) and !empty($_GET['category'])) {        
            $this->showCategory($_GET['category']);
        } else {
            $this->showCategoryOverview();  
        }
    }
    
    /***************************************************************************
     * Private functions for creating the gui for administrative users.
     **************************************************************************/
    
    /**
     * This functions processes an overview that contains all available
     * categories.
     */
    private function showCategoryOverview()
    {  
        $content = array();
        $content[] = '<h1>Kategorie-Übersicht</h1>';  
        $content[] = '<table class="table">';    
        $content[] = '<thead><tr><th>Titel</th><th></th></tr></thead>';    
        
        foreach ($this->categories as $category => $fields) {
            $content[] = 
                '<tr><td><a href="' . $_SERVER['SCRIPT_NAME'] . '?category=' .
                urlencode($category) . '">' .$category . '</a></td><td><a class="btn btn-primary btn-sm" href="' .
                $_SERVER['SCRIPT_NAME'] . '?category=' . urlencode($category) . '">bearbeiten</a></td></tr>';    
        }      
      
        $content[] = '</table>';    
      
        echo '<div class="tinyCms">'.implode(PHP_EOL, $content).'</div>';
    }
    
    /**
     * This function processes an overview that contains all news items
     * related to this category.
     * 
     * @param string Name of the category.
     */
    private function showCategory($category)
    {  
        if (!isset($this->categories[$category]))
            die('Kategorie " ' .$category. ' "existiert nicht.');
      
        $content = array();
        $content[] = '<h1>Kategorie: ' . $category . '</h1>';
        $content[] = '<table class="table"><thead><tr><th>ID</th><th>Titel</th><th>Datum</th><th></th></tr></thead>';
      
        $result = $this->sqliteHandle->query("SELECT * FROM `items` WHERE `item_category` = '" . SQLite3::escapeString($category) . "'");
        
        while ($row = $result->fetchArray()) {
            $content[] = 
                '<tr><td>' .$row['item_id'] . '</td><td><a href="' .
                $_SERVER['SCRIPT_NAME'] . '?category=' . urlencode($category) . '&item=' .
                $row['item_id'] . '">' . $row['item_title'] . '</a></td><td>' .
                $row['item_date'] . '</td><td><a class="btn btn-primary btn-sm" href="' .
                $_SERVER['SCRIPT_NAME'] . '?category=' . urlencode($category) . '&item=' .
                $row['item_id'] . '">bearbeiten</a>
                <a class="btn btn-danger btn-sm" href="' . $_SERVER['SCRIPT_NAME'] .
                '?category=' . urlencode($category) . '&delete=' . $row['item_id'] .
                '">löschen</a></td></tr>';
        }
        $content[] = 
            '<tr><td><a href="' . $_SERVER['SCRIPT_NAME'] . '" class="btn btn-default btn-sm">zurück</a></td><td></td><td></td><td><a href="' .
            $_SERVER['SCRIPT_NAME'] . '?category=' . urlencode($category) .
            '&item=0" class="btn btn-primary btn-sm">Neuer Eintrag</a></td><td></td></tr>';
        $content[] = '</table>';
      
        echo '<div class="tinyCms">'.implode(PHP_EOL, $content).'</div>';
    }
     
    private function showInputForm($itemId, $category)
    {  
        if (!isset($this->categories[$category]))
            die('Kategorie " ' .$category. ' "existiert nicht.');
      
        $values = array('item_title' => '', 'item_data' => '{}', 'item_date' => date('Y-m-d'));
      
        $itemId = (int) $itemId;
        if ($itemId > 0) {
            $result = $this->sqliteHandle->query('SELECT * FROM `items` WHERE `item_id` = "' . $itemId . '"');
            $values = $result->fetchArray();
        } 
        $values['item_data'] = json_decode($values['item_data'], true);
            
        $content = array();
        $content[] = '<h1>Kategorie: ' . $category . '</h1>';
        $content[] = '<form action="' . $_SERVER['SCRIPT_NAME'] . '" method="post" role="form">';
      
        $content[] = '<div><input type="hidden" name="item_id" value="' . $itemId . '" /></div>';
        $content[] = '<div><input type="hidden" name="item_category" value="' . str_replace('"', '&quot;', $category) . '" /></div>';
      
        $content[] = '<div class="form-group"><label for="item_title">Titel:</label>';
        $content[] = '<input class="form-control" type="text" name="item_title" value="' . str_replace('"', '&quot;', $values['item_title']) . '" /></div>';

        foreach ($this->categories[$category] as $field) {
            $field = explode(':', $field);
            $fieldName = self::convertText($field[0]);
        
            $value = '';
            if (isset($values['item_data'][$fieldName]))
                $value = str_replace('"', '&quot;', $values['item_data'][$fieldName]);
        
            $content[] = '<div class="form-group"><label for="item_data[' . $fieldName . ']">' . $field[0] . ':</label>';
        
            if ($field[1] == 'text')
                $content[] = '<input class="form-control" type="text" name="item_data[' . $fieldName . ']" value="' . $value . '" /></div>';
            if ($field[1] == 'number')
                $content[] = '<input class="form-control" type="number" name="item_data[' . $fieldName . ']" value="' . $value . '" /></div>';
            if ($field[1] == 'textarea')
                $content[] = '<textarea class="form-control" rows="7" name="item_data[' . $fieldName . ']">' . $value . '</textarea></div>';
        } 
      
        $content[] = '<div class="form-group"><label for="item_date">Datum:</label>';
        $content[] = '<input class="form-control" type="date" name="item_date" value="' . $values['item_date'] . '" /></div>';
      
        $content[] = '<a href="' . $_SERVER['SCRIPT_NAME'] . '?category=' . $category . '" class="btn btn-default btn-sm">zurück</a>';
        $content[] = '<input type="submit" name="save" class="btn btn-primary btn-sm" value="absenden" />';
        $content[] = '</form>';

        echo '<div class="tinyCms">'.implode(PHP_EOL, $content).'</div>';
    }

    private function saveForm($fields)
    {  
        $relocate = $_SERVER['SCRIPT_NAME'] . '?category=' . urlencode($fields['item_category']);
      
        $fields['item_id'] = (int) $fields['item_id'];
        $fields['item_title'] = SQLite3::escapeString($fields['item_title']);
        $fields['item_data'] = json_encode($fields['item_data']);
        $fields['item_data'] = SQLite3::escapeString($fields['item_data']);
        $fields['item_category'] = SQLite3::escapeString($fields['item_category']);
        $fields['item_date'] = SQLite3::escapeString($fields['item_date']);
      
        if ($fields['item_id'] <= 0) {
            $this->sqliteHandle->query("INSERT INTO
                                            `items`
                                            (
                                                `item_title`,
                                                `item_data`,
                                                `item_category`,
                                                `item_date`
                                            ) VALUES (
                                                '" . $fields['item_title'] ."',
                                                '" . $fields['item_data'] ."',
                                                '" . $fields['item_category'] . "',
                                                '" . $fields['item_date'] ."'
                                            );");  
        } else {
            $this->sqliteHandle->query("UPDATE
                                            `items`
                                        SET
                                            `item_title` = '" . $fields['item_title'] . "',
                                            `item_data` = '" . $fields['item_data'] . "',
                                            `item_category` = '" . $fields['item_category'] . "',
                                            `item_date` = '" . $fields['item_date'] . "'
                                        WHERE
                                            `item_id` = " . $fields['item_id'] . "
                                        LIMIT 1");                  
        }    
      
        header('Location: '.$relocate);
        exit();
    }

    private function deleteItem($itemId, $category)
    {  
        $relocate = $_SERVER['SCRIPT_NAME'] . '?category=' . urlencode($category);
      
        $itemId = (int) $itemId;

        if ($itemId > 0) {
            $this->sqliteHandle->query("DELETE FROM
                                            `items`
                                        WHERE
                                            `item_id` = " . $itemId ."
                                        LIMIT 1");
        }  
      
        header('Location: '.$relocate);
        exit();
    }
     
    /***********************************************************************
     * Private functions for maintenance.
     **********************************************************************/
    
    private static function convertText($string)
    {
        $string = strtolower($string);
        $string = preg_replace('#[^\da-z]#', ' ', $string);
        $string = trim($string);
        $string = str_replace(' ', '_', $string);
        return $string;
    }

    private function initDb()
    {
        date_default_timezone_set('Europe/Berlin');

        try {
            $this->sqliteHandle = new SQLite3( dirname(__FILE__) . '/' . self::dbFilename );
        } catch (Exception $e) {
            die('Die Datenbank kann nicht geladen/erstellt werden. Schreibrechte gesetzt?');  
        }
        $this->sqliteHandle->query(
            'CREATE TABLE IF NOT EXISTS
                `items` (
                    `item_id` INTEGER PRIMARY KEY AUTOINCREMENT,
                    `item_title` VARCHAR(128) NOT NULL,
                    `item_data` TEXT NOT NULL,
                    `item_category` VARCHAR(128) NOT NULL,
                    `item_date` DATE NOT NULL
            );
        ');
    }
    
}

class tinyCmsItem {
    private $fields = array();
    
    public function __construct($array)
    {
        $this->fields = $array;
    }
    
    public function get($field, $truncate = -1)
    {  
        if ($field == 'Titel')
            return self::truncate($this->fields['item_title'], $truncate);
        if ($field == 'Datum') {
            $date = new DateTime($this->fields['item_date']);
            return $date->format('d.m.Y');
        }
      
        $this->fields[self::convertText($field)] = str_replace("\n", '<br />', $this->fields[self::convertText($field)]);
      
        if (isset($this->fields[self::convertText($field)]))
            return self::truncate($this->fields[self::convertText($field)], $truncate);
    }
    
    public function getLink($page)
    {
      return $page.'?id='.$this->fields['item_id'];
    }
    
    private static function convertText($string)
    {
        $string = strtolower($string);
        $string = preg_replace('#[^\da-z]#', ' ', $string);
        $string = trim($string);
        $string = str_replace(' ', '_', $string);
        return $string;
    }
    
    private static function truncate($string, $maxLength = -1)
    {
        $maxLength = (int) $maxLength;
      
        if ($maxLength > 0)
            $string = substr($string, 0, strrpos(substr($string, 0, $maxLength), ' ')) . ' ...';
      
        return $string;
    }
}
