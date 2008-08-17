<?php

/**
 * 打率ランキング
 */

require_once "MS_Module_Bat_Rank_Base.php";

final class MS_Module_Bat_Rank_Ave extends MS_Module_Bat_Rank_Base
{
    public function __construct()
    {
        parent::__construct();
        $this->name = "打率";
        $this->title = "打率";
        $this->type = "average";
        $this->icon = "images/1ro.png";
        $this->unit = "";
    }

    public static function instance()
    {
        static $inst = null;
        if (!isset($inst)) {
            $inst = new MS_Module_Bat_Rank_Ave;
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
