<?php

require_once('Party.class.php');
require_once('PartyVoteCache.class.php');


class Council {
	//I think this shitty code exists just because we can't do multiple joins
	//in normal record classes... anyway too much time to incorporate this functionality
	//in record classes

	private static $_p;

	private $members = array();
	private $parties = array();

	private function __construct($members = null) {
		if (is_array($members))
			$this->members = $members;
	}

	public function addMember(CouncilMember $m) {
		$this->members[$m->id] = $m;
		if (!($p = &$this->parties[$m->party])) {
			$p = new CouncilParty($m->party, self::getParty($m->party)->name, self::getParty($m->party)->short_form);
		}
		$p->addMember($m);
	}

	public function getMembers() {
		return $this->members;
	}

	public function getView() {
		return new CouncilView($this->members, $this->parties);
	}

	private static function getParty($id) {
		if (!self::$_p) {
			$p = new Party();
			self::$_p = $p->getList();
		}
		return self::$_p[$id];
	}

	public static function getCouncilByDate($region, $date = false) {
		if (!$date) $date = time();
		//return self::getCouncil($region, 'SELECT pf.politician, po.first_name, po.last_name, po.extern_id, pf.party FROM pol_party_regions pr JOIN pol_politician_functions pf ON pf.region = pr.region AND pf.party = pr.party JOIN pol_politicians po ON po.id = pf.politician WHERE '.strftime('\'%Y-%m-%d\'', $date).' BETWEEN pf.time_start AND pf.time_end AND '.strftime('\'%Y-%m-%d\'', $date).' BETWEEN pr.time_start AND pr.time_end AND pr.region = % ORDER BY name_sortkey, last_name, first_name');

		return self::getCouncil($region, 'SELECT pf.politician, po.first_name, po.last_name, po.extern_id, pf.party
											FROM pol_politician_functions pf
											JOIN pol_politicians po ON po.id = pf.politician

											WHERE pf.region = %i
  											AND '.strftime('\'%Y-%m-%d\'', $date).' BETWEEN pf.time_start AND pf.time_end
  											AND po.def_party IS NULL
											ORDER BY name_sortkey, last_name, first_name');
	}

	public static function getCurrentCouncil($region) {
		//return self::getCouncil($region, 'SELECT pf.politician, po.first_name, po.last_name, po.extern_id, pf.party FROM pol_party_regions pr JOIN pol_politician_functions pf ON pf.region = pr.region AND pf.party = pr.party JOIN pol_politicians po ON po.id = pf.politician WHERE now() BETWEEN pf.time_start AND pf.time_end AND now() BETWEEN pr.time_start AND pr.time_end AND pr.region = % ORDER BY name_sortkey, last_name, first_name');

		//[REQUIRE: pol_politician_functions for (region, time) has unique(party).length = 1
		// if not, bastard will belong to single party from politician perspective, while belonging to multiple
		// parties from party perspective. ]
		return self::getCouncil($region, 'SELECT pf.politician, po.first_name, po.last_name, po.extern_id, pf.party
										  FROM pol_politician_functions pf
										  JOIN pol_politicians po ON po.id = pf.politician

										  WHERE pf.region = %i AND now() BETWEEN pf.time_start AND pf.time_end
										  AND po.def_party IS NULL
										  ORDER BY name_sortkey, last_name, first_name');
	}

	/** Returns council view for given Raadsstuk */
	public static function getCouncilFromRaadsstuk($rs) {
		//[Note: this method should be part of Raadsstuk instead]

		//[FUCK THIS: hell, pol_party_regions have *derrived* time ranges (sum of all politician function time ranges), so we don't need to check this
		// unless we know our database is totally fucked up.]
		// Note: this assumption is not enforced anywhere in code so not sure why it is being made -- Ralf
		
		//return self::getCouncil($rs, 'SELECT pf.politician, po.first_name, po.last_name, po.extern_id, pf.party FROM rs_raadsstukken r JOIN pol_party_regions pr ON r.region = pr.region JOIN pol_politician_functions pf ON pf.region = pr.region AND pf.party = pr.party JOIN pol_politicians po ON po.id = pf.politician WHERE r.vote_date BETWEEN pf.time_start AND pf.time_end AND r.vote_date BETWEEN pr.time_start AND pr.time_end AND r.id = % ORDER BY name_sortkey, last_name, first_name');

		//[FIXME: it is allowed to have multiple functions as long (region, party) is equal (different categories)
		// so this query may return the same politician multiple times. This will be unnoticed since we hash-key everything
		// on id ]
		return self::getCouncil($rs, 'SELECT pf.politician, po.first_name, po.last_name, po.extern_id, pf.party
											FROM rs_raadsstukken r
											JOIN pol_politician_functions pf ON pf.region = r.region AND r.vote_date BETWEEN pf.time_start AND pf.time_end
											JOIN pol_politicians po ON po.id = pf.politician
											WHERE r.id = %i
											AND po.def_party IS NULL
											ORDER BY name_sortkey, last_name, first_name');
	}

	private static function getCouncil($obj, $query) {
		$id = is_object($obj) ? $obj->id : $obj;
		$me = new self();
		foreach (DBs::inst(DBs::SYSTEM)->query($query, $id)->fetchAllRows() as $v) {
			$me->addMember(new CouncilMember($v['politician'], $v['first_name'], $v['last_name'], $v['party'], $v['extern_id']));
		}
		return $me;
	}
}

class CouncilParty {
	public $id;
	public $name;
	public $short_form;
	private $members = array();

	public function __construct($id, $name, $short_form = null, $members = null) {
		$this->id = $id;
		$this->name = $name;
		$this->short_form = $short_form;
		if (is_array($members))
			$this->members = $members;
	}

	public function addMember(CouncilMember $m) {
		$this->members[$m->id] = $m;
	}

	public function getMembers() {
		return $this->members;
	}
}


class CouncilMember {
	public $id;
	public $firstName;
	public $lastName;
	public $party;
	public $extern_id; //this code is pretty much fucked up... we have CouncilMember's and Politician's which handles the same records...

	public function __construct($id, $firstName, $lastName, $party, $extern_id = null) {
		$this->id = $id;
		$this->firstName = $firstName;
		$this->lastName = $lastName;
		$this->party = $party;
		$this->extern_id = $extern_id;
	}

	public function formatName() {
		return $this->firstName . ' ' . $this->lastName;
	}
}

class CouncilView {
	private $_m;
	private $_p;

	public function __construct(&$members, &$parties) {
		$this->_m =& $members;
		$this->_p =& $parties;
	}

	public function getMembersByParty() {
		$ret = array();
		foreach ($this->_p as $p) {
			$party = &$ret[$p->name];
			$party = array();
			foreach ($p->getMembers() as $m) {
				$party[$m->id] = $m->formatName();
			}
		}
		ksort($ret);
		return $ret;
	}

	public function getPartiesByMember() {
		$ret = array();
		foreach ($this->_m as $m) {
			$party = &$this->_p[$m->party];
			$ret[$m->id] = $party;//->name;
		}
		ksort($ret);
		return $ret;
	}


	public function getMembersByPartyWithVotesAndNames($votes) {
		//[FIXME: this method has no right to exist!
		//  fetching all bastards that *may* vote and link this with
		//  real votes made is sick and silly ]

		$ret = array();
		foreach ($this->_p as $p) {
			$party = &$ret[$p->name];
			$party = array('id' => $p->id, 'short_form' => $p->short_form, 'politicians' => array());
			foreach ($p->getMembers() as $m) {
				$party['politicians'][$m->id] = array(
				  'name' => $m->formatName(),
				  'extern_id' => $m->extern_id,

				  /* [FIXME: if $votes doesn't contain mapping for $m->id, then
				   *         we will catch deadly "method called on non object" in our templates and die unexpectedly!] */
				  'vote' => @$votes[$m->id]);
			}
		}
		ksort($ret);
		return $ret;
	}

	public function toXml($partyVotes, $votes, $xml) {
		$s = $xml->getTag('parties');
		foreach ($this->getMembersByPartyWithVotesAndNames($votes) as $pname => $party) {
			$s .= $xml->getTag('party').
				$xml->fieldToXml('id', $party['id']).
				$xml->fieldToXml('name', $pname, true);
			if (null == ($pv = @$partyVotes[$party['id']]))
				$pv = new PartyVoteCache();
				$s .= $pv->toXml($xml).$xml->getTag('politicians');
			foreach ($party['politicians'] as $pid => $politician) {
				$s .= $xml->getTag('politician').
					$xml->fieldToXml('id', $pid).
					$xml->fieldToXml('name', $politician['name'], true).
					$xml->fieldToXml('vote', @$politician['vote']->vote).
					$xml->getTag('politician', true);
			}
			$s .= $xml->getTag('politicians', true).
				$xml->getTag('party', true);
		}
		return $s.$xml->getTag('parties', true);
	}

	private function votesToXml($votes, $xml) {
		return $xml->getTag('votes').
			$votes->toXml($xml);
			$xml->getTag('votes', true);
	}
}

?>