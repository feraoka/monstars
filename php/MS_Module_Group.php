<?php

/**
 * MonStars Module group class
 */

final class MS_Module_Group
{
    public $name; // Module Group Name
    public $modules = array();
    public $opened; // true if opened

    function __construct($name, $opened = true)
    {
        $this->name = $name;
        $this->opened = $opened;
    }

    function add($module)
    {
        $this->modules[] = $module;
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
