<?php

require_once('Raadsstuk.class.php');
require_once('Region.class.php');
require_once('Party.class.php');
require_once('Vote.class.php');
require_once('VoteMessage.class.php');
require_once('Message.class.php');
require_once('GettextPOModule.class.php');
require_once('Council.class.php');

class VotePage {
	private $rs;
	private $votes;

	private $id; 
	
	public function processGet($get) {
		try {
			$this->id = $get['id'];
			$this->rs = new Raadsstuk($get['id']);
		} catch (Exception $e) {
			Dispatcher::forbidden();
		}
//		if (!$this->rs->showVotes()) Dispatcher::forbidden();

		$vote = new Vote();
		$this->votes = $vote->loadByRaadsstuk($this->rs->id);
		$this->partyMessages = $this->rs->getPartyMessages();
	}

	public function processPost($post) {
		$preview = array_key_exists('preview', $_POST) || array_key_exists('preview', $_GET);

		if (array_key_exists('cancel', $_POST))
			Dispatcher::header('/raadsstukken/');

		$pofile = new GettextPOModule('vote_form.po');
		$voters = $this->rs->getVoters();
		try {
			$votePids = array();
			if ($preview) $_SESSION['preview']['votes'] = array();
			if (!@$post['politician']) $post['politician'] = array();
			foreach ($post['politician'] as $pid => $vote) {
				if (ctype_digit($vote) && $voters[$pid]) {
					if ($preview) {
						$voteObj = new stdClass();
						$voteObj->politician = $pid;
						$pol = new Politician($pid);
						foreach(array('title', 'first_name', 'last_name', 'gender_is_male') as $key)
							$voteObj->$key = $pol->$key;
						$voteObj->raadsstuk = $this->rs->id;
						$voteObj->vote = $vote;
						$voteObj->vote_title = Vote::getVoteTitleStatic($vote);
						$_SESSION['preview']['votes'][$pid] = $voteObj;
					} else {
						if ($voteObj = &$this->votes[$pid] == null)
							$voteObj = new Vote();
						
                        if($vote == 3){ //'Afwezig'
                        	$_SESSION['absents'][$pid] = true;
                        }

						if($vote == 4) { //No votes
							$_SESSION['absents'][$pid] = false;
							$query = "DELETE FROM rs_votes WHERE raadsstuk = %i AND politician = %i";
							$db = DBs::inst(DBs::SYSTEM);
							$db->query('BEGIN');
							$db->query($query, $this->rs->id, $pid);
							$db->query('COMMIT');
						}else {
                        	$voteObj->politician = $pid;
							$voteObj->raadsstuk = $this->rs->id;
							$voteObj->vote = $vote;
							$voteObj->message = isset($post['message_politician'][$pid]) ? $post['message_politician'][$pid] : null;
							$voteObj->save();
							$votePids[] = (int) $pid;
						}
					}
				}
			}

            if(!$preview && $this->rs) {
                PartyVoteCache::randomizeParty($this->rs);
            }
			
			if(!$preview && $this->rs) {
				$this->rs->consensus = (int) $post['consensus'];
                $this->rs->save();
			}
			
			if (!$preview)
				foreach (array_filter($post['message_party']) as $pid => $message) {
					$messageObj = @$this->partyMessages[$pid];
					if (!$messageObj)
						$messageObj = new VoteMessage();

					$messageObj->party = (int) $pid;
					$messageObj->raadsstuk = (int) $this->rs->id;
					$messageObj->message = $message;
					$messageObj->save();
			}
			if (!$preview)
				DBs::inst(DBs::SYSTEM)->query('DELETE FROM rs_votes WHERE raadsstuk = % %l', $this->rs->id,
					$votePids ? 'AND politician NOT IN ('.implode(', ', $votePids).')' : '');

			if ($preview) {
				$_SESSION['preview']['result'] = $post['result'];
			} else {
				$this->rs->result = @$post['result'];
				if (array_key_exists('show', $post)) {
					//[FIXME: hack needed for IE6, could lead to localization hell!!! ]
                    if($post['show'] == 'Stemming publiceren') {
                        $this->rs->show = 1;
                    } elseif ($post['show'] == '1') {
                        $this->rs->show = 1;
                    } else {
                        $this->rs->show = 0;
                    }
                }
					//$this->rs->show = (is_numeric($post['show'])) ? $post['show'] : 1;
				$this->rs->vote_message = @$post['message_council'] ? $post['message_council'] : null;
				$this->rs->save();
			}
		} catch (Exception $e) {
			MessageQueue::addMessage(new Message(Message::ERROR, $pofile->getMsgStr('votes.error')));
			if (DEVELOPER) throw $e;
			return;
		}
		MessageQueue::addMessage(new Message(Message::SUCCESS, $pofile->getMsgStr('votes.success')));

		if ($preview) {
			$region = new Region();
			$region->load($this->rs->region);
			echo('http://'.$region->subdomain.'.'.Dispatcher::inst()->domain.'.'.Dispatcher::inst()->tld.'/raadsstukken/raadsstuk/'.$this->rs->id.'/?preview=vote');
			die;
		}
        
		if (isset($post['submit_edit']))
			Dispatcher::header('/raadsstukken/edit/'.$this->rs->id);
		else
			Dispatcher::header('/raadsstukken/');
	}

	public function show($smarty) {
		$party = new Party();
		$council = Council::getCouncilFromRaadsstuk($this->rs);
		$vote_parties = $party->listPotentialVotingParties($this->rs->vote_date);

        $smarty->assign('consensus', array(1 => 'Totale consensus', 2 => 'Midden verdeeld', 3 => 'Oppositie en Collegepartijen'));
        $smarty->assign('consensus_selected', 1);
		$smarty->assign('region', new Region($this->rs->region));
		$smarty->assign('council', $council->getView()->getMembersByPartyWithVotesAndNames($this->votes));
		$smarty->assign('absents', @$_SESSION['absents']);
		$smarty->assign('voting_parties', $vote_parties);
		$smarty->assign('partyMessages', $this->partyMessages);
		$smarty->assign('raadsstuk', $this->rs);
		$smarty->assign('results', Raadsstuk::getResultArray());
		$smarty->display('votePage.html');
	}
}

?>
