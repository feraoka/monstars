<?php

/**
 * 打点ランキング
 */

require_once "MS_Module_Bat_Rank_Base.php";

final class MS_Module_Bat_Rank_RBI extends MS_Module_Bat_Rank_Base
{
    public function __construct()
    {
        parent::__construct();
        $this->name = "打点";
        $this->title = "打点";
        $this->type = "rbi";
        $this->icon = "images/bat.png";
        $this->unit = "点";
    }

    public static function instance()
    {
        static $inst = null;
        if (!isset($inst)) {
            $inst = new MS_Module_Bat_Rank_RBI;
        }
        return $inst;
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
