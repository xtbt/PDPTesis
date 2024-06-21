<?php
	// SYSTEM GLOBALS
	define('PROJECT_NAME', 'PDPTesis');

	define('WEBSERVER', 0); // 0 = Local, 1 = Shared Hosting
	
	// DATABASE CONSTANTS #####################################################
	define('DB_CONTROLLER', 'mysql');
	define('DB_HOST', 'localhost'); // Compatibility
	define('DB_PORT', '3306');

	if ( WEBSERVER == 1 ) {
		define('DB_NAME', 'u881531570_PDPTESIS_DB');
		define('DB_USER', 'u881531570_PDPTESIS_USR');
		define('DB_PASSWORD', 'AdmPDPTesis2023!');
		define('SERVER_FILESYSTEM_PREFIX', '/home/u881531570/domains/ip20soft.tech/public_html/');
	} else {
		define('DB_NAME', 'PDPTesisDB');
		define('DB_USER', 'root');
		define('DB_PASSWORD', ''); // XAMPP Test Server
		//define('DB_PASSWORD', 'root'); // MAMP Test Server
		define('SERVER_FILESYSTEM_PREFIX', '/Applications/XAMPP/htdocs/');
	};
	// ########################################################################

	define('DEBUG_MODE', true);

	// APP GLOBALS
	date_default_timezone_set('America/Tijuana');

	// APP SETTINGS
	define('ITEMS_PER_PAGE', 30);
?>