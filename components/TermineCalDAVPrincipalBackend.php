<?php

namespace app\components;


class TermineCalDAVPrincipalBackend extends Sabre\DAVACL\PrincipalBackend\AbstractBackend
{
    private $termin_id;

    public function __construct($termin_id)
    {
        $this->termin_id = $termin_id;
    }

    public function getPrincipalsByPrefix($prefixPath)
    {
        $base = YII::app()->createUrl("termine/dav", ["termin_id" => $this->termin_id]);
        return [
            [
                '{DAV:}displayname' => 'Gast',
                'uri'               => "principals/guest",
            ],
        ];
    }

    public function getPrincipalByPath($path)
    {
        $base = YII::app()->createUrl("termine/dav", ["termin_id" => $this->termin_id]);
        if ($path == 'principals/guest') return [
            '{DAV:}displayname' => 'Gast',
            'uri'               => "principals/guest",
        ];
        return null;
    }

    function updatePrincipal($path, \Sabre\DAV\PropPatch $propPatch)
    {
        throw new \Sabre\DAV\Exception\NotImplemented('Not Implemented');
    }

    function searchPrincipals($prefixPath, array $searchProperties, $test = 'allof')
    {
        throw new \Sabre\DAV\Exception\NotImplemented('Not Implemented');
    }

    public function getGroupMemberSet($principal)
    {
        // not implemented, this could return all principals for a share-all calendar server
        return [];
    }

    public function getGroupMembership($principal)
    {
        // not implemented, this could return a list of all principals
        // with two subprincipals: calendar-proxy-read and calendar-proxy-write for a share-all calendar server
        return [];

    }

    public function setGroupMemberSet($principal, array $members)
    {
        throw new \Sabre\DAV\Exception\NotImplemented('Not Implemented');
    }
}