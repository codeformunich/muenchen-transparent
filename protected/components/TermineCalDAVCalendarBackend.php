<?php

use Sabre\DAV;
use Sabre\DAVACL;

class TermineCalDAVCalendarBackend implements Sabre\CalDAV\Backend\BackendInterface
{

	/**
	 * Returns a list of calendars for a principal.
	 *
	 * Every project is an array with the following keys:
	 *  * id, a unique id that will be used by other functions to modify the
	 *    calendar. This can be the same as the uri or a database key.
	 *  * uri, which the basename of the uri with which the calendar is
	 *    accessed.
	 *  * principaluri. The owner of the calendar. Almost always the same as
	 *    principalUri passed to this method.
	 *
	 * Furthermore it can contain webdav properties in clark notation. A very
	 * common one is '{DAV:}displayname'.
	 *
	 * Many clients also require:
	 * {urn:ietf:params:xml:ns:caldav}supported-calendar-component-set
	 * For this property, you can just return an instance of
	 * Sabre\CalDAV\Property\SupportedCalendarComponentSet.
	 *
	 * If you return {http://sabredav.org/ns}read-only and set the value to 1,
	 * ACL will automatically be put in read-only mode.
	 *
	 * @param string $principalUri
	 * @return array
	 */

	private $termin_id;

	public function __construct($termin_id)
	{
		$this->termin_id = $termin_id;
	}

	function getCalendarsForUser($principalUri)
	{
		/** @var Termin $termin */
		$termin = Termin::model()->findByPk($this->termin_id);
		if (!$termin) return array();

		list(, $name) = \Sabre\HTTP\URLUtil::splitPath($principalUri);
		if ($name !== 'guest') return array();

		return array(
			array(
				'id'                                                                        => $this->termin_id,
				'uri'                                                                       => $this->termin_id,
				'principaluri'                                                              => $principalUri,
				'{DAV:}displayname'                                                         => $termin->gremium->getName(),
				'{' . \Sabre\CalDAV\Plugin::NS_CALENDARSERVER . '}getctag'                  => 'http://sabre.io/ns/sync/0',
				'{http://sabredav.org/ns}sync-token'                                        => '0',
				'{' . \Sabre\CalDAV\Plugin::NS_CALDAV . '}supported-calendar-component-set' => new \Sabre\CalDAV\Property\SupportedCalendarComponentSet(array("VEVENT")),
				'{' . \Sabre\CalDAV\Plugin::NS_CALDAV . '}schedule-calendar-transp'         => new \Sabre\CalDAV\Property\ScheduleCalendarTransp('opaque'),
				'{http://sabredav.org/ns}read-only'                                         => '1',
			)
		);
	}

	/**
	 * Creates a new calendar for a principal.
	 *
	 * If the creation was a success, an id must be returned that can be used to reference
	 * this calendar in other methods, such as updateCalendar.
	 *
	 * @param string $principalUri
	 * @param string $calendarUri
	 * @param array $properties
	 * @throws DAV\Exception\NotImplemented
	 */
	function createCalendar($principalUri, $calendarUri, array $properties)
	{
		throw new \Sabre\DAV\Exception\NotImplemented('Not Implemented');
	}

	/**
	 * Updates properties for a calendar.
	 *
	 * The list of mutations is stored in a Sabre\DAV\PropPatch object.
	 * To do the actual updates, you must tell this object which properties
	 * you're going to process with the handle() method.
	 *
	 * Calling the handle method is like telling the PropPatch object "I
	 * promise I can handle updating this property".
	 *
	 * Read the PropPatch documenation for more info and examples.
	 *
	 * @param $calendarId
	 * @param \Sabre\DAV\PropPatch $propPatch
	 * @throws DAV\Exception\NotImplemented
	 * @internal param string $path
	 */
	function updateCalendar($calendarId, \Sabre\DAV\PropPatch $propPatch)
	{
		throw new \Sabre\DAV\Exception\NotImplemented('Not Implemented');
	}

	/**
	 * Delete a calendar and all it's objects
	 *
	 * @param mixed $calendarId
	 * @throws DAV\Exception\NotImplemented
	 */
	function deleteCalendar($calendarId)
	{
		throw new \Sabre\DAV\Exception\NotImplemented('Not Implemented');
	}

	/**
	 * Returns all calendar objects within a calendar.
	 *
	 * Every item contains an array with the following keys:
	 *   * calendardata - The iCalendar-compatible calendar data
	 *   * uri - a unique key which will be used to construct the uri. This can
	 *     be any arbitrary string, but making sure it ends with '.ics' is a
	 *     good idea. This is only the basename, or filename, not the full
	 *     path.
	 *   * lastmodified - a timestamp of the last modification time
	 *   * etag - An arbitrary string, surrounded by double-quotes. (e.g.:
	 *   '"abcdef"')
	 *   * size - The size of the calendar objects, in bytes.
	 *   * component - optional, a string containing the type of object, such
	 *     as 'vevent' or 'vtodo'. If specified, this will be used to populate
	 *     the Content-Type header.
	 *
	 * Note that the etag is optional, but it's highly encouraged to return for
	 * speed reasons.
	 *
	 * The calendardata is also optional. If it's not returned
	 * 'getCalendarObject' will be called later, which *is* expected to return
	 * calendardata.
	 *
	 * If neither etag or size are specified, the calendardata will be
	 * used/fetched to determine these numbers. If both are specified the
	 * amount of times this is needed is reduced by a great degree.
	 *
	 * @param mixed $calendarId
	 * @return array
	 * @throws DAV\Exception\NotFound
	 */
	function getCalendarObjects($calendarId)
	{
		/** @var Termin $termin */
		$termin = Termin::model()->findByPk($calendarId);
		if (!$termin) throw new \Sabre\DAV\Exception\NotFound('Calendar not found');

		$alle_termine = $termin->alleTermineDerReihe();

		$dav_termine = array();
		foreach ($alle_termine as $akt_termin) {
			$dav_termine[] = array(
				'id'           => $akt_termin->id,
				'uri'          => $akt_termin->id,
				'lastmodified' => $akt_termin->datum_letzte_aenderung,
				'etag'         => '"' . addslashes($akt_termin->datum_letzte_aenderung) . '"',
				'calendarid'   => $calendarId,
				'size'         => (int)10, // Dummywert
				'component'    => 'VEVENT',
			);
		}
		return $dav_termine;
		/*
		list(,$name) = \Sabre\HTTP\URLUtil::splitPath($principalUri);
		if ($name !== 'guest') return array();

		return array(
			$calendar = array(
				'id' => 3537868,
				'uri' => "3537868",
				'principaluri' => $principalUri,
//				'{' . \Sabre\CalDAV\Plugin::NS_CALDAV . ':}displayname' => 'Testkalender',
				'{' . \Sabre\CalDAV\Plugin::NS_CALENDARSERVER . '}getctag' => 'http://sabre.io/ns/sync/0',
				'{http://sabredav.org/ns}sync-token' => '0',
				'{' . \Sabre\CalDAV\Plugin::NS_CALDAV . '}supported-calendar-component-set' => new \Sabre\CalDAV\Property\SupportedCalendarComponentSet(array("VEVENT")),
				'{' . \Sabre\CalDAV\Plugin::NS_CALDAV . '}schedule-calendar-transp' => new \Sabre\CalDAV\Property\ScheduleCalendarTransp('opaque'),
			)
		);
		*/
	}

	/**
	 * Returns information from a single calendar object, based on it's object
	 * uri.
	 *
	 * The object uri is only the basename, or filename and not a full path.
	 *
	 * The returned array must have the same keys as getCalendarObjects. The
	 * 'calendardata' object is required here though, while it's not required
	 * for getCalendarObjects.
	 *
	 * This method must return null if the object did not exist.
	 *
	 * @param mixed $calendarId
	 * @param string $objectUri
	 * @return array|null
	 * @throws DAV\Exception\NotFound
	 */
	function getCalendarObject($calendarId, $objectUri)
	{
		/** @var Termin $termin */
		$termin = Termin::model()->findByPk($objectUri);
		if (!$termin) throw new \Sabre\DAV\Exception\NotFound('Calendar not found');

		$vcalendar = new \Sabre\VObject\Component\VCalendar();
		$vcalendar->add('VEVENT', $termin->getVEventParams());
		$calendardata = $vcalendar->serialize();


		return array(
			'id'           => $termin->id,
			'uri'          => $termin->id,
			'lastmodified' => $termin->datum_letzte_aenderung,
			'etag'         => '"' . addslashes($termin->datum_letzte_aenderung) . '"',
			'calendarid'   => $calendarId,
			'calendardata' => $calendardata,
			'size'         => strlen($calendardata),
			'component'    => 'VEVENT',
		);
	}

	/**
	 * Returns a list of calendar objects.
	 *
	 * This method should work identical to getCalendarObject, but instead
	 * return all the calendar objects in the list as an array.
	 *
	 * If the backend supports this, it may allow for some speed-ups.
	 *
	 * @param mixed $calendarId
	 * @param array $uris
	 * @return array
	 */
	function getMultipleCalendarObjects($calendarId, array $uris)
	{
		die("3");
		// TODO: Implement getMultipleCalendarObjects() method.
	}

	/**
	 * Creates a new calendar object.
	 *
	 * The object uri is only the basename, or filename and not a full path.
	 *
	 * It is possible return an etag from this function, which will be used in
	 * the response to this PUT request. Note that the ETag must be surrounded
	 * by double-quotes.
	 *
	 * However, you should only really return this ETag if you don't mangle the
	 * calendar-data. If the result of a subsequent GET to this object is not
	 * the exact same as this request body, you should omit the ETag.
	 *
	 * @param mixed $calendarId
	 * @param string $objectUri
	 * @param string $calendarData
	 * @return string|null
	 */
	function createCalendarObject($calendarId, $objectUri, $calendarData)
	{
		// TODO: Implement createCalendarObject() method.
	}

	/**
	 * Updates an existing calendarobject, based on it's uri.
	 *
	 * The object uri is only the basename, or filename and not a full path.
	 *
	 * It is possible return an etag from this function, which will be used in
	 * the response to this PUT request. Note that the ETag must be surrounded
	 * by double-quotes.
	 *
	 * However, you should only really return this ETag if you don't mangle the
	 * calendar-data. If the result of a subsequent GET to this object is not
	 * the exact same as this request body, you should omit the ETag.
	 *
	 * @param mixed $calendarId
	 * @param string $objectUri
	 * @param string $calendarData
	 * @return string|null
	 */
	function updateCalendarObject($calendarId, $objectUri, $calendarData)
	{
		// TODO: Implement updateCalendarObject() method.
	}

	/**
	 * Deletes an existing calendar object.
	 *
	 * The object uri is only the basename, or filename and not a full path.
	 *
	 * @param mixed $calendarId
	 * @param string $objectUri
	 * @return void
	 */
	function deleteCalendarObject($calendarId, $objectUri)
	{
		// TODO: Implement deleteCalendarObject() method.
	}

	/**
	 * Performs a calendar-query on the contents of this calendar.
	 *
	 * The calendar-query is defined in RFC4791 : CalDAV. Using the
	 * calendar-query it is possible for a client to request a specific set of
	 * object, based on contents of iCalendar properties, date-ranges and
	 * iCalendar component types (VTODO, VEVENT).
	 *
	 * This method should just return a list of (relative) urls that match this
	 * query.
	 *
	 * The list of filters are specified as an array. The exact array is
	 * documented by Sabre\CalDAV\CalendarQueryParser.
	 *
	 * Note that it is extremely likely that getCalendarObject for every path
	 * returned from this method will be called almost immediately after. You
	 * may want to anticipate this to speed up these requests.
	 *
	 * This method provides a default implementation, which parses *all* the
	 * iCalendar objects in the specified calendar.
	 *
	 * This default may well be good enough for personal use, and calendars
	 * that aren't very large. But if you anticipate high usage, big calendars
	 * or high loads, you are strongly adviced to optimize certain paths.
	 *
	 * The best way to do so is override this method and to optimize
	 * specifically for 'common filters'.
	 *
	 * Requests that are extremely common are:
	 *   * requests for just VEVENTS
	 *   * requests for just VTODO
	 *   * requests with a time-range-filter on either VEVENT or VTODO.
	 *
	 * ..and combinations of these requests. It may not be worth it to try to
	 * handle every possible situation and just rely on the (relatively
	 * easy to use) CalendarQueryValidator to handle the rest.
	 *
	 * Note that especially time-range-filters may be difficult to parse. A
	 * time-range filter specified on a VEVENT must for instance also handle
	 * recurrence rules correctly.
	 * A good example of how to interprete all these filters can also simply
	 * be found in Sabre\CalDAV\CalendarQueryFilter. This class is as correct
	 * as possible, so it gives you a good idea on what type of stuff you need
	 * to think of.
	 *
	 * @param mixed $calendarId
	 * @param array $filters
	 * @return array
	 */
	function calendarQuery($calendarId, array $filters)
	{
		// TODO: Implement calendarQuery() method.
	}

	/**
	 * Searches through all of a users calendars and calendar objects to find
	 * an object with a specific UID.
	 *
	 * This method should return the path to this object, relative to the
	 * calendar home, so this path usually only contains two parts:
	 *
	 * calendarpath/objectpath.ics
	 *
	 * If the uid is not found, return null.
	 *
	 * This method should only consider * objects that the principal owns, so
	 * any calendars owned by other principals that also appear in this
	 * collection should be ignored.
	 *
	 * @param string $principalUri
	 * @param string $uid
	 * @return string|null
	 */
	function getCalendarObjectByUID($principalUri, $uid)
	{
		// TODO: Implement getCalendarObjectByUID() method.
	}
}