<?php

class VoteException extends Record {
	private static $pofile;

	protected $data = array(
		'party' => null,
		'raadsstuk' => null,
		'exception' => null,
	);

	protected $tableName = 'rs_vote_exception';
}

?>
