<?php

interface IRISItemHasDocuments extends IRISItem
{
    /** @return Dokument[] */
    public function getDokumente();
}
