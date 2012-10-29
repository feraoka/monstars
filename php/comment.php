<?php

/**
 * $Id: comment.php 63 2007-04-29 05:51:57Z dai $
 */

/**
 * 試合結果へのコメント
 */

require_once 'MS_DB_Base.php';

/** データベース設置パス */
define('DEFAULT_DATA_PATH', './var/comment.db');

class MS_Comment extends MS_DB_Base {
    /**
     * constructor
     * @param dataPath データベースのパス
     */
    function __construct($filePath = DEFAULT_DATA_PATH) {
        parent::__construct($filePath);
    }
 
    function init() {
        $q = $this->query("PRAGMA table_info(comment_tbl)");
        if ($q->rowCount() == 0) {
            $sql = "CREATE TABLE comment_tbl (
                    id INTEGER PRIMARY KEY,
                    date DATE,
                    time TIME,
                    ipAddr VARCHAR,   -- ip address
                    browser VARCHAR,  -- browser type
                    hostName VARCHAR, -- host name
                    gameId INT,
                    name VARCHAR,    -- お名前
                    comment VARCHAR, -- コメント
                    checked INT
              );";
            $this->query($sql);
        }
        chmod($this->file, 0666);
    }

    function post($gameId, $name, $comment) {
        $now = getdate();
        extract($now);
        $d = "$year/$mon/$mday";
        $t = "$hours:$minutes";
        $ipAddr = getenv("REMOTE_ADDR");
        $browser = getenv("HTTP_USER_AGENT");
        $hostName = getenv("REMOTE_HOST");
        $comment = nl2br($comment);
        $striped_comment = strip_tags($comment, "<br><b><i><u>");
        $checked = 0; //unchecked
        $sql = "INSERT INTO comment_tbl
                (date, time, ipAddr, browser, hostName,
                 gameId, name, comment, checked)
                VALUES (\"$d\", \"$t\", \"$ipAddr\",
                        \"$browser\", \"$hostName\",
                        $gameId, \"$name\", \"$striped_comment\",
                        $checked);";
        $ans = $this->query($sql);
    }

    function getAll() {
        $sql = "SELECT * FROM comment_tbl;";
        $ans = $this->query($sql);
        if ($ans != "") {
            // no record
            $ans = $ans->fetchAll();
        }
        return $ans;
    }

    function getComment($gameId) {
        $sql = "SELECT *
                FROM comment_tbl
                WHERE gameId = $gameId;";
        $ans = $this->query($sql);
        if ($ans != "") {
            // no record
            $ans = $ans->fetchAll();
        }
        return $ans;
    }
}

/*
 * Local variables:
 * tab-width: 4
 * End:
 */

?>
