<?php 

namespace Vorbind\InfluxAnalytics\Client;

use \Exception;
use Vorbind\InfluxAnalytics\Exception\AnalyticsException;
/**
 * Client Period
 */
class ClientPeriod implements ClientInterface {

	protected $db;
	protected $service;
	protected $metrix;
	protected $startDt;
	protected $endDt;
	protected $granularity;

	CONST GRANULARITY_HOURLY = 'hourly';
	CONST GRANULARITY_DAILY = 'daily';
	CONST GRANULARITY_WEEKLY = 'weekly';
	
	public function __construct($db, $inputData) {
		$this->db = $db;
		$this->metrix = isset($inputData["metrix"]) ? $inputData["metrix"] : null;
		$this->startDt = isset($inputData["startDt"]) ? $this->normalizeUTC($inputData["startDt"]) : null;
		$this->endDt = isset($inputData["endDt"]) ? $this->normalizeUTC($inputData["endDt"]) : null;
		$this->tags = isset($inputData["tags"]) ? $inputData["tags"] : array();
		$this->service = isset($this->tags["service"]) ? $this->tags["service"] : null;
		$this->granularity = isset($inputData["granularity"]) ? $inputData["granularity"] : null;
	}

	/**
	 * Get data
	 * @return array data
	 */
	public function getData() {
		$data = array();
		$where = array();

		try {
			$query = $this->db->getQueryBuilder()
						->select('news')
						->count('value')
						->from($this->metrix);


			if (!isset($this->tags["service"])) {
				$where[] = "service='" . $this->service . "'";
			}
			$where[] = "time >= '". $this->startDt . "' AND time <= '" . $this->endDt . "'";
			foreach($this->tags as $key => $val) {
				$where[] = "$key = '" . $val . "'";
			}

			$query->where($where);

			//granularity
			if( $this->granularity == self::GRANULARITY_HOURLY ) {
				$query->groupBy('time(1h)');
			}	
			else if( $this->granularity == self::GRANULARITY_WEEKLY ) {
				$query->groupBy('time(1w)');
			}
			//daily by default
			else {
				$query->groupBy('time(1d)');
			}	
			
			$data = $query->getResultSet()
		          ->getPoints();
      	} catch (Exception $e) {
      		throw new AnalyticsException("Analytics client period get data exception");
      	}

		return $data;
	}

	/**
	 * Get total
	 * @return int total
	 */
	public function getTotal() {
		try {

			$where = [];
			
			if (!isset($this->tags["service"])) {
				$where[] = "service='" . $this->service . "'";
			}
			$where[] = "time >= '". $this->startDt . "' AND time <= '" . $this->endDt . "'";
			foreach($this->tags as $key => $val) {
				$where[] = "$key = '" . $val . "'";
			}

			$results = $this->db->getQueryBuilder()
					->select('news')
					->from($this->metrix)
					->where($where)
					->sum('value')
					->getResultSet();

			$points = $results->getPoints();
		} catch (Exception $e) {
			throw new AnalyticsException("Analytics client period get total exception", 0, $e);
		}
		return isset($points[0]) && isset($points[0]["sum"]) ? $points[0]["sum"] : 0;
	}


	protected function normalizeUTC($date) {
		$parts = explode(" ", $date);
		return is_array($parts) && count($parts) == 2  ? $parts[0] . "T" . $parts[1] . "Z" : $date;
	}

}