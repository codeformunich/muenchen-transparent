<?php

class Benachrichtigungen_VerschickenCommand extends CConsoleCommand
{
    private function verschickeNeueBenachrichtigungen_txt(BenutzerIn $benutzerIn, array $data): string
    {
        $path = Yii::getPathOfAlias('application.views.benachrichtigungen') . '/suchergebnisse_email_txt.php';
        if (!file_exists($path)) throw new Exception('Template ' . $path . ' does not exist.');
        ob_start();
        ob_implicit_flush(false);
        require($path);
        return ob_get_clean();

    }

    private function verschickeNeueBenachrichtigungen_html(BenutzerIn $benutzerIn, array $data): string
    {
        $path = Yii::getPathOfAlias('application.views.benachrichtigungen') . '/suchergebnisse_email_html.php';
        if (!file_exists($path)) throw new Exception('Template ' . $path . ' does not exist.');
        ob_start();
        ob_implicit_flush(false);
        require($path);
        return ob_get_clean();
    }

    private function benachrichtigeBenutzerIn(BenutzerIn $benutzerIn, int $zeitspanne = 0): void
    {
        $ergebnisse = $benutzerIn->benachrichtigungsErgebnisse($zeitspanne);

        if (count($ergebnisse["antraege"]) == 0 && count($ergebnisse["termine"]) == 0 && count($ergebnisse["vorgaenge"]) == 0) return;

        $mail_txt  = $this->verschickeNeueBenachrichtigungen_txt($benutzerIn, $ergebnisse);
        $mail_html = $this->verschickeNeueBenachrichtigungen_html($benutzerIn, $ergebnisse);
        RISTools::send_email($benutzerIn->email, "Neues auf München Transparent", $mail_txt, $mail_html, "newsletter");

        $benutzerIn->datum_letzte_benachrichtigung = new CDbExpression("NOW()");
        $benutzerIn->save();
    }

    public function run($args)
    {
        if (count($args) == 1) die("./yiic benachrichtigungen_verschicken [e@mail tage]\n");

        if (count($args) >= 2) {
            if (is_numeric($args[0])) {
                $benutzerIn = BenutzerIn::model()->findByPk($args[0]);
            } else {
                $benutzerIn = BenutzerIn::model()->findByAttributes(["email" => $args[0]]);
            }
            if (!$benutzerIn) die("BenutzerIn nicht gefunden.\n");
            /** @var BenutzerIn $benutzerIn */
            $this->benachrichtigeBenutzerIn($benutzerIn, intval($args[1]));
        } else {
            $benutzerInnen = BenutzerIn::heuteZuBenachrichtigendeBenutzerInnen();
            foreach ($benutzerInnen as $benutzerIn) try {
                $this->benachrichtigeBenutzerIn($benutzerIn);
            } catch (Exception $e) {
                var_dump($e);
            }
        }
    }
}
