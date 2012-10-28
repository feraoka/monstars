<?php

/**
 * Teams Module
 */

require_once "MS_Module_Base.php";
require_once "MS_DB.php";
require_once "MS_Team_Table.php";
require_once "MS_Module_Game.php";
require_once "MS_Module_Games.php";

final class MS_Module_Teams extends MS_Module_Base
{
    private $mTable;

    protected function __construct()
    {
        $this->name = "対戦相手別";
        $this->title = "対戦成績";
        $this->mTable = new MS_Team_Table($this->linkToSelf());
    }

    public static function instance()
    {
        static $inst = null;
        if (!isset($inst)) {
            $inst = new MS_Module_Teams;
        }
        return $inst;
    }

    public function link()
    {
        return $this->linkToSelf();
    }

    public function html()
    {
        $this->mTable->refresh(MS_Module_Game::instance(), MS_Module_Games::instance());
        $out = "<h3>$this->title</h3>";
        $out .= $this->mTable->html();
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
