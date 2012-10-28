<?php

/**
 * Individual Batting Module
 */

require_once "MS_Module_Base.php";
require_once "MS_DB.php";

final class MS_Module_Individual_Bat extends MS_Module_Base
{
    protected function __construct()
    {
        $this->name = "おっ！パイチャート";
        $this->title = $this->name;
    }

    public static function instance()
    {
        static $inst = null;
        if (!isset($inst)) {
            $inst = new MS_Module_Individual_Bat;
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

//         $db = MS_DB::instance();
//         $sql = "SELECT memberId FROM batting_tbl WHERE {$db->mFilter} GROUP BY memberId;";
//         $ans = $db->query($sql);
//         if ($ans == "") {
//             return "No record";
//         }
//         while ($player = $ans->fetch()) {
//             $out .= MS_DB::getNameByMemberId($player['memberId']);
//         }

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
