<?php

class AddPartyPage {
	protected $post = array();
	protected $error = array();

	public function __construct() {
		if (!array_key_exists('role', $_SESSION) || !($_SESSION['role'] instanceof BOUserRoleClerk))
			Dispatcher::forbidden();

		$this->region = $_SESSION['role']->getRecord();
		$p = new Party();
		$this->parties = $p->getList('', DBs::inst(DBs::SYSTEM)->formatQuery(
			'WHERE t.id NOT IN (SELECT party FROM pol_party_regions WHERE region = % AND time_start <= % AND %2 < time_end)',
			$this->region->id, $_SESSION['setup_wizard']['date']),
			'ORDER BY name'
		);
	}

	public function processPost($post) {
		if (array_key_exists('cancel', $post)) {
				Dispatcher::header('/wizard/party');
				return;
		}

		$this->post = $post;
		if (array_key_exists('party', $post)) {
			if ($post['party'] && $post['party'] != 'new' && !array_key_exists($post['party'], $this->parties))
				return;
			if ($post['party'] == 'new') {
				if (!@$post['name'] || strlen($post['name']) > 255)
					$this->error['name'] = true;
				if (@$post['has_short_form'] && (!@$post['short_form'] || strlen($post['short_form']) > 255))
					$this->error['short_form'] = true;
				if (@$post['combination']) {
					if (@$post['parent'])
						$this->post['parent'] = $post['parent'] = array_filter(array_unique($post['parent']));
					if (!@$post['parent'] || count($post['parent']) < 2)
						$this->error['parent'] = true;
				}
				if ($this->error)
					return;

				$p = new Party();
				$p->name = $post['name'];
				$p->combination = (int) ((boolean) @$post['combination']);
				$p->owner = $this->region->id;
				$p->short_form = @$post['has_short_form'] ? $post['short_form'] : null;
				$p->save();
				if ($p->combination)
					foreach ($post['parent'] as $parent)
						DBs::inst(DBs::SYSTEM)->query('INSERT INTO pol_party_parents (party, parent) VALUES (%, %)', $p->id, $parent);
				$post['party'] = $p->id;
			}
			if ($post['party']) {
				$lp = new LocalParty();
				$lp->party = $post['party'];
				$lp->region = $this->region->id;
				$lp->time_start = $_SESSION['setup_wizard']['date'];
				$lp->time_end = 'infinity';
				$lp->save();
				Dispatcher::header('/wizard/party');
			} else
				$this->error['party'] = true;
		}
	}
	
	public function show($smarty) {
		$smarty->assign('parties', $this->parties);
		$smarty->assign('post', $this->post);
		$smarty->assign('error', $this->error);
		$smarty->assign('region', $this->region);
		$smarty->display('addParty.html');
	}
}