<?php

require_once('IndexHandler.class.php');
require_once('SecurityException.class.php');
require_once('FormPage.class.php');
require_once('LocalParty.class.php');
require_once('PartyParent.class.php');

/**
 *
 * Description of addPartyBaseclass
 *
 * @author polichism
 */
class AddPartyPage extends IndexHandler {

    private $parties;
    private $region;

    public function processGet($get) {
        if(!$get['region']) {
            Dispatcher::header('/');
        }
        $this->region = $get['region'];
        $p = new LocalParty();
        $this->parties = $p->loadByRegion(@$get['region']);

    }

    public function processPost($post) {
        $p = new Party();
        $db = DBs::inst(DBs::SYSTEM);

        $party_result = $p->getCount('', $db->formatQuery('WHERE t.name = %s', $post['party_new']));

        if($party_result == '0') {
            $p->name = $post['party_new'];
            $p->owner = $post['region'];
            if(!empty($post['short_form']) && $post['short'] != '0') {
                $p->short_form = $post['short_form'];
            }

            if($post['combination'] != '0') {
                $p->combination = 0;
            } else {
                $p->combination = 1;
            }
        
            $p->save();

            $p_id = $db->query('SELECT t.id FROM pol_parties t WHERE t.name = %s', $p->name)->fetchRow();
            if($post['combination'] == '1') {
                
                foreach($post['combi'] as $combination) {
                    $pp = new PartyParent();
                    $pp->party = $p_id['id'];
                    $pp->parent = $combination;
                    $pp->save();
                }

            }


        }
        $lp = new LocalPartyManager($p_id['id']);
        $lp->addRange($post['region'], null, null);
        $lp->playChanges();
        Dispatcher::header('/appointments/');
    }
    
	public function show($smarty) {
        $smarty->assign('region', $this->region);
        $smarty->assign('parties', $this->parties);
        parent::show($smarty);
	}
}
?>
