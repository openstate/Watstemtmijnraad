<?php


/** Builds raadsstukken selection query used by exporter. */
class ExportQueryBuilder {
	private $codes = array();
	private $title = array();
	private $summary = array();
	private $date_ranges = array();
	private $parties = array();
	private $categories = array();
	private $tags = array();
	private $regions = array();
	private $empty = false;
	
	/** AND code is in $code */
	function code($code) {
		if(trim($code)) $this->codes[] = trim($code);
	}
	
	/** AND title contains keywords {OR title contains keywords2...} */
	function title($keywords) {
		$keywords = array_filter(preg_split('#[^\w\d]+#', $keywords));
		if($keywords) {
			$kws = array();
			foreach ($keywords as $kw) {
				$kw = trim(str_replace(array('%', '_'), '', $kw));
				if($kw) $kws[] = $kw;
			}
			if($kws) $this->title[] = $keywords;
		}
	}
	
	/** AND summary contains keywords {OR summary contains keywords2...} */
	function summary($keywords) {
		$keywords = array_filter(preg_split('#[^\w\d]+#', $keywords));
		if($keywords) {
			$kws = array();
			foreach ($keywords as $kw) {
				$kw = trim(str_replace(array('%', '_'), '', $kw));
				if($kw) $kws[] = $kw;
			}
			if($kws) $this->summary[] = $keywords;
		}
	}
	
	/** AND vote data is between {intervals}. */
	function voteDate($from, $to) {
		if(!$from && !$to) return;
		$this->date_ranges[] = array($from, $to);
	}
	
	/** AND voted by parties $id */
	function party($id) {
		$this->parties[] = intval($id);
	}
	
	/** AND has category $id */
	function category($id) {
		$this->categories[] = intval($id);
	}
	
	/** AND has tag in $id */
	function tag($id) {
		$this->tags[] = intval($id);
	}
	
	/** AND region is in $id */
	function region($id) {
		$this->regions[] = intval($id);
	}
	
	/** AND FALSE */
	function emptySet() {
		$this->empty = true;
	}
	
	/** ugly way to build PDO query */
	function buildWhere($db) {
		$mwhere = "";
		
		if($this->empty) {
			return " AND FALSE";
		}
		
		if($this->codes) {
			$cd = array();
			foreach ($this->codes as $code) $ct[] = $db->quote($code);
			$mwhere .= " AND r.code IN (".implode(', ', $ct).")";
		}
		
		if($this->title) {
			$cd = array();
			foreach ($this->title as $titlekw) {
				$cd[] = 'r.title ILIKE '.$db->quote('%'.implode('%', $titlekw).'%');
			}
			$mwhere .= ' AND ('.implode(' OR ', $cd).')';
		}
		
		if($this->summary) {
			$cd = array();
			foreach ($this->summary as $kws) {
				$cd[] = 'r.summary ILIKE '.$db->quote('%'.implode('%', $kws).'%');
			}
			$mwhere .= ' AND ('.implode(' OR ', $cd).')';
		}
		
		if($this->date_ranges) {
			$dt = array();
			foreach ($this->date_ranges as $rng) {
				if($rng[0] && $rng[1]) $dt[] = 'r.vote_date BETWEEN '.$db->quote($rng[0]).' AND '.$db->quote($rng[1]);
				elseif($rng[0]) $dt[] = 'r.vote_date >= '.$db->quote($rng[0]);
				else $dt[] = 'r.vote_date <= '.$db->quote($rng[1]);
			}
			$mwhere .= ' AND '.implode(' OR ', $dt);
		}
		
		if($this->regions) {
			$mwhere .= ' AND r.region IN ('.implode(', ', $this->regions).')';
		}
		
		if($this->categories) {
			$mwhere .= ' AND r.id IN (SELECT raadsstuk FROM rs_raadsstukken_categories WHERE category IN ('.implode(', ', $this->categories).'))';
		}
		
		if($this->tags) {
			$mwhere .= ' AND r.id IN (SELECT raadsstuk FROM rs_raadsstukken_tags WHERE tag IN ('.implode(', ', $this->tags).'))';
		}
		
		if($this->parties) {
			$mwhere .= ' AND r.id IN (SELECT raadsstuk FROM rs_raadsstukken_submitters rs JOIN pol_politician_functions pf ON pf.politician = rs.politician WHERE pf.party IN ('.implode(', ', $this->parties).'))';
		}
		
		return $mwhere;
	}
}

?>