<?php

declare(strict_types=1);

use PHPUnit\Framework\TestCase;
use Spatie\Snapshots\MatchesSnapshots;

class StadtraetInnenDataTest extends TestCase
{
    use MatchesSnapshots;

    public function testParseTest1()
    {
        $htmlFraktionen = file_get_contents(__DIR__ . '/data/StadtraetInnenParser_Test1_Mitgliedschaften.html');
        $htmlAusschuesse = file_get_contents(__DIR__ . '/data/StadtraetInnenParser_Test1_Ausschuesse.html');
        $data = StadtraetInnenData::parseFromHtml($htmlFraktionen, $htmlAusschuesse);

        $this->assertSame(3312434, $data->id);
        $this->assertSame('Kathrin Abele', $data->name);
        $this->assertSame('2014-03-16', $data->gewaehltAm->format('Y-m-d'));
        $this->assertSame('2014-05-01', $data->mandatSeit->format('Y-m-d'));
        $this->assertSame('https://risi.muenchen.de/risi/bild/6183892', $data->fotoUrl);

        $this->assertTrue(str_starts_with($data->lebenslauf, 'Geboren'));
        $this->assertTrue(str_ends_with($data->lebenslauf, 'Gesundheitsangebote für alle.'));

        $this->assertCount(2, $data->fraktionsMitgliedschaften);
        $this->assertCount(10, $data->ausschussMitgliedschaften);

        $this->assertSame(5986278, $data->fraktionsMitgliedschaften[0]->gremiumId);
        $this->assertSame('SPD / Volt - Fraktion', $data->fraktionsMitgliedschaften[0]->gremiumName);
        $this->assertSame('Mitglied', $data->fraktionsMitgliedschaften[0]->funktion);
        $this->assertSame('2020-05-01', $data->fraktionsMitgliedschaften[0]->seit->format('Y-m-d'));
        $this->assertNull($data->fraktionsMitgliedschaften[0]->bis);
        $this->assertSame(5666210, $data->fraktionsMitgliedschaften[0]->wahlperiode);

        $this->assertSame(31, $data->fraktionsMitgliedschaften[1]->gremiumId);
        $this->assertSame('SPD-Fraktion', $data->fraktionsMitgliedschaften[1]->gremiumName);
        $this->assertSame('Mitglied', $data->fraktionsMitgliedschaften[1]->funktion);
        $this->assertSame('2014-05-01', $data->fraktionsMitgliedschaften[1]->seit->format('Y-m-d'));
        $this->assertSame('2020-04-30', $data->fraktionsMitgliedschaften[1]->bis->format('Y-m-d'));
        $this->assertSame(3184778, $data->fraktionsMitgliedschaften[1]->wahlperiode);

        $this->assertSame(4, $data->ausschussMitgliedschaften[0]->gremiumId);
        $this->assertSame('Ausschuss für Stadtplanung und Bauordnung', $data->ausschussMitgliedschaften[0]->gremiumName);
        $this->assertSame('2020-05-13', $data->ausschussMitgliedschaften[0]->seit->format('Y-m-d'));
        $this->assertSame('Mitglied', $data->ausschussMitgliedschaften[0]->funktion);
        $this->assertNull($data->ausschussMitgliedschaften[0]->bis);
        $this->assertSame(5666210, $data->ausschussMitgliedschaften[0]->wahlperiode);
    }

    public function testParseTest2()
    {
        $htmlFraktionen = file_get_contents(__DIR__ . '/data/StadtraetInnenParser_Test2_Mitgliedschaften.html');
        $htmlAusschuesse = file_get_contents(__DIR__ . '/data/StadtraetInnenParser_Test1_Ausschuesse.html');
        $data = StadtraetInnenData::parseFromHtml($htmlFraktionen, $htmlAusschuesse);

        $this->assertSame(3312434, $data->id);
        $this->assertSame('Kathrin Abele', $data->name);
    }
}
