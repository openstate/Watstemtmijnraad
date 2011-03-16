<?php

require_once('BOUserRolePolitician.class.php');
require_once('BOUserRoleSecretary.class.php');
require_once('BOUserRoleClerk.class.php');

require_once 'Tag.class.php';

define('HNS_SYNCING', true);

class indexPage {
	protected $errors = array();

	public function processGet($get) {
        if (empty($get['key']) || $get['key'] != '3d7F8jhGk98CAa2Dv5') {
            throw new Exception('Invalid key for HNS sync');
        }

		$this->header();

		$this->startTime = microtime(true);
		$batchSize = 15;

		while ($className = $this->nextRecordType()) {
			debug("Processing records of type {$className}", 'heading');

			if (!isset($this->offset[$className])) $this->offset[$className] = 0;

			$recordManager  = new $className();
			$recordTypeDone = false;

			while (!$recordTypeDone) {
				$offset = $this->offset[$className];

				$records = $recordManager->getList(" LEFT JOIN sys_hns_ids AS hns ON hns.record_type = '{$className}' AND hns.record_id = t.id ", ' WHERE hns.hns_id IS NULL', '', 'LIMIT '. $batchSize .' OFFSET '. $offset, 'hns.hns_id');

				$rowCount = count($records);

				if ($rowCount < $batchSize) $recordTypeDone = true;

				debug("Received {$rowCount} records of type {$className}, skipping {$offset}", 'debug');

				foreach ($records as $record) {
					debug("Processing {$className} ({$record->id})");

					$this->saveRecord($record);
					$this->checkElapsed();
				}
			}

			debug("Type {$className} completed", 'heading');
		}

		$this->footer();
        $this->sendErrors();
        exit;
	}

	protected $classes = array(
		'LocalParty',
		'Politician',
		'Appointment',
		'Raadsstuk',
		'Vote',
		false
	);

	protected $currentType = -1;

	protected function nextRecordType() {
		$this->currentType++;

		return $this->classes[$this->currentType];
	}

	protected $offset = array();

	protected $startTime;
	protected $maxExecutionTime = 10; // seconds

	protected function saveRecord($record) {
		try {
			$record->save();
			return;
		} catch (HnsApiError $e) {
			$this->showError(get_class($e), $e->getFullMessage(), $e->getTrace());
		} catch (DatabaseQueryException $e) {
			$message = $e->getMessage();
			$message .= (DEVELOPER) ? ' '. $e->getSql() : '';
			$this->showError(get_class($e), $message, $e->getTrace());
        } catch (HNSCannotSyncError $e) {
            debug($e->getMessage(), 'notice');
        } catch (HNSCannotSaveError $e) {
            debug($e->getMessage(), 'notice');
		} catch (Exception $e) {
			$this->showError(get_class($e), $e->getMessage(), $e->getTrace());
		}

		// Failed to save a record, increase offset for this type by one, so we don't process it again
		$this->offset[get_class($record)]++;
	}

	protected function showError($class, $message, $trace) {
		debug("{$class}: {$message}", 'error');
		$this->errors[] = array($class, $message);
	}

	protected function checkElapsed() {
		$elapsed = round(microtime(true) - $this->startTime);

		if ($elapsed > $this->maxExecutionTime) {
			debug("Max execution time ({$this->maxExecutionTime} secs) exceeded ({$elapsed} secs). Exiting.", 'success');
			$this->footer();

            $this->sendErrors();

            exit;
		}
	}

	protected function header() {
		echo '<!DOCTYPE html PUBLIC "-//W3C//DTD XHTML 1.1//EN" "http://www.w3.org/TR/xhtml11/DTD/xhtml11.dtd">
		<html xmlns="http://www.w3.org/1999/xhtml">
		<head>
		<meta http-equiv="Content-Type" content="text/html; charset=utf-8" />
		<style type="text/css">
		body {
			font-family: verdana;
			font-size: 12px;
		}
		div.trace {
			display: none;
		}

		div.debug { height: auto; }
		div.debug > span.heading { font-style: italic; padding-top: 10px; }
		div.debug > span.debug   { color: #999999; }
		div.debug > span.default { color: #000000; }
		div.debug > span.success { color: green; }
		div.debug > span.notice  { color: yellow; }
		div.debug > span.error   { color: red; }

		</style>
		</head>
		<body>';
	}

	protected function footer() {
		echo '<br />HNS queries: ', HNSSyncedRecord::queryCount(), '<br />';
		echo 'Errors: ', count($this->errors), '<br />';

		echo '</body></html>';
	}

    protected function sendErrors() {
        if (count($this->errors) == 0) {
            return;
        }

        $error_text = '';

        foreach ($this->errors as $error) {
            $error_text .= "{$error[0]}: {$error[1]}\n";
        }

        $disp = Dispatcher::inst();

        $data = array(
            'message' => 'Errors during sync to HNS',
            'file' => '',
            'line' => '',
            'trace' => array(),
            'string' => $error_text
        );

        require_once('HtmlMailer.class.php');
        $mail = new HtmlMailer(new CustomSmarty($disp->locale));
        $mail->setTemplate($_SERVER['DOCUMENT_ROOT'].'/../emails/'.$disp->activeSite['publicdir'].'/'.$disp->activeSite['template']);
        $mail->setSubject('Exception for '.$disp->activeSite['title']);
        $mail->setContent($_SERVER['DOCUMENT_ROOT'].'/../emails/exception.html');
        $mail->setFrom($disp->activeSite['systemMail'], $disp->activeSite['title']);

        $mail->assignByRef('data', $data);
        $mail->addAddress('exceptions@getlogic.nl');
        $mail->send();
    }
}

?>
