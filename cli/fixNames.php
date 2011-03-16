<?php

// utf8_to_ascii(coalesce(first_name||' ', '')||coalesce(last_name, ''))
// select utf8_to_ascii(coalesce(first_name||' ', '')||coalesce(last_name, '')) as name, concat(id||' ') from pol_politicians group by name having count(*) = 2;

/*
SELECT concat(id||' '), MIN(id)
FROM pol_politicians
GROUP BY utf8_to_ascii(coalesce(first_name||' ', '')||coalesce(last_name, '')), title, gender_is_male
HAVING COUNT(*) > 1
*/

require_once(dirname(__FILE__).'/../includes/prequel.cli.php');

$db = DBs::inst(DBs::SYSTEM);
$dups = $db->query("SELECT utf8_to_ascii(coalesce(first_name||' ', '')||coalesce(last_name, '')) as key_name, * FROM pol_politicians WHERE last_name != 'Onbekend' AND
             id NOT IN (
                 SELECT MIN(id) FROM pol_politicians GROUP BY utf8_to_ascii(coalesce(first_name||' ', '')||coalesce(last_name, '')), title, gender_is_male
             )")->fetchAllRows();


if(count($dups) > 0) {
	echo "Found ".count($dups)." dublicates. Generating patch...\n";

	//preffer old records (not damaged by importer)
	$good = $db->query("SELECT utf8_to_ascii(coalesce(first_name||' ', '')||coalesce(last_name, '')) as key_name, * FROM pol_politicians WHERE last_name != 'Onbekend' AND
             id IN (
                 SELECT MIN(id) FROM pol_politicians GROUP BY utf8_to_ascii(coalesce(first_name||' ', '')||coalesce(last_name, '')), title, gender_is_male HAVING COUNT(*) > 1
             )")->fetchAllRows('id');

	$good_lookp = array();
	foreach ($good as $row) {
		$good_lookp[$row['key_name']] = $row;
	}

	$sql = "START TRANSACTION;\n\n";
	foreach ($dups as $dub) {
		if(!isset($good_lookp[$dub['key_name']])) die("Can't match correct row! This tool is broken, don't use it!\n");
		$real = $good_lookp[$dub['key_name']];

		//before we delete dublicate we have to:
		//  - ensure real politician has the same functions as dublicate politician
		//  -- unique (region, time_range):extend(time_range):resolve_dublicate(distinct category and equal party)
		//
		//  - ensure rs_raadsstukken_submitters.real merge rs_raadsstukken_submitters.dublicate
		//  -- delete rs_raadsstukken_submitters.dublicate (not needed, cascaded)
		//
		//  - ensure rs_votes.real merge rs_votes.dublicate
		//  -- delete rs_votes.dublicate


		//cascade on politician delete:
		//  - pol_pending_functions
		//  - pol_politician_functions
		//  - pol_politician_users
        //  - rs_raadsstukken_submitters


        //========- Check functions -=======
        //fetch reals
		$ranges = array();
		$real_functions = $db->query("SELECT id, party, region, category, to_char(time_start, 'yyyy-mm-dd') as start, to_char(time_end, 'yyyy-mm-dd') as end FROM pol_politician_functions WHERE politician = %i", $real['id'])->fetchAllRows();
		foreach ($real_functions as $func) {
			$ranges[] = array(
				'start' => $func['start'],
				'end' => $func['end'],
				'id' => $func['id'],
				'party' => $func['party'],
				'region' => $func['region'],
				'category' => $func['category']
			);
		}

		//fetch dublicates
		$dublicate_functions = $db->query("SELECT id, party, region, category, to_char(time_start, 'yyyy-mm-dd') as start, to_char(time_end, 'yyyy-mm-dd') as end FROM pol_politician_functions WHERE politician = %i", $dub['id'])->fetchAllRows();
		foreach ($dublicate_functions as $func) {
			$consumed = false;
			$join_ranges = array();
			$shadow = array();

			foreach ($ranges as $kk => $rn) { //search equal function
				if($rn['party'] != $func['party'] || $rn['region'] != $func['region']) continue; //different raad

				//1 - we need to extend to left
				$left = ($func['start'] == $rn['start']? 0: ($func['start'] != '' && $rn['start'] == ''? -1: ($func['start'] != '' && strcmp($func['start'], $rn['start']) >= 0? -1: 1)));
				//-1 - we need to extend to right
				$right = ($func['end'] == $rn['end']? 0: ($func['end'] != '' && $rn['end'] == ''? 1: ($func['end'] != '' && strcmp($func['end'], $rn['end']) <= 0? 1: -1)));

				//if  r:[ d:[ r:] d:], where (start == end) is not a cross
				if($left <= 0 && $right < 0) $cross = ($func['start'] != '' && $rn['end'] != '' && strcmp($func['start'], $rn['end']) < 0);
				elseif($right >= 0 && $left > 0) $cross = ($func['end'] != '' && $rn['start'] != '' && strcmp($func['end'], $rn['start']) > 0);
				else $cross = true; //one contains other

				if($left <=0 && $right >=0 && $rn['category'] == $func['category']) { //our function includes new one
					if(!empty($join_ranges)) {
						echo "Overlapping real politicus function with equal category ({$func['category']}): \n";
						foreach ($join_ranges as $rg) echo " -- {$rg['id']}: {$rg['start']} - {$rg['end']}\n";
						echo "And 'containing all function': {$rn['start']} - {$rn['end']} of the same category!\n";
						echo "Database is inconsistent. Delete one of the functions such that time ranges do not overlap or have distinct categories!\n";
						die();
					}

					if(!empty($shadow)) {
						echo "There are real politicus functions completely shadowed by dublicate function.";
						die();
					}

					$consumed = true;
					break;
				} elseif ($left > 0 && $right < 0 && $rn['category'] == $func['category']) { //new function overlap completely old function
					$shadow[$rn['start']] = $rn;
			    } elseif($cross && $rn['category'] == $func['category']) { //consider this range as possible for extension
					if(isset($join_ranges[$rn['start']])) {
						echo "Functions of the same category overlap in the start time:\n";
						echo "  -- {$rn['id']}: {$rn['start']} - {$rn['end']}\n";
						echo "  -- {$join_ranges[$rn['start']]['id']}: {$join_ranges[$rn['start']]['start']} - {$join_ranges[$rn['start']]['end']}\n";
						die();
					}
					//[FIXME: check join ranges do not overlap! start only is not enough]
					$join_ranges[$rn['start']] = array('range' => $rn, 'left' => $left, 'right' => $right, 'cross' => $cross);
				}
			}

			if(!$consumed) { //so we need to extend things
				if(!empty($join_ranges)) { //we have ranges to merge
					$sql .= '-- Joining ranges: '.count($join_ranges)."\n";
					ksort($join_ranges);
					foreach ($join_ranges as $rng) {
						//left boundary
						if($rng['left'] > 0 && $rng['right'] >= 0) {
							$sql .= "UPDATE pol_politician_functions SET time_start = '".($rng['range']['start'] == ''? '-infinity': $rng['range']['start'])."' WHERE id = {$rng['range']['id']};\n";
						}
						//right boundary
						elseif($rng['right'] < 0 && $rng['left'] <= 0)
							$sql .= "UPDATE pol_politician_functions SET time_end = '".($rng['range']['end'] == ''? 'infinity': $rng['range']['end'])."' WHERE id = {$rng['range']['id']};\n";

						//middle in
						else {
							var_dump($ranges);
							var_dump($func);
							die("Not implemented\n");
						}
					}
				} else { //insert new range
					if(!empty($shadow)) die("not deleting previous functions");

					$sql .= "-- Inserting new function\n";
					$sql .= "INSERT INTO pol_politician_functions (politician, party, region, category, time_start, time_end, description)
				         VALUES ({$real['id']}, {$func['party']}, {$func['region']}, {$func['category']}, '".($func['start'] == ''? '-infinity': $func['start'])."', '".($func['end'] == ''? 'infinity': $func['end'])."', '');\n";
				}
			}


			/*if($new) { //we need new function
				$sql .= "-- Inserting new function\n";
				$sql .= "INSERT INTO pol_politician_functions (politician, party, region, category, time_start, time_end, description)
				         VALUES ({$duidx[$k]['id']}, {$func['party']}, {$func['region']}, {$func['category']}, '{$func['start']}', '{$func['end']}');\n";

				$ranges[] = array(
					'start' => $func['start'],
					'end' => $func['end'],
					'id' => null,
					'party' => $func['party'],
					'region' => $func['region'],
					'category' => $func['category']
				);
			}*/
		}


		//submitters
		$sql .= "UPDATE rs_raadsstukken_submitters SET politician = {$real['id']} WHERE politician = {$dub['id']} AND raadsstuk NOT IN (SELECT raadsstuk FROM rs_raadsstukken_submitters WHERE politician = {$real['id']});\n";

		//votes
		//$sql .= "UPDATE rs_votes SET politician = {$real['id']} WHERE politician = {$dub['id']} AND raadsstuk NOT IN (SELECT raadsstuk FROM rs_votes WHERE politician = {$real['id']});\n";
		$sql .= "INSERT INTO rs_votes(politician, raadsstuk, vote) SELECT {$real['id']}, raadsstuk, vote FROM rs_votes WHERE politician = {$dub['id']} AND raadsstuk NOT IN (SELECT raadsstuk FROM rs_votes WHERE politician = {$real['id']});\n";
		$sql .= "DELETE FROM rs_votes WHERE politician = {$dub['id']};\n";

		//delete politician (cascades)
		$sql .= "DELETE FROM pol_politicians WHERE id = {$dub['id']};\n\n\n";
	}

	$sql .= "ROLLBACK;\n";

	//die($sql);

	$fp = fopen('patch.sql', 'w');
	fwrite($fp, $sql);
	fclose($fp);
} else echo "No dublicates found.\n";

?>
