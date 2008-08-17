<?php

require_once "comment.php";

class MS_Game {

	public $gameId;
	public $season;
	public $date;
	public $time;
	public $type;
	public $location;
	public $team;
	public $comment;
	public $scoreboard;
	public $battings = array();

	private $positionStr = array('1' => '投',
								 '2' => '補',
								 '3' => '一',
								 '4' => '二',
								 '5' => '三',
								 '6' => '遊',
								 '7' => '左',
								 '8' => '中',
								 '9' => '右',
								 '@' => '指',
								 '>' => '代');

	function toString()
    {
		return "not implemented yet";
	}

    function getType()
    {
        return $this->type;
    }

	function toHtml()
    {
		$inning = 0;
		$bMax = array();
		foreach ($this->battings as $batting) {
			$b = array();
			foreach ($batting->bats as $bat) {
				if (isset($b[$bat->inning])) {
					$b[$bat->inning] ++;
				} else {
					$b[$bat->inning] = 1;
				}
				if ($bat->inning > $inning) {
					$inning = $bat->inning;
				}
			}
			foreach (array_keys($b) as $i) {
				if (!isset($bMax[$i]) or $b[$i] > $bMax[$i]) {
					$bMax[$i] = $b[$i];
				}
			}
		}

		$batHtml = "<table class=table1><tr class=hdr><td colspan=3></td>";
		for ($i = 1; $i <= $inning; $i++) {
			$batHtml .= "<td align=center colspan={$bMax[$i]}>$i</td>";
		}
		$batHtml .= "</tr>";
		$col = 0;
		$bgcolor = array("row1", "row2");
		foreach ($this->battings as $batting) {
			$bg = $bgcolor[$col++ & 1];
            $name = MS_DB::instance()->convertName($batting->name);
			$batHtml .=
                "<tr class=$bg>
                 <td>{$batting->order}</td>
                 <td>{$batting->position} : {$this->positionStr[$batting->position]}</td>
                 <td>
                   $name
                 </td>";

			for ($i = 1; $i <= $inning; $i++) {
				$n = 0;
				foreach ($batting->bats as $bat) {
					if ($bat->inning == $i) {
						$r = ereg_replace("^[0-9]+-", "", $bat->raw);
						$data = explode("-", ereg_replace("\*$", "", $r));
						if ($data[0] == "K") {
							$class = "sout";
						} else if ($data[0] == "B" or $data[0] == "D") {
							$class = "fball";
						} else if ($data[1] == "H1" or $data[1] == "H2"
								   or $data[1] == "H3" or $data[1] == "HR") {
							$class = "hit";
						} else {
							$class = "nomal";
						}
						$batHtml .= "<td class=$class>$r</td>";
						$n ++;
					}
				}
				while ($n < $bMax[$i]) {
					$n ++;
					$batHtml .= "<td></td>";
				}
			}
			$batHtml .= "</tr>";
		}
		$batHtml .= "</table>";

		if ($this->time) {
			$t = $this->time;
		} else {
			$t = "&nbsp;";
		}

		// removing return codes at both beginning and end of the comment
		$this->comment = ereg_replace ("^(<br>)*", "", $this->comment);
		$this->comment = ereg_replace ("(<br>)*$", "", $this->comment);

		$form = "";

		$db = new MS_Comment;
		$comments = $db->getComment($this->gameId);
		$form .= "<p>";
		foreach ($comments as $comment) {
			$auther = $comment['name'];
			if ($auther == "") {
				$auther = "無名選手";
			}
			$form .= "<dl id=comment>
                  <dt>$auther</dt>
                  <dd>{$comment['comment']}</dd>
                </dl>";
		}
		$form .= "</p>";
    
		if (file_exists("test")) {
			$warning = "<div class=warning>
                  テスト中につき、書き込みは消去されます。
                  </div>";
			$form .= $warning;
		}

		$form .= "<p><form id=commentForm method=\"post\">
                <input type=hidden name=\"gameComment\" value=true>
                <input type=hidden name=\"gameId\" value=$this->gameId>
                <dl id=game>
                  <dt>お名前:</dt>
                  <dd><input type=\"text\" name=\"name\" size=8 class=text></dd>
                  <dt>コメント:</dt>
                  <dd><textarea name=\"comment\" cols=50 rows=3 class=text></textarea></dd>
                  <dt></dt>
                  <dd><input type=\"submit\" value=\"OK\" class=submit></d>
                </dl>
              </form></p>";

		$out = "<h3>Game Info</h3>
            <dl id=game>
              <dt> Date: </dt>
              <dd> {$this->date} </dd>
              <dt> Time: </dt>
              <dd> $t </dd>
              <dt> Team: </dt>
              <dd><b>{$this->team}</b>&nbsp;</dd>
              <dt> Type: </dt>
              <dd> {$this->type}&nbsp;</dd>
              <dt> Location: </dt>
              <dd> {$this->location}&nbsp;</dd>
            </dl>
            <h3>Scoreboard</h3>
            {$this->scoreboard->toHtml()}
            <h3>Battings</h3>
            $batHtml
            <h3>Comments and Details</h3>
              <p> {$this->comment} </p>
            $form<br>
            </p>";
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
