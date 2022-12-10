<?php

class ExportCommand extends CConsoleCommand
{
    private function serializeDokument(Dokument $dokument): ?array
    {
        return [
            'id' => intval($dokument->id),
            'name' => $dokument->getName(true),
            'datum' => $dokument->datum_dokument,
            'url' => $dokument->getLinkZumOrginal(),
        ];
    }

    private function serializeStadtraetin(int $stadtraetIn): ?array
    {
        $stadtraetIn = StadtraetIn::model()->findByPk($stadtraetIn);
        return [
            'id' => intval($stadtraetIn->id),
            'name' => $stadtraetIn->getName(false),
            'mitgliedschaften' => array_map(function (StadtraetInGremium $gremium): array {
                return [
                    'von' => $gremium->datum_von,
                    'bis' => $gremium->datum_bis,
                    'ba_nr' => $gremium->gremium->ba_nr,
                    'gremium_id' => intval($gremium->gremium_id),
                    'gremium_name' => $gremium->gremium->getName(false),
                ];
            }, $stadtraetIn->mitgliedschaften),
        ];
    }

    private function serializeAntrag(int $antragId): ?array
    {
        $antrag = Antrag::model()->findByPk($antragId);
        return [
            'id' => intval($antrag->id),
            'gestellt_am' => $antrag->gestellt_am,
            'gestellt_von' => $antrag->gestellt_von,
            'initiative' => $antrag->initiatorInnen,
            'personen' => array_map(function (StadtraetIn $str) { return intval($str->id); }, $antrag->stadtraetInnen),
            'erledigt_am' => $antrag->erledigt_am,
            'registriert_am' => $antrag->registriert_am,
            'bearbeitungsfrist' => $antrag->bearbeitungsfrist,
            'datum' => $antrag->gestellt_am,
            'nummer' => $antrag->antrags_nr,
            'referat' => $antrag->referat,
            'referat_id' => intval($antrag->referat_id),
            'referent' => $antrag->referent,
            'betreff' => $antrag->betreff,
            'typ' => $antrag->typ,
            'status' => $antrag->status,
            'dokumente' => array_map(['ExportCommand', 'serializeDokument'], $antrag->getDokumente()),
        ];
    }

    private function serializeVorlage(int $vorlageId): ?array
    {
        $vorlage = Antrag::model()->findByPk($vorlageId);

        $antraege = [];
        foreach ($vorlage->vorlage2antraege as $antrag) {
            $antraege[] = intval($antrag->id);
        }

        return [
            'id' => intval($vorlage->id),
            'datum' => $vorlage->gestellt_am,
            'nummer' => $vorlage->antrags_nr,
            'referat' => $vorlage->referat,
            'referat_id' => intval($vorlage->referat_id),
            'referent' => $vorlage->referent,
            'betreff' => $vorlage->betreff,
            'status' => $vorlage->status,
            'antraege' => $antraege,
            'dokumente' => array_map(['ExportCommand', 'serializeDokument'], $vorlage->getDokumente()),
        ];
    }

    private function serializeTermin(int $terminId): ?array
    {
        $termin = Termin::model()->findByPk($terminId);

        $tops = array_map(function (Tagesordnungspunkt $top): array {
            $vorlage_id = null;
            if ($top->antrag && $top->antrag->typ === Antrag::TYP_STADTRAT_VORLAGE) {
                $vorlage_id = $top->antrag_id;
            }

            return [
                'position' => $top->top_pos,
                'nummer' => $top->top_nr,
                'betreff' => $top->top_betreff,
                'vorlage_id' => $vorlage_id,
                'geheim' => ($top->status === Tagesordnungspunkt::STATUS_NONPUBLIC),
            ];
        }, $termin->tagesordnungspunkte);

        return [
            'id' => intval($termin->id),
            'germium_id' => intval($termin->gremium_id),
            'germium_name' => $termin->gremium ? $termin->gremium->name : null,
            'datum' => $termin->termin,
            'ort' => $termin->sitzungsort,
            'tagesordnungspunkte' => $tops,
        ];
    }

    private function serializeGremium(int $gremiumId): ?array
    {
        $gremium = Gremium::model()->findByPk($gremiumId);

        return [
            'id' => intval($gremium->id),
            'name' => $gremium->getName(),
            'ba_nr' => $gremium->ba_nr,
            'typ' => $gremium->gremientyp,
        ];
    }

    public function run($args)
    {
        if (count($args) < 2) die("./yii export [antrag|vorlage|termin] [ID]\n");

        $data = null;
        switch ($args[0]) {
            case 'antrag':
                $data = $this->serializeAntrag(intval($args[1]));
                break;
            case 'person':
                $data = $this->serializeStadtraetin(intval($args[1]));
                break;
            case 'vorlage':
                $data = $this->serializeVorlage(intval($args[1]));
                break;
            case 'termin':
                $data = $this->serializeTermin(intval($args[1]));
                break;
            case 'gremium':
                $data = $this->serializeGremium(intval($args[1]));
                break;
        }
        if (!$data) {
            die("Document not found");
        }

        echo json_encode($data, JSON_PRETTY_PRINT);
    }
}
