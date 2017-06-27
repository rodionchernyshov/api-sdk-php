<?php

namespace Qordoba\Test;

use Qordoba;

class QordobaProjectTest extends \PHPUnit\Framework\TestCase {

  public $apiUrl    = "https://app.qordoba.com/api/";
  public $login     = "polina.popadenko@dev-pro.net";
  public $pass      = "WE54iloCKa";
  public $projectId = 3693;
  public $orgId     = 3144;

  public function testProjectFetchMetadata() {
    $Conn = new Qordoba\Connection($this->apiUrl,$this->login, $this->pass);
    $Proj = new Qordoba\Project($this->projectId, $this->orgId, $Conn);

    $data = $Proj->getMetadata();
    print_r(json_encode($data, JSON_PRETTY_PRINT));
  }

}