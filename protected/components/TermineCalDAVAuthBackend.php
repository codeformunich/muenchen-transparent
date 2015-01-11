<?php

use Sabre\DAV;
use Sabre\HTTP;

class TermineCalDAVAuthBackend implements \Sabre\DAV\Auth\Backend\BackendInterface {

	/**
	 * Returns information about the currently logged in username.
	 *
	 * If nobody is currently logged in, this method should return null.
	 *
	 * @return string|null
	 */
	function getCurrentUser() {
		return "guest";
	}


	/**
	 * Authenticates the user based on the current request.
	 *
	 * If authentication is successful, true must be returned.
	 * If authentication fails, an exception must be thrown.
	 *
	 * @param DAV\Server $server
	 * @param string $realm
	 * @throws DAV\Exception\NotAuthenticated
	 * @return bool
	 */
	function authenticate(DAV\Server $server, $realm) {
		return true;
	}


}

