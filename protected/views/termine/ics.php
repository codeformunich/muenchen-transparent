<?php
/**
 * @var Termin $termin
 */

/** @var Termin[] $alle_termine */
$alle_termine = array();

/**
 * @param Termin[] $alle_termine
 * @param Termin $termin
 */
function termine_add(&$alle_termine, $termin)
{
	if (isset($alle_termine[$termin->id])) return;
	$alle_termine[$termin->id] = $termin;
	if ($termin->termin_next_id > 0) {
		$next = Termin::model()->findByPk($termin->termin_next_id);
		if ($next) termine_add($alle_termine, $next);
	}
	if ($termin->termin_prev_id > 0) {
		$prev = Termin::model()->findByPk($termin->termin_prev_id);
		if ($prev) termine_add($alle_termine, $prev);
	}
}

termine_add($alle_termine, $termin);
usort($alle_termine, function ($termin1, $termin2) {
	/** @var Termin $termin1 */
	/** @var Termin $termin2 */
	$ts1 = RISTools::date_iso2timestamp($termin1->termin);
	$ts2 = RISTools::date_iso2timestamp($termin2->termin);
	if ($ts1 < $ts2) return 1;
	if ($ts1 > $ts2) return -1;
	return 0;
});


$vcalendar = new \Sabre\VObject\Component\VCalendar();

foreach ($alle_termine as $curr_termin) {
	$vcalendar->add('VEVENT', $curr_termin->getVEventParams());

}

echo $vcalendar->serialize();