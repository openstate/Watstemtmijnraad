<?php

require_once('FormHandler.class.php');
require_once('Raadsstuk.class.php');
require_once('GettextPOModule.class.php');
require_once('Message.class.php');
require_once('Category.class.php');
require_once('RaadsstukCategory.class.php');
require_once('RaadsstukType.class.php');
require_once('RaadsstukTag.class.php');
require_once('RaadsstukSubmitType.class.php');
require_once('Council.class.php');
require_once('Submitter.class.php');
require_once('Tag.class.php');
require_once('InputFilterFactory.class.php');
require_once('Site.class.php');

abstract class FormPage extends FormHandler {
	protected $pofile;
	protected $council;
	protected $parents;
	protected $toVotes;
    protected $active_parties;

	const MAX_SUMMARY = 20480; //20k

	public function __construct() {
		$this->data = array(
			'id' => null,
			'title' => null,
			'metainfo' => null,
			'vote_date_array' => null,
			'vote_date' => null,
			'summary' => null,
			'code' => null,
			'type' => null,
			'type_name' => null,
			'tags' => null,
			'submitters' => null,
			'parent' => null,
			'show' => true,
			'unrestrict_parent' => false,
		);

		$this->errors = array(
			'title_required' => false,
			'date_invalid' => false,
			'summary_too_large' => false,
			'code_required' => false,
			'type_invalid' => false,
			'submitters_required' => false,
		);
	}

	public function processPost($post) {
		if (array_key_exists('cancel', $post))
			Dispatcher::header('/raadsstukken');
		parent::processPost($post);
	}

	public function processGet() {
		$this->loadLists();
	}

	protected function formatParent($rs) {
		return ($rs->code ? $rs->code.': ' : '').$rs->title;
	}

	protected function loadLists() {
		$date = strtotime($this->data['vote_date']);
		$this->council = Council::getCouncilByDate($_SESSION['role']->getRecord()->id, $date);
		$rs = new Raadsstuk();
		$this->parents = array(0 =>  "\xC2\xA0") + array_map(array($this, 'formatParent'), $rs->getList('', 'WHERE region = '.$_SESSION['role']->getRecord()->id.($this->data['id'] ? ' AND t.id != '.$this->data['id'] : '').' AND t.parent IS NULL ORDER BY vote_date DESC '.(!$this->data['unrestrict_parent'] ? ' LIMIT 15' : '')));
	}

	protected function assign($post) {
        //var_dump($post['show']); die;
		$this->data['site'] = '1';
		$this->data['title'] = @trim($post['title']);
		$this->data['metainfo'] = @trim($post['metainfo']);
		$this->data['vote_date'] = @trim($post['vote_date']);
		$this->data['summary'] = @trim($post['summary']);
		$this->data['code'] = @trim($post['code']);
		$this->data['type'] = @$post['type'];
		$this->data['tags'] = @array_filter(array_unique(array_map('trim', $post['tags'])));
		$this->data['submitters'] = @$post['submitters'];
		$this->data['categories'] = @array_filter(array_unique($post['cats']));
		$this->data['parent'] = in_array(@$post['type'], array(3, 4)) ? @$post['parent'] : 0;
		$this->data['show'] = isset($post['show']);
		$this->data['unrestrict_parent'] = isset($post['unrestrict_parent']);
        $this->data['party'] = @trim($post['party']);
        $this->data['ext_url'] = @trim($post['ext_url']);
		$this->toVotes = isset($post['submit_vote']);
		$this->loadLists();
	}

	protected function validate() {
		if (array_key_exists('preview', $_POST) || array_key_exists('preview', $_GET)) {
			return parent::validate();
		}

        if (!strlen($this->data['title'])) $this->errors['title_required'] = true;
		list($d, $m, $y) = explode('-', $this->data['vote_date']);
		if (!checkdate($m, $d, $y))
			$this->errors['date_invalid'] = true;
		if (strlen($this->data['summary']) > self::MAX_SUMMARY) $this->errors['summary_too_large'] = true;
		if (!strlen($this->data['code'])) $this->errors['code_required'] = true;
		if (!ctype_digit($this->data['type'])) $this->errors['type_invalid'] = true;
		if (!ctype_digit($this->data['site'])) $this->errors['site_invalid'] = true;
        if (!strlen($this->data['party'])) $this->errors['party'] = true;
        
        //5 - Burgerinitiatief (Burger)
		//6 - Onbekend (Onbekend)
		if ($this->data['type'] != 5 && $this->data['type'] != 6 && !@count($this->data['submitters'])) $this->errors['submitters_required'] = true;
		return parent::validate();
	}

	protected function preview() {
		$rst = new RaadsstukSubmitType();
		$rt = new RaadsstukType($this->data['type']);

        $rs = new stdClass();
		$rs->region = $_SESSION['role']->getRecord()->id;
		$rs->region_name = $_SESSION['role']->getRecord()->name;
		$rs->title = $this->data['title'];
		$rs->metainfo = $this->data['metainfo'];
		$rs->vote_date = $this->data['vote_date'];
		$filter = InputFilterFactory::filterHtmlStrict();
		$rs->summary = $filter->process($this->data['summary']);
		$rs->code = $this->data['code'];
		$rs->type = $this->data['type'];
		$rs->type_name = $rt->name;
		$rs->submitter = $rst->getSubmitType($this->data['type'], $this->data['submitters']);
		$rs->parent = $this->data['parent'] ? $this->data['parent'] : null;
		$rs->show = ($data['show'] == 'Stemming publiceren' || $data['show'] == 'Opslaan en publiceren' || $data['show'] == 1)? 1: 0;
		$rs->site_id = $this->data['site'];
		$rs->result = 0;
        $rs->party = $this->data['party'] ? $this->data['party'] : null;
        $rs->ext_url_info = $this->data['ext_url'] ? $this->data['ext_url'] : null;

		$cat = array();
		if (@$this->data['categories'])
			foreach ($this->data['categories'] as $c) {
				$ct = new Category($c);
				$cat[$ct->id] = $ct->name;
			}

		$subs = array();
		// 3 = Raadslid (Type = Motie/Amendement?)
		if (3 == $rs->submitter && isset($this->data['submitters'])) {
			foreach ($this->data['submitters'] as $s) {
				$pol = new Politician($s);
				$subs[$pol->id] = $pol->formatName();
			}
		}

		$tag = @$this->data['tags'];
		if (!$tag) $tag = array();

		$votes = $totals = array();
		foreach (array('rs', 'cat', 'subs', 'tag', 'votes', 'totals') as $var) $_SESSION['preview'][$var] = $$var;
	}

	protected function save(Record $r) {
		if (array_key_exists('preview', $_POST) || array_key_exists('preview', $_GET)) {
			$this->preview();
			return;
		}
        //var_dump($_POST['show']); die;
		$rst = new RaadsstukSubmitType();
       //var_dump($this->data); die;
		$r->region = $_SESSION['role']->getRecord()->id;
		$r->title = $this->data['title'];
		$r->metainfo = $this->data['metainfo'];
		$r->vote_date = implode('-', array_reverse(explode('-', $this->data['vote_date'])));;
		$filter = InputFilterFactory::filterHtmlStrict();
		$r->summary = $filter->process($this->data['summary']);
		$r->code = $this->data['code'];
		$r->type = $this->data['type'];
		$r->submitter = $rst->getSubmitType($this->data['type'], $this->data['submitters']);
		$r->parent = $this->data['parent'] ? $this->data['parent'] : null;
		
		if(isset($_POST['show'])) {
			//[FIXME: terrible IE6 hack, language specific! ]
			$r->show = ($_POST['show'] == 'Stemming publiceren' || $_POST['show'] == 'Opslaan en publiceren' || $_POST['show'] == 1)? 1: 0;
		} // else "Wijzigen en naar stemming" -- do not change show field
		//note: new raadsstukken have show = 0
		
		$r->site_id = $this->data['site'];
        $r->consensus = null;
        $r->party = (int) $this->data['party'] ? $this->data['party'] : null;
        $r->ext_url_info = $this->data['ext_url'] ? $this->data['ext_url'] : null;
		$r->save();

        $rc = new RaadsstukCategory();
		$rc->deleteByRaadsstuk($r->id);

		foreach (@$this->data['categories'] as $c) {
			$rc = new RaadsstukCategory();
			$rc->raadsstuk = $r->id;
			$rc->category = $c;
			$rc->save();
		}

		$obj = new Submitter();
		$obj->deleteByRaadsstuk($r->id);

		// 3 = Raadslid (Type = Motie/Amendement?)
		if (3 == $r->submitter) {
			foreach ($this->data['submitters'] as $s) {
				$obj = new Submitter();
				$obj->raadsstuk = $r->id;
				$obj->politician = $s;
				$obj->save();
			}
		}

		$rt = new RaadsstukTag();
		$currTags = $rt->getTagsByRaadsstukOnName($r->id);
		$allTags = Tag::getAssociativeOnName();

		foreach (@$this->data['tags'] as $t) {
			//$t = ucfirst($t);
			if (!($id = @$allTags[$t])) {
				$tag = new Tag();
				$tag->name = $t;
				$tag->save();
				$id = $tag->id;
			}
			if (!isset($currTags[$t])) {
				$rt = new RaadsstukTag();
				$rt->raadsstuk = $r->id;
				$rt->tag = $id;
				$rt->save();
			} else {
				unset($currTags[$t]);
			}
		}
		foreach ($currTags as $t) {
			$t->delete();
		}

		DBs::inst(DBs::SYSTEM)->query('select add_tags_to_vector('.$r->id.')');
	}

	private function getPOFile() {
		if (null == $this->pofile)
			$this->pofile = new GettextPOModule('index.po');
		return $this->pofile;
	}

	protected function action() {
		if ($this->toVotes) {
			$this->addMessage(Message::SUCCESS, 'success');
			Dispatcher::header('/raadsstukken/vote/'.$this->getRecord()->id);
		} elseif (array_key_exists('preview', $_POST) || array_key_exists('preview', $_GET)) {
			echo('http://'.$_SESSION['role']->getRecord()->subdomain.'.'.Dispatcher::inst()->domain.'.'.Dispatcher::inst()->tld.'/raadsstukken/raadsstuk/?preview=rs');
			die;
		} else
			Dispatcher::header('/raadsstukken/');
	}

	protected function error($e) {
		$this->addMessage(Message::ERROR, 'error');
	}

	protected function addMessage($mtype, $type) {
		MessageQueue::addMessage(new Message($mtype, sprintf($this->getPOFile()->getMsgStr('index.'.$type),
								 $this->getPOFile()->getMsgStr('index.action.'.$this->getAction()))));
	}

	abstract protected function getAction();

	public function show($smarty) {
		$rt = new RaadsstukType();
		$rst = new RaadsstukSubmitType();
        $types = $rt->getAssociativeOnId();
        
		$sites = array();
		foreach (Dispatcher::sessionUser()->listSites() as $site) $sites[$site->id] = $site->title;

		parent::show($smarty);
		$smarty->assign('sites', $sites);
		$smarty->assign('categories', Category::getDropdownCategoriesAll());
		$smarty->assign('types', $types);
		$smarty->assign('allTags', json_encode(Tag::getNames()));
		$smarty->assign('councilMembers', $this->council->getMembers());
		$smarty->assign('councilView', $this->council->getView()->getMembersByParty());
		$smarty->assign('rs_submitters', $rst->getRaadsstukTypes());
		$smarty->assign('rs_parents', $this->parents);
		
		$smarty->display('formPage.html');
	}
}

?>
