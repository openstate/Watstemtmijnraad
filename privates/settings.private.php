<?php

define('DEVELOPER', false);


/**
* Base path to the log file directory.
*/
define('JLOGGER_FILE_BASE', '/tmp');


/** Used by importer, die on any changes to database */
//define('DRY_RUN', true);

/** Used by exporter, TTL for signed timestamp in seconds. */
define('EXPORT_SIGNATURE_TIMEOUT', 5);

/** Party correlations cronjob, sample limit per party pair */
define('PARTY_CORRELATION_SAMPLE_LIMIT', 100);

/** Show debug output on the bottom of the website.
 * - true - enable the output
 * - false - disable the output
 */
define('DEBUG', false);

/** Maximum raadsstukken to be served. */
define('OPENSOCIAL_MAX_RAADSSTUK_LIMIT', 100);

/** Default amount of raadsstukken to send. */
define('OPENSOCIAL_RAADSSTUK_LIMIT', 5);

/** Number of characters for raadsstuk summary. */
define('OPENSOCIAL_RAADSSTIK_SUMMARY_LIMIT', 150);

/** Enable caching. */
define('OPENSOCIAL_ENABLE_CACHE', true);

/** HNS DEV API */

// Disable HNS sync (use when working on local WSMR features/bugs)
define('HNS_DISABLE_SYNC', false); // DISABLES ENTIRE HNS SYNC!

define('HNS_LOG_EVENTS', true); // Log HNS sync events to database

define('HNSDEV_URL', 'http://api.dev.hetnieuwestemmen.nl/query/'); // http://hnsdev.gl/query/
define('HNSDEV_USER', 'watstemtmijnraad');

$privateKey = dirname(__FILE__).'/hns.key';

define('HNSDEV_KEY', file_get_contents($privateKey));

define('DEBUG_HNSDEV_SYNC', true); // Debug events to response
define('DEBUG_HNSDEV_QUERIES', true); // Log/debug queries 

?>
