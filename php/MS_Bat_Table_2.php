<?php

/**
 * Batting Table
 */

require_once "MS_DB.php";
require_once "MS_Bat_Table.php";

class MS_Bat_Table_2 extends MS_Bat_Table
{
    public function __construct($link)
    {
        // set lavel
        $this->label = array('名前',
                             '試合',
                             '打席',
                             '打数',
                             '単打',
                             '二塁打',
                             '三塁打',
                             '本塁打',
                             '四球',
                             '死球',
                             '長打率',
                             '打率');
        $this->align = array('名前' => 'left');
        $this->url = $link;
        $this->gray = array('打率', '長打率');
    }
}

/*
 * Local variables:
 * tab-width: 4
 * c-basic-offset: 4
 * c-hanging-comment-ender-p: nil
 * End:
 */

?>
