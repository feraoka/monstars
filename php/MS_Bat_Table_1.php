<?php

/**
 * Batting Table
 */

require_once "MS_DB.php";
require_once "MS_Bat_Table.php";

class MS_Bat_Table_1 extends MS_Bat_Table
{
    public function __construct($link)
    {
        // set lavel
        $this->label = array('名前',
                             '試合',
                             '打席',
                             '打数',
                             '安打',
                             '本塁打',
                             '塁打',
                             '四死球',
                             '三振',
                             '打点',
                             '得点',
                             '盗塁',
                             '出塁率',
                             '打率',
                             'OPS',
                             'NOI');
        $this->align = array('名前' => 'left');
        $this->url = $link;
        $this->gray = array('出塁率', '打率', '長打率', 'OPS', 'NOI');
        $this->help = array('OPS' => 'http://ja.wikipedia.org/wiki/OPS_%28%E9%87%8E%E7%90%83%29',
                            'NOI' => 'http://ja.wikipedia.org/wiki/NOI_%28%E9%87%8E%E7%90%83%29');
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
