<?php

interface IRISItemHasDocuments extends IRISItem
{
	/** @return AntragDokument[] */
	public function getDokumente();
}