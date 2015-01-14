<?php

interface HistorienEintrag
{

    public function getLink();

    public function getDatum();

    public function getFormattedDiff();

}

class HistorienEintragFeld
{
    private $feld, $alt, $neu;

    public function __construct($feld, $alt, $neu)
    {
        $this->feld = $feld;
        $this->alt  = $alt;
        $this->neu  = $neu;
    }

    public function getFeld()
    {
        return $this->feld;
    }

    public function getAlt()
    {
        return $this->alt;
    }

    public function getNeu()
    {
        return $this->neu;
    }
}
