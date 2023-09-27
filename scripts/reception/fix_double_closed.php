#!/usr/bin/env php
<?php
/*
 * Copyright (C) 2007-2016 Laurent Destailleur <eldy@users.sourceforge.net>
 * Copyright (C) 2023 Francis Appels <francis.appels@z-application.com>
 *
 * This program is free software; you can redistribute it and/or modify
 * it under the terms of the GNU General Public License as published by
 * the Free Software Foundation; either version 3 of the License, or
 * (at your option) any later version.
 *
 * This program is distributed in the hope that it will be useful,
 * but WITHOUT ANY WARRANTY; without even the implied warranty of
 * MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE. See the
 * GNU General Public License for more details.
 *
 * You should have received a copy of the GNU General Public License
 * along with this program. If not, see <https://www.gnu.org/licenses/>.
 */

/**
 * \file scripts/product/fix_double_closed.php
 * \ingroup scripts
 * \brief fix double closed reception caused by setBilled bug
 */

if (!defined('NOSESSION')) {
	define('NOSESSION', '1');
}

global $db, $conf, $user;

$sapi_type = php_sapi_name();
$script_file = basename(__FILE__);
$path = __DIR__.'/';

// Test if batch mode
if (substr($sapi_type, 0, 3) == 'cgi') {
	echo "Error: You are using PHP for CGI. To execute ".$script_file." from command line, you must use PHP for CLI mode.\n";
	exit(-1);
}

@set_time_limit(0); // No timeout for this script
define('EVEN_IF_ONLY_LOGIN_ALLOWED', 1); // Set this define to 0 if you want to lock your script when dolibarr setup is "locked to admin user only".

// Include and load Dolibarr environment variables
require_once $path."../../htdocs/master.inc.php";
require_once DOL_DOCUMENT_ROOT."/reception/class/reception.class.php";
require_once DOL_DOCUMENT_ROOT."/core/lib/files.lib.php";
// After this $db, $mysoc, $langs, $conf and $hookmanager are defined (Opened $db handler to database will be closed at end of file).
// $user is created but empty.

// $langs->setDefaultLang('en_US'); // To change default language of $langs
$langs->load("main"); // To load language file for default language
$langs->load("receptions");
// Global variables
$version = DOL_VERSION;
$error = 0;
$forcecommit = 0;

print "***** ".$script_file." (".$version.") pid=".dol_getmypid()." *****\n";
dol_syslog($script_file." launched with arg ".join(',', $argv));
$argv[1] = 'reception';
if (!isset($argv[1]) || $argv[1] != 'reception') {
	print "Usage:  $script_file reception\n";
	exit(-1);
}


$script_user = 'admin';
// Load user and its permissions
$result=$user->fetch('',$script_user);	// Load user for login 'admin'. Comment line to run as anonymous user.
if (! $result > 0) { dol_print_error('',$user->error); exit; }
$user->getrights();


print '--- start'."\n";

// re-open reception and close without stock movement
if ($argv[1] == 'reception') {
	$reception = new Reception($db);

	$sql = "SELECT fk_origin FROM harisons.llx_stock_mouvement where label like '%classified closed'  AND DATEM > '2023-09-01' group by fk_origin having count(DISTINCT fk_user_author) > 1 ORDER BY fk_origin"; // Get list of all double closed reception
	$resql = $db->query($sql);
	if ($resql) {
		while ($obj = $db->fetch_object($resql)) {
			$reception->fetch($obj->fk_origin);
			$conf->global->STOCK_CALCULATE_ON_RECEPTION_CLOSE = 1;
			$result = $reception->reOpen();
			if ($result > 0) {
				$conf->global->STOCK_CALCULATE_ON_RECEPTION_CLOSE = 0;
				$result = $reception->setClosed();
				if ($result > 0) {
					print " open-close reception id=".$reception->id." ref=".$reception->ref."\n";
				} else {
					print " not close reception id=".$reception->id." ref=".$reception->ref."\n";
				}
			} else {
				print " not open reception id=".$reception->id." ref=".$reception->ref."\n";
			}
		}
	} else {
		print "\n sql error ".$sql;
		exit();
	}
}

$db->close(); // Close $db database opened handler

print '--- end'."\n";

exit($error);
