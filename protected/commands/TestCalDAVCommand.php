<?php

class TestCalDAVCommand extends CConsoleCommand
{
    public function run($args)
    {
        $ch = curl_init();
        curl_setopt($ch, CURLOPT_URL, "https://www.muenchen-transparent.de/termine/3448925/dav/calendars/guest/3448925/");
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
        curl_setopt($ch, CURLOPT_USERPWD, "user:pw");
        curl_setopt($ch, CURLOPT_CUSTOMREQUEST, "REPORT");
        curl_setopt($ch, CURLOPT_HTTPHEADER, array(
            "Content-Type: text/xml",
        ));
        curl_setopt($ch, CURLOPT_POSTFIELDS, '<?xml version="1.0" encoding="UTF-8"?><calendar-query xmlns="urn:ietf:params:xml:ns:caldav"><prop xmlns="DAV:"><getetag /></prop><filter><comp-filter name="VCALENDAR"><comp-filter name="VEVENT"><time-range start="20150111T000000Z" end="20150712T000000Z" /></comp-filter></comp-filter></filter></calendar-query>');

        $output = curl_exec($ch);
        if (strpos($output, '/termine/3448925/dav/calendars/guest/3448925/9300') !== false) {
            echo "OK\n";
        } else {
            echo "Error - got:\n$output\n";
        }
    }
}