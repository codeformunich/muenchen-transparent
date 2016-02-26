<?php
/**
 * @var AntraegeController
 * @var StadtraetIn[]      $stadtraetInnen,
 * @var Person[]           $personen
 */
?>
<section class="well">

<h1>StadträtInnen/Personen-Verknüpfung</h1>

<form method="POST" style="overflow: auto;">
    <div style="float: left; width: 400px;">
        <? foreach ($personen as $person) {
            echo "<label ";
            if (!is_null($person->stadtraetIn) || $person->typ == Person::$TYP_FRAKTION) echo "style='color: gray;';";
            echo "><input type='radio' name='person' value='" . $person->id . "'> " . CHtml::encode($person->name);
            if (!is_null($person->stadtraetIn)) echo " ( => " . CHtml::encode($person->stadtraetIn->name) . ")";
            if ($person->typ == Person::$TYP_FRAKTION) echo " (Fraktion)";
            echo "</label><br>\n";
        } ?>
    </div>

    <div style="float: left; width: 400px;">
        <label><input type="checkbox" name="fraktion"> Als Fraktion markieren</label><br><br>
        <? foreach ($stadtraetInnen as $stadtraetIn) {
            echo "<label><input type='radio' name='stadtraetIn' value='" . $stadtraetIn->id . "'>";
            $name = $stadtraetIn->name;
            $frakts = array();
            foreach ($stadtraetIn->stadtraetInnenFraktionen as $fr) {
                $ba = ($fr->fraktion->ba_nr > 0 ? "BA " . $fr->fraktion->ba_nr : "StR");
                $frakts[] = "$ba: " . $fr->fraktion->name;
            }
            if (count($frakts) > 0) $name .= " (" . implode(", ", $frakts) . ")";
            echo CHtml::encode($name);
            echo "</label><br>\n";
        } ?>
    </div>

    <div style="position: fixed; bottom: 0; left: 45%;">
        <button type="submit" class="btn btn-primary" name="<?=AntiXSS::createToken("save")?>">Speichern</button>
    </div>
</form>
</section>
