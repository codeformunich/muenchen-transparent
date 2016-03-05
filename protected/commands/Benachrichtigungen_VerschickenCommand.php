<?php

class Benachrichtigungen_VerschickenCommand extends CConsoleCommand
{
    /**
     * @param BenutzerIn $benutzerIn
     * @param array      $data
     *
     * @throws Exception
     *
     * @return string
     */
    private function verschickeNeueBenachrichtigungen_txt(&$benutzerIn, $data)
    {
        $path = Yii::getPathOfAlias('application.views.benachrichtigungen').'/suchergebnisse_email_txt.php';
        if (!file_exists($path)) throw new Exception('Template '.$path.' does not exist.');
        ob_start();
        ob_implicit_flush(false);
        require $path;

        return ob_get_clean();

    }

    /**
     * @param BenutzerIn $benutzerIn
     * @param array      $data
     *
     * @throws Exception
     *
     * @return string
     */
    private function verschickeNeueBenachrichtigungen_html($benutzerIn, $data)
    {
        $path = Yii::getPathOfAlias('application.views.benachrichtigungen').'/suchergebnisse_email_html.php';
        if (!file_exists($path)) throw new Exception('Template '.$path.' does not exist.');
        ob_start();
        ob_implicit_flush(false);
        require $path;

        return ob_get_clean();
    }

    /**
     * @param BenutzerIn $benutzerIn
     * @param int        $zeitspanne
     */
    private function benachrichtigeBenutzerIn($benutzerIn, $zeitspanne = 0)
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
            /* @var BenutzerIn $benutzerIn */
            $this->benachrichtigeBenutzerIn($benutzerIn, $args[1]);
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
