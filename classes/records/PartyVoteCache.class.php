<?php

require_once('Vote.class.php');

class PartyVoteCache extends Record {
	protected $data = array(
		'id' => null,
		'party' => null,
		'party_name' => null,
		'party_short_name' => null,
		'party_logo' => null,
		'raadsstuk' => null,
		'vote_0' => 0,
		'vote_1' => 0,
		'vote_2' => 0,
		'vote_3' => 0,
        'special' => 0,
	);

	protected $extraCols = array(
		'party_name' => 'p.name',
		'party_short_name' => 'p.short_form',
		'party_logo' => 'p.image',
	);

	protected $tableName = 'rs_party_vote_cache';
	protected $multiTables = 'rs_party_vote_cache t JOIN pol_parties p ON p.id = t.party';


	public function loadVotesListAssociativeOnParty($raadsstuk, $order = null) {
		$result = array();
		foreach ($this->loadVotesList($raadsstuk, $order) as $votes) {
			$result[$votes->party] = $votes;
		}
		return $result;
	}

	public function getResult() {
		if ($this->vote_0 && $this->vote_1) return -1;
		for ($i = 0; $i < 4; $i++) {
			if ($this->{'vote_'.$i}) return $i;
		}
	}

	public function getResultTitle() {
		return Vote::getVoteTitleStatic($this->getResult());
	}

	public function toXml($xml) {
		return $xml->getTag('votes').
			$xml->fieldToXml('yea', $this->vote_0).
			$xml->fieldToXml('nay', $this->vote_1).
			$xml->fieldToXml('abstain', $this->vote_2).
			$xml->fieldToXml('absent', $this->vote_3).
			$xml->getTag('votes', true);
	}

	public function save() {
		throw new Exception('Operation not supported');
	}

    public function loadSpecialList($radpks, $order = 'ORDER BY party_name ASC') {
        if(!$radpks) return array();
        $pks = implode(',', array_map('intval', $radpks));
		return $this->getList('', "WHERE t.raadsstuk IN ({$pks}) AND t.special > 0", $order);
    }

    /**
     * Assign random voting parties to raadsstuk.
     * @param Raadsstuk|integer $raadsstuk
     */
    public static function randomizeParty($raadsstuk) {
        $id = is_object($raadsstuk)? $raadsstuk->id: intval($raadsstuk);
        //FIXME: nothing is available from static context, db link, tableName etc.
        
        $db = DBs::inst(DBs::SYSTEM);
        $db->query("UPDATE rs_party_vote_cache SET special = 0 WHERE raadsstuk = %i", $id); //clear selects
        
        $pro = $db->query("SELECT id FROM rs_party_vote_cache WHERE raadsstuk = %i AND vote_0 > 0 ORDER BY RANDOM() LIMIT 1", $id)->fetchCell();
        if($pro) { //set vote
            $db->query("UPDATE rs_party_vote_cache SET special = 1 WHERE id = %i", $pro);
        }

        $contra = $db->query("SELECT id FROM rs_party_vote_cache WHERE raadsstuk = %i AND vote_1 > 0" . ($pro? ' AND id <> %i': '') . " ORDER BY RANDOM() LIMIT 1", $id, $pro)->fetchCell();
        if($contra) {
            $db->query("UPDATE rs_party_vote_cache SET special = 2 WHERE id = %i", $contra);
        }
    }

	/**
	 * Fetch vote cache entries for given raadsstuk.
	 *
	 * @param Raadsstuk|integer $raadsstuk
	 * @param string $order
	 * @return array of PartyVoteCache
	 */
	public function loadVotesList($raadsstuk, $order = 'ORDER BY party_name ASC') {
		$id = is_object($raadsstuk)? $raadsstuk->id: intval($raadsstuk);
		return $this->getList('', $this->db->formatQuery('WHERE t.raadsstuk = %i', $id), $order);
	}

    /**
     * Fetch vote caches entries for given raadsstuk and party
     *
     * @param Raadsstuk|integer $raadsstuk
     * @param Party|integer $party
     * @param string $order
     * @return array of PartyVoteCache
     */
     public function loadVotesListWithParty($raadsstuk, $party, $order = 'ORDER BY party_name ASC') {
         $id = is_object($raadsstuk)? $raadsstuk->id: intval($raadsstuk);
         $party = is_object($party)? $party->id: intval($party);
         return $this->getList('', $this->db->formatQuery('WHERE raadsstuk = % AND party = %', $id, $party));
     }

}




?>