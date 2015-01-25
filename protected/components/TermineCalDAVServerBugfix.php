<?php

class TermineCalDAVServerBugfix extends \Sabre\DAV\Server {
    /**
     * Windows Phone liefert kein "Depth" mit, daher funktioniert der Calendar-Query nicht,
     * ohne den aber keine Daten abgerufen werden können.
     *
     * @param int $default
     * @return int
     */
    function getHTTPDepth($default = self::DEPTH_INFINITY) {
        $depth = $this->httpRequest->getHeader('Depth');
        if (is_null($depth) && $default == 0) return 1;
        if ($depth == 0 && $default == 0) return 1;
        return parent::getHTTPDepth($default);
    }
}