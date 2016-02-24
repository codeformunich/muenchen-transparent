<?php

namespace app\models;


interface IRISItemHasDocuments extends IRISItem
{
    /** @return Dokument[] */
    public function getDokumente();
}