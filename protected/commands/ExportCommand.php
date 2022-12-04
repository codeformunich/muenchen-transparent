<?php

class ExportCommand extends CConsoleCommand
{
    private function serializeAntrag(int $antragId): ?array
    {
        $antrag = Antrag::model()->findByPk($antragId);
        return [
            'id' => $antrag->id,
            'gestellt_am' => $antrag->gestellt_am,
            'gestellt_von' => $antrag->gestellt_von,
            'initiative' => $antrag->initiatorInnen,
            'erledigt_am' => $antrag->erledigt_am,
            'registriert_am' => $antrag->registriert_am,
            'bearbeitungsfrist' => $antrag->bearbeitungsfrist,
            'datum' => $antrag->gestellt_am,
            'nummer' => $antrag->antrags_nr,
            'referat' => $antrag->referat,
            'referent' => $antrag->referent,
            'betreff' => $antrag->betreff,
            'typ' => $antrag->typ,
            'status' => $antrag->status,
        ];
    }

    private function serializeVorlage(int $antragId): ?array
    {
        $antrag = Antrag::model()->findByPk($antragId);
        return [
            'id' => $antrag->id,
            'datum' => $antrag->gestellt_am,
            'nummer' => $antrag->antrags_nr,
            'referat' => $antrag->referat,
            'referent' => $antrag->referent,
            'betreff' => $antrag->betreff,
            'status' => $antrag->status,
        ];
    }

    private function serializeTermin(int $terminId): ?array
    {
        $termin = Termin::model()->findByPk($terminId);

        $tops = array_map(function (Tagesordnungspunkt $top): array {
            $antrag_id = $vorlage_id = null;
            if ($top->antrag && $top->antrag->typ === Antrag::TYP_STADTRAT_ANTRAG) {
                $antrag_id = $top->antrag_id;
            }
            if ($top->antrag && $top->antrag->typ === Antrag::TYP_STADTRAT_VORLAGE) {
                $vorlage_id = $top->antrag_id;
            }

            return [
                'position' => $top->top_pos,
                'nummer' => $top->top_nr,
                'betreff' => $top->top_betreff,
                'antrag_id' => $antrag_id,
                'vorlage_id' => $vorlage_id,
                'geheim' => ($top->status === Tagesordnungspunkt::STATUS_NONPUBLIC),
            ];
        }, $termin->tagesordnungspunkte);

        return [
            'id' => $termin->id,
            'germium_id' => $termin->gremium_id,
            'germium_name' => $termin->gremium ? $termin->gremium->name : null,
            'datum' => $termin->termin,
            'ort' => $termin->sitzungsort,
            'tagesordnungspunkte' => $tops,
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
            case 'vorlage':
                $data = $this->serializeVorlage(intval($args[1]));
                break;
            case 'termin':
                $data = $this->serializeTermin(intval($args[1]));
                break;
        }
        if (!$data) {
            die("Document not found");
        }

        echo json_encode($data, JSON_PRETTY_PRINT);
    }
}
