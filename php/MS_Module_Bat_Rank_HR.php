<?php

/**
 * ホームランランキング
 */

require_once "MS_Module_Bat_Rank_Base.php";

final class MS_Module_Bat_Rank_HR extends MS_Module_Bat_Rank_Base
{
    public function __construct()
    {
        parent::__construct();
        $this->name = "本塁打";
        $this->title = "本塁打";
        $this->type = "hr";
        $this->icon = "images/oh.png";
        $this->unit = "本";
    }

    public static function instance()
    {
        static $inst = null;
        if (!isset($inst)) {
            $inst = new MS_Module_Bat_Rank_HR;
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
