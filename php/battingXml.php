<?php

require_once "batting.php";

class battingXml extends batting {

    function __construct($xml) {
        $this->name = $xml->getAttribute("name");
        $this->order = $xml->getAttribute("order");
        $this->position = $xml->getAttribute("position");
        foreach ($xml->childNodes as $d) {
            if ($d->nodeType == XML_ELEMENT_NODE) {
                $bat = new bat($d->getAttribute("raw"));
                array_push($this->bats, $bat);
            }
        }
    }

}

?>
