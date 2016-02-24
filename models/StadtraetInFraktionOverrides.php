<?php

namespace app\models;


/*
 * Dieser Hack ist nötig, da nicht alle Fraktionen im RIS gelistst werden.
 */

class StadtraetInFraktionOverrides
{
    public static $BETROFFENE_FRAKTIONEN = [
        -126, // AGS BA9
        -38, // Grüne BA9
        -37, // SPD BA9
        -40, // FDP BA9
        -39, // FW BA9
        -36, // CSU BA9
        -102, // Fraktionslos, BA21
    ];


    /*
     * Keys beziehen sich auf StadtraetIn-ID
     * Z.B. AGS - Arbeitsgemeinschaft für ein soziales Neuhausen-Nymphenburg
     */
    public static $FRAKTION_ADD = [

        // AGS - Arbeitsgemeinschaft für ein soziales Neuhausen-Nymphenburg
        // Hinweis von von Anna Hanusch (BA-Vorsitzende) per Feedback-Formular am 4.2.2015
        514     => [
            [
                "fraktion_id" => -126,
                "datum_von"   => "2014-09-01", // ungefähr
                "datum_bis"   => null,
                "wahlperiode" => "2014-2020",
            ]
        ],
        526     => [
            [
                "fraktion_id" => -126,
                "datum_von"   => "2014-09-01", // ungefähr
                "datum_bis"   => null,
                "wahlperiode" => "2014-2020",
            ]
        ],
        528     => [
            [
                "fraktion_id" => -126,
                "datum_von"   => "2014-09-01", // ungefähr
                "datum_bis"   => null,
                "wahlperiode" => "2014-2020",
            ]
        ],

        // SPD
        // Hinweis von von Anna Hanusch (BA-Vorsitzende) per Feedback-Formular am 4.2.2015
        1431217 => [
            [
                "fraktion_id" => -37,
                "datum_von"   => "2014-05-01",
                "datum_bis"   => null,
                "wahlperiode" => "2014-2020",
            ]
        ],
        1431198 => [
            [
                "fraktion_id" => -39,
                "datum_von"   => "2014-05-01",
                "datum_bis"   => null,
                "wahlperiode" => "2014-2020",
            ]
        ],
        1431229 => [
            [
                "fraktion_id" => -36,
                "datum_von"   => "2014-05-01",
                "datum_bis"   => null,
                "wahlperiode" => "2014-2020",
            ]

        ],
    ];

    public static $FRAKTION_DEL = [
        // Hinweis von von Anna Hanusch (BA-Vorsitzende) per Feedback-Formular am 4.2.2015
        1431217 => [
            [
                "fraktion_id" => -38,
                "datum_von"   => "2014-05-01",
            ]
        ],
        1431198 => [
            [
                "fraktion_id" => -40,
                "datum_von"   => "2014-05-01",
            ]
        ],
        1431229 => [
            [
                "fraktion_id" => -37,
                "datum_von"   => "2014-05-01",
            ]
        ],
        3310431 => [
            [
                "fraktion_id" => -102,
                "datum_von"   => "2014-05-01",
            ]
        ],
    ];
}