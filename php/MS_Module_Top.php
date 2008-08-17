<?php

/**
 * Top Module
 */

require_once "MS_Module_Base.php";
require_once "MS_DB.php";

final class MS_Module_Top extends MS_Module_Base
{
    protected function __construct()
    {
        $this->name = "Home";
        $this->title = "Top Page";
    }

    public static function instance()
    {
        static $inst = null;
        if (!isset($inst)) {
            $inst = new MS_Module_Top;
        }
        return $inst;
    }

    public function link()
    {
        $current = MS_DB::instance()->getCurrentYear();
        $top = self::$top;
        return "$top?year=$current";
    }

    public function html()
    {
        return "<h3>{$this->title}</h3>";
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
