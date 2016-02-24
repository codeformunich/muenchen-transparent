<?php
/**
 * @var $this IndexController
 * @var $error array
 * @var string $code
 * @var string $message
 * @var BenutzerIn $ich
 */

$this->pageTitle = "Benachrichtigungen";

$bens                 = $ich->getBenachrichtigungen();
$abo_vorgaenge        = $ich->abonnierte_vorgaenge;
$benachrichtigungstag = $ich->getEinstellungen()->benachrichtigungstag;
?>

<section class="well">

    <form style="float: right;" method="POST" action="<?= Html::encode($this->createUrl("index/startseite")) ?>">
        <button type="submit" name="<?= AntiXSS::createToken("abmelden") ?>" class="btn btn-default">Abmelden</button>
    </form>

    <h1>Benachrichtigung<? if (count($bens) + count($abo_vorgaenge) != 1) echo "en"; ?> an <?= Html::encode($ich->email) ?>:</h1>

    <div class="row">
        <form method="POST" action="<?= Html::encode($this->createUrl("index/benachrichtigungen")) ?>" class="col col-lg-8 einstellungen_form" style="margin-left: 23px;">
            <h3>Ich möchte benachrichtigt werden...</h3>

            <div>
                <div class="radio radio-success">
                    <label>
                        <input type="radio" name="intervall" value="tag" <? if ($benachrichtigungstag === null) echo "checked"; ?>>
                        Täglich
                    </label>
                </div>
                <div class="radio radio-success">
                    <label>
                        <input type="radio" name="intervall" value="woche" <? if ($benachrichtigungstag !== null) echo "checked"; ?>>
                        Wöchentlich
                    </label>
                </div>
                <div class="tage_auswahl" style="margin-left: 40px;">
                    <div class="radio radio-info">
                        <label>
                            <input type="radio" name="wochentag" value="1" <? if ($benachrichtigungstag === 1) echo "checked"; ?>>
                            Montags
                        </label>
                    </div>
                    <div class="radio radio-info">
                        <label>
                            <input type="radio" name="wochentag" value="2" <? if ($benachrichtigungstag === 2) echo "checked"; ?>>
                            Dienstags
                        </label>
                    </div>
                    <div class="radio radio-info">
                        <label>
                            <input type="radio" name="wochentag" value="3" <? if ($benachrichtigungstag === 3) echo "checked"; ?>>
                            Mittwochs
                        </label>
                    </div>
                    <div class="radio radio-info">
                        <label>
                            <input type="radio" name="wochentag" value="4" <? if ($benachrichtigungstag === 4) echo "checked"; ?>>
                            Donnerstags
                        </label>
                    </div>
                    <div class="radio radio-info">
                        <label>
                            <input type="radio" name="wochentag" value="5" <? if ($benachrichtigungstag === 5) echo "checked"; ?>>
                            Freitags
                        </label>
                    </div>
                    <div class="radio radio-info">
                        <label>
                            <input type="radio" name="wochentag" value="6" <? if ($benachrichtigungstag === 6) echo "checked"; ?>>
                            Samstags
                        </label>
                    </div>
                    <div class="radio radio-info">
                        <label>
                            <input type="radio" name="wochentag" value="0" <? if ($benachrichtigungstag === 0) echo "checked"; ?>>
                            Sonntags
                        </label>
                    </div>

                </div>
            </div>

            <div style="text-align: center;">
                <button class="btn btn-primary" name="<?= AntiXSS::createToken("einstellungen_speichern") ?>" type="submit">Speichern</button>
            </div>
            <script>
                $(function () {
                    var $form = $(".einstellungen_form");
                    $form.find("button[type=submit]").hide();
                    $form.find(".tage_auswahl").hide();
                    $form.find("input[type=radio]").change(function (ev, data) {
                        if (!data || !data.init) $form.find("button[type=submit]").show();
                        if ($form.find("input[name=intervall][value=woche]").prop("checked")) {
                            $form.find(".tage_auswahl").show();
                        } else {
                            $form.find(".tage_auswahl").hide();
                        }
                    }).trigger("change", {"init": true});
                })
            </script>
        </form>
    </div>


    <?

    if (count($bens) == 0 && count($abo_vorgaenge) == 0) {
        ?>
        <div class="benachrichtigung_keine">Noch keine E-Mail-Benachrichtigungen</div>
        <p class="benachrichtigung_keine">Wenn neue Dokumente zu einem von Ihnen abonnierten Thema oder Vorgang veröffentlicht werden, dann erhalten Sie automatisch eine
            Benachrichtigung an <?= Html::encode($ich->email) ?>.</p>
    <?
    } else {
        ?>
        <div class="row">
            <form method="POST" action="<?= Html::encode($this->createUrl("index/benachrichtigungen")) ?>" class="col col-lg-8" style="margin-left: 23px;">
                <? if (count($bens) > 0) { ?>
                    <h3>Abonnierte Suchabfragen</h3>
                    <ul class="benachrichtigungsliste">
                        <li class="header">
                            <div class="del_holder">Löschen</div>
                            <div class="krit_holder">Suchkriterium</div>
                            <div class="such_holder">Suchen</div>
                        </li>
                        <?
                        foreach ($bens as $ben) {
                            $del_form_name = AntiXSS::createToken("del_ben") . "[" . RISTools::bracketEscape(CHtml::encode(json_encode($ben->krits))) . "]";
                            $such_url      = $ben->getUrl();
                            ?>
                            <li>
                                <div class='del_holder'>
                                    <button type='submit' class='del' name='<?= $del_form_name ?>'><span class='glyphicon glyphicon-minus-sign'></span></button>
                                </div>
                                <div class='krit_holder'><?= $ben->getTitle() ?></div>
                                <div class='such_holder'><a href='<?= RISTools::bracketEscape(CHtml::encode($ben->getUrl())) ?>'><span
                                            class='glyphicon glyphicon-search'></span></a>
                                </div>
                            </li>
                        <?
                        }
                        ?>
                    </ul>
                <?
                }
                if (count($abo_vorgaenge) > 0) {
                    ?>
                    <h3>Abonnierte Anträge / Vorgänge</h3>
                    <ul class="benachrichtigungsliste">
                        <li class="header">
                            <div class="del_holder">Löschen</div>
                            <div class="krit_holder">Vorgang</div>
                        </li>
                        <?
                        foreach ($abo_vorgaenge as $vorgang) {
                            $item = $vorgang->wichtigstesRisItem();
                            if (!$item) continue;
                            $del_form_name = AntiXSS::createToken("del_vorgang_abo") . "[" . $vorgang->id . "]";
                            ?>
                            <li>
                                <div class='del_holder'>
                                    <button type='submit' class='del' name='<?= $del_form_name ?>'><span class='glyphicon glyphicon-minus-sign'></span></button>
                                </div>
                                <div class='krit_holder'>
                                    <a href="<?= Html::encode($item->getLink()) ?>"><span class="fontello-right-open"></span> <?= Html::encode($item->getName()) ?></a>
                                </div>
                            </li>
                        <?
                        }
                        ?>
                    </ul>
                <? } ?>
            </form>

        </div>
        <? if (count($bens) > 0) { ?>
            <div class="row">
                <div class="ben_alle_holder col col-lg-8">
                    <a href="<?= Html::encode($this->createUrl("benachrichtigungen/alleSuchergebnisse")) ?>" class="ben_alle_suche"><span
                            class="glyphicon glyphicon-chevron-right"></span>
                        Alle Suchergebnisse</a>
                    <a href="<?= Html::encode($this->createUrl("benachrichtigungen/alleFeed", ["code" => $ich->getFeedCode()])) ?>" class="ben_alle_feed"><span
                            class="fontello-rss"></span>
                        Alle Suchergebnisse als Feed</a>
                </div>
            </div>
        <? } ?>
        <br style="clear: both;">
    <? } ?>

    <form method="POST" action="<?= Html::encode($this->createUrl("index/benachrichtigungen")) ?>" class="benachrichtigung_add">
        <fieldset>
            <legend>Benachrichtige mich bei neuen Dokumenten...</legend>

            <label for="suchbegriff"><span class="glyphicon glyphicon-search"></span> <span class="name">... mit diesem Suchbegriff:</span></label><br>

            <div class="input-group col col-lg-8" style="padding-left: 10px; padding-right: 10px; margin-left: 23px;">
                <input type="text" placeholder="Suchbegriff" id="suchbegriff" name="suchbegriff" class="form-control">
            <span class="input-group-btn">
                <button class="btn btn-primary" name="<?= AntiXSS::createToken("ben_add_text") ?>" type="submit">Benachrichtigen!</button>
            </span>
            </div>
        </fieldset>
    </form>

    <form method="POST" action="<?= Html::encode($this->createUrl("index/benachrichtigungen")) ?>" class="benachrichtigung_add">
        <fieldset>
            <label for="suchbegriff"><span class="glyphicon glyphicon-map-marker"></span> <span class="name">... aus diesem Stadtteil:</span></label><br>

            <div class="input-group col col-lg-8" style="padding-left: 10px; padding-right: 10px; margin-left: 23px;">
                <select name="ba" class="form-control"><?
                    $bas = Bezirksausschuss::model()->findAll();
                    /** @var Bezirksausschuss $ba */
                    foreach ($bas as $ba) echo '<option value="' . $ba->ba_nr . '">BA ' . $ba->ba_nr . ": " . Html::encode($ba->name) . '</option>';
                    ?>
                </select>
            <span class="input-group-btn">
                <button class="btn btn-primary" name="<?= AntiXSS::createToken("ben_add_ba") ?>" type="submit">Benachrichtigen!</button>
            </span>
                <!--
            <input type="text" placeholder="Suchbegriff" id="suchbegriff" name="suchbegriff" class="form-control">
            <span class="input-group-btn">
                <button class="btn btn-primary" name="<?= AntiXSS::createToken("ben_add_text") ?>" type="submit">Benachrichtigen!</button>
            </span>
            -->
            </div>
        </fieldset>
    </form>


    <form method="POST" action="<?= Html::encode($this->createUrl("index/benachrichtigungen")) ?>" class="benachrichtigung_add" id="benachrichtigung_add_geo_form">
        <fieldset>
            <label for="geo_radius"><span class="glyphicon glyphicon-map-marker"></span> <span class="name">... mit diesem Ortsbezug:</span></label>

            <br style="clear: both;">

            <div class="input-group col col-lg-8" style="padding: 10px; margin-left: 23px;">
                <div id="ben_mapholder">
                    <div id="ben_map"></div>
                </div>

                <hr style="margin-top: 10px; margin-bottom: 10px;">

                <div id="benachrichtigung_hinweis_text" class="input-group">
                    <input type="hidden" name="geo_lng" value="">
                    <input type="hidden" name="geo_lat" value="">
                    <input type="hidden" name="geo_radius" value="">
                    <input type="text" placeholder="noch kein Ort ausgewählt" id="ort_auswahl" class="form-control" disabled>
                <span class="input-group-btn">
                    <button class="btn btn-primary ben_add_geo" disabled name="<?= AntiXSS::createToken("ben_add_geo") ?>" type="submit">Benachrichtigen!</button>
                </span>
                </div>
            </div>

        </fieldset>

        <script>
            $(function () {
                var $ben_holder = $("#benachrichtigung_hinweis_text");
                $("#ben_map").AntraegeKarte({
                    benachrichtigungen_widget: true,
                    show_BAs: false,
                    benachrichtigungen_widget_zoom: 9,
                    size: 11,
                    onSelect: function (latlng, rad) {
                        $.ajax({
                            "url": "<?=CHtml::encode($this->createUrl("index/geo2Address"))?>?lng=" + latlng.lng + "&lat=" + latlng.lat,
                            "success": function (ret) {
                                $("#benachrichtigung_hinweis_text").find("input[type=text]").val("Etwa " + parseInt(rad) + "m um " + ret["ort_name"]);
                                $(".ben_add_geo").prop("disabled", false);

                            }
                        });
                        $ben_holder.find("input[name=geo_lng]").val(latlng.lng);
                        $ben_holder.find("input[name=geo_lat]").val(latlng.lat);
                        $ben_holder.find("input[name=geo_radius]").val(rad);
                    }
                });
            });
        </script>

    </form>

    <br><br>
    <h3>Andere Aktionen</h3>

    <button class="btn btn-danger" data-toggle="modal" data-target="#passwortaendernmodal">Passwort Ändern</button>

    <div class="modal fade" id="passwortaendernmodal" tabindex="-1" role="dialog" aria-labelledby="passwortaendernmodal" aria-hidden="true">
      <div class="modal-dialog">
        <form class="form-horizontal form-signin" method="POST" action="<?= $this->createUrl("/benachrichtigungen/index") ?>">
          <fieldset>
            <div class="modal-content">
              <div class="modal-header">
                <button type="button" class="close" data-dismiss="modal" aria-label="Close"><span aria-hidden="true">&times;</span></button>
              </div>
              <div class="modal-body">
                <legend class="form_row">Neues Passwort setzen</legend>

                <div class="form_row">
                  <label for="password" class="control-label sr-only">Passwort</label>
                  <input id="password" name="password" type="password" class="form-control" placeholder="Passwort" required autofocus>
                </div>
                <div class="form_row">
                  <label for="password2" class="control-label sr-only">Passwort bestätigen</label>
                  <input id="password2" name="password2" type="password" class="form-control" placeholder="Passwort bestätigen" required>
                </div>
              </div>
              <div class="modal-footer">
                <button class="btn btn-primary btn-raised" style="float: right"  type="submit" name="<?php echo AntiXSS::createToken("passwort_aendern"); ?>">Setzen</button>
                <button class="btn btn-default btn-raised" style="margin-right: 5px" data-dismiss="modal">Abbrechen</button>
              </div>
            </div>
          </fieldset>
        </form>
      </div>
    </div>


    <button class="btn btn-danger" data-toggle="modal" data-target="#accountloeschenmodal">Account Löschen</button>

    <div class="modal fade" id="accountloeschenmodal" tabindex="-1" role="dialog" aria-labelledby="accountloeschenmodal" aria-hidden="true">
      <div class="modal-dialog">
        <div class="modal-content">
          <div class="modal-header">
            <button type="button" class="close" data-dismiss="modal" aria-label="Close"><span aria-hidden="true">&times;</span></button>
            <h3 class="modal-title">Account Löschen</h3>
          </div>
          <div class="modal-body">
            <p class="benachrichtigung_keine" >Willst du deinen Account wirklich unwiderruflich löschen?</p>
          </div>
          <div class="modal-footer">

            <form class="form-horizontal form-signin" method="POST" action="<?= $this->createUrl("/benachrichtigungen/index") ?>">
              <button class="btn btn-danger btn-raised" style="float: right" name="<?= AntiXSS::createToken("account_loeschen") ?>" type="submit">Account Löschen!</button>
            </form>

            <button class="btn btn-default btn-raised" style="margin-right: 5px" data-dismiss="modal">Abbrechen</button>

          </div>
        </div>
      </div>
    </div>

</section>
