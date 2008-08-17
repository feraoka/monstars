<?php

/**
 * MonStars Module base class
 */

abstract class MS_Module_Base
{
    public static $top;
    public $selected = false;
    public $name; // module name
    public $title; // module title
    public $visible = true;

    abstract public static function instance(); // return instance of module
    abstract public function link(); // return link to module
    abstract public function html(); // return html of module

    protected function linkToSelf()
    {
        $encode = rawurlencode($this->name);
        $top = self::$top;
        return "$top?module=$encode";
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
