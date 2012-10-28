<?php

/**
 * Titles
 */

require_once "MS_Module_Base.php";
require_once "MS_DB.php";
require_once "MS_Module_Bat_Table.php";

final class MS_Module_Titles extends MS_Module_Base
{
    private $titles;

    protected function __construct()
    {
        $this->name = "タイトル";
        $this->title = "歴代タイトル";

        $this->titles = array('average' => '首位打者',
                              'rbi' => '打点王',
                              'hr' => 'ホームラン王',
                              'steal' => '盗塁王');
    }

    public static function instance()
    {
        static $inst = null;
        if (!isset($inst)) {
            $inst = new MS_Module_Titles;
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
        $years = MS_DB::instance()->getYearList();
        $table = array();

        // Header
        $header = array('header' => true, 'year' => '年');
        foreach (array_keys($this->titles) as $title) {
            $header[$title] = $this->titles[$title];
        }
        $table[] = $header;

        // Data
        foreach ($years as $year) {
            MS_DB::instance()->setFilterByYear($year);
            $data = array('year' => $year);
            foreach (array_keys($this->titles) as $title) {
                $tops = MS_DB::instance()->getTheTop($title, 1);
                if (count($tops) > 0) {
                    $names = "";
                    foreach ($tops as $top) {
                        $name = MS_DB::instance()->getNameByMemberId($top['memberId']);
                        $value = $top['value'];
                        //                     $out .= "$name - $value";
                        if ($names != "") {
                            $names .= ", ";
                        }
                        $names .= $name;
                    }
                    $names .= " ($value)";
                    $data[$title] = $names;
                } else {
                    $data[$title] = '-';
                }
            }
            $table[] = $data;
        }

        // Creating table
        $currentYear = MS_DB::instance()->getCurrentYear();
        $bg = array("row1", "row2");
        $i = 0;
        $out .= "\n<table>";
        foreach ($table as $row) {
            $out .= "<tr>";
            if (isset($row['header'])) {
                $td = "th class=hdr";
                $out .= "<$td>{$row['year']}</$td>";
            } else {
                $td = "td class={$bg[$i]} align=center";
                $link = MS_Module_Bat_Table::instance()->link();
                $link .= "&year={$row['year']}";
                if ($row['year'] == $currentYear) {
                    $current = "*";
                } else {
                    $current = "";
                }
                $out .= "<$td>$current<a href=$link>{$row['year']}</a></$td>";
            }
            foreach (array_keys($this->titles) as $title) {
                $out .= "<$td>{$row[$title]}</$td>";
            }
            $out .= "</tr>";
            $i ^= 1;
        }
        $out .= "</table>";
        $out .= "<p>*シーズン中</p>";
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
