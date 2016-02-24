<?php


/**
 * @var Termin[] $alle_termine
 */


Header("Content-Type: text/calendar");

$vcalendar = new \Sabre\VObject\Component\VCalendar();

foreach ($alle_termine as $curr_termin) {
	$vcalendar->add('VEVENT', $curr_termin->getVEventParams());
}

echo $vcalendar->serialize();
