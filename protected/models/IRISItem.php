<?php

interface IRISItem
{

	/** @return string */
	public function getLink();

	/** @return string */
	public function getTypName();

	/**
	 * @param bool $langfassung
	 * @return string
	 */
	public function getName($langfassung = false);
}