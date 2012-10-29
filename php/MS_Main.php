<?php

/**
 * Main class
 */

require_once "version.php";
require_once "MS_Main_Menu.php";

// Modules
require_once "MS_Module_Base.php";
require_once "MS_Module_Top.php";
require_once "MS_Module_Games.php";
require_once "MS_Module_Teams.php";
require_once "MS_Module_Game.php";
require_once "MS_Module_Matches.php";
require_once "MS_Module_Members.php";
require_once "MS_Module_Bat_Table.php";
require_once "MS_Module_Bat_Table_2.php";
require_once "MS_Module_Bat_Rank_Ave.php";
require_once "MS_Module_Bat_Rank_RBI.php";
require_once "MS_Module_Bat_Rank_HR.php";
require_once "MS_Module_Bat_Rank_Steal.php";
require_once "MS_Module_Individual_Bat.php";
require_once "MS_Module_Titles.php";
require_once "MS_Module_Bat_Coming_Soon.php";

require_once "comment.php";

class MS_Main
{
    private $mMainMenu;
    private $mHome;
    private $mHomeLink;
    public $mYear; // filter

    public function __construct($top)
    {
        MS_Module_Base::$top = $top;

        $this->mHome = MS_Module_Top::instance();
        $this->mHomeLink = $this->mHome->link();

        // Filter
        $this->mYear = MS_DB::instance()->getCurrentYear();

        // Creating Main Menu
        $this->mMainMenu = new MS_Main_Menu();
        $group = new MS_Module_Group('Games');
        $group->add(MS_Module_Games::instance());
        $group->add(MS_Module_Teams::instance());
        $group->add(MS_Module_Game::instance());
        $this->mMainMenu->add($group);
        $group = new MS_Module_Group('Battings');
        $group->add(MS_Module_Bat_Table::instance());
        $group->add(MS_Module_Bat_Table_2::instance());
        $group->add(MS_Module_Bat_Rank_Ave::instance());
        $group->add(MS_Module_Bat_Rank_RBI::instance());
        $group->add(MS_Module_Bat_Rank_HR::instance());
        $group->add(MS_Module_Bat_Rank_Steal::instance());
        $group->add(MS_Module_Individual_Bat::instance());
        $group->add(MS_Module_Bat_Coming_Soon::instance());
        $this->mMainMenu->add($group);
        $group = new MS_Module_Group('History');
        $group->add(MS_Module_Matches::instance());
        $group->add(MS_Module_Members::instance());
        $group->add(MS_Module_Titles::instance());
        $this->mMainMenu->add($group);
    }

    public function html()
    {
        $request = NULL;
        if (isset($_GET)) {
            $request = $_GET;
            extract($_GET);
            if (isset($year)) {
                $this->mYear = $year;
            }
        }
        MS_DB::instance()->setFilterByYear($this->mYear);

        // post
        if (isset($_POST)) {
            extract($_POST);
            // comment for a game
            if (isset($gameComment)) {
                $commentDb = new MS_Comment;
                $commentDb->post($gameId, $name, $comment);
                $commentDb = NULL;
            }
        }

        $update = $this->lastUpdateHtml();
        $title = $this->titleHtml();
        $main = $this->mMainMenu->mainHtml($request);
        if (!isset($main)) {
            $main = $this->mHome->html();
        }

        // create main menu. must be called after mainHtml
        $menu = $this->mMainMenu->menuHtml();

        $out = "<div id=topBar>
                  $update
                  $title
                </div>
                <div id=mainMenu>
                  $menu
                </div>
                <div id=mainView>
                  $main
                </div>";
        return $out;
    }

    private function titleHtml()
    {
        $out = "<div id=title><h1><a href={$this->mHomeLink}&session=clear>
                MonStars 秘密基地
                </a></h1></div>";
        return $out;
    }

    private function lastUpdateHtml()
    {
        $date = getLastDate();
        $out = "<div id=version>
                LastUpdate: $date
                </div>";
        return $out;
    }
}

/*
 * Local variables:
 * tab-width: 4
 * End:
 */

?>
