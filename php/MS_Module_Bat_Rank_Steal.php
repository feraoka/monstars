<?php

/**
 * 盗塁ランキング
 */

require_once "MS_Module_Bat_Rank_Base.php";

final class MS_Module_Bat_Rank_Steal extends MS_Module_Bat_Rank_Base
{
    public function __construct()
    {
        parent::__construct();
        $this->name = "盗塁";
        $this->title = "盗塁";
        $this->type = "steal";
        $this->icon = "images/asimo.png";
        $this->unit = "回";
    }

    public static function instance()
    {
        static $inst = null;
        if (!isset($inst)) {
            $inst = new MS_Module_Bat_Rank_Steal;
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
