<?php

interface IRISItem
{

    /**
     * @param array $add_params
     * @return string
     */
    public function getLink($add_params = []);

    /** @return string */
    public function getTypName();

    /** @return string */
    public function getDate();

    /**
     * @param bool $kurzfassung
     * @return string
     */
    public function getName($kurzfassung = false);
}
