<?php

/**
 * Main Menu class
 */

require_once "MS_Module_Group.php";

class MS_Main_Menu
{
    const SCRIPT = 'treeMenu';
    const PREFIX = 'treeMenu';

    private $mGroups = array();

    public function __construct()
    {
    }

    // Add a group into main menu
    public function add($group)
    {
        $this->mGroups[] = $group;
    }

    public function mainHtml($request = NULL)
    {
        if (isset($request)) {
            extract($request);
            if (isset($module)) {
                return $this->html($module);
            } else {
                return NULL;
            }
        }
        return "no request";
    }

    // Return Html of the module specified by name
    public function html($name)
    {
        foreach ($this->mGroups as $group) {
            //$group->opened = false;
            foreach ($group->modules as $module) {
                if ($module->name == $name) {
                    $ret = $module->html();
                    $module->selected = true;
                    $group->opened = true;
                } else {
                    $module->selected = false;
                }
            }
        }
        if (!isset($ret)) {
            $ret = "Error: No module named $name.";
        }
        return $ret;
    }

    // Create main menu
    public function menuHtml()
    {
        $out = "";
        foreach ($this->mGroups as $group) {
            $display = $group->opened ? 'block' : 'none';
            $out .= "<h4><a href=\"javaScript:" . self::SCRIPT . "('"
                . self::PREFIX . "$group->name')\">$group->name</a></h4>
                <div id=\"" . self::PREFIX . "$group->name\" style=\"display:$display\">
                <ul>";
            foreach ($group->modules as $module) {
                $selected = $module->selected ? "id=\"selected\"" : "";
                if ($module->visible) {
                    $out .= "<li>
                             <a href={$module->link()} $selected>{$module->name}</a>
                             </li>";
                }
            }
            $out .= "</ul></div>\n";
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
