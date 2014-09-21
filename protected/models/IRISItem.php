<?php

interface IRISItem
{

	/** @return string */
	public function getLink();

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