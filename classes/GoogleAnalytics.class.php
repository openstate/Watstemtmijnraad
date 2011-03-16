<?php

class GoogleAnalyticsException extends Exception { }

class GoogleAnalytics {
	protected static $source = 'getlogic-wsmr-v2.0';
	protected static $resultsPerPage = 10000;

	protected $auth = null;

	protected function __call($method, $args) {
		if (!in_array($method, array('GET', 'POST')))
			throw new GoogleAnalyticsException('Unknown method '.$method);
		if (count($args) < 2)
			throw new GoogleAnalyticsException('Insufficient arguments for method '.$method);
		list($url, $data) = $args;
		$data = http_build_query($data);

		$ch = curl_init();
		curl_setopt($ch, CURLOPT_HEADER,         0);
		curl_setopt($ch, CURLOPT_VERBOSE,        0);
		curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);
		curl_setopt($ch, CURLOPT_URL,            $url.($method == 'GET' ? '?'.$data : ''));
		curl_setopt($ch, CURLOPT_SSL_VERIFYHOST, 2);
		curl_setopt($ch, CURLOPT_POST,           $method == 'POST');
		if ($method == 'POST')
			curl_setopt($ch, CURLOPT_POSTFIELDS, $data);
			
		if ($this->auth)
			curl_setopt($ch, CURLOPT_HTTPHEADER, array('Authorization: GoogleLogin '.$this->auth));

		$result = curl_exec($ch);
		$error = curl_errno($ch) != 0 ? curl_error($ch) : null;
		curl_close($ch);
		
		if ($error)
			throw new GoogleAnalyticsException($method.' returned error '.$error);
		else
			return $result;
	}
	
	public function __construct($username, $password, $profileId) {
		$data = $this->POST('https://www.google.com/accounts/ClientLogin', array(
			'accountType' => 'GOOGLE',
			'Email' => $username,
			'Passwd' => $password,
			'service' => 'analytics',
			'source' => self::$source,
		));

		$this->auth = reset(array_filter(explode("\n", $data), create_function('$s', 'return substr($s, 0, 5) == "Auth=";')));
		if (!$this->auth)
			throw GoogleAnalyticsException('Authentication failed');
		$this->profileId = $profileId;
	}
	
	public function getData($startDate, $endDate, $dimensions, $metrics, $sort = null, $filters = null) {
		$params = array_filter(array(
			'start-date' => strftime('%Y-%m-%d', is_int($startDate) ? $startDate : strtotime($startDate)),
			'end-date' => strftime('%Y-%m-%d', is_int($endDate) ? $endDate : strtotime($endDate)),
			'dimensions' => implode(',', is_array($dimensions) ? $dimensions : array($dimensions)),
			'metrics' => implode(',', is_array($metrics) ? $metrics : array($metrics)),
			'sort' => $sort,
			'filters' => $filters,
			'ids' => 'ga:'.$this->profileId,
			'max-results' => self::$resultsPerPage,
		));
		
		$result = array();
		while (true) {
			$data = $this->GET('https://www.google.com/analytics/feeds/data', $params);
			if ($data[0] != '<')
				throw new GoogleAnalyticsException($data);
			$xml = simplexml_load_string($data);

			$namespaces = $xml->getDocNamespaces(false);
			$openSearch = $namespaces['openSearch'];
			$dxp = $namespaces['dxp'];

			foreach ($xml->entry as $entry) {
				$row = array();
				$dxpChildren = $entry->children($dxp);
				foreach ($dxpChildren->dimension as $dimension) {
					$attrs = $dimension->attributes();
					$row[substr((string) $attrs['name'], 3)] = (string) $attrs['value'];
				}
				foreach ($dxpChildren->metric as $metric) {
					$attrs = $metric->attributes();
					$value = (string) $attrs['value'];
					if ((string) $attrs['type'] == 'integer')
						$value = (int) $value;
					$row[substr((string) $attrs['name'], 3)] = $value;
				}
				$result[] = $row;
			}

			$totalResults = (int) (string) $xml->children($openSearch)->totalResults;
			if ($totalResults == count($result))
				return $result;

			$params['start-index'] = count($result) + 1;
		}
		
		return $result;
	}
	
	public function getHourData($dimensions, $metrics, $sort = null, $filters = null) {
		return $this->getData(time() - 3600, time() - 3600, $dimensions, $metrics, $sort, 'ga:hour=='.ltrim(strftime('%H', time()-3600), '0').($filters ? ';'.$filters : ''));
	}
}