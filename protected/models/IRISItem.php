<?php

interface IRISItem
{

    public function getLink(array $add_params = []): string;

    public function getTypName(): string;

    public function getDate(): string;

    public function getName(bool $kurzfassung = false): string;
}
