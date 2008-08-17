<?php

/**
 * Member History Module
 */

require_once "MS_Module_Base.php";

final class MS_Module_Members extends MS_Module_Base
{
    protected function __construct()
    {
        $this->name = "選手";
        $this->title = "選手在籍期間";
    }

    public static function instance()
    {
        static $inst = null;
        if (!isset($inst)) {
            $inst = new MS_Module_Members;
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
        $out .= "<img src=\"images/memberHistory.png\">";
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
