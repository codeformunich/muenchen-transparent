<?php

declare(strict_types=1);

class GremienmitgliedschaftData
{
    public int $gremiumId;
    public string $gremiumName;
    public ?string $funktion = null;
    public ?DateTime $seit = null;
    public ?DateTime $bis = null;
    public ?int $wahlperiode = null;
    public ?int $baNr = null;
    public ?string $baName = null;

    public static function parseFromHtml(string $html): ?self
    {
        $entry = new self();

        if (!preg_match('/<a class="headline-link[^>]*href="[\.\/]*gremium\/detail\/(?<id>\d+)[^\d][^>]*>(?<name>[^<]*)<\/a>/siuU', $html, $matches)) {
            throw new ParsingException('Not found: title');
        }
        $entry->gremiumId = intval($matches['id']);
        $entry->gremiumName = $matches['name'];

        if (preg_match('/Zugehörigkeit:<\/div>\s*<div class="keyvalue-value">\s*' .
                        '(<div>)?(von|seit) (?<seit>\d+\.\d+\.\d+)( bis (?<bis>\d+\.\d+\.\d+))?\s*<\/div>/siuU', $html, $matches)) {
            $entry->seit = (\DateTime::createFromFormat('d.m.Y', $matches['seit']))->setTime(0, 0, 0);
            if (isset($matches['bis'])) {
                $entry->bis = (\DateTime::createFromFormat('d.m.Y', $matches['bis']))->setTime(0, 0, 0);
            }
        }

        if (preg_match('/Funktion:<\/div>\s*<div class="keyvalue-value">\s*<div>(?<funktion>[^<]*)\s*<\/div>/siuU', $html, $matches)) {
            $entry->funktion = $matches['funktion'];
        } elseif (preg_match('/Funktion<\/div>\s*<div class="keyvalue-value">(?<funktion>[^<]*)\s*<\/div>/siuU', $html, $matches)) {
            $entry->funktion = $matches['funktion'];
        }

        if (preg_match('/Wahlperiode:<\/div>\s*<div class="keyvalue-value">\s*(?<wahlperiode>[^<]*)\s*<\/div>/siuU', $html, $matches)) {
            $entry->wahlperiode = Wahlperioden::WAHLPERIODEN_BY_YEAR[intval($matches['wahlperiode'])];
        }

        if (preg_match('/<a class="headline-link text-keepwhitespace"[^>]*>(?<baNo>\d+) - (?<baName>[^<]*)<\/a>/siu', $html, $match)) {
            $entry->baNr = intval($match['baNo']);
            $entry->baName = $match['baName'];
        }

        return $entry;
    }

    /**
     * @param GremienmitgliedschaftData[] $mitgliedschaften
     */
    public static function setGremienmitgliedschaftenToPerson(StadtraetIn $person, array $mitgliedschaften, string $typ, ?int $baNr): void
    {
        $mitgliedschaftenNeu = [];
        foreach ($mitgliedschaften as $mitgliedschaft) {
            // The key should resemble the unique key contraint in the database
            $key = $mitgliedschaft->gremiumId . "-" . $mitgliedschaft->seit?->format('Y-m-d') . "-" . $mitgliedschaft->funktion;
            $mitgliedschaftenNeu[$key] = $mitgliedschaft;
        }

        $foundKeys = [];
        foreach ($person->mitgliedschaften as $mitgliedschaft) {
            $key = $mitgliedschaft->gremium_id . "-" . $mitgliedschaft->datum_von . "-" . $mitgliedschaft->funktion;
            if ($mitgliedschaft->gremium->gremientyp !== $typ) {
                continue;
            }
            if (isset($mitgliedschaftenNeu[$key])) {
                $bis = $mitgliedschaftenNeu[$key]->bis?->format('Y-m-d');
                if ($mitgliedschaft->datum_bis !== $bis) {
                    echo "Bis geändert: ". $mitgliedschaft->datum_bis . " => $bis\n";
                    $mitgliedschaft->datum_bis = $bis;
                    $mitgliedschaft->save();
                }
            } else {
                $mitgliedschaft->delete();
                echo "Removing: " . $key . "\n";
            }
            $foundKeys[] = $key;
        }

        foreach ($mitgliedschaftenNeu as $key => $mitgliedschaft) {
            if (in_array($key, $foundKeys)) {
                continue;
            }

            $gremium = Gremium::getOrCreate($mitgliedschaft->gremiumId, $mitgliedschaft->gremiumName, $typ, $baNr);
            echo "Creating: " . $key . "\n";

            $created = new StadtraetInGremium();
            $created->gremium_id = $gremium->id;
            $created->stadtraetIn_id = $person->id;
            $created->funktion = $mitgliedschaft->funktion;
            $created->datum_von = $mitgliedschaft->seit?->format('Y-m-d');
            $created->datum_bis = $mitgliedschaft->bis?->format('Y-m-d');
            $created->save();
        }

        $person->refresh();
    }
}
