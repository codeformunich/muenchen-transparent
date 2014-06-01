<?php

class Reindex_BA_GremiumCommand extends CConsoleCommand {
	public function run($args) {
		if (!isset($args[0]) || $args[0] <= 1) die("./yiic reindex_ba_gremium [Gremium-ID]\n");

		Gremium::parse_ba_gremien($args[0]);

	}
}