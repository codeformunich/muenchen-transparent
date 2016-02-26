<?php
/**
 * @var Termin
 */
header("Content-Type: text/calendar");

$vcalendar = new \Sabre\VObject\Component\VCalendar();
$vcalendar->add('VEVENT', $termin->getVEventParams());

echo $vcalendar->serialize();
