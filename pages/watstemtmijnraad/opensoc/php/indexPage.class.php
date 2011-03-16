<?php
require_once('Raadsstuk.class.php');
require_once('LocalParty.class.php');
require_once('Region.class.php');
require_once('smarty/dist/plugins/modifier.truncate.php');

/**
 * PostgreSQL interval, defines time to live for cache.
 * By default 1 day.
 */
define('OPENSOCIAL_CACHE_TTL', '1 day');

/**
 * Get and echo cached data.
 * If data is found, then function should echo the data and return false. If cache
 * entry is not found, then function should return true and await opensocial_cache()
 * call with data.
 *
 * @param string $key cache key
 * @return bool true if no key was found, thus generate output again
 */
function opensocial_readcache($key) {
	$res = DBs::inst(DBs::SYSTEM)->query('SELECT data FROM opensocial_cache WHERE cache_key = %s AND created + interval %s > NOW()', $key, OPENSOCIAL_CACHE_TTL)->fetchCell();
	if($res) {
		echo $res;
		return false;
	}
	return true;
}


/**
 * Cache generated data.
 * This function will be called if opensocial_readcache() returns true. The data
 * will be generated again and passed to this function for storage.
 * 
 * @param string $key the key
 * @param string $data json data
 */
function opensocial_cache($key, $data) {
	//postgres has no insert on duplicate update syntax. if insert fails, we will get exception here, update.
	try {
		$notupdate = DBs::inst(DBs::SYSTEM)->query('INSERT INTO opensocial_cache(cache_key, data, created) VALUES(%s, %s, NOW())', $key, $data)->affectedRows() > 0;
	} catch (Exception $e){
		$notupdate = false;
	}
	
	if(!$notupdate) { //either failed or INSERT query has returned 0 or NULL (failed silently)
		DBs::inst(DBs::SYSTEM)->query('UPDATE opensocial_cache SET data = %s, created = NOW() WHERE cache_key = %s', $data, $key);
	}
}


/** Shows raadsstukken of specific politician. */
class IndexPage {

	/**
	 * Returns information about "namespace" and the list of raadsstukken.
	 * The "namespace" is politician or party or region.
	 * 
	 * Queries (! - exclusive paramters):
	 *   -- raadsstukken: flag, reqeust raadsstukken
	 *   	-- !openid   - openid is provided, map openid -> politicus id, politicus mode
	 *   	-- !polid    - politicus id is provided directly, politicus mode
	 *   	-- !partyid  - party id is provided directly, party mode
	 *   	-- !regionid - region id is provided directly, region mode
	 * 		-- limit	- limit number of raadstukken
	 * 
	 *   -- regionlist: flag, list active region's
	 * 
	 *   -- partylist: flag, list active parties in given region
	 *   	-- regionid	- list parties of given region
	 * 
	 *   -- pollist: flag, list active politici
	 * 		-- partyid	- list politici of given party
	 *
	 * 
	 * @param array $get GET parameters
	 */
	public function processGet($get) {
		static $params = array('raadsstukken' => 1, 'openid' => 1, 'polid' => 1, 'partyid' => 1, 'regionid' => 1, 'limit' => 1, 'regionlist' => 1,
		                       'partylist' => 1, 'regionid' => 1, 'pollist' => 1, 'partyid' => 1);
		
		header('Content-type: text/x-json');
		//header('Content-type: text/plain');
		$host = 'http://'.$_SERVER['HTTP_HOST'];
		try {
			$key = sha1(serialize(array_intersect_key($get, $params)));
			if(opensocial_readcache($key)) {
				//dispatch request
				if(isset($get['raadsstukken'])) { //raadsstukken list request
					$limit = isset($get['limit'])? intval($get['limit']): OPENSOCIAL_RAADSSTUK_LIMIT;
					$limit = $limit < 0? OPENSOCIAL_RAADSSTUK_LIMIT: $limit > OPENSOCIAL_MAX_RAADSSTUK_LIMIT? OPENSOCIAL_MAX_RAADSSTUK_LIMIT: $limit;

					if(isset($get['openid']) || isset($get['polid'])) { //fetch by politician
						if(isset($get['openid'])) { //openid -> politician id mapping
							$polid = DBs::inst(DBs::SYSTEM)->query('SELECT po.politician FROM pol_politicians_opensocial po WHERE po.opensocial_id = %s', $get['openid'])->fetchCell();
							if(!$polid) {
								echo json_encode(array('error' => 'Unknown politician', 'code' => 'id_not_mapped'));
								return;
							}
						} else $polid = intval($get['polid']);
						
						//fetch politician profile info
						$pol = new Politician();
						try {
							$pol->load($polid);
						} catch (Exception $e) {
							echo json_encode(array('error' => 'Unknown politician', 'code' => 'politician_does_not_exist'));
							return;
						}
					
						$profile = array(
							'id' => $pol->id,
							'profile_url' => "{$host}/politicians/politician/{$pol->id}",
							'name' => $pol->formatName(),
							'photo' => $pol->photo? "{$host}{$pol->photo}": "{$host}/images/iframe/profile_empty.png",
						);
					
						$r = new Raadsstuk();
						$raadstukken = $r->listRecentByPolitician($limit, $pol->id);
						$data = array(
							'politician' => $profile,
							'raadsstukken' => $this->packRaadsstukken($raadstukken),
						);
					} elseif(isset($get['partyid'])) { //fetch by party
						$party = new LocalParty();
						try {
							$party->load(intval($get['partyid']));
						} catch (Exception $e) {
							echo json_encode(array('error' => 'Unknown party', 'code' => 'party_does_not_exist'));
							return;
						}
						
						$profile = array(
							'id' => $party->party,
							'local_id' => $party->id,
							'name' => $party->party_name,
							'short_name' => $party->party_short,
							'region_id' => $party->region,
							'region_name' => $party->formatRegionName(),
							'profile_url' => "{$host}/parties/party/{$party->party}/?region={$party->region}",
						);

						//lookup for the raadsstukken
						$r = new Raadsstuk();
						$raadstukken = $r->listRecentByParty($limit, $party->party, $party->region);
						$data = array(
							'party' => $profile,
							'raadsstukken' => $this->packRaadsstukken($raadstukken),
						);
					} elseif(isset($get['regionid'])) { //fetch by region
						$region = new Region();
						try {
							$region->load(intval($get['regionid']));
						} catch (Exception $e) {
							echo json_encode(array('error' => 'Unknown region', 'code' => 'region_does_not_exist'));
							return;
						}

						$profile = array(
							'id' => $region->id,
							'name' => $region->formatName(),
							'profile_url' => "{$host}/regions/region/{$region->id}",
						);
						
						//lookup for the raadsstukken
						$r = new Raadsstuk();
						$raadstukken = $r->listRecentByRegion($limit, $region->id);
						$data = array(
							'region' => $profile,
							'raadsstukken' => $this->packRaadsstukken($raadstukken),
						);
					} else {
						echo json_encode(array('error' => 'Invalid request', 'code' => 'invalid_request'));
						return;
					}
				} elseif(isset($get['regionlist'])) { //list of regions
					$regions = new Region();
					//AND t.id IN (SELECT region FROM pol_party_regions pr WHERE NOW() BETWEEN pr.time_start AND pr.time_end)
					$reglist = $regions->getList('', 'WHERE t.level > 3 AND t.id IN(SELECT region FROM rs_raadsstukken)', 'ORDER BY t.level ASC, t.name ASC');
					$tree = array();
					$root = array();
					$ids = array();
					foreach ($reglist as $reg) {
						$tree[$reg->parent][$reg->id] = $reg;
						if($reg->level <= 4) $root[$reg->id] = $reg;
						$ids[] = $reg->parent;
					}
					
					//fetch empty parents that have non empty children
					$reglist = $regions->getList('', 'WHERE t.level > 3 AND t.id IN ('.implode(', ', $ids).')', 'ORDER BY t.level ASC, t.name ASC');
					foreach ($reglist as $reg) {
						$tree[$reg->parent][$reg->id] = $reg;
						if($reg->level <= 3 && !isset($root[$reg->id])) $root[$reg->id] = $reg;
					}
					
					$data = $this->convRegTree($root, $tree, $host);
				} elseif(isset($get['partylist'])) { //parties list
					if(!isset($get['regionid'])) {
						echo json_encode(array('error' => 'Invalid request', 'code' => 'invalid_request'));
						return;
					}
					
					$party = new LocalParty();
					$parties = $party->getList('', 'WHERE  NOW() BETWEEN time_start AND time_end AND region = '.intval($get['regionid']), 'ORDER BY p.name');
					$data = array();
					foreach ($parties as $party) {
						$p = array(
							'id' => $party->party,
							'local_id' => $party->id,
							'name' => $party->party_name,
							'short_name' => $party->party_short,
							'region_id' => $party->region,
							'region_name' => $party->formatRegionName(),
							'profile_url' => "{$host}/parties/party/{$party->party}/?region={$party->region}",
						);
						array_push($data, $p);
					}
				} elseif(isset($get['pollist'])) { //list of politicians
					if(!isset($get['partyid'])) {
						echo json_encode(array('error' => 'Invalid request', 'code' => 'invalid_request'));
						return;
					}
					
					$pol = new Politician();
					//Database is trash, don't check anything, they like it: AND pf.time_start >= pr.time_start AND pf.time_end <= pr.time_end
					$politicians = $pol->getList('JOIN pol_politician_functions pf ON t.id = pf.politician JOIN pol_party_regions pr ON pf.party = pr.party AND pf.region = pr.region',
												 'WHERE pr.id = '.intval($get['partyid']).' AND NOW() BETWEEN pf.time_start AND pf.time_end AND t.def_party IS NULL');

					$data = array();
					foreach ($politicians as $pol) {
						$p = array(
							'id' => $pol->id,
							'profile_url' => "{$host}/politicians/politician/{$pol->id}",
							'name' => $pol->formatName(),
							'photo' => $pol->photo? "{$host}{$pol->photo}": "{$host}/images/iframe/profile_empty.png",
						);
						array_push($data, $p);
					}
				} else {
					//unknown/invalid request
					echo json_encode(array('error' => 'Invalid request', 'code' => 'invalid_request'));
					return;
				}
				
				// request dispatched, cache data
				$data = json_encode($data);
				opensocial_cache($key, $data);
				echo $data;
			}
		} catch (Exception $e) {
			echo json_encode(array('error' => 'Internal error', 'code' => 'internal'));
		}
	}

	
	public function show($smarty) {
		
	}
	
	private function convRegTree($list, $tree, $host) {
		$ret = array();
		foreach ($list as $reg) {
			$r = array(
			'id' => $reg->id,
			'name' => $reg->formatName(),
			'profile_url' => "{$host}/regions/region/{$reg->id}",
			);
			
			if(isset($tree[$reg->id]) && !empty($tree[$reg->id])) {
				$r['children'] = $this->convRegTree($tree[$reg->id], $tree, $host);
			}
			
			array_push($ret, $r);
		}
		return $ret;
	}
	
	/** Format raadsstukken into simple dict */
	private function packRaadsstukken($list) {		
		static $result_map = array(
			0 => 'new',
			1 => 'pro',
			2 => 'contra',
		);
				
		static $votemap = array(
			0 => 'pro',
			1 => 'contra',
			2 => 'abstain',
			3 => 'absent',
		);

			
		$ret = array();
		$keys = array('id', 'title', 'vote_date', 'region_name', 'level_name', 'type_name', 'submit_type_name'); //, 'result', 'vote_0', 'vote_1', 'vote_2', 'vote_3', 'polvote');
		$host = 'http://'.$_SERVER['HTTP_HOST'];

		foreach ($list as $rad) {
			$rec = array();
			foreach ($keys as $k) $rec[$k] = $rad->$k;
					
			//mapped info
			$rec['result'] = $result_map[$rad->result];
			$rec['count_pro'] = $rad->vote_0;
			$rec['count_contra'] = $rad->vote_1;
			$rec['count_abstain'] = $rad->vote_2;
			$rec['count_absent'] = $rad->vote_3;
			
			//extra info
			if($rad->hasProperty('polvote')) $rec['polvote'] = $votemap[$rad->polvote];
			if($rad->hasProperty('party_vote_0')) {
				$rec['party_vote_pro'] = $rad->party_vote_0;
				$rec['party_vote_contra'] = $rad->party_vote_1;
				$rec['party_vote_abstain'] = $rad->party_vote_2;
				$rec['party_vote_absent'] = $rad->party_vote_3;
			}
			
			$txt = str_replace('><', '> <', $rad->summary);
			$rec['summary'] = smarty_modifier_truncate(strip_tags($txt), OPENSOCIAL_RAADSSTIK_SUMMARY_LIMIT);
					
			$rec['view_url'] = "{$host}/raadsstukken/raadsstuk/{$rad->id}";
			array_push($ret, $rec);
		}
		
		return $ret;
	}
}


?>