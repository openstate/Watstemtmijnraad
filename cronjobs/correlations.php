<?php
/**
 * Computes party correlations.
 * @author Sardar Yumatov (ja.doma@gmail.com)
 */

//setup environment
require_once(dirname(__FILE__).'/../includes/prequel.cli.php');


try {
	//connect to database
	$db = getPDOConnection();
	
	$stat_stm = $db->prepare('SELECT vc1.party as party_1, vc2.party as party_2, vc1.raadsstuk as raadsstuk,
	                            vc1.vote_0 as pro_1, vc1.vote_1 as contra_1, vc1.vote_2 as abstain_1, vc1.vote_3 as absent_1, (vc1.vote_0 + vc1.vote_1 + vc1.vote_2 + vc1.vote_3) as tot_1,
	                            vc2.vote_0 as pro_2, vc2.vote_1 as contra_2, vc2.vote_2 as abstain_2, vc2.vote_3 as absent_2, (vc2.vote_0 + vc2.vote_1 + vc2.vote_2 + vc2.vote_3) as tot_2
                              FROM rs_party_vote_cache vc1
                              JOIN rs_party_vote_cache vc2 ON vc1.raadsstuk = vc2.raadsstuk
                              JOIN rs_raadsstukken r ON r.id = vc1.raadsstuk
							  WHERE vc1.party = :party_1 AND vc2.party = :party_2
							  ORDER BY r.vote_date DESC
							  LIMIT :limit');
  
    $reset_stm = $db->prepare('UPDATE pol_party_correlations SET active = false WHERE (party_1 = :party_1 AND party_2 = :party_2) OR (party_1 = :party_2 AND party_2 = :party_1)');
	$store_stm = $db->prepare('INSERT INTO pol_party_correlations(party_1, party_2, ref_date, active, samples, pro_cor, contra_cor, abstain_cor, absent_cor, total_fit)
	                                       VALUES(:party_1, :party_2, now(), true, :samples, :pro_cor, :contra_cor, :abstain_cor, :absent_cor, :total_fit)');
	
	//every party pair
	$ids = $db->query('SELECT id FROM pol_parties')->fetchAll(PDO::FETCH_COLUMN, 0);
	$tot_parties = sizeof($ids);
	while(sizeof($ids) > 1) {
		$cur_id = array_pop($ids);
		foreach ($ids as $party_id) {
			$stat_stm->execute(array(
				':party_1' => $cur_id,
				':party_2' => $party_id,
				':limit' => PARTY_CORRELATION_SAMPLE_LIMIT,
			));
			
			//compute statistics (move it to DB server if postgres is updated to 8.2)
			$pro_1 = $contra_1 = $abstain_1 = $absent_1 = array();
			$pro_2 = $contra_2 = $abstain_2 = $absent_2 = array();
			$party_vote = 0; $tot = 0;
			while($row = $stat_stm->fetch(PDO::FETCH_ASSOC)) {
				if($row['tot_1'] > 0 && $row['tot_2'] > 0) {
					$pro_1[] = $row['pro_1'] / $row['tot_1'];
					$contra_1[] = $row['contra_1'] / $row['tot_1'];
					$abstain_1[] = $row['abstain_1'] / $row['tot_1'];
					$absent_1[] = $row['absent_1'] / $row['tot_1'];
					$pro_2[] = $row['pro_2'] / $row['tot_2'];
					$contra_2[] = $row['contra_2'] / $row['tot_2'];
					$abstain_2[] = $row['abstain_2'] / $row['tot_2'];
					$absent_2[] = $row['absent_2'] / $row['tot_2'];
				} //else strange vote cache record, party of no members has "voted" for raadsstuk... can happen in our shitty DB
				
				//compute party vote
				//BIG FAT WARNING: the winner takes all algorithm, could be different from reality!
				if($row['pro_1'] == $row['contra_1']) $party_1_vote = 'abstain';
				else $party_1_vote = $row['pro_1'] > $row['contra_1']? 'pro': 'contra';
				
				if($row['pro_2'] == $row['contra_2']) $party_2_vote = 'abstain';
				else $party_2_vote = $row['pro_2'] > $row['contra_2']? 'pro': 'contra';
				
				$party_vote += (($party_1_vote == $party_2_vote)? 1: 0);
				$tot++;
			}
			
			//total fitness
			$party_vote = $tot > 0? $party_vote/$tot: 0;
			
			if(sizeof($pro_1) > 1) {
				// compute correlations
				$cor_pro = correlation($pro_1, $pro_2);
				$cor_contra = correlation($contra_1, $contra_2);
				$cor_abstain = correlation($abstain_1, $abstain_2);
				$cor_absent = correlation($absent_1, $absent_2);
			} else {
				// these two parties have never (or jsut 1 time) voted for the same raadsstuk
				// correlation is not defined here, but in our case we can (logically) assign 0 (no) correlation
				$cor_pro = $cor_contra = $cor_abstain = $cor_absent = 0;
			}
			
			//store correlations
			$reset_stm->execute(array(
				':party_1' => $cur_id,
				':party_2' => $party_id,
			));
			
			//main pair
			$store_stm->execute(array(
				':party_1' => $cur_id,
				':party_2' => $party_id,
				':samples' => sizeof($pro_1),
				':pro_cor' => $cor_pro,
				':contra_cor' => $cor_contra,
				':abstain_cor' => $cor_abstain,
				':absent_cor' => $cor_absent,
				':total_fit' => $party_vote,
			));
			
			//reflection for speedup, OR joins are slower
			$store_stm->execute(array(
				':party_1' => $party_id,
				':party_2' => $cur_id,
				':samples' => sizeof($pro_1),
				':pro_cor' => $cor_pro,
				':contra_cor' => $cor_contra,
				':abstain_cor' => $cor_abstain,
				':absent_cor' => $cor_absent,
				':total_fit' => $party_vote,
			));
		}
	}
	
	echo "OK\nComputed correlations of: {$tot_parties} parties\n";
	exit(0);
} catch (Exception $e) {
	echo "Failure\nError: ".($e->getMessage())."\n";
	exit(1);
}


/** Computes sample standard score Z = (Xi - Xmean) / Xstandard_deviation */
function standard_score($sample) {	
	$mean = array_sum($sample) / count($sample);
	
	$df2 = array();
	foreach ($sample as $sm) $df2[] = ($sm - $mean) * ($sm - $mean);
	$sample_standard_deviation = sqrt(array_sum($df2) / (sizeof($sample) - 1));
	unset($df2);
	
	if($sample_standard_deviation == 0) return null;
	
	$df = array();
	foreach ($sample as $sm) $df[] = ($sm - $mean) / $sample_standard_deviation;
	return $df;
}

function correlation($sample_1, $sample_2) {
	assert(count($sample_1) == count($sample_2));
	
	$score_1 = standard_score($sample_1);
	$score_2 = standard_score($sample_2);
	
	//if one of two samples is a constant, then deviation == 0, then correlation is simply not defined here
	//we can consider it as no correlation in our application, so return 0
	if($score_1 === null || $score_2 == null) return 0;
	
	return (array_sum(array_map('mul', $score_1, $score_2)) / count($sample_1));
}

function mul($a, $b) {
	return $a * $b;
}


?>