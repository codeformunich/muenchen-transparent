<?php

declare(strict_types=1);

use PHPUnit\Framework\TestCase;
use Spatie\Snapshots\MatchesSnapshots;

class BAMitgliederDataTest extends TestCase
{
    use MatchesSnapshots;

    public function testParseTest1()
    {
        $htmlMitgliedschaft = file_get_contents(__DIR__ . '/data/BAMitgliederParser_Test1_BA.html');
        $htmlFraktionen = file_get_contents(__DIR__ . '/data/BAMitgliederParser_Test1_Fraktionen.html');
        $htmlAusschuesse = file_get_contents(__DIR__ . '/data/BAMitgliederParser_Test1_Ausschuesse.html');
        $data = BAMitgliederData::parseFromHtml($htmlMitgliedschaft, $htmlFraktionen, $htmlAusschuesse, null);

        $this->assertSame(954, $data->id);
        $this->assertSame('Josef Mögele', $data->name);

        $this->assertCount(3, $data->fraktionsMitgliedschaften);
        $this->assertCount(5, $data->baAusschuesse);
        $this->assertCount(4, $data->baMitgliedschaften);

        $this->assertSame(6038229, $data->fraktionsMitgliedschaften[0]->gremiumId);
        $this->assertSame('SPD', $data->fraktionsMitgliedschaften[0]->gremiumName);
        $this->assertSame('Fraktions-Mitglied', $data->fraktionsMitgliedschaften[0]->funktion);
        $this->assertSame('2020-05-01', $data->fraktionsMitgliedschaften[0]->seit->format('Y-m-d'));
        $this->assertNull($data->fraktionsMitgliedschaften[0]->bis);
        $this->assertSame(5666210, $data->fraktionsMitgliedschaften[0]->wahlperiode);

        $this->assertSame(379, $data->baAusschuesse[0]->gremiumId);
        $this->assertSame('BA 25 - Vollgremium', $data->baAusschuesse[0]->gremiumName);
        $this->assertSame('2020-05-12', $data->baAusschuesse[0]->seit->format('Y-m-d'));
        $this->assertSame('Vorsitzende/r', $data->baAusschuesse[0]->funktion);
        $this->assertNull($data->baAusschuesse[0]->bis);
        $this->assertSame(5666210, $data->baAusschuesse[0]->wahlperiode);

        $this->assertSame(234, $data->baMitgliedschaften[0]->gremiumId);
        $this->assertSame(25, $data->baMitgliedschaften[0]->baNr);
        $this->assertSame('25 - Laim', $data->baMitgliedschaften[0]->gremiumName);
        $this->assertSame('2020-05-12', $data->baMitgliedschaften[0]->seit->format('Y-m-d'));
        $this->assertSame('Vorsitzende/r', $data->baMitgliedschaften[0]->funktion);
        $this->assertNull($data->baMitgliedschaften[0]->bis);
        $this->assertSame(5666210, $data->baMitgliedschaften[0]->wahlperiode);
    }
}