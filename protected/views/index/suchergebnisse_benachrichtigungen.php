<?php

/**
 * @var RISBaseController $this
 * @var RISSucheKrits $krits
 * @var bool $email_bestaetigt
 * @var bool $email_angegeben
 * @var bool $eingeloggt
 * @var bool $wird_benachrichtigt
 * @var BenutzerIn $ich
 */

?>
<form method="POST" action="<?= CHtml::encode(Yii::app()->createUrl("index/suche")) ?>" id="login_benachrichtigung_form">
    <?
    $krits = $krits->getUrlArray();
    for ($i = 0; $i < count($krits["krit_typ"]); $i++) {
        echo '<input type="hidden" name="krit_typ[]" value="' . CHtml::encode($krits["krit_typ"][$i]) . '">' . "\n";
        echo '<input type="hidden" name="krit_val[]" value="' . CHtml::encode($krits["krit_val"][$i]) . '">' . "\n";
    }

    if ($eingeloggt) {
        if ($wird_benachrichtigt) {
            ?>
            <div class="button_hover_change">
                <button type="submit" name="<?= AntiXSS::createToken("benachrichtigung_del") ?>" class="btn btn-success btn-nohover benachrichtigung_std_button" style="width: 250px;">
                    <span class="glyphicon glyphicon-ok"></span> Du wirst benachrichtigt
                </button>
                <button type="submit" name="<?= AntiXSS::createToken("benachrichtigung_del") ?>" class="btn btn-primary btn-hover benachrichtigung_std_button" style="width: 250px;">
                    Nicht mehr benachrichtigen!
                </button>
            </div>
            <?
        } else {
            ?>
            <button type="submit" name="<?= AntiXSS::createToken("benachrichtigung_add") ?>" class="btn btn-default benachrichtigung_std_button">
                <span class="glyphicon">@</span> Über neue Treffer benachrichtigen
            </button>
            <?
        }
    } else {
        ?>
        <button type="button" class="btn btn-default benachrichtigung_std_button" data-toggle="modal" data-target="#benachrichtigung_login">
            <span class="glyphicon">@</span> Über neue Treffer benachrichtigen
        </button>
        <input type="hidden" name="<?= AntiXSS::createToken("benachrichtigung_add") ?>" value="1">
        <?
        $this->renderPartial("login_modal");
    }
    ?>
</form>
