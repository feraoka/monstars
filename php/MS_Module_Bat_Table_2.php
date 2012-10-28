<?php

/**
 * Batting Table Module
 */

require_once "MS_Module_Base.php";
require_once "MS_Bat_Table_2.php";

final class MS_Module_Bat_Table_2 extends MS_Module_Base
{
    private $mTable;

    protected function __construct()
    {
        $this->name = "打撃成績表 2";
        $this->title = $this->name;
        $this->mTable = new MS_Bat_Table_2($this->linkToSelf());
    }

    public static function instance()
    {
        static $inst = null;
        if (!isset($inst)) {
            $inst = new MS_Module_Bat_Table_2;
        }
        return $inst;
    }

    public function link()
    {
        return $this->linkToSelf();
    }

    public function html()
    {
        $menu = MS_DB::instance()->quickJumpHtml($this->linkToSelf());
        $out = "<div id=contentMenu>$menu</div>";
        $out .= "<div id=content>";
        $out .= "<h3>$this->title</h3>";
        $this->mTable->refresh();
        $out .= $this->mTable->html();
        $out .= "</div>";
        return $out;
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
