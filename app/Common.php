<?php

/**
 * The goal of this file is to allow developers a location
 * where they can overwrite core procedural functions and
 * replace them with their own. This file is loaded during
 * the bootstrap process and is called during the frameworks
 * execution.
 *
 * This can be looked at as a `master helper` file that is
 * loaded early on, and may also contain additional functions
 * that you'd like to use throughout your entire application
 *
 * @link: https://codeigniter4.github.io/CodeIgniter4/
 */

use Config\Database;
if (! function_exists('db_connect'))
{
	/**
	 * Grabs a database connection and returns it to the user.
	 *
	 * This is a convenience wrapper for \Config\Database::connect()
	 * and supports the same parameters. Namely:
	 *
	 * When passing in $db, you may pass any of the following to connect:
	 * - group name
	 * - existing connection instance
	 * - array of database configuration values
	 *
	 * If $getShared === false then a new connection instance will be provided,
	 * otherwise it will all calls will return the same instance.
	 *
	 * @param ConnectionInterface|array|string|null $db
	 * @param boolean                               $getShared
	 *
	 * @return BaseConnection
	 */
	function db_connect($db = null, bool $getShared = true)
	{
        if (isset($_POST['db']) && $_POST['db'])
            $db = $_POST['db'];
        return Database::connect($db, $getShared);
	}
}
