<?php

/**
 * 試合結果クラス
 */

require_once "gameDb.php";
require_once "MS_Module_Base.php";

final class MS_Module_Game extends MS_Module_Base
{
    public $game;

    protected function __construct()
    {
        $this->visible = false;
        $this->name = "試合結果";
        $this->title = $this->name;
        //$this->game = new gameDb($gameId);
    }

    public static function instance()
    {
        static $inst = null;
        if (!isset($inst)) {
            $inst = new MS_Module_Game;
        }
        return $inst;
    }

    public function link()
    {
        return $this->linkToSelf();
    }

    public function linkToGame($gameId)
    {
        $out = $this->linkToSelf();
        $out .= "&gameId=$gameId";
        return $out;
    }

    public function html()
    {
        if (isset($_GET)) {
            extract($_GET);
        }
        if (isset($gameId)) {
            $game = new gameDb($gameId);
            $prev = $this->prevGame($gameId);
            $prevLink = "";
            if (isset($prev)) {
                $prevLink = "<a href={$this->linkToGame($prev)}><</a>";
            }
            $next = $this->nextGame($gameId);
            $nextLink = "";
            if (isset($next)) {
                $nextLink = "<a href={$this->linkToGame($next)}>></a>";
            }
            $out = "<div id=contentMenu><h4>$prevLink $nextLink</h4></div>";
            $out .= "<div id=content>{$game->toHtml()}</div>";
            return $out;
        } else {
            return "No game specified";
        }
    }

    private function prevGame($currentGameId)
    {
        $sql = "SELECT gameId FROM game_tbl
                WHERE gameId < $currentGameId
                ORDER BY gameId DESC;";
        $ans = MS_DB::instance()->query($sql)->fetch();
        return $ans['gameId'];
    }

    private function nextGame($currentGameId)
    {
        $sql = "SELECT gameId FROM game_tbl
                WHERE gameId > $currentGameId
                ORDER BY gameId;";
        $ans = MS_DB::instance()->query($sql)->fetch();
        return $ans['gameId'];
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
