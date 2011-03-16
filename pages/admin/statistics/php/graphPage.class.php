<?php

require_once('pchart/pData.class');
require_once('pchart/pChart.class');

class GraphPage {
	public function __construct() {
		if (!array_key_exists('role', $_SESSION) || !($_SESSION['role'] instanceof BOUserRoleClerk))
			Dispatcher::forbidden();
		$this->hostname = $_SESSION['role']->getRecord()->subdomain;
	}

	public function processGet($get) {
		$data = array_map('intval', DBs::inst(DBs::SYSTEM)->query(
			'SELECT %l, extract(epoch from start) AS start FROM stats_region WHERE hostname = % AND start BETWEEN % AND % GROUP BY start',
			$get['metric'] == 'visits' ? 'sum(visits)' : 'sum(time_on_site)/sum(visits)',
			$this->hostname, $get['start'], $get['end'])->fetchAllCells('start'));

		$start = $get['start'] == '-infinity' ? min(array_keys($data)) : strtotime($get['start']);
		$end = $get['end'] == 'infinity' ? max(array_keys($data)) : strtotime($get['end']);
		for ($i = $start; $i <= $end; $i += 86400) {
			if (!array_key_exists($i, $data))
				$data[$i] = 0;
		}
		ksort($data);

		$dataset = new pData();
		$dataset->addPoint(array_values($data), 'Y');
		$dataset->addPoint(array_keys($data), 'X');
		$dataset->addSerie('Y');
		$dataset->setAbsciseLabelSerie('X');
		$dataset->setYAxisName($get['metric'] == 'visits' ? 'Bezoeken' : 'Gemiddelde bezoekduur');
		if ($get['metric'] != 'visits')
			$dataset->setYAxisFormat('time');
		$dataset->setXAxisFormat('date');
		
		$chart = new pChart(700, 230);
		$chart->setFontProperties($_SERVER['DOCUMENT_ROOT'].'/../includes/pchart/fonts/tahoma.ttf', 8); 
		$chart->setGraphArea(85, 30, 650, 200);
		$chart->drawFilledRoundedRectangle(7, 7, 693, 223, 5, 240, 240, 240);
		$chart->drawRoundedRectangle(5, 5, 695, 225, 5, 230, 230, 230);
		$chart->drawGraphArea(255, 255, 255, true);
		$chart->drawScale($dataset->getData(), $dataset->getDataDescription(), SCALE_NORMAL, 150, 150, 150, true, 0, 2);
		$chart->drawGrid(4, true, 230, 230, 230, 50);

		$chart->drawTreshold(0, 143, 55, 72, true, true);

		$chart->drawLineGraph($dataset->getData(), $dataset->getDataDescription());
		$chart->drawPlotGraph($dataset->getData(), $dataset->getDataDescription(), 3, 2, 255, 255, 255);

		$chart->stroke();
	}
	
	public function show($smarty) {
	}
}

?>