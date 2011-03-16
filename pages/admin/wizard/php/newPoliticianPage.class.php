<?php

class NewPoliticianPage {
	protected $post = array('time_start_default' => 1);
	protected $error = array();

	public function __construct() {
		if (!array_key_exists('role', $_SESSION) || !($_SESSION['role'] instanceof BOUserRoleClerk))
			Dispatcher::forbidden();

		$this->region = $_SESSION['role']->getRecord();
		$cat = new Category();
		$this->categories = $cat->getList('', '', 'ORDER BY id');
	}

	public function processGet($get) {
		if (array_key_exists('party', $get)) {
			$this->party = new Party();
			$this->party->load($get['party']);
		}
	}

	public function processPost($post) {
		if (array_key_exists('prev', $post)) {
			Dispatcher::header('/wizard/politician');
		}
		
		if (array_key_exists('autocomplete', $post)) {
			$pol = new Politician();
			$pols = DBs::inst(DBs::SYSTEM)->query('
				SELECT p.id, p.last_name||\', \'||p.first_name AS pol_name,
					MIN(COALESCE(COALESCE(pr.short_form, pr.name)||\', \'||r.name, \'\')) AS description
				FROM pol_politicians p
				LEFT JOIN pol_politician_functions f ON f.politician = p.id
				LEFT JOIN pol_parties pr ON f.party = pr.id
				LEFT JOIN sys_regions r ON f.region = r.id
				WHERE (last_name ILIKE % OR name_sortkey ILIKE %1) AND p.id NOT IN (
					SELECT politician FROM pol_politician_functions
					WHERE region = %2 AND %3 BETWEEN time_start AND time_end
				)
				GROUP BY p.id, pol_name
				ORDER BY pol_name',
				strtr($post['autocomplete'], array('%' => '\%', '_' => '\_', '\\' => '\\\\')).'%',
				$this->region->id, $_SESSION['setup_wizard']['date'])->fetchAllRows();
			header('Content-Type: application/json');
			echo(json_encode(array_merge(array(array('id' => '', 'name' => '', 'description' => 'Nieuwe politicus toevoegen')),
					array_map(create_function('$p',
						'$p["name"] = $p["pol_name"]; unset($p["pol_name"]); return $p;'),
						$pols))));
			die;
		}

		$this->post = $post;
		if (!@$post['category'] || !array_key_exists($post['category'], $this->categories))
			$this->error['category'] = true;
		if (!@$post['time_start_default']) {
			if (!@$post['time_start'])
				$this->error['time_start'] = true;
			else {
				$post['time_start'] = implode('-', array_reverse(explode('-', $post['time_start'])));
				if (strtotime($post['time_start']) === false)
					$this->error['time_start_invalid'] = true;
			}
		}
		if (@$post['time_end']) {
				$post['time_end'] = implode('-', array_reverse(explode('-', $post['time_end'])));
				if (strtotime($post['time_end']) === false)
					$this->error['time_end_invalid'] = true;
		}
		if ($this->error)
			return;
		if (!@$post['politician_id']) {
			if (!@$post['last_name'] || strlen($post['last_name']) > 255)
				$this->error['last_name'] = true;
			else {
				if (!@$post['first_name'] || strlen($post['first_name']) > 255)
					$this->error['first_name'] = true;
				if (@$post['last_name'] && strlen($post['title']) > 255)
					$this->error['title'] = true;
				if (!@$post['gender'])
					$this->error['gender'] = true;
				if (!@$post['email'] || strlen($post['email']) > 255)
					$this->error['email'] = true;
				elseif (!preg_match('/^[-a-z0-9.!#$%&\'*+\/=?^_`{}|~]+@[-a-z0-9.]+\.[a-z]{2,6}$/i', $post['email']))
					$this->error['email_invalid'] = true;
				if (@$post['extern_id'] && (strlen($post['extern_id']) > 10 || !ctype_digit($post['extern_id'])))
					$this->error['extern_id_invalid'] = true;
				if (!@$post['category'] || $post['category'] == -1)
					$this->error['category'] = true;
			}
			if ($this->error)
				return;
			$pol = new Politician();
			$pol->last_name = $post['last_name'];
			$pol->first_name = $post['first_name'];
			$pol->title = $post['title'];
			$pol->gender_is_male = (int) $post['gender'] == 'm';
			$pol->email = $post['email'];
			$pol->extern_id = (int) $post['extern_id'];
			$pol->save();
			$post['politician_id'] = $pol->id;
		}
		$app = new Appointment();
		$app->politician = $post['politician_id'];
		$app->party = $this->party->id;
		$app->region = $this->region->id;
		$app->category = $post['category'];
		$app->time_start = @$post['time_start_default'] ? $_SESSION['setup_wizard']['date'] : $post['time_start'];
		$app->time_end = @$post['time_end'] ? $post['time_end'] : 'infinity';
		$app->save();
		Dispatcher::header('/wizard/politician/?open='.$this->party->id);
	}

	public function show($smarty) {
		$smarty->assign('post', $this->post);
		$smarty->assign('error', $this->error);
		$smarty->assign('region', $this->region);
		$smarty->assign('party', $this->party);
		$smarty->assign('categories', $this->categories);
		$smarty->display('newPolitician.html');
	}
}