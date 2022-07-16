<?php

class Export_Stadtrat_VorlageCommand extends CConsoleCommand
{
    private function serializeAntrag(Antrag $antrag): array
    {
        $dokumente = [];
        foreach ($antrag->dokumente as $dokument) {
            $correctedText = preg_replace('/########## SEITE (\d+) ##########/siu', "\n", $dokument->text_ocr_corrected);
            $dokumente[] = [
                'id' => $dokument->id,
                'name' => $dokument->name,
                'datum' => $dokument->datum,
                'text' => $correctedText,
            ];
        }

        return [
            'id' => $antrag->id,
            'datum' => $antrag->gestellt_am,
            'nummer' => $antrag->antrags_nr,
            'referat' => $antrag->referat,
            'referent' => $antrag->referent,
            'betreff' => $antrag->betreff,
            'status' => $antrag->status,
            'dokumente' => $dokumente,
        ];
    }

    public function run($args)
    {
        if (count($args) == 0) die("./yii reindex_stadtrat_vorlage [Vorlagen-ID]\n");

        /** @var Antrag $a */
        $a = Antrag::model()->findByPk($args[0]);
        if (!$a) {
            die("Document not found");
        }
        if ($a->typ !== Antrag::TYP_STADTRAT_VORLAGE) {
            die("Document not of type " . Antrag::TYP_STADTRAT_VORLAGE);
        }

        echo json_encode($this->serializeAntrag($a), JSON_PRETTY_PRINT);
    }
}
