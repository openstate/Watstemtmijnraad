<?php


/**
* Time range managing utility.
*/
class TimeRange {
	
	private $ranges = array();
	private $changes = array();
	private $virtual_id = -1;
	private $range_ids = array();
	
	/**
	 * Create new mananger.
	 */
	public function __construct() {
	}
	
	/**
	 * Add existing range.
	 * This method automatically merges ranges to disjoint ranges.
	 * 
	 * Note: null $id's will be optimized, that means if a range was added with $id == null
	 * and later this range is discarded by another range, then unnecessary 'insert'-'delete'
	 * sequence will disappear from changelog. Any non-null $id is considered to be really
	 * existing, so it will never be optimized.
	 * 
	 * Warning: the $id must be > 0 or null.
	 * 
	 * @param integer $id range object id, null if range is new
	 * @param string $start start time as 'yyyy-mm-dd' string or null for -infinity
	 * @param string $end end time as 'yyyy-mm-dd' string or null for infinity
	 * @param TimeRange $move_range if set, then range will be moved from given $range to this time range
	 * @return boolean true if time range was changed, false if new range was unneeded
	 */
	public function addRange($id, $start = null, $end = null, $move_range = null) {
		if($start != null && $end != null && strcmp($start, $end) > 0) throw new InvalidArgumentException("Invalid time range, start({$start}) > end({$end})!");
		
		$affected = array();
		$reuse = null;
		$left_extend = null;
		$right_extend = null;
		
		//database inconsistency is big fucking case, so make everything monkeyproof
		$id = $id === null? $this->virtual_id--: $id;
		if($id >= 0) {
			if(isset($this->range_ids[$id])) {
				if($this->range_ids[$id]['start'] != $start || $this->range_ids[$id]['end'] != $end) throw new InvalidArgumentException("Range with id: {$id} is already defined as: [{$this->range_ids[$id]['start']}:{$this->range_ids[$id]['end']}], but now being redefined as: [{$start}:{$end}]");
				else return; //dublicate
			} else $this->range_ids[$id] = array('start' => $start, 'end' => $end);
		}
		
		foreach ($this->ranges as $k => $range) {
			//0 - equal, -1 - good, we contain $start, 1 - bad, we need to extend to left
			$left_left = ($start == $range['start']? 0: ($start != '' && $range['start'] == ''? -1: ($start != '' && strcmp($start, $range['start']) >= 0? -1: 1)));
			//0 - equal, -1 - bad, overlap, 1 - good, no overlap
			$left_right = ($end == $range['start']? 0: ($end != '' && $range['start'] == ''? -1: ($end != '' && strcmp($end, $range['start']) <= 0? 1: -1))); //new range before old range
			
			//0 - equal, 1 - good, we contain $end, -1 - we need to extend to right
			$right_right = ($end == $range['end']? 0: ($end != '' && $range['end'] == ''? 1: ($end != '' && strcmp($end, $range['end']) <= 0? 1: -1)));
			//0 - equal, 1 - bad, overlap, -1 good, no overlap
			$right_left = ($start == $range['end']? 0: ($start != '' && $range['end'] == ''? 1: ($start != '' && strcmp($start, $range['end']) >= 0? -1: 1))); //new range after old range
			
			//if(isset($GLOBALS['fuck'])) echo "{$left_left} : {$left_right} : {$right_left} : {$right_right} <br>\n";
			
			if($right_left < 0 || $left_right > 0) continue; //no overlap, keep looking (end up with insert)
			elseif($left_left <= 0 && $right_right >= 0) { //new range is contained within current range, discard changes
				if($move_range) $range->release($id);
				//delete new range (discard)
				if($id >= 0) $this->changes[] = array(
					'id' => $id,
					'action' => 'delete',
					'start' => $start,
					'end' => $end
				);
				return false;
			} elseif ($left_left >= 0 && $right_right <= 0) { //old range is contained withing new one				
				if($id < 0 && $reuse === null && $range['id'] >= 0) $reuse = $k;
				else $affected[$k] = $range; //delete old range
			} elseif($right_right >= 0 && $left_right <= 0) { //extend left
				if($left_extend != null) throw new ErrorException("Overlapping left extension! Software bug detected!");
				$left_extend = $k;
				$affected[$k] = $range;
			} elseif($left_left <= 0 && $right_left >= 0) { //extend right
				if($right_extend != null) throw new ErrorException("Overlapping right extension! Software bug detected!");
				$right_extend = $k;	
				$affected[$k] = $range;    
			} else {
				throw new RuntimeException("Unknown descision: {$left_left} : {$left_right} : {$right_right} | {$right_left}");
			}
		}
		
		//echo "|r:{$right_extend}|l:{$left_extend}|u:{$reuse} = {$id}({$start} : {$end})\n";
		
		//execute extension
		if($left_extend === null && $right_extend === null) { //insert new range
			if($reuse !== null) { //reuse to delete real range, new range is virtual, ignore insert
				$this->changes[] = array(
					'id' => $this->ranges[$reuse]['id'],
					'action' => 'update',
					'start' => $start,
					'end' => $end
				);
				$this->ranges[$reuse]['start'] = $start;
				$this->ranges[$reuse]['end'] = $end;
			} else {
				if($id < 0 || $move_range != null) $this->changes[] = array( //only newly defined or moved range
					'id' => $id,
					'action' => $id < 0? 'insert': 'update',
					'start' => $start,
					'end' => $end,
				);
				
				$this->ranges[] = array(
					'id' => $id,
					'start' => $start,
					'end' => $end
				);
			}
		} elseif ($right_extend === null) { //right edge, extend to left
			//choice, either right edge or new $id
			if($id < 0 || $this->ranges[$left_extend]['id'] >= 0) { //real object
				$update = $this->ranges[$left_extend]['id'];
				$delete = $id;
			} else { //possibly $id is real
				$update = $id;
				$delete = $this->ranges[$left_extend]['id'];
			}
			
			$this->ranges[$left_extend]['start'] = $start; //extend to left, we know we don't touch left edge
			$this->ranges[$left_extend]['id'] = $update;
			unset($affected[$left_extend]); //prevent deleting
			
			$this->changes[] = array(
				'id' => $update,
				'action' => 'update',
				'start' => $start,
				'end' => $this->ranges[$left_extend]['end'],
			);
					
			if($delete != $id || $id >= 0) $this->changes[] = array( //delete new range
				'id' => $delete,
				'action' => 'delete',
				'start' => null,
				'end' => null
			);
		} else { //left edge and possibly right edge, extend to right (or collapse with right edge)
			//choice, $id, right and possibly left end
			$del = array();
			if($this->ranges[$right_extend]['id'] >= 0) { //left edge, oldest record
				$update = $this->ranges[$right_extend]['id'];
				if($id >= 0) $del[] = $id;
				if($left_extend !== null) $del[] = $this->ranges[$left_extend]['id'];
			} elseif ($left_extend !== null && $this->ranges[$left_extend]['id'] >= 0) { //right edge, newest record
				$update = $this->ranges[$left_extend]['id'];
				if($id >= 0) $del[]  = $id;
				$del[] = $this->ranges[$right_extend]['id'];
			} else { //both edges are virtual, possibly $id is not virtual
				$update = $id;
				$del[] = $this->ranges[$right_extend]['id'];
				if($left_extend !== null) $del[] = $this->ranges[$left_extend]['id'];
			}
			
			$this->ranges[$right_extend]['end'] = $left_extend === null? $end: $this->ranges[$left_extend]['end'];
			$this->ranges[$right_extend]['id'] = $update;
			unset($affected[$right_extend]); //prevent deleting
			if($left_extend !== null) {
				unset($affected[$left_extend]); //maybe id reuseal, prevent deleting
				unset($this->ranges[$left_extend]);
			}
			
			$this->changes[] = array(
				'id' => $update,
				'action' => 'update',
				'start' => $this->ranges[$right_extend]['start'],
				'end' => $this->ranges[$right_extend]['end']
			);
			
			foreach ($del as $delid) {
				$this->changes[] = array( //delete object
					'id' => $delid,
					'action' => 'delete',
					'start' => null,
					'end' => null
				);
			}
		}
		
		//delete obsolete ranges
		foreach ($affected as $k => $range) {
			$this->changes[] = array(
				'id' => $range['id'],
				'action' => 'delete',
				'start' => null,
				'end' => null
			);
			
			unset($this->ranges[$k]);
		}
		
		if($move_range) $move_range->release($id);
		return true;
	}
	
	/**
	 * Release ownership of given range.
	 * TimeRange-to-TimeRange movements.
	 *
	 * @param integer $id range id
	 */
	protected function release($id) {
		unset($this->range_ids[$id]);
		
		foreach ($this->ranges as $pos => $rng) {
			if($rng['id'] == $id) {
				unset($this->ranges[$pos]);
				break;
			}
		}
		
		foreach ($this->changes as $pos => $chg) {
			if($chg['id'] = $id) unset($this->changes[$pos]);
		}
	}
	
	/**
	 * Add a dependent range.
	 * This method ensures given $start/$end range lies within registered ranges.
	 * If not, then existing ranges will be extended.
	 * 
	 * Note: this method simply adds new range with id = null.
	 *
	 * @param string $start start time as 'yyyy-mm-dd' string or null for -infinity
	 * @param string $end end time as 'yyyy-mm-dd' string or null for infinity
	 * @return boolean true if time range was changed, false if new range was unneeded
	 */
	public function addContentRange($start = null, $end = null) {
		return $this->addRange(null, $start, $end);
	}
	
	
	/**
	 * Returns true if range with given id is defined and is not deleted by another range.
	 * @param integer $id range id
	 * @return boolean true - update/delete will not fail with exception, false - update/delete is not possible
	 */
	public function isUpdateable($id) {
		if(!isset($this->range_ids[$id])) return false; //never added
		foreach ($this->ranges as $rng) if($rng['id'] == $id) return true;
		return false; //deleted
	}
	
	/**
	 * Update specific range.
	 * If range is not <tt>isUpdateable($id)</tt> then exception will be raised.
	 * This means you better do not try to update range after add/merge range operations
	 * that may cause range removal (your range is deleted).
	 * 
	 * If new range overlaps existing range, then both ranges will be merged (this
	 * range will be updated, other involved ranges will be deleted).
	 * 
	 * @throws InvalidArgumentException range $id is not found
	 * @param integer $id the range to edit
	 * @param string $start range start in 'yyyy-mm-dd' format or null for '-infinity'
	 * @param string $end range stop in 'yyyy-mm-dd' format or null for 'infinity'
	 */
	public function updateRange($id, $start = null, $end = null) {
		if(isset($this->range_ids[$id])) {
			foreach ($this->ranges as $k => $rng) if($rng['id'] == $id) {
				if($start != null && $end != null && strcmp($start, $end) > 0) throw new InvalidArgumentException("Invalid time range, start({$start}) > end({$end})!");
				$affected = array(); //to delete
				$min_start = $start;
				$max_end = $end;
				
				if($rng['start'] != '' || $rng['end'] != '') { //search affected ranges
					foreach ($this->ranges as $schidx => $srch) {
						if($schidx != $k && $rng['start'] != '' && strcmp($srch['end'], $rng['start']) <= 0 && ($start == '' || strcmp($srch['end'], $start) >= 0)) { //left wing possible
							$affected[] = $schidx;
							if($min_start != '') $min_start = $srch['start'] == ''? null: strcmp($srch['start'], $min_start) < 0? $srch['start']: $min_start;
						}
						if($schidx != $k && $rng['end'] != '' && strcmp($rng['end'], $srch['start']) <= 0 && ($end == '' || strcmp($srch['start'], $end) <= 0)) { //right wing possible
							$affected[] = $schidx;
							if($max_end != '') $max_end = $srch['end'] == ''? null: strcmp($srch['end'], $max_end) > 0? $srch['end']: $max_end;
						}
					}
				}
				
				//update range
				$this->ranges[$k]['start'] = $min_start;
				$this->ranges[$k]['end'] = $max_end;
				$this->changes[] = array(
					'id' => $id,
					'action' => 'update',
					'start' => $min_start,
					'end' => $max_end
				);
				
				//delete affected ranges
				foreach ($affected as $aff) {
					$this->changes[] = array(
						'id' => $this->ranges[$aff]['id'],
						'action' => 'delete',
						'start' => null,
						'end' => null
					);
					unset($this->ranges[$aff]);
				}
				return;
			}
		}
		throw new InvalidArgumentException("Can't update range. Range id: {$id} is not defined or already deleted!");
	}
	
	/**
	 * Delete specific range.
	 * If range is not <tt>isUpdateable($id)</tt> then exception will be raised.
	 * This means you better do not try to update range after add/merge range operations
	 * that may cause range removal (your range is deleted).
	 * 
	 * @throws InvalidArgumentException range $id is not found
	 * @param integer $id the range to edit
	 */
	public function deleteRange($id) {
		if(isset($this->range_ids[$id])) {
			foreach ($this->ranges as $k => $rng) if($rng['id'] == $id) {
				unset($this->ranges[$k]);
				$this->changes[] = array(
					'id' => $id,
					'action' => 'delete',
					'start' => null,
					'end' => null
				);
				return;
			}
		}
		throw new InvalidArgumentException("Can't update range. Range id: {$id} is not defined or already deleted!");
	}
	
	/**
	 * Deletes specific time range.
	 * This method ensures the range [$start - $end] is not allocated by any other range.
	 * This may involve splitting a range into two ranges, in this case existing range-id
	 * will be assigned to left part.
	 * 
	 * Note: specifying (null, null) clears all ranges
	 *
	 * @param string $start range start in 'yyyy-mm-dd' format or null for '-infinity'
	 * @param string $end range stop in 'yyyy-mm-dd' format or null for 'infinity'
	 */
	public function deleteContentRange($start = null, $end = null) {
		
	}
	
	/**
	 * Returns true if given range touches any other defined range.
	 *
	 * @param string $start range start in 'yyyy-mm-dd' format or null for '-infinity'
	 * @param string $end range stop in 'yyyy-mm-dd' format or null for 'infinity'
	 * @return boolean true range is overlapped with any other range in this object, false - given range is completely disjoint
	 */
	public function isOverlaped($start = null, $end = null) {
		if($start != null && $end != null && strcmp($start, $end) > 0) throw new InvalidArgumentException("Invalid time range, start({$start}) >= end({$end})!");
		foreach ($this->ranges as $k => $range) {
			//0 - equal, -1 - good, we contain $start, 1 - bad, we need to extend to left
			$left_left = ($start == $range['start']? 0: ($start != '' && $range['start'] == ''? -1: ($start != '' && strcmp($start, $range['start']) >= 0? -1: 1)));
			//0 - equal, -1 - bad, overlap, 1 - good, no overlap
			$left_right = ($end == $range['start']? 0: ($end != '' && $range['start'] == ''? -1: ($end != '' && strcmp($end, $range['start']) <= 0? 1: -1))); //new range before old range
			
			//0 - equal, 1 - good, we contain $end, -1 - we need to extend to right
			$right_right = ($end == $range['end']? 0: ($end != '' && $range['end'] == ''? 1: ($end != '' && strcmp($end, $range['end']) <= 0? 1: -1)));
			//0 - equal, 1 - bad, overlap, -1 good, no overlap
			$right_left = ($start == $range['end']? 0: ($start != '' && $range['end'] == ''? 1: ($start != '' && strcmp($start, $range['end']) >= 0? -1: 1))); //new range after old range
			
			if($right_left < 0 || $left_right > 0) continue; //no overlap, keep looking
			else return true;
		}
		return false;
	}
	
	/**
	 * Returns true if given date is covered by any range.
	 *
	 * @param string $date the date to check 'yyyy-mm-dd' format
	 * @return boolean true this range contains given date, false otherwise
	 */
	public function containsDate($date) {
		if(!$date) throw new InvalidArgumentException("Date is not given: {$date}");
		
		foreach ($this->ranges as $k => $range) {
			//0 - equal, -1 - good, we contain $date, 1 - bad, we do not contain $date
			$left_left = ($date == $range['start']? 0: ($range['start'] == ''? -1: (strcmp($date, $range['start']) >= 0? -1: 1)));			
			//0 - equal, 1 - good, we contain $date, -1 - we do not contain $date
			$right_right = ($date == $range['end']? 0: ($range['end'] == ''? 1: (strcmp($date, $range['end']) <= 0? 1: -1)));

			if($left_left <= 0 && $right_right >= 0) return true;
		}
		return false;
	}
	
	/**
	 * Play merge changes.
	 * Method returns list of delete/insert/update actions to commit the merge.
	 *
	 * @return array of actions [(id, 'delete'|'insert'|'update', start, end)]
	 */
	public function playChanges() {
		//play all changes, then report the last snapshot only
		$ranges = array();
		$deletes = array();
		foreach ($this->changes as $chg) {
			$id = $chg['id'];
			if(!isset($ranges[$id])) { //insert - virtual, update/delete - real
				if(isset($deletes[$id])) throw new RuntimeException("Updating real range object after deletion: {$id}. Software bug detected!");
                switch ($chg['action']) {
					case 'delete':
                        if($id > 0) $deletes[$id] = $chg; //real object deletion
						unset($ranges[$id]); //double delete will be left unnoticed =)
						break;
                }
				$ranges[$id] = $chg;
			} else { //update existing range
				switch ($chg['action']) {
					case 'delete': if($id > 0) $deletes[$id] = $chg; //real object deletion
								   unset($ranges[$id]); //double delete will be left unnoticed =)
							 	   break;
					case 'update': $ranges[$id] = $chg; break;
					case 'insert': throw new RuntimeException("Primary key collision! Software bug detected!");
				}
			}
		}
		
		//updated of virtuals change to insert, normalize
		foreach ($ranges as $id => $chg) {
			if($id < 0) {
				if($ranges[$id]['action'] == 'delete') throw new RuntimeException("Virtual deletion without create: {$ranges[$id]['start']}:{$ranges[$id]['end']}");
				$ranges[$id]['action'] = 'insert';
				//$ranges[$id]['id'] = null;
			} else if($this->range_ids[$id]['start'] == $chg['start'] && $this->range_ids[$id]['end'] == $chg['end']) unset($ranges[$id]); //skip unchaged ranges
		}
		foreach ($deletes as $id => $chg) $ranges[$id] = $chg;
		
		return array_values($ranges);
	}
	
	/**
	 * Clear changelog.
	 * After you have successfully commited changes and you want to keep this object,
	 * call this method to clear the change log (clears playChanges).
	 */
	public function clearChanges(array $resolved_ids) {
		$this->changes = array();
		
		foreach ($this->ranges as &$rng) {
			if($rng['id'] < 0) $rng['id'] = isset($resolved_ids[$rng['id']])? $resolved_ids[$rng['id']]: $rng['id'];
			$this->range_ids[$rng['id']] = array('start' => $rng['start'], 'end' => $rng['end']);
		}
	}
	
	/**
	 * List unordered set of ranges.
	 * @return array of (id, start, end) structures
	 */
	public function listRanges() {
		return $this->ranges;
	}
	
	
	
	/**
	 * Accepts PostgresSQL timezone as 'yyyy-mm-dd hh:mm:ss.shit' and returns
	 * correct 'yyyy-mm-dd'.
	 *
	 * @param string $start postregres timestamp ($obj->time_start)
	 * @param stirng $end postgres timestamp ($obj->time_end)
	 * @return array (start, end), can be used with list()
	 */
	public static function postgresTimes($start, $end) {
		$start = ($start == '' || $start == '-infinity')? null: reset(explode(' ', $start));
		$end = ($end == '' || $end == 'infinity')? null: reset(explode(' ', $end));
		return array($start, $end);
	}
}

/*
error_reporting(E_ALL);
ini_set('display_errors', 'on');


$r = new TimeRange();
*/
/*
$r->addRange(null, '2006-05-01', '2007-03-01'); //[*]
$r->addRange(null, '2008-05-01', '2009-03-01'); //[] [*]
$r->addRange(40, '2006-07-01', '2007-05-01'); // overlap left
$r->addRange(null, '2008-03-01', '2009-01-01'); //overlap right
$r->addRange(null, '2006-06-01', '2006-08-01'); //fully discarded
$r->addRange(null, '2009-03-02', '2010-05-01'); // [] [] [*]
$r->addRange(null, '2006-07-01', '2010-03-01'); // overlap left, right and discard middle
*/

/*
$r->addRange(1, null, null);
$r->addRange(2, null, null);
$r->addRange(3, '2008-02-01', null);
$r->addRange(null, null, '2008-05-01');
$r->addRange(null, ' 2006-08-09', '2010-01-01');

echo "<pre>\n";
//print_r($r);

var_dump($r->listRanges());

try {
	foreach ($r->playChanges() as $chg) {
		echo "Change, id: {$chg['id']}, action: {$chg['action']}, range: {$chg['start']} - {$chg['end']}\n<br>";
	}
}catch (Exception $e) {
	echo $e;
}

die();
*/
?>
