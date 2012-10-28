<?php

require_once "db.php";
require_once "gameDb.php";
require_once "todo.php";
require_once "filter.php";
require_once "table.php";
require_once "version.php";
require_once "comment.php";
require_once "MS_Module_Base.php";
require_once "MS_Module_Group.php";
require_once "MS_Module_Matches.php";
require_once "MS_Main_Menu.php";

define ('MEMBER_FILE', "data/member.txt");

function nGames($a, $b) {
    if ($a['nGames'] < $b['nGames']) return 1;
    else if ($a['nGames'] > $b['nGames']) return -1;
    else return 0;
}

function nGamesReverse($a, $b) {
    if ($a['nGames'] > $b['nGames']) return 1;
    else if ($a['nGames'] < $b['nGames']) return -1;
    else return 0;
}

final class monstars {

    private $top;
    private $view;
    private $game;
    public $memberId;

    private $jName = array();

    // filters
    public $filter;

    // sorting
    private $sort;
    private $reverse = false;

    // Menu
    private $mMainMenu;

    function __construct($top) {
        $this->top = $top;
        MS_Module_Base::$top = $top;

        // Filter
        $this->filter = new filter();

        // Creating name table
        $fp = fopen(MEMBER_FILE, "r");
        $n = 1;
        while (!feof($fp)) {
            $line = fgets($fp);
            if (!ereg("^#", $line)) {
                $line = preg_replace("/[\r\n]/", "", $line);
                $a = explode(",", $line);
                if ($a[0] == NULL) {
                    continue;
                }
                $this->jName[$a[0]] = $a[1]; // By name
                $this->jName[$n++] = $a[1]; // By ID
            }
        }
        fclose($fp);

        // Creating Main Menu
        $this->mMainMenu = new MS_Main_Menu();
        $group = new MS_Module_Group('Games');
        $group->add(new MS_Module_Matches);
        $this->mMainMenu->add($group);
    }

    function post($post) {
        extract($post);
        if (isset($view)) {
            $this->view = $view;
        }
        $this->filter->post($post);

        if (isset($gameComment)) {
            $commentDb = new MS_Comment;
            $commentDb->post($gameId, $name, $comment);
            $commentDb = NULL;
        }
    }

    function get($get) {
        extract($get);
        if (isset($sort)) {
            if ($this->sort == $sort) {
                if ($this->reverse) {
                    $this->reverse = false;
                } else {
                    $this->reverse = true;
                }
            } else {
                $this->reverse = false;
            }
            $this->sort = $sort;
        } else {
            $this->sort = NULL;
        }
        if (isset($view)) {
            $this->view = $view;
            if ($view == "individualBatting" and isset($memberId)) {
                $this->memberId = $memberId;
            }
        }
        if (isset($game)) {
            $this->game = $game;
        }
        $this->filter->get($get);
    }

    function titleHtml() {
        return "<h1>MonStars秘密基地</h1>";
    }

    // メインページ
    function homeHtml() {
        $out = "";
        // 最近の試合結果
        $out .= "<h3 class=main>Recent games</h3>";
        $daysBefore = 14;
        $d = date("Y/m/d", strtotime("-$daysBefore day"));
        $sql = "SELECT date, gameId
            FROM game_tbl
            WHERE \"$d\" < date 
            ORDER BY date DESC;";
        $ans = query($sql);
        $games = $ans->fetchAll();
        if ($games) {
            foreach ($games as $game) {
                $g = new gameDb($game['gameId']);
                //$out .= "<h4 class=main>{$game['date']}</h4>";
                $out .= $g->toHtml($this->jName);
                $g = NULL;
            }
        } else {
            $out .= "<p>最近{$daysBefore}日間試合がありません。</p>";
        }
        // もうすぐ記録
        // comming soon

        return $out;
    }

    function filtersHtml() {
        $target = "{$this->top}?view={$this->view}";
        return $this->filter->formHtml($target);
    }

    function gamesHtml($cond = NULL) {
        // SQL for statistics
        if ($cond == NULL) {
            $cond = $this->filter->sqlCond();
        }
        $sql = "SELECT *
            FROM game_tbl
            WHERE $cond
            ORDER BY date;";
        $ans = query($sql);
        if ($ans == "") {
            return "No data";
        }
        $games = $ans->fetchAll();
        $statGame = 0;
        $statWon = 0;
        $statLost = 0;
        $statDrew = 0;
        $statConWons = 0;
        $statConLosts = 0;
        $statPointGot = 0;
        $statPointLost = 0;
        $statPointGotAtWon = 0;
        $statPointLostAtWon = 0;
        $statPointGotAtLost = 0;
        $statPointLostAtLost = 0;
        $statAveWin = 0;

        $conWons = 0;
        $conLosts = 0;
        foreach ($games as $game) {
            if ($game['type'] == "紅白戦" or !isset($game['pointGot'])) {
                // no game
            } else {
                $statGame++;
                if ($game['pointGot'] > $game['pointLost']) {
                    $statWon++;
                    $conWons++;
                    $conLosts = 0;
                    if ($conWons > $statConWons) $statConWons = $conWons;
                    $statPointGotAtWon += $game['pointGot'];
                    $statPointLostAtWon += $game['pointLost'];
                } else if ($game['pointGot'] < $game['pointLost']) {
                    $statLost++;
                    $conWons = 0;
                    $conLosts ++;
                    if ($conLosts > $statConLosts) $statConLosts = $conLosts;
                    $statPointGotAtLost += $game['pointGot'];
                    $statPointLostAtLost += $game['pointLost'];
                } else {
                    $statDrew++;
                }
                $statPointGot += $game['pointGot'];
                $statPointLost += $game['pointLost'];
            }
        }
        if ($statWon + $statLost > 0) {
            $statAveWin = sprintf("%0.3f", $statWon / ($statWon + $statLost));
        } else {
            $statAveWin = "-";
        }

        if ($statGame > 0) {
            $statAvePointGot = sprintf("%0.1f", $statPointGot / $statGame);
            $statAvePointLost = sprintf("%0.1f", $statPointLost / $statGame);
        } else {
            $statAvePointGot = "-";
            $statAvePointLost = "-";
        }

        if ($statWon > 0) {
            $statAvePointGotAtWon = sprintf("%0.1f", $statPointGotAtWon / $statWon);
            $statAvePointLostAtWon = sprintf("%0.1f", $statPointLostAtWon / $statWon);
        } else {
            $statAvePointGotAtWon = "-";
            $statAvePointLostAtWon = "-";
        }

        if ($statLost > 0) {
            $statAvePointGotAtLost = sprintf("%0.1f", $statPointGotAtLost / $statLost);
            $statAvePointLostAtLost = sprintf("%0.1f", $statPointLostAtLost / $statLost);
        } else {
            $statAvePointGotAtLost = "-";
            $statAvePointLostAtLost = "-";
        }

        // SQL for table
        $sql = "SELECT * FROM game_tbl
                WHERE $cond";
        if ($this->sort == "date"
            or $this->sort == "team"
            or $this->sort == "result"
            or $this->sort == "pointGot"
            or $this->sort == "pointLost"
            or $this->sort == "type") {
            $sql .= " ORDER BY {$this->sort}";
            if ($this->reverse) {
                $sql .= " DESC";
            }
        }
        $sql .= ";";
        $games = query($sql)->fetchAll();

        $out = "<h3 class=main>Summary</h3>
    <p>
    <em>$statGame</em> 戦 <em>$statWon</em> 勝 <em>$statLost</em> 敗 <em>$statDrew</em> 分
    </p>
    <h3 class=main>Statistics</h3>
    <p>
    勝率 <em>$statAveWin</em> <br>
    総得点 <em>$statPointGot</em> / 総失点 <em>$statPointLost</em> <br>
    平均得点 <em>$statAvePointGot</em> / 平均失点 <em>$statAvePointLost</em> <br>
    連勝記録 <em>$statConWons</em> / 連敗記録 <em>$statConLosts</em> <br>
    勝ち試合 平均得点 <em>$statAvePointGotAtWon</em> / 平均失点 <em>$statAvePointLostAtWon</em> <br>
    負け試合 平均得点 <em>$statAvePointGotAtLost</em> / 平均失点 <em>$statAvePointLostAtLost</em> <br>
    </p>
    <h3 class=main>Games</h3>
    <p>
      <table>
      <tr class=hdr>
      <th><a href={$this->top}?sort=date>年月日</a></th>
      <th><a href={$this->top}?sort=team>対戦相手</a></th>
      <th><a href={$this->top}?sort=result>結果</a></th>
      <th><a href={$this->top}?sort=pointGot>得点</a></th>
      <th><a href={$this->top}?sort=pointLost>失点</a></th>
      <th><a href={$this->top}?sort=type>備考</a></th>
      </tr>";

        $i = 0;
        $bgcolor = array("row1", "row2");
        foreach ($games as $game) {
            $bg = $bgcolor[$i++ & 1];
            if ($game['type'] == "紅白戦" or !isset($game['pointGot'])) {
                $result = "-";
            } else if ($game['pointGot'] > $game['pointLost']) {
                $result = "<img src=\"images/win.png\">";
            } else if ($game['pointGot'] < $game['pointLost']) {
                $result = "<img src=\"images/lose.png\">";
            } else {
                $result = "<img src=\"images/draw.png\">";
            }
            $teamName = rawurlencode($game['team']);
            $out .= "<tr class=$bg>
	    <td align=center>
          <a href={$this->top}?view=game&game={$game['gameId']}> {$game['date']} </a>
        </td>
        <td align=left>
          <a href={$this->top}?view=team&team=$teamName&year=all>
          {$game['team']}
          </a>
        </td>
        <td align=center> {$result} </td>
        <td align=right> {$game['pointGot']} </td>
        <td align=right> {$game['pointLost']} </td>
        <td align=left> {$game['type']} </td>
        </tr>";
        }
        $out .= "</table>
    </p>";
        return $out;
    }

    function gameHtml() {
        // 前の試合
        $sql = "SELECT gameId FROM game_tbl
             WHERE gameId < $this->game
                   and {$this->filter->sqlCond()}
             ORDER BY gameId DESC;";
        $ans = query($sql)->fetch();
        if (isset($ans['gameId'])) {
            $prev_link = "<a href={$this->top}?view=game&game={$ans['gameId']} class=prev>
                    <img src=\"images/left.png\" border=0></a>";
        } else {
            $prev_link = "";
        }

        // 次の試合
        $sql = "SELECT gameId FROM game_tbl
             WHERE gameId > $this->game
                   and {$this->filter->sqlCond()}
             ORDER BY gameId;";
        $ans = query($sql)->fetch();
        if (isset($ans['gameId'])) {
            $next_link = "<a href={$this->top}?view=game&game={$ans['gameId']} class=next>
                    <img src=\"images/right.png\" border=0></a>";
        } else {
            $next_link = "";
        }
        $out = "<div class=navi>$prev_link $next_link</div>";
        $g = new gameDb($this->game);
        $target = "{$this->top}?view={$this->view}";
        $out .= $g->toHtml($this->jName);
        return $out;
    }

    function battingHtml($cond = NULL) {
        if ($cond == NULL) {
            $cond = $this->filter->sqlCond();
        }

        $this->rbiLineChart("average");

        if ($this->sort == "numBats"
            or $this->sort == "numBats"
            or $this->sort == "numAtBats"
            or $this->sort == "numHits"
            or $this->sort == "numHrs"
            or $this->sort == "numBases"
            or $this->sort == "numFballs"
            or $this->sort == "numSouts"
            or $this->sort == "numRbis"
            or $this->sort == "numRruns"
            or $this->sort == "numSteals"
            or $this->sort == "runAverage"
            or $this->sort == "average") {
            $orderBy = $this->sort;
        } else {
            $orderBy = "average";
        }

        if ($this->reverse) {
            $dir = "ASC";
        } else {
            $dir = "DESC";
        }

        $sql = "SELECT memberId,
                   COUNT(*) numBats, ---------------------- 打数
                   SUM(atBat) numAtBats, ------------------ 打席数
                   SUM(hit1 + hit2 + hit3 + hr) numHits, -- 安打数
                   SUM(hr) numHrs, ------------------------ 本塁打
                   SUM(hit) numBases, --------------------- 塁打
                   SUM(steal) numSteals, ------------------ 盗塁
                   SUM(fball) numFballs, ------------------ 四死球
                   SUM(sout) numSouts, -------------------- 三振
                   SUM(rbi) numRbis, ---------------------- 打点
                   SUM(rrun) numRruns, -------------------- 得点
                   SUM(run) numRuns, ---------------------- 出塁
                   CAST(SUM(hit1 + hit2 + hit3 + hr) AS FLOAT) / CAST(SUM(atBat) AS FLOAT) average,
                   CAST(SUM(run) AS FLOAT) / CAST(COUNT(*) AS FLOAT) runAverage
            FROM bat_tbl
            WHERE $cond
            GROUP BY memberId
            ORDER BY $orderBy $dir;";
        $ans = query($sql);
        if ($ans == "") {
            return "No data";
        }
        $rows = $ans->fetchAll();

        // 全試合数
        $sql = "SELECT COUNT(*) numGames
            FROM game_tbl
            WHERE $cond;";
        $game = query($sql)->fetch();

        // 個人別出場試合数
        $sql = "SELECT memberId, COUNT(*) numGames
            FROM batting_tbl
            WHERE $cond
            GROUP BY memberId;";
        $games = query($sql)->fetchAll();

        foreach ($games as $g) {
            $numGames[$g['memberId']] = $g['numGames'];
        }

        $data = array();
        foreach ($rows as $row) {
            $row['nGames'] = $numGames[$row['memberId']];
            array_push($data, $row);
        }

        if ($this->sort == "numGames") {
            if ($this->reverse) {
                usort($data, "nGamesReverse");
            } else {
                usort($data, "nGames");
            }
        }

        // Finding the Tops
        $topN = 10;
        $r = $this->getTheTop("average", $cond, $topN);
        if ($game['numGames'] > 1 and count($r) > 0) {
            $aveGraph = "<div id=frame>
              <img class=chart src=\"php/rbiLineChart.php?target=average\"></div>";
        }
        $aveTopTable = $this->topTableHtml($r, "");
    
        $r = $this->getTheTop("hr", $cond, $topN);
        if ($game['numGames'] > 1 and count($r) > 0) {
            $hrGraph = "<img class=chart src=\"php/rbiLineChart.php?target=hr\">";
        }
        $hrTopTable = $this->topTableHtml($r, "本");

        $r = $this->getTheTop("rbi", $cond, $topN);
        if ($game['numGames'] > 1 and count($r) > 0) {
            $rbiGraph = "<img class=chart src=\"php/rbiLineChart.php?target=rbi\">";
        }
        $rbiTopTable = $this->topTableHtml($r, "点");

        $r = $this->getTheTop("steal", $cond, $topN);
        if ($game['numGames'] > 1 and count($r) > 0) {
            $stealGraph = "<img class=chart src=\"php/rbiLineChart.php?target=steal\">";
        }
        $stealTopTable = $this->topTableHtml($r, "個");
        $out = "<h3 class=main>The Top of</h3>
            <h4 class=bat><img src=\"images/1ro.png\"> 首位打者</h4>
              $aveTopTable
              $aveGraph
            <h4 class=bat><img src=\"images/oh.png\"> ホームラン王</h4>
              $hrTopTable
              $hrGraph
            <h4 class=bat><img src=\"images/bat.png\"> 打点王</h4>
              $rbiTopTable
              $rbiGraph
            <h4 class=bat><img src=\"images/asimo.png\"> 盗塁王</h4>
              $stealTopTable
              $stealGraph
            </p>";

        $out .= "<h3 class=main>Batters</h3>
    <p>
    <table>
    <tr class=hdr>
      <th align=center> 名前 </th>
      <th align=center>
        <a href={$this->top}?sort=numGames> 試合 </a>
      </th>
      <th align=center>
        <a href={$this->top}?sort=numBats> 打席 </a>
      </th>
      <th align=center>
        <a href={$this->top}?sort=numAtBats> 打数 </a>
      </th>
      <th align=center>
        <a href={$this->top}?sort=numHits> 安打 </a>
      </th>
      <th align=center>
        <a href={$this->top}?sort=numHrs> 本塁打 </a>
      </th>
      <th align=center>
        <a href={$this->top}?sort=numBases> 塁打 </a>
      </th>
      <th align=center>
        <a href={$this->top}?sort=numFballs> 四死球 </a>
      </th>
      <th align=center>
        <a href={$this->top}?sort=numSouts> 三振 </a>
      </th>
      <th align=center>
        <a href={$this->top}?sort=numRbis> 打点 </a>
      </th>
      <th align=center>
        <a href={$this->top}?sort=numRruns> 得点 </a>
      </th>
      <th align=center>
        <a href={$this->top}?sort=numSteals> 盗塁 </a>
      </th>
      <th align=center>
        <a href={$this->top}?sort=runAverage> 出塁率 </a>
      </th>
      <th align=center>
        <a href={$this->top}?sort=average> 打率 </a>
      </th>
    </tr>";

        $bgcolor = array("row1", "row2");
        $i = 0;
        $total['numBats'] = 0;
        $total['numAtBats'] = 0;
        $total['numHits'] = 0;
        $total['numHrs'] = 0;
        $total['numBases'] = 0;
        $total['numFballs'] = 0;
        $total['numSouts'] = 0;
        $total['numRbis'] = 0;
        $total['numRruns'] = 0;
        $total['numSteals'] = 0;
        $total['numRuns'] = 0;

        foreach ($data as $row) {
            $bg = $bgcolor[$i++ & 1];
            extract ($row);
            $total['numBats'] += $numBats;
            $total['numAtBats'] += $numAtBats;
            $total['numHits'] += $numHits;
            $total['numHrs'] += $numHrs;
            $total['numBases'] += $numBases;
            $total['numFballs'] += $numFballs;
            $total['numSouts'] += $numSouts;
            $total['numRbis'] += $numRbis;
            $total['numRruns'] += $numRruns;
            $total['numSteals'] += $numSteals;
            $total['numRuns'] += $numRuns;

            $runAve = sprintf ("%0.3f", $runAverage);
            $ave = sprintf ("%0.3f", $average);
            $runAve = ereg_replace("^0", "", $runAve);
            $ave = ereg_replace("^0", "", $ave);
            if ($nGames * 2 >= $game['numGames']
                or
                ($orderBy != "average" and $orderBy != "runAverage")) {
                $col = "#000000";
            } else {
                $col = "#888888";
            }
            $out .= "<tr class=$bg>
        <td align=center>
          <a href={$this->top}?view=individualBatting&memberId=$memberId>
          {$this->jName[$memberId]}
          </a>
        </td>
        <td align=right><font color=$col>{$nGames}</font></td>
        <td align=right><font color=$col>$numBats</font></td>
        <td align=right><font color=$col>$numAtBats</font></td>
        <td align=right><font color=$col>$numHits</font></td>
        <td align=right><font color=$col>$numHrs</font></td>
        <td align=right><font color=$col>$numBases</font></td>
        <td align=right><font color=$col>$numFballs</font></td>
        <td align=right><font color=$col>$numSouts</font></td>
        <td align=right><font color=$col>$numRbis</font></td>
        <td align=right><font color=$col>$numRruns</font></td>
        <td align=right><font color=$col>$numSteals</font></td>
        <td align=right><font color=$col>$runAve</font></td>
        <td align=right><font color=$col>$ave</font></td>
      </tr>";
        }
    
        $bg = $bgcolor[$i++ & 1];
        $runAveTotal = sprintf("%0.3f", $total['numRuns'] / $total['numBats']);
        $aveTotal = sprintf("%0.3f", $total['numHits'] / $total['numAtBats']);
        $runAveTotal = ereg_replace("^0", "", $runAveTotal);
        $aveTotal = ereg_replace("^0", "", $aveTotal);

        $out .= "<tr class=hdr>
        <td align=center>Total</td>
        <td align=right>{$game['numGames']}</td>
        <td align=right>{$total['numBats']}</td>
        <td align=right>{$total['numAtBats']}</td>
        <td align=right>{$total['numHits']}</td>
        <td align=right>{$total['numHrs']}</td>
        <td align=right>{$total['numBases']}</td>
        <td align=right>{$total['numFballs']}</td>
        <td align=right>{$total['numSouts']}</td>
        <td align=right>{$total['numRbis']}</td>
        <td align=right>{$total['numRruns']}</td>
        <td align=right>{$total['numSteals']}</td>
        <td align=right>$runAveTotal</td>
        <td align=right>$aveTotal</td>
        </tr>
        </table>
        <a href={$this->top}?view=battingMore> more... </a>
      </p>";

        return $out;
    }

    function batting2Html() {
        $cond = $this->filter->sqlCond();

        if ($this->sort == "numBats"
            or $this->sort == "numBats"
            or $this->sort == "numAtBats"
            or $this->sort == "numHit1"
            or $this->sort == "numHit2"
            or $this->sort == "numHit3"
            or $this->sort == "numHrs"
            or $this->sort == "numRruns"
            or $this->sort == "runAverage"
            or $this->sort == "average"
            or $this->sort == "slg"
            or $this->sort == "ops"
            or $this->sort == "noi") {
            $orderBy = $this->sort;
        } else {
            $orderBy = "ops";
        }
        if ($this->reverse) {
            $dir = "ASC";
        } else {
            $dir = "DESC";
        }

        $sql = "SELECT memberId,
              COUNT(*) numBats, ---------------------- 打数
              SUM(atBat) numAtBats, ------------------ 打席数
              SUM(hit1 + hit2 + hit3 + hr) numHits, -- 安打数
              SUM(hit1) numHit1, --------------------- 単打
              SUM(hit2) numHit2, --------------------- 二塁打
              SUM(hit3) numHit3, --------------------- 三塁打
              SUM(hr) numHrs, ------------------------ 本塁打
              SUM(hit) numBases, --------------------- 塁打
              SUM(steal) numSteals, ------------------ 盗塁
              SUM(fball) numFballs, ------------------ 四死球
              SUM(sout) numSouts, -------------------- 三振
              SUM(rbi) numRbis, ---------------------- 打点
              SUM(rrun) numRruns, -------------------- 得点
              SUM(run) numRuns, ---------------------- 出塁
              CAST(SUM(hit) AS FLOAT) / CAST(SUM(atBat) AS FLOAT) slg, -- 長打率
              CAST(SUM(hit1 + hit2 + hit3 + hr) AS FLOAT) / CAST(SUM(atBat) AS FLOAT) average, -- 打率
              CAST(SUM(run) AS FLOAT) / CAST(COUNT(*) AS FLOAT) runAverage, -- 出塁率
              CAST(SUM(run) AS FLOAT) / CAST(COUNT(*) AS FLOAT)
              + CAST(SUM(hit1 + hit2 * 2 + hit3 * 3 + hr * 4) AS FLOAT) / CAST(SUM(atBat) AS FLOAT) ops, -- OPS
              (CAST(SUM(run) AS FLOAT) / CAST(COUNT(*) AS FLOAT)
              + CAST(SUM(hit1 + hit2 * 2 + hit3 * 3 + hr * 4) AS FLOAT) / CAST(SUM(atBat) AS FLOAT) / 3) 
              * 1000 noi -- NOI
            FROM bat_tbl
            WHERE $cond
            GROUP BY memberId
            ORDER BY $orderBy $dir;";
        $ans = query($sql)->fetchAll();

        // 全試合数
        $sql = "SELECT COUNT(*) numGames
            FROM game_tbl
            WHERE $cond;";
        $game = query($sql)->fetch();

        // 個人別出場試合数
        $sql = "SELECT memberId, COUNT(*) numGames
            FROM batting_tbl
            WHERE $cond
            GROUP BY memberId;";
        $games = query($sql)->fetchAll();

        foreach ($games as $g) {
            $numGames[$g['memberId']] = $g['numGames'];
        }

        $data = array();
        foreach ($ans as $row) {
            $row['nGames'] = $numGames[$row['memberId']];
            array_push($data, $row);
        }

        if ($this->sort == "numGames") {
            if ($this->reverse) {
                usort($data, "nGamesReverse");
            } else {
                usort($data, "nGames");
            }
        }

        $out .= "<h3 class=main>Batters</h3>
    <p>
    <table>
    <tr class=hdr>
      <th align=center> 名前 </th>
      <th align=center>
        <a href={$this->top}?view={$this->view}&sort=numGames> 試合 </a>
      </th>
      <th align=center>
        <a href={$this->top}?view={$this->view}&sort=numBats> 打席 </a>
      </th>
      <th align=center>
        <a href={$this->top}?view={$this->view}&sort=numAtBats> 打数 </a>
      </th>
      <th align=center>
        <a href={$this->top}?view={$this->view}&sort=numHit1> 単打 </a>
      </th>
      <th align=center>
        <a href={$this->top}?view={$this->view}&sort=numHit2> 二塁打 </a>
      </th>
      <th align=center>
        <a href={$this->top}?view={$this->view}&sort=numHit3> 三塁打 </a>
      </th>
      <th align=center>
        <a href={$this->top}?view={$this->view}&sort=numHrs> 本塁打 </a>
      </th>
      <th align=center>
        <a href={$this->top}?view={$this->view}&sort=runAverage> 出塁率 </a>
      </th>
      <th align=center>
        <a href={$this->top}?view={$this->view}&sort=average> 打率 </a>
      </th>
      <th align=center>
        <a href={$this->top}?view={$this->view}&sort=slg> 長打率 </a>
      </th>
      <th align=center>
        <a href={$this->top}?view={$this->view}&sort=ops> OPS </a>
        (<a href=http://ja.wikipedia.org/wiki/OPS_%28%E9%87%8E%E7%90%83%29>?</a>)
      </th>
      <th align=center>
        <a href={$this->top}?view={$this->view}&sort=noi> NOI </a>
        (<a href=http://ja.wikipedia.org/wiki/NOI_%28%E9%87%8E%E7%90%83%29>?</a>)
      </th>
    </tr>";

        $bgcolor = array("row1", "row2");
        $i = 0;
        $total['numBats'] = 0;
        $total['numAtBats'] = 0;
        $total['numHits'] = 0;
        $total['numHit1'] = 0;
        $total['numHit2'] = 0;
        $total['numHit3'] = 0;
        $total['numHrs'] = 0;
        $total['numRuns'] = 0;
        $total['numBases'] = 0;

        foreach ($data as $row) {
            $bg = $bgcolor[$i++ & 1];
            extract ($row);
            $total['numBats'] += $numBats;
            $total['numAtBats'] += $numAtBats;
            $total['numHits'] += $numHits;
            $total['numHit1'] += $numHit1;
            $total['numHit2'] += $numHit2;
            $total['numHit3'] += $numHit3;
            $total['numHrs'] += $numHrs;
            $total['numRuns'] += $numRuns;
            $total['numBases'] += $numBases;

            $runAve = sprintf ("%0.3f", $runAverage);
            $runAve = ereg_replace("^0", "", $runAve);
            $ave = sprintf ("%0.3f", $average);
            $ave = ereg_replace("^0", "", $ave);
            $slg = ereg_replace("^0", "", sprintf ("%0.3f", $slg));
            $ops = ereg_replace("^0", "", sprintf ("%0.3f", $ops));
            $noi = sprintf ("%4.0f", $noi);

            if ($nGames * 2 >= $game['numGames']
                or
                ($orderBy != "average"
                 and $orderBy != "runAverage"
                 and $orderBy != "slg"
                 and $orderBy != "ops"
                 and $orderBy != "noi")) {
                $col = "#000000";
            } else {
                $col = "#888888";
            }
            $out .= "<tr class=$bg>
        <td align=center>
          <a href={$this->top}?view=individualBatting&memberId=$memberId>
          {$this->jName[$memberId]}
          </a>
        </td>
        <td align=right><font color=$col>{$nGames}</font></td>
        <td align=right><font color=$col>$numBats</font></td>
        <td align=right><font color=$col>$numAtBats</font></td>
        <td align=right><font color=$col>$numHit1</font></td>
        <td align=right><font color=$col>$numHit2</font></td>
        <td align=right><font color=$col>$numHit3</font></td>
        <td align=right><font color=$col>$numHrs</font></td>
        <td align=right><font color=$col>$runAve</font></td>
        <td align=right><font color=$col>$ave</font></td>
        <td align=right><font color=$col>$slg</font></td>
        <td align=right><font color=$col>$ops</font></td>
        <td align=right><font color=$col>$noi</font></td>
      </tr>";
        }
    
        $bg = $bgcolor[$i++ & 1];
        $runAveTotal = sprintf("%0.3f", $total['numRuns'] / $total['numBats']);
        $aveTotal = sprintf("%0.3f", $total['numHits'] / $total['numAtBats']);
        $runAveTotal = ereg_replace("^0", "", $runAveTotal);
        $aveTotal = ereg_replace("^0", "", $aveTotal);
        $slgTotal = sprintf("%0.3f", $total['numBases'] / $total['numAtBats']);
        $slgTotal = ereg_replace("^0", "", $slgTotal);
        $opsTotal = $runAveTotal + $slgTotal;
        $noiTotal = sprintf("%d", ($runAveTotal + $slgTotal / 3) * 1000);

        $out .= "<tr class=hdr>
        <td align=center>Total</td>
        <td align=right>{$game['numGames']}</td>
        <td align=right>{$total['numBats']}</td>
        <td align=right>{$total['numAtBats']}</td>
        <td align=right>{$total['numHit1']}</td>
        <td align=right>{$total['numHit2']}</td>
        <td align=right>{$total['numHit3']}</td>
        <td align=right>{$total['numHrs']}</td>
        <td align=right>$runAveTotal</td>
        <td align=right>$aveTotal</td>
        <td align=right>$slgTotal</td>
        <td align=right>$opsTotal</td>
        <td align=right>$noiTotal</td>
        </tr>
        </table>
        <a href={$this->top}?view=batting> standard... </a>
        <a href={$this->top}?view=battingEvenmore> even more... </a>
      </p>";

        return $out;
    }

    function individualBattingHtml() {
        $out = "";

        // side navigation メンバー一覧
        $sql = "SELECT memberId
                FROM batting_tbl
                WHERE {$this->filter->sqlCond()}
                GROUP BY memberId;";
        $ans = query($sql);
        if ($ans != NULL) {
            $out .= "<div id=memberList>";
            $members = $ans->fetchAll();
            foreach($members as $member) {
                if ($member['memberId'] != $this->memberId) {
                    $name = $this->jName[$member['memberId']];
                    $out .= "<a href={$this->top}?view=individualBatting&memberId={$member['memberId']}>
                     $name<br>
                   </a>";
                }
            }
            $out .= "</div>";
        }

        if ($this->memberId == NULL) {
            $out = "Internal error<br>";
        } else {
            $cond = $this->filter->sqlCond();

            $sql = "SELECT raw,
                           atBat,
                           hit,
                           gameId -- For debugging
                    FROM bat_tbl
                    WHERE memberId = {$this->memberId}
                          and $cond;";
            $rows = query($sql)->fetchAll();
            $numBats = 0;
            $sout = 0;
            $others = 0;
            $fball = 0;
            $gida = 0;
            $hitDir = array(0, 0, 0, 0, 0, 0, 0, 0, 0, 0);
            $hit = array('H1' => 0, 'H2' => 0, 'H3' => 0, 'HR' => 0);
            $gOut = array(0, 0, 0, 0, 0, 0, 0, 0, 0, 0);
            $fOut = array(0, 0, 0, 0, 0, 0, 0, 0, 0, 0);
            $fball = array('b' => 0, 'd' => 0);
            $unknownOut = 0;
            $unknown = 0;
            $hits = array();
            foreach ($rows as $bat) {
                $numBats++;
                $raw = $bat['raw'];
                $raw = ereg_replace("\*$", "", $raw);
                $data = explode("-", $raw);
                if ($data[1] == "R") {
                    $numBats --;
                } else if ($data[1] == "K") {
                    $sout ++;
                } else if ($data[1] == "I") {
                    $others ++;
                } else if ($data[1] == "B") {
                    $fball['b'] ++;
                } else if ($data[1] == "D") {
                    $fball['d'] ++;
                } else if ($data[2] == "G") {
                    $gida ++;
                } else if ($data[2] == "H1" or $data[2] == "H2"
                           or $data[2] == "H3" or $data[2] == "HR") {
                    ereg("^([1-9])([GFL]*)", $data[1], $dir);
                    if (isset($dir[1]))
                        $hitDir[$dir[1]] ++;
                    $hit[$data[2]] ++;
                } else if ($data[2] == "O" or $data[2] == "E") {
                    $dir = array();
                    ereg("^([1-9])([GFL])", $data[1], $dir);
                    if (isset($dir[2])) {
                        if ($dir[2] == "G") {
                            $gOut[$dir[1]] ++;
                        } else if ($dir[2] == "F" or $dir[2] == "L") {
                            $fOut[$dir[1]] ++;
                        } else {
                            $unknownOut ++;
                        }
                    } else {
                        $unknownOut ++;
                    }
                } else {
                    $unknown ++;
                }
            }

            $hitTotal = array_sum($hitDir);
            $gOutTotal = array_sum($gOut);
            $fOutTotal = array_sum($fOut);

            if (!isset($hit['H1'])) $hit['H1'] = 0;
            if (!isset($hit['H2'])) $hit['H2'] = 0;
            if (!isset($hit['H3'])) $hit['H3'] = 0;
            if (!isset($hit['HR'])) $hit['HR'] = 0;
            if (!isset($fball['b'])) $fball['b'] = 0;
            if (!isset($fball['d'])) $fball['d'] = 0;

            $id = uniqid();
            $out .= "<h2>{$this->jName[$this->memberId]}選手</h2>";
            $out .= "<h3 class=main>打席サマリ</h3>
               <table class=table1>
                 <tr class=hdr>
                   <th rowspan=2>打席</th><th colspan=4>安打</th>
                   <th colspan=2>四死球</th><th colspan=4>アウト</th>
                   <th rowspan=2>不明</th>
                 </tr>
                 <tr class=hdr>
                   <th>単打</th><th>二塁打</th><th>三塁打</th><th>本塁打</th>
                   <th>四球</th><th>死球</th>
                   <th>三振</th><th>ゴロ</th><th>フライ</th><th>その他</th>
                 </tr>
                 <tr class=row2>
                   <td align=right>$numBats</td>
                   <td align=right>{$hit['H1']}</td>
                   <td align=right>{$hit['H2']}</td>
                   <td align=right>{$hit['H3']}</td>
                   <td align=right>{$hit['HR']}</td>
                   <td align=right>{$fball['b']}</td>
                   <td align=right>{$fball['d']}</td>
                   <td align=right>$sout</td>
                   <td align=right>$gOutTotal</td>
                   <td align=right>$fOutTotal</td>
                   <td align=right>$unknownOut</td>
                   <td align=right>$unknown</td>
                 </tr>
               </table>";
                   
            // the margin should be specified by css
            $out .= "<img class=chart src=\"php/iBatPieChart.php?id=$id\">";

            $out .= "<h3 class=main>打球方向</h3><table class=table1>
                 <tr class=hdr><th>方向</th><th>安打</th><th>ゴロ</th><th>フライ</th></tr>";
            $position = array("ピッチャー", "キャッチャー", "ファースト", "セカンド",
                              "サード", "ショート", "レフト", "センター", "ライト");
            $bgcolor = array("row1", "row2");
            for ($i = 1; $i <= 9; $i++) {
                if (!isset($hitDir[$i])) $hitDir[$i] = 0;
                if (!isset($gOut[$i])) $gOut[$i] = 0;
                if (!isset($fOut[$i])) $fOut[$i] = 0;

                $bg = $bgcolor[$i & 1];
                $out .= "<tr class=$bg>
                   <td align=center>{$position[$i-1]}</td>
                   <td align=right>{$hitDir[$i]}</td>
                   <td align=right>{$gOut[$i]}</td>
                   <td align=right>{$fOut[$i]}</td>
                 </tr>";
            }
            $out .= "</table>";

            // added an unique ID so that firefox reloads the image
            $out .= "<h3 class=main>打率推移</h3>
               <img class=chart src=\"php/iAveLineChart.php?id=$id\">";
        }
        return $out;
    }

    function rbiLineChart($target) {
        // Top 5 に限定
        $topN = 10;
        $top5 = $this->getTheTop($target, $this->filter->sqlCond(), $topN);
        $cond = "and (";
        $notFirst = false;
        foreach ($top5 as $top) {
            if ($notFirst) {
                $cond .= " or ";
            }
            $cond .= "memberId = {$top['memberId']}";
            $notFirst = true;
        }
        $cond .= ")";

        if ($target == "average") {
            $total = "SUM(hit1 + hit2 + hit3 + hr)";
        } else {
            $total = "SUM($target)";
        }

        $rbi = array();
        $numAtBatsArray = array();
        $games = array();
        $count = 0;
        foreach ($this->filter->condGames as $gameId) {
            $sql = "SELECT
                      memberId,
                      $total $target,
                      SUM(atBat) numAtBats
                    FROM bat_tbl
                    WHERE gameId == {$gameId['gameId']} $cond
                    GROUP BY memberId;";
            $ans = query($sql);
            if ($ans != "") {
                array_push($games, $gameId['gameId']); 
                $results = $ans->fetchAll();
                foreach ($results as $result) {
                    $memberId = $result['memberId'];
                    if (isset($rbi[$memberId])) {
                        $sum = end($rbi[$memberId]) + $result[$target];
                        array_push($rbi[$memberId], $sum);
                        // if average
                        $numAtBats = end($numAtBatsArray[$memberId]) + $result['numAtBats'];
                        array_push($numAtBatsArray[$memberId], $numAtBats);
                    } else { // new record
                        $rbi[$memberId] = array();
                        $numAtBatsArray[$memberId] = array();
                        for ($i = 0; $i < $count; $i++) {
                            array_push($rbi[$memberId], '');
                            array_push($numAtBatsArray[$memberId], '');
                        }
                        array_push($rbi[$memberId], $result[$target]);
                        array_push($numAtBatsArray[$memberId], $result['numAtBats']);
                    }
                }
                $count ++;

                // 記録のない場合、以前の結果を継続
                foreach (array_keys($rbi) as $player) {
                    if (count($rbi[$player]) < $count) {
                        array_push($rbi[$player], end($rbi[$player]));
                        array_push($numAtBatsArray[$player], end($numAtBatsArray[$player]));
                    }
                }
            }
        }

        // 打率の場合、データを計算
        if ($target == "average") {
            foreach(array_keys($rbi) as $player) {
                $count = count($rbi[$player]);
                for ($i = 0; $i < $count; $i++) {
                    $hit = array_shift($rbi[$player]);
                    $numAtBats = array_shift($numAtBatsArray[$player]);
                    if ($numAtBats > 0) {
                        $ave = $hit / $numAtBats;
                    } else {
                        $ave = '';
                    }
                    array_push($rbi[$player], $ave);
                }
            }
        }

        // お名前を配列の先頭に追加
        foreach (array_keys($rbi) as $k) {
            array_unshift($rbi[$k], $this->jName[$k]);
        }

        // 上位からソート
        $orderedData = array();
        foreach ($top5 as $top) {
            foreach(array_keys($rbi) as $aPlayer) {
                if ($top['memberId'] == $aPlayer) {
                    array_push($orderedData, $rbi[$aPlayer]);
                }
            }
        }
        return array($games, $orderedData);
    }
  
    function teamsHtml() {
        $sql = "SELECT team,
                COUNT(*) numGames
                FROM game_tbl
                WHERE {$this->filter->sqlCond()}
                GROUP BY team
                ORDER BY numGames DESC, date DESC;";
        $ans = query($sql);
        if ($ans == "") {
            return "No data";
        }
        $ans = query($sql)->fetchAll();
        $data = array();
        foreach ($ans as $row) {
            extract($row);
            $teamName = rawurlencode($team);
            $sql = "SELECT *
                    FROM game_tbl
                    WHERE team = \"$team\";";
            $games = query($sql)->fetchAll();
            $teamLink = "<a href={$this->top}?view=team&team=$teamName>$team</a>";
            $win = 0;
            $lose = 0;
            $draw = 0;
            foreach ($games as $game) {
                if ($game['type'] == "紅白戦" or !isset($game['pointGot'])) {
                    // no game
                } else if ($game['pointGot'] > $game['pointLost']) {
                    $win++;
                } else if ($game['pointGot'] < $game['pointLost']) {
                    $lose++;
                } else {
                    $draw++;
                }
            }
            if ($win + $lose > 0) {
                $wRate = sprintf("%0.3f", $win / ($win + $lose));
                $wRate = ereg_replace("^0", "", $wRate);
            } else {
                $wRate = "-";
            }
            array_push($data, array('チーム' => $teamLink,
                                    '試合' => $row['numGames'],
                                    '勝' => $win,
                                    '負' => $lose,
                                    '分' => $draw,
                                    '勝率' => $wRate));
      
        }
        $tbl = new table(array('',
                               "{$this->top}?",
                               array('チーム', '試合', '勝', '負', '分', '勝率'),
                               $data,
                               array('チーム' => 'left')
                               )
                         );
        $out = "<h3 class=main>対戦成績</h3>";
        $out .= $tbl->toHtml($this->sort, $this->reverse);
        return $out;
    }

    function teamHtml() {
        $out = "";
        $sql = "SELECT gameId
            FROM game_tbl
            WHERE ({$this->filter->sqlCond()})
            ORDER BY date DESC;";
        $games = query($sql);
        if ($games == "") {
            return "No data";
        }
        $games = $games->fetchAll();
        $cond = NULL;
        foreach ($games as $game) {
            if ($cond == NULL) {
                $cond .= "gameId = {$game['gameId']}";
            } else {
                $cond .= " or gameId = {$game['gameId']}";
            }
        }
        $out .= "<h2>vs {$this->filter->filterTeam}</h2>";
        $out .= $this->gamesHtml($cond);
        $out .= $this->battingHtml($cond);
        $out .= "<h3 class=main>Details</h3><p>";
        foreach ($games as $game) {
            $g = new gameDb($game['gameId']);
            $out .= $g->toHtml($this->jName);
            $out .= "<hr>";
        }
        $out .= "</p>";
        return $out;
    }

    function createBattingLink($target, $str) {
        if ($target == $this->sort) {
            $reverse = "&reverse=true";
        } else {
            $reverse = "";
        }
        return "<a href={$this->top}?view=batting&sort=$target$reverse> $str </a>";
    }
  
    // filterで指定する範囲の項目$typeについて、人数が$nまでの順位を
    // $resultのarrayで返す。$n = NULLの場合はすべて返す
    // $result = (['order'] => 順位, ['memberId'] => ID, ['value'] => 値)
    function getTheTop($type, $cond, $n = NULL) {
        if ($type == "average") {
            $target = "CAST(SUM(hit1 + hit2 + hit3 + hr) AS FLOAT)
                 / CAST(SUM(atBat) AS FLOAT)";
            // 全試合数
            $sql = "SELECT COUNT(*) numGames
            FROM game_tbl
            WHERE $cond;";
            $game = query($sql)->fetch();

            // 個人別出場試合数
            $sql = "SELECT memberId, COUNT(*) numGames
            FROM batting_tbl
            WHERE $cond
            GROUP BY memberId;";
            $games = query($sql)->fetchAll();

            foreach ($games as $g) {
                $numGames[$g['memberId']] = $g['numGames'];
            }
        } else {
            $target = "SUM($type)";
        }
        $sql = "SELECT
              memberId,
              $target
            FROM bat_tbl
            WHERE $cond
            GROUP BY memberId
            ORDER BY $target DESC;";
        $ans = query($sql)->fetchAll();
        $i = 1; //人数
        $order = 0;
        $orderDelta = 1;
        $last = 0;
        $ret = array();
        while($top = array_shift($ans)) {
            if ($type == "average" and $numGames[$top['memberId']] * 2 < $game['numGames']) {
                //打率タイプで規定打席未満はスキップ
                continue;
            }
            if ($n != NULL and $i > $n and $last != $top[$target]) {
                break;
            }
            if ($top[$target] == 0) {
                break;
            }

            if ($last == $top[$target]) {
                $orderDelta ++;
            } else {
                $order += $orderDelta;
                $orderDelta = 1;
                $last = $top[$target];
            }
            $result = array();
            $result['order'] = $order;
            $result['memberId'] = $top['memberId'];
            if ($type == "average") {
                $result['value'] = sprintf("%0.3f", $top[$target]);
            } else {
                $result['value'] = $top[$target];
            }
            array_push($ret, $result);
            $i++;
        }
        return $ret;
    }

    function topTableHtml($a, $unit = NULL) {
        $out = "<table class=top>";
        if ($a == NULL) {
            $out .= "<tr class=row2><td colspan=3 align=center>該当なし</td></tr>";
        } else {
            foreach ($a as $top) {
                if ($top['order'] == 1) {
                    $bg = "class=first";
                } else {
                    $bg = "class=row2";
                }
                $out .= "<tr $bg>
                   <td align=right class=top>{$top['order']}</td>
                   <td align=center class=top>
                     <a href={$this->top}?view=individualBatting&memberId={$top['memberId']}>
                     {$this->jName[$top['memberId']]}
                     </a>
                   </td>
                   <td align=right class=top>{$top['value']}$unit</td>
                 </tr>";
            }
        }
        $out .= "</table>";
        return $out;
    }

    function historyHtml() {
        $out = "<h2>History</h2>
      <h3 class=main>勝敗推移</h3>
      <p>
        <img src=\"images/resultHistory.png\">
      </p>
      <h3 class=main>選手在籍期間</h3>
      <p>
        <img src=\"images/memberHistory.png\">
      </p>";
        return $out;
    }

    function mainViewHtml() {
        if ($this->view == "games") {
            return $this->gamesHtml();
        } else if ($this->view == "game") {
            return $this->gameHtml();
        } else if ($this->view == "todo") {
            return todoHtml();
        } else if ($this->view == "batting") {
            return $this->battingHtml();
        } else if ($this->view == "battingMore") {
            return $this->batting2Html();
        } else if ($this->view == "teams") {
            return $this->teamsHtml();
        } else if ($this->view == "team") {
            return $this->teamHtml();
        } else if ($this->view == "history") {
            return $this->historyHtml();
        } else if ($this->view == "individualBatting") {
            return $this->individualBattingHtml();
        } else {
            // main view
            //       $out = "";
            //       $out .= $this->gamesHtml();
            //       $out .= $this->battingHtml();
            return $this->homeHtml();
        }
    }
  
    function topHtml() {
        $sql = "SELECT date FROM game_tbl ORDER BY date DESC;";
        $ans = query($sql)->fetch();
        $lastYear = explode("/", $ans['date']);
        $lastYear = $lastYear[0];
        $revision = getLastRevision();
        $date = getLastDate();
        $newMenu = "";
        $newMenu = $this->mMainMenu->menu();
        $main = $this->mMainMenu->main($post);
        return "
<div id=topBar>

  <div id=version>
    <p>
    Rev: $revision<br>
    LastUpdate: $date
    </p>
  </div>

  <div id=title>
    <h1>
      <a href={$this->top}?view=main&year=$lastYear&season=true>
        MonStars 秘密基地
      </a>
    </h1>
  </div>

</div>

<div id=mainMenu>
  new menu<br>
  $newMenu
  <br>end of new menu<br>
  <h4><a href=\"javaScript:treeMenu('tMenuGames')\">Games</a></h4>
  <div id=\"tMenuGames\" style=\"display:block\">
  <ul>
    <li><a href={$this->top}?view=games>Live</a></li>
    <li><a href={$this->top}?view=teams>Team</a></li>
  </ul>
  </div>
  <h4><a href=\"javaScript:treeMenu('tMenuBattings')\">Battings</a></h4>
  <div id=\"tMenuBattings\" style=\"display:block\">
  <ul>
    <li>Table</li>
    <li>Average</li>
    <li>HR</li>
    <li>RBI</li>
    <li>Steal</li>
    <li>Individual</li>
  </ul>
  </div>
  <h4><a href=\"javaScript:treeMenu('tMenuHistory')\">History</a></h4>
  <div id=\"tMenuHistory\" style=\"display:none\">
  <ul>
    <li><a href={$this->top}?view=history>Matches</a></li>
    <li><a href={$this->top}?view=history>Players</a></li>
  </ul>
  </div>
  <h4><a href=\"javaScript:treeMenu('tMenuLinks')\">Links</a></h4>
  <div id=\"tMenuLinks\" style=\"display:none\">
  <ul>
    <li>Photo</li>
    <li>ML</li>
  </ul>
  </div>
  <h4><a href=\"javaScript:treeMenu('tMenuDevelop')\">Developers</a></h4>
  <div id=\"tMenuDevelop\" style=\"display:none\">
  <ul>
    <li>ToDo</li>
    <li>Support BBS</li>
    <li>blog</li>
    <li>Links</li>
  </ul>
  </div>
<!--
  <ul id=menu>
    <li><a href={$this->top}?view=games>Games</a></li>
    <li><a href={$this->top}?view=batting>Battings</a></li>
    <li><a href={$this->top}?view=teams>Teams</a></li>
    <li><a href={$this->top}?view=history>History</a></li>
    <li><a href={$this->top}?view=todo>ToDo</a></li>
    <li><a href=\"javaScript:pullDown()\">Filter</a></li>
  </ul>
-->
</div>

<div id=\"ID\" style=\"position:absolute;visibility:hidden;\">
  {$this->filtersHtml()}
</div>

<div id=mainView>
 
  {$this->mainViewHtml()}
</div>
";

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
