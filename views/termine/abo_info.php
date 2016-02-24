<?php
/**
 * @var Termin $termin
 */

?>


    <section class="well">
        <ul class="breadcrumb" style="margin-bottom: 5px;">
            <li><a href="<?= Html::encode(Yii::app()->createUrl("index/startseite")) ?>">Startseite</a><br></li>
            <li><a href="<?= Html::encode(Yii::app()->createUrl("termine/index")) ?>">Termine</a><br></li>
            <li><a href="<?= Html::encode(Yii::app()->createUrl("termine/anzeigen", ["termin_id" => $termin->id])) ?>">Termin</a><br></li>
            <li class="active">Export</li>
        </ul>

        <h1>Abonnieren / Exportieren</h1>

        <br><br>

        <h2>Adressen</h2>
        <table>
            <tr>
                <th>Einzeltermin (ICS):</th>
                <td><?
                    $link = Html::encode(Yii::app()->createUrl("termine/icsExportSingle", array("termin_id" => $termin->id)));
                    echo '<a href="' . $link . '">' . SITE_BASE_URL . $link . '</a>';
                    ?></td>
            </tr>
            <tr>
                <th>Terminreihe (ICS):</th>
                <td><?
                    $link = Html::encode(Yii::app()->createUrl("termine/icsExportAll", array("termin_id" => $termin->id)));
                    echo '<a href="' . $link . '" rel="nofollow">' . SITE_BASE_URL . $link . '</a>';
                    ?></td>
            </tr>
            <tr>
                <th>Terminreihe (CalDAV):</th>
                <td><?
                    $link = Html::encode(Yii::app()->createUrl("termine/dav", array("termin_id" => $termin->id)));
                    echo '<a href="' . $link . '" rel="nofollow">' . SITE_BASE_URL . $link . '</a>';
                    ?></td>
            </tr>
        </table>

        <br><br>

        <h2>Erklärung: Google Mail</h2>

        <br>@TODO<br><br><br>

        <h2>Erklärung: Apple Mail</h2>

        <br>@TODO<br><br><br>

        <h2>Erklärung: iPhone</h2>

        <br>@TODO<br><br><br>

        <h2>Erklärung: Android</h2>

        <br>@TODO<br><br><br>

        <h2>Erklärung: Windows Phone</h2>

        <br>@TODO<br><br><br>


    </section>


<?