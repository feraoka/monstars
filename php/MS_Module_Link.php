<?php

/**
 * Link Module
 */

require_once "MS_Module_Base.php";

class MS_Module_Link extends MS_Module_Base
{
    function __construct()
    {
        $this->name = "Link";
        $this->title = "Link";
        $this->help = "";
    }

    public function link()
    {
        return "{$this->top}?view={$this->name}";
    }

    function html()
    {
        return "fuck";
        //        return "<h3>Hello</h3><img src=\"images/resultHistory.png\">";
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
