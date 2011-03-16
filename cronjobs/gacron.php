<?php

require_once(dirname(__FILE__).'/../includes/prequel.cli.php');
require_once('GoogleAnalytics.class.php');

function format_copy($data) {
	return array_map('format_copy_line', $data);
}

function format_copy_line($row) {
	return implode("\t", array_map('format_copy_cell', $row))."\n";
}

function format_copy_cell($cell) {
	return $cell === null ? '\N' : strtr($cell, array("\x8" => '\b', "\xc" => '\f', "\xa" => '\n', "\xd" => '\r', "\x9" => '\t', "\xb" => '\v', "\\" => '\\\\'));
}

$ga = new GoogleAnalytics('acceptestats@gmail.com', 'accepte!@#', 15873146);
$db = DBs::inst(DBs::SYSTEM);
$date = time() - 86400;
$regionStats = $ga->getData($date, $date, array('ga:country', 'ga:region', 'ga:city', 'ga:hostname'), array('ga:timeOnSite', 'ga:visits'));
$pageStats = $ga->getData($date, $date, array('ga:hostname', 'ga:pagePath'), array('ga:pageviews'));

foreach ($regionStats as &$row) {
	$row['start'] = strftime('%Y-%m-%d', $date);
	$row['end'] = strftime('%Y-%m-%d', $date);
	$row['time_on_site'] = $row['timeOnSite'];
	unset($row['timeOnSite']);
	$row['hostname'] = reset(explode('.', $row['hostname']));
	$row['avg_time_on_site'] = $row['time_on_site'] / $row['visits'];
}
unset($row);

if ($regionStats) {
	$db->query('COPY stats_region ("%l") FROM STDIN', implode('", "', array_keys(reset($regionStats))));
	foreach (format_copy($regionStats) as $line)
		pg_put_line($db->connection, $line);
	pg_put_line($db->connection, "\\.\n");
	pg_end_copy($db->connection);
}

foreach ($pageStats as &$row) {
	$row['start'] = strftime('%Y-%m-%d', $date);
	$row['end'] = strftime('%Y-%m-%d', $date);
	$row['page_path'] = $row['pagePath'];
	unset($row['pagePath']);
	$row['hostname'] = reset(explode('.', $row['hostname']));
}
unset($row);

if ($pageStats) {
	$db->query('COPY stats_page ("%l") FROM STDIN', implode('", "', array_keys(reset($pageStats))));
	foreach (format_copy($pageStats) as $line)
		pg_put_line($db->connection, $line);
	pg_put_line($db->connection, "\\.\n");
	pg_end_copy($db->connection);
}

$db->query('
	INSERT INTO stats_region (start, "end", country, region, city, hostname, time_on_site, visits, avg_time_on_site)
	SELECT MIN(start), MAX("end"), country, region, city, hostname, SUM(time_on_site), SUM(visits), SUM(time_on_site)/SUM(visits)
	FROM stats_region
	WHERE start = "end" AND "end" + \'1 month\'::interval < NOW()
	GROUP BY country, region, city, hostname, substring("end"::text from 0 for 7)');
$db->query('DELETE FROM stats_region WHERE start = "end" AND "end" + \'1 month\'::interval < NOW()');

$db->query('
	INSERT INTO stats_page (start, "end", hostname, page_path, pageviews)
	SELECT MIN(start), MAX("end"), hostname, page_path, SUM(pageviews)
	FROM stats_page
	WHERE start = "end" AND "end" + \'1 month\'::interval < NOW()
	GROUP BY hostname, page_path, substring("end"::text from 0 for 7)');
$db->query('DELETE FROM stats_page WHERE start = "end" AND "end" + \'1 month\'::interval < NOW()');

?>