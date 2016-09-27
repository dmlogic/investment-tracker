<?php

/**
 * Database Configuration
 *
 * All of your system's database configuration settings go in here.
 * You can see a list of the default settings in craft/app/etc/config/defaults/db.php
 */

return array(

	// The database server name or IP address. Usually this is 'localhost' or '127.0.0.1'.
    'server' => ENV_DB_HOST,

    // The database username to connect with.
    'user' => ENV_DB_USER,

    // The database password to connect with.
    'password' => ENV_DB_PWD,

    // The name of the database to select.
    'database' => ENV_DB_NAME,

	// The prefix to use when naming tables. This can be no more than 5 characters.
	'tablePrefix' => 'craft',

);
