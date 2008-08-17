<?php

/**
 * Table class
 *
 */

class MS_Table {
    public $title;
    public $url;
    public $label = array();
    public $data = array();
    public $total = array();
    public $gray = array();
    public $help = array();

    protected $mKey;
    private $mReverse = false;
    private $bg = array("row1", "row2");

    public function html()
    {
        if (isset($_GET)) {
            extract($_GET);
        }

        if (isset($sort)) {
            if ($this->mKey == $sort) {
                $this->mReverse = $this->mReverse ? false : true;
            } else {
                $this->mKey = $sort;
                $this->mReverse = false;
            }

            $cmp = $this->mReverse ? '>' : '<';
            $func =  '$aa = strip_tags($a[\'' . $sort . '\']);';
            $func .= '$bb = strip_tags($b[\'' . $sort . '\']);';
            if (in_array($sort, $this->gray)) {
                $func .= 'if ($a[\'gray\'] and !$b[\'gray\']) return 1;';
                $func .= 'if (!$a[\'gray\'] and $b[\'gray\']) return -11;';
                $func .= 'else ';
            }
            $func .= 'if ($aa ' . $cmp . '$bb) return 1;';
            $func .= 'else if ($bb ' . $cmp . '$aa) return -1;';
            $func .= 'else return 0;';
            usort($this->data, create_function('$a, $b', $func));
        }

        $out = "<table>";
        if (isset($this->title)) {
            $out .= "<tr><th class=title>{$this->title}</th></tr>";
        }
        $out .= "<tr>";
        foreach ($this->label as $label) {
            $keyEnc = rawurlencode($label);
            $out .= "<th class=hdr align=center valign=center>";
            $reverse = ($this->mKey == $label and !$this->mReverse) ? "true" : "false";
            $out .= "<a href={$this->url}&sort=$keyEnc&reverse=$reverse>$label</a>";
            if (isset($this->help[$label])) {
                $out .= "<font size=\"-2\">(<a href=" . $this->help[$label] . ">?</a>)</font>";
            }
            $out .= "</th>";
        }
        $out .= "</tr>";
        $i = 0;
        $separator = false;
        foreach ($this->data as $row) {
            if (isset($sort) and in_array($sort, $this->gray)
                and $row['gray']
                and $separator == false) {
                $cols = count($this->label);
                $out .= "<tr><td colspan=$cols align=center>規定試合未満</td></tr>";
                $separator = true;
            }
            $out .= "<tr>";
            foreach (array_keys($this->label) as $labelIdx) {
                $data = $row[$this->label[$labelIdx]];
                if (isset($this->align)
                    and isset($this->align[$this->label[$labelIdx]])) {
                    $align = $this->align[$this->label[$labelIdx]];
                } else {
                    $align = "right"; // default
                }

                // グレー判定
                $color = "#000000";
                if (in_array($this->label[$labelIdx], $this->gray)
                    and $row['gray']) {
                    $color = "#888888";
                }

                $out .= "<td class={$this->bg[$i]} align=$align>
                         <font color=$color>
                         $data
                         </font>
                         </td>";
            }
            $out .= "</tr>";
            $i ^= 1;
        }
        if (count($this->total) > 0) {
            $out .= "<tr>";
            foreach (array_keys($this->label) as $labelIdx) {
                if (isset($this->align)
                    and isset($this->align[$this->label[$labelIdx]])) {
                    $align = $this->align[$this->label[$labelIdx]];
                } else {
                    $align = "right"; // default
                }
                $out .= "<td align=$align class=hdr>
                         {$this->total[$this->label[$labelIdx]]}
                         </td>";
            }
            $out .= "</tr>";
        }
        $out .= "</table>";
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
