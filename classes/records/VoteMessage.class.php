<?php

class VoteMessage extends Record {
	private static $pofile;

	protected $data = array(
		'party' => null,
		'raadsstuk' => null,
		'message' => null,
	);

	protected $tableName = 'rs_vote_messages';
}

?>
