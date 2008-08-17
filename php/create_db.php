<?php

/**
 * $Id: create_db.php 123 2007-07-26 15:07:55Z dai $
 * xmlファイルからデータベースを構築する
 */

//define('DEFAULT_DATA_PATH', "../data");
define('DEFAULT_DATA_PATH', "/dev/shm");

require_once "gameXml.php";

final class monstarsdb {
    private $dbh;
    private $dbFile = "monstars.db";
    private $member_tbl = "member_tbl";
    private $game_tbl = "game_tbl";
    private $batting_tbl = "batting_tbl";
    private $bat_tbl = "bat_tbl";
  
    function __construct($dataPath = DEFAULT_DATA_PATH) {
        $file = $dataPath."/".$this->dbFile;
        try {
            $this->dbh = new PDO("sqlite:$file", '', '');
        } catch (Exception $e) {
            die ($e->getMessage());
        }
 
        if (!file_exists($file)) {
            die("Permission Denied!");
        }

        $q = $this->dbh->query("PRAGMA table_info(" . $this->member_tbl . ")");
        if ($q->rowCount() == 0) {
            $sql = "
                 CREATE TABLE $this->member_tbl ( -- 選手テーブル
                 memberId INTEGER PRIMARY KEY,    -- 選手ID
                 name VARCHAR NOT NULL UNIQUE,    -- 記録
                 jName VARCHAR NOT NULL,          -- 日本語
                 eName VARCHAR,                   -- アルファベット
                 title VARCHAR                    -- 肩書き
                 );
            ";
            $this->dbh->query($sql);
        }

        $q = $this->dbh->query("PRAGMA table_info(" . $this->game_tbl . ")");
        if ($q->rowCount() == 0) {
            $sql = "
                CREATE TABLE $this->game_tbl (    -- 試合テーブル
                gameId INTEGER PRIMART KEY,       -- 試合ID
                season INTEGER,                   -- シーズン
                date DATE,                        -- 年月日
                time TIME,                        -- 試合開始時刻
                location VARCHAR,                 -- グランド
                team VARCHAR NOT NULL,            -- 相手チーム名
                type VARCHAR,                     -- 試合タイプ
                pointGot INTEGER,                 -- 得点
                pointLost INTEGER,                -- 失点
                result INTEGER,                   -- 試合結果 1:勝ち -1:負け 0:分け
                scoreboard VARCHAR,               -- スコアボード
                comment VARCHAR                   -- コメント
                );
            ";
            $this->dbh->query($sql);
        }

        $q = $this->dbh->query("PRAGMA table_info(" . $this->batting_tbl . ")");
        if ($q->rowCount() == 0) {
            $sql = "
                CREATE TABLE $this->batting_tbl (    -- 打者テーブル
                batterId INTEGER PRIMARY KEY,        -- 打者ID
                memberId INTEGER,                    -- 選手ID
                gameId INTEGER,                      -- 試合ID
                bOrder INTEGER,                      -- 打順
                position VARCHAR                     -- 守備位置
                );
            ";
            $this->dbh->query($sql);
        }

        $q = $this->dbh->query("PRAGMA table_info(" . $this->bat_tbl . ")");
        if ($q->rowCount() == 0) {
            $sql = "
                CREATE TABLE $this->bat_tbl (        -- 打席テーブル
                id INTEGER PRIMARY KEY,              -- 打席ID
                batterId INTEGER,                    -- 打者ID
                gameId INTEGER,                      -- 高速化のために追加
                memberId INTEGER,                    -- 高速化のために追加
                raw VARCHAR,                         -- rawデータ
                inning INTEGER,                      -- イニング
                hit INTEGER,                         -- 安打 [1-4]
                hit1 INTEGER,                        -- 単打
                hit2 INTEGER,                        -- 二塁打
                hit3 INTEGER,                        -- 三塁打
                hr INTEGER,                          -- 本塁打
                rbi INTEGER,                         -- 打点 [1-4]
                rrun INTEGER,                        -- 得点 [01]
                steal INTEGER,                       -- 盗塁 [1-3]
                direction INTEGER,                   -- 打球方向 [1-9]
                ball CAHR,                           -- 打球 [FGLO]
                sout INTEGER,                        -- 三振
                fball INTEGER,                       -- 四死球
                dball INTEGER,                       -- 死球
                run INTEGER,                         -- 出塁
                atBat INTEGER                        -- 打数 [01]
                );
            ";
            $this->dbh->query($sql);
        }
    }

    function addMember($file = "../data/member.txt") {
        $query = "INSERT INTO $this->member_tbl
                (name, jName, eName, title)
                VALUES
                (:name, :jName, :eName, :title)
             ";
        $stmt = $this->dbh->prepare($query); 
        $stmt->bindParam(':name', $name, PDO::PARAM_STR, 16);
        $stmt->bindParam(':jName', $jName, PDO::PARAM_STR, 32);
        $stmt->bindParam(':eName', $eName, PDO::PARAM_STR, 32);
        $stmt->bindParam(':title', $title, PDO::PARAM_STR, 32);

        $fp = fopen($file, "r");
        while (!feof($fp)) {
            $line = fgets($fp);
            if (!ereg("^#", $line)) {
                $line = preg_replace("/[\r\n]/", "", $line);
                $a = explode(",", $line);
                if ($a[0] == NULL) {
                    continue;
                }
                $name = $a[0];
                $jName = $a[1];
                $eName = $a[2];
                $title = "";
                if (isset($a[3])) $title = $a[3];
                $stmt->execute();
            }
        }
    }

    function addXmlData($dataFilePath = DEFAULT_DATA_PATH) {
        $xmlDataFilePath = $dataFilePath."/xml";
        $query = "INSERT INTO $this->game_tbl
              (gameId, season, date, time, location, team , type,
               pointGot, pointLost, result, scoreboard, comment)
              VALUES
              (:gameId, :season, :date, :time, :location, :team, :type,
               :pointGot, :pointLost, :result, :scoreboard, :comment);";
        $stmt = $this->dbh->prepare($query); 

        $stmt->bindParam(':gameId', $gameId, PDO::PARAM_INT);
        $stmt->bindParam(':season', $season, PDO::PARAM_INT);
        $stmt->bindParam(':date', $date, PDO::PARAM_STR, 16);
        $stmt->bindParam(':time', $time, PDO::PARAM_STR, 16);
        $stmt->bindParam(':location', $location, PDO::PARAM_STR, 32);
        $stmt->bindParam(':team', $team, PDO::PARAM_STR, 32);
        $stmt->bindParam(':type', $type, PDO::PARAM_STR, 32);
        $stmt->bindParam(':pointGot', $pointGot, PDO::PARAM_INT);
        $stmt->bindParam(':pointLost', $pointLost, PDO::PARAM_INT);
        $stmt->bindParam(':result', $result, PDO::PARAM_INT);
        $stmt->bindParam(':scoreboard', $scoreboard, PDO::PARAM_STR, 64);
        $stmt->bindParam(':comment', $comment);

        $query_batting = "INSERT INTO $this->batting_tbl
                      (batterId, memberId, gameId, bOrder, position)
                      VALUES
                      (:batterId, :memberId, :gameId, :bOrder, :position);";
        $stmt_batting = $this->dbh->prepare($query_batting);
        $stmt_batting->bindParam(':batterId', $batterId, PDO::PARAM_INT);
        $stmt_batting->bindParam(':memberId', $memberId, PDO::PARAM_INT);
        $stmt_batting->bindParam(':gameId', $gameId, PDO::PARAM_INT);
        $stmt_batting->bindParam(':bOrder', $bOrder, PDO::PARAM_INT);
        $stmt_batting->bindParam(':position', $position, PDO::PARAM_STR);

        $query_bat = "INSERT INTO $this->bat_tbl
                  (batterId, gameId, memberId, raw,
                   inning, hit, hit1,
                   hit2, hit3, hr,
                   rbi, rrun, steal,
                   direction, ball, sout,
                   fball, dball, run, atBat)
                  VALUES
                  (:batterId, :gameId, :memberId, :raw,
                   :inning, :hit, :hit1,
                   :hit2, :hit3, :hr,
                   :rbi, :rrun, :steal,
                   :direction, :ball, :sout,
                   :fball, :dball, :run, :atBat);";
        $stmt_bat = $this->dbh->prepare($query_bat);
        $stmt_bat->bindParam(':batterId', $batterId, PDO::PARAM_INT);
        $stmt_bat->bindParam(':gameId', $gameId, PDO::PARAM_INT);
        $stmt_bat->bindParam(':memberId', $memberId, PDO::PARAM_INT);
        $stmt_bat->bindParam(':raw', $raw, PDO::PARAM_STR, 16);
        $stmt_bat->bindParam(':inning', $inning, PDO::PARAM_INT);
        $stmt_bat->bindParam(':hit', $hit, PDO::PARAM_INT);
        $stmt_bat->bindParam(':hit1', $hit1, PDO::PARAM_INT);
        $stmt_bat->bindParam(':hit2', $hit2, PDO::PARAM_INT);
        $stmt_bat->bindParam(':hit3', $hit3, PDO::PARAM_INT);
        $stmt_bat->bindParam(':hr', $hr, PDO::PARAM_INT);
        $stmt_bat->bindParam(':rbi', $rbi, PDO::PARAM_INT);
        $stmt_bat->bindParam(':rrun', $rrun, PDO::PARAM_INT);
        $stmt_bat->bindParam(':steal', $steal, PDO::PARAM_INT);
        $stmt_bat->bindParam(':direction', $direction, PDO::PARAM_INT);
        $stmt_bat->bindParam(':ball', $ball, PDO::PARAM_STR);
        $stmt_bat->bindParam(':sout', $sout, PDO::PARAM_INT);
        $stmt_bat->bindParam(':fball', $fball, PDO::PARAM_INT);
        $stmt_bat->bindParam(':dball', $dball, PDO::PARAM_INT);
        $stmt_bat->bindParam(':run', $run, PDO::PARAM_INT);
        $stmt_bat->bindParam(':atBat', $atBat, PDO::PARAM_INT);

        // Reading XML file
        $dh = opendir($xmlDataFilePath);
        $dataFiles = array();
        while ($file = readdir($dh)) {
            if (ereg("\.xml$", $file)) {
                array_push($dataFiles, $file);
            }
        }
        asort($dataFiles);

        // Initializing unique ids
        $batterId = 0;

        foreach ($dataFiles as $file) {
            print "Creating DB ($file)\n";
            $dom = new domDocument();
            $dom->load("$xmlDataFilePath/$file");
            $root = $dom->documentElement;
            foreach ($root->childNodes as $game) {
                if ($game->nodeType == XML_ELEMENT_NODE
                    && $game->nodeName == "game") {
                    $g = new gameXml($game);

                    $gameId = $g->gameId;
                    print "- $gameId\n";
                    $season = $g->season;
                    $date = $g->date;
                    $time = $g->time;
                    $location = $g->location;
                    $team = $g->team;
                    $type = $g->type;
                    if ($g->scoreboard != NULL) {
                        $pointGot = $g->scoreboard->pointGot;
                        $pointLost = $g->scoreboard->pointLost;
                        $scoreboard = $g->scoreboard->toString();
                        if ($type == "紅白戦") {
                            $result = 0;
                        } else if ($pointGot > $pointLost) {
                            $result = 1;
                        } else if ($pointGot < $pointLost) {
                            $result = -1;
                        } else {
                            $result = 0;
                        }
                    } else {
                        $pointGot = NULL;
                        $pointLost = NULL;
                        $scoreboard = NULL;
                        $result = NULL;
                    }
                    $comment = $g->comment;
                    $stmt->execute(); // Adding a game to the game table

                    foreach ($g->battings as $batter) {
                        $memberId = $this->getMemberId($batter->name);
                        $bOrder = $batter->order;
                        $position = $batter->position;
                        $stmt_batting->execute(); // Adding a player to the batting table

                        foreach ($batter->bats as $bat) {
                            $raw = $bat->raw;
                            $inning = $bat->inning;
                            $hit = $bat->hit;
                            $hit1 = $bat->hit1;
                            $hit2 = $bat->hit2;
                            $hit3 = $bat->hit3;
                            $hr = $bat->hr;
                            $rbi = $bat->rbi;
                            $rrun = $bat->rrun;
                            $steal = $bat->steal;
                            $direction = $bat->direction;
                            $ball = $bat->ball;
                            $sout = $bat->sout;
                            $fball = $bat->fball;
                            $dball = $bat->dball;
                            $run = $bat->run;
                            $atBat = $bat->atBat;
                            $stmt_bat->execute(); // Adding a bat to the bat table
                        }
                        $batterId ++;
                    }
                }
            }
        }
    }

    function query($str) {
        return $this->dbh->query($str);
    }

    function getMemberId($name) {
        $sql = "SELECT * FROM $this->member_tbl WHERE name == '$name';";
        foreach ($this->dbh->query($sql) as $row) {
            $ret = $row['memberId'];
        }
        if (!isset($ret)) {
            print "Warning: Unkown name [$name]\n";
        }
        return $ret;
    }

}

print "Processing... It may take few minutes\n";
$db = new monstarsdb();
$db->addMember();
$db->addXmlData();
print "Completed\n";

/*
 * Local variables:
 * tab-width: 4
 * c-basic-offset: 4
 * c-hanging-comment-ender-p: nil
 * End:
 */

?>
