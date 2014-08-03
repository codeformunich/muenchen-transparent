<?php

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
		$felder = array();
		if ($this->antrag_alt->betreff != $this->antrag_neu->betreff) $felder[] = new HistorienEintragFeld(
			"Betreff", CHtml::encode($this->antrag_alt->betreff), CHtml::encode($this->antrag_neu->betreff)
		);
		if ($this->antrag_alt->gestellt_am != $this->antrag_neu->gestellt_am) $felder[] = new HistorienEintragFeld(
			"Gestellt am", CHtml::encode($this->antrag_alt->gestellt_am), CHtml::encode($this->antrag_neu->gestellt_am)
		);
		if ($this->antrag_alt->gestellt_von != $this->antrag_neu->gestellt_von) $felder[] = new HistorienEintragFeld(
			"Gestellt von", CHtml::encode($this->antrag_alt->gestellt_von), CHtml::encode($this->antrag_neu->gestellt_von)
		);
		if ($this->antrag_alt->antrags_nr != $this->antrag_neu->antrags_nr) $felder[] = new HistorienEintragFeld(
			"Antragsnummer", CHtml::encode($this->antrag_alt->antrags_nr), CHtml::encode($this->antrag_neu->antrags_nr)
		);
		if ($this->antrag_alt->bearbeitungsfrist != $this->antrag_neu->bearbeitungsfrist) $felder[] = new HistorienEintragFeld(
			"Bearbeitungsfrist", CHtml::encode($this->antrag_alt->bearbeitungsfrist), CHtml::encode($this->antrag_neu->bearbeitungsfrist)
		);
		if ($this->antrag_alt->registriert_am != $this->antrag_neu->registriert_am) $felder[] = new HistorienEintragFeld(
			"Registriert am", CHtml::encode($this->antrag_alt->registriert_am), CHtml::encode($this->antrag_neu->registriert_am)
		);
		if ($this->antrag_alt->referat != $this->antrag_neu->referat) $felder[] = new HistorienEintragFeld(
			"Referat", CHtml::encode($this->antrag_alt->referat), CHtml::encode($this->antrag_neu->referat)
		);
		if ($this->antrag_alt->referent != $this->antrag_neu->referent) $felder[] = new HistorienEintragFeld(
			"Referent", CHtml::encode($this->antrag_alt->referent), CHtml::encode($this->antrag_neu->referent)
		);
		if ($this->antrag_alt->wahlperiode != $this->antrag_neu->wahlperiode) $felder[] = new HistorienEintragFeld(
			"Wahlperiode", CHtml::encode($this->antrag_alt->wahlperiode), CHtml::encode($this->antrag_neu->wahlperiode)
		);
		if ($this->antrag_alt->antrag_typ != $this->antrag_neu->antrag_typ) $felder[] = new HistorienEintragFeld(
			"Typ", CHtml::encode($this->antrag_alt->antrag_typ), CHtml::encode($this->antrag_neu->antrag_typ)
		);
		if ($this->antrag_alt->kurzinfo != $this->antrag_neu->kurzinfo) $felder[] = new HistorienEintragFeld(
			"Kurzinfo", CHtml::encode($this->antrag_alt->kurzinfo), CHtml::encode($this->antrag_neu->kurzinfo)
		);
		if ($this->antrag_alt->status != $this->antrag_neu->status) $felder[] = new HistorienEintragFeld(
			"Status", CHtml::encode($this->antrag_alt->status), CHtml::encode($this->antrag_neu->status)
		);
		if ($this->antrag_alt->bearbeitung != $this->antrag_neu->bearbeitung) $felder[] = new HistorienEintragFeld(
			"Bearbeitung", CHtml::encode($this->antrag_alt->bearbeitung), CHtml::encode($this->antrag_neu->bearbeitung)
		);
		if ($this->antrag_alt->fristverlaengerung != $this->antrag_neu->fristverlaengerung) $felder[] = new HistorienEintragFeld(
			"Fristverlängerung", CHtml::encode($this->antrag_alt->fristverlaengerung), CHtml::encode($this->antrag_neu->fristverlaengerung)
		);
		if ($this->antrag_alt->initiatorInnen != $this->antrag_neu->initiatorInnen) $felder[] = new HistorienEintragFeld(
			"Initiatoren", CHtml::encode($this->antrag_alt->initiatorInnen), CHtml::encode($this->antrag_neu->initiatorInnen)
		);
		if ($this->antrag_alt->initiative_to_aufgenommen != $this->antrag_neu->initiative_to_aufgenommen) $felder[] = new HistorienEintragFeld(
			"TO Aufgenommen", CHtml::encode($this->antrag_alt->initiative_to_aufgenommen), CHtml::encode($this->antrag_neu->initiative_to_aufgenommen)
		);
		return $felder;
	}
}
