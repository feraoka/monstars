<?php

/**
 * Coming soon records in Batting
 */

require_once "MS_DB.php";

final class MS_Module_Bat_Coming_Soon extends MS_Module_Base
{
    private $rules;

    protected function __construct()
    {
        $this->name = "もうすぐ記録";
        $this->title = $this->name;

        $this->rules =
            array('hr' => array('name' => '本塁打', 'min' => 10, 'step' => 10, 'start' => 3),
                  'hits' => array('name' => '安打', 'min' => 50, 'step' => 50, 'start' => 5),
                  'rbi' => array('name' => '打点', 'min' => 50, 'step' => 50, 'start' => 5),
                  'steal' => array('name' => '盗塁', 'min' => 50, 'step' => 50, 'start' => 5),
                  'sout' => array('name' => '三振', 'min' => 50, 'step' => 50, 'start' => 5),
                  'ball' => array('name' => '四死球', 'min' => 50, 'step' => 50, 'start' => 5),

                  'fball' => array('name' => '四球', 'min' => 50, 'step' => 50, 'start' => 5),
                  'dball' => array('name' => '死球', 'min' => 10, 'step' => 10, 'start' => 2),
                  'hit2' => array('name' => '二塁打', 'min' => 50, 'step' => 50, 'start' => 5),
                  'hit3' => array('name' => '三塁打', 'min' => 50, 'step' => 50, 'start' => 5),
                  'hit' => array('name' => '塁打', 'min' => 50, 'step' => 50, 'start' => 10),
                  );
    }

    public static function instance()
    {
        static $inst = null;
        if (!isset($inst)) {
            $inst = new MS_Module_Bat_Coming_Soon;
        }
        return $inst;
    }

    public function link()
    {
        return $this->linkToSelf();
    }

    public function html()
    {
        $out = "<h3>{$this->title}</h3>";
        $db = MS_DB::instance();

        // 個人別出場試合数
        $sql = "SELECT memberId, COUNT(*) 試合
                FROM batting_tbl
                GROUP BY memberId;";
        $ans = $db->query($sql);
        if ($ans == "") {
            return "No games";
        }
        $numGamesArray = $ans->fetchAll();
        foreach ($numGamesArray as $numGames) {
            $n = $numGames['試合'];
            $start = 5;
            $step = 100;
            $min = 100;
            if (($n % $step) + $start >= $step and $n > $min) {
                $g = $step - $n % $step;
                $target = $n + $g;
                $jname = $db->getNameByMemberId($numGames['memberId']);
                $out .= "<b>{$jname}</b>選手、 <b>{$target} 試合出場</b> まで　あと<b>$g</b><br>";
            }
        }

        $sql = "SELECT memberId,
                COUNT(*) 打席,
                SUM(atBat) 打数,
                SUM(hit1 + hit2 + hit3 + hr) 安打,
                SUM(hit1) 単打,
                SUM(hit2) 二塁打,
                SUM(hit3) 三塁打,
                SUM(hr) 本塁打,
                SUM(hit) 塁打,
                SUM(steal) 盗塁,
                SUM(fball) 四死球,
                SUM(dball) 死球,
                SUM(fball) - SUM(dball) 四球,
                SUM(sout) 三振,
                SUM(rbi) 打点,
                SUM(rrun) 得点,
                SUM(run) 出塁
                FROM bat_tbl
                GROUP BY memberId;";
        $ans = $db->query($sql);

        if ($ans != "") {
            $records = $ans->fetchAll();
            foreach (array_keys($this->rules) as $key) {
                //print "{$this->rules[$key]['name']}<br>";
                foreach ($records as $record) {
                    $n = $record[$this->rules[$key]['name']];
                    $start = $this->rules[$key]['start'];
                    $step = $this->rules[$key]['step'];
                    $min = $this->rules[$key]['min'];
                    if (($n % $step) + $start >= $step and $n > $min) {
                        $g = $step - $n % $step;
                        $target = $n + $g;
                        //print "$n ($g to $target)<br>";
                        $jname = $db->getNameByMemberId($record['memberId']);
                        $out .= "<b>{$jname}</b>選手、 <b>$target {$this->rules[$key]['name']}</b> まで　あと<b>$g</b><br>";
                    }
                }
            }
        }

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
