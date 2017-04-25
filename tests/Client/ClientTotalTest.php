<?php 

use PHPUnit\Framework\TestCase;
use Vorbind\InfluxAnalytics\Connection;
use Vorbind\InfluxAnalytics\Client\ClientFactory;
use Vorbind\InfluxAnalytics\Client\ClientPeriod;


class ClientTotalTest extends TestCase {	

  protected static $db;

  public static function setUpBeforeClass() {
    $conn = new Connection();
    self::$db = $conn->getDatabase("news");
  }

  public function providerTotalByDateData() {
      return [          
          //total    
          ["d354fe67-87f2-4438-959f-65fde4622044", "sms", "2017-03-04 01:12:12", "daily"],
      ];
  }

  public function providerTotalData() {
      return [          
          //total    
          ["d354fe67-87f2-4438-959f-65fde4622044", "sms"],
      ];
  }

  /**
   * @dataProvider providerTotalByDateData 
   * @test
   */
  public function getTotalByDate($service, $metrix, $date, $granularity) {
    $inputData = [
      "serviceId" => $service,
      "metrix"  => $metrix,
      "date"   => $date,
      "granularity"   => $granularity,
    ];
    $factory = new ClientFactory();
    $client = $factory->create(self::$db, 'total', $inputData);
    $total = $client->getTotal();

    $this->assertTrue(is_integer($total));
    $this->assertEquals(36, $total);
  }
  
  /**
   * @dataProvider providerTotalData 
   * @test
   */
  public function getTotal($service, $metrix) {
    $inputData = [
      "serviceId" => $service,
      "metrix"  => $metrix     
    ];
    $factory = new ClientFactory();
    $client = $factory->create(self::$db, 'total', $inputData);
    $total = $client->getTotal();

    $this->assertTrue(is_integer($total));
    $this->assertEquals(2, $total);
  }  
  
}