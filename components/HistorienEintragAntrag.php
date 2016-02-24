<?php

namespace app\components;

use app\components\RISTools;
use yii\helpers\Html;

class HistorienEintragAntrag implements HistorienEintrag
{
    /**
     * @var AntragHistory $antrag_alt
     * @var AntragHistory $antrag_neu
     */
    private $antrag_alt, $antrag_neu;


    /**
     * @param AntragHistory $antrag_alt
     * @param AntragHistory $antrag_neu
     */
    public function __construct($antrag_alt, $antrag_neu)
    {
        $this->antrag_alt = $antrag_alt;
        $this->antrag_neu = $antrag_neu;
    }

    public function getLink()
    {
        // TODO: Implement getLink() method.
    }

    public function getDatum()
    {
        return RISTools::datumstring($this->antrag_neu->datum_letzte_aenderung);
    }

    /**
     * @return HistorienEintragFeld[]
     */
    public function getFormattedDiff()
    {
        $felder = [];
        if ($this->antrag_alt->betreff != $this->antrag_neu->betreff) $felder[] = new HistorienEintragFeld(
            "Betreff", Html::encode($this->antrag_alt->betreff), Html::encode($this->antrag_neu->betreff)
        );
        if ($this->antrag_alt->gestellt_am != $this->antrag_neu->gestellt_am) $felder[] = new HistorienEintragFeld(
            "Gestellt am", Html::encode($this->antrag_alt->gestellt_am), Html::encode($this->antrag_neu->gestellt_am)
        );
        if ($this->antrag_alt->gestellt_von != $this->antrag_neu->gestellt_von) $felder[] = new HistorienEintragFeld(
            "Gestellt von", Html::encode($this->antrag_alt->gestellt_von), Html::encode($this->antrag_neu->gestellt_von)
        );
        if ($this->antrag_alt->antrags_nr != $this->antrag_neu->antrags_nr) $felder[] = new HistorienEintragFeld(
            "Antragsnummer", Html::encode($this->antrag_alt->antrags_nr), Html::encode($this->antrag_neu->antrags_nr)
        );
        if ($this->antrag_alt->bearbeitungsfrist != $this->antrag_neu->bearbeitungsfrist) $felder[] = new HistorienEintragFeld(
            "Bearbeitungsfrist", Html::encode($this->antrag_alt->bearbeitungsfrist), Html::encode($this->antrag_neu->bearbeitungsfrist)
        );
        if ($this->antrag_alt->registriert_am != $this->antrag_neu->registriert_am) $felder[] = new HistorienEintragFeld(
            "Registriert am", Html::encode($this->antrag_alt->registriert_am), Html::encode($this->antrag_neu->registriert_am)
        );
        if ($this->antrag_alt->referat != $this->antrag_neu->referat) $felder[] = new HistorienEintragFeld(
            "Referat", Html::encode($this->antrag_alt->referat), Html::encode($this->antrag_neu->referat)
        );
        if ($this->antrag_alt->referent != $this->antrag_neu->referent) $felder[] = new HistorienEintragFeld(
            "Referent", Html::encode($this->antrag_alt->referent), Html::encode($this->antrag_neu->referent)
        );
        if ($this->antrag_alt->wahlperiode != $this->antrag_neu->wahlperiode) $felder[] = new HistorienEintragFeld(
            "Wahlperiode", Html::encode($this->antrag_alt->wahlperiode), Html::encode($this->antrag_neu->wahlperiode)
        );
        if ($this->antrag_alt->antrag_typ != $this->antrag_neu->antrag_typ) $felder[] = new HistorienEintragFeld(
            "Typ", Html::encode($this->antrag_alt->antrag_typ), Html::encode($this->antrag_neu->antrag_typ)
        );
        if ($this->antrag_alt->kurzinfo != $this->antrag_neu->kurzinfo) $felder[] = new HistorienEintragFeld(
            "Kurzinfo", Html::encode($this->antrag_alt->kurzinfo), Html::encode($this->antrag_neu->kurzinfo)
        );
        if ($this->antrag_alt->status != $this->antrag_neu->status) $felder[] = new HistorienEintragFeld(
            "Status", Html::encode($this->antrag_alt->status), Html::encode($this->antrag_neu->status)
        );
        if ($this->antrag_alt->bearbeitung != $this->antrag_neu->bearbeitung) $felder[] = new HistorienEintragFeld(
            "Bearbeitung", Html::encode($this->antrag_alt->bearbeitung), Html::encode($this->antrag_neu->bearbeitung)
        );
        if ($this->antrag_alt->fristverlaengerung != $this->antrag_neu->fristverlaengerung) $felder[] = new HistorienEintragFeld(
            "Fristverlängerung", Html::encode($this->antrag_alt->fristverlaengerung), Html::encode($this->antrag_neu->fristverlaengerung)
        );
        if ($this->antrag_alt->initiatorInnen != $this->antrag_neu->initiatorInnen) $felder[] = new HistorienEintragFeld(
            "Initiatoren", Html::encode($this->antrag_alt->initiatorInnen), Html::encode($this->antrag_neu->initiatorInnen)
        );
        if ($this->antrag_alt->initiative_to_aufgenommen != $this->antrag_neu->initiative_to_aufgenommen) $felder[] = new HistorienEintragFeld(
            "TO Aufgenommen", Html::encode($this->antrag_alt->initiative_to_aufgenommen), Html::encode($this->antrag_neu->initiative_to_aufgenommen)
        );
        return $felder;
    }
}
