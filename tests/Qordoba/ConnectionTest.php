<?php

namespace Qordoba\Test;

use Faker\Factory;
use PHPUnit\Framework\TestCase;
use Qordoba;
use Qordoba\Connection;

/**
 * Class QordobaConnectionTest
 * @package Qordoba\Test
 */
class QordobaConnectionTest extends TestCase
{

    /**
     * @var string
     */
    private $fakeApiUrl = 'http://app.qordoba.com';
    /**
     * @var string
     */
    private $apiUrl = 'https://app.qordoba.com/api/';
    /**
     * @var string
     */
    private $login = 'rodion.chernyshov@easternpeak.com';
    /**
     * @var string
     */
    private $password = 'NeoMacuser571';

	/**
	 * @throws \Exception
	 * @throws \Qordoba\Exception\AuthException
	 * @throws \Qordoba\Exception\ConnException
	 * @throws \Qordoba\Exception\ServerException
	 */
    public function testConnectionAbsentParams()
    {
        $connection = new Connection();

        $this->expectException('Qordoba\Exception\AuthException');
        $token = $connection->requestAuthToken();

        $this->assertToken($token);
    }

	/**
	 * @throws \Exception
	 * @throws \Qordoba\Exception\AuthException
	 * @throws \Qordoba\Exception\ConnException
	 * @throws \Qordoba\Exception\ServerException
	 */
    public function testConnectionAbsentUsername()
    {
        $connection = new Connection();

        $connection->setPassword(Factory::create()->password(8));
        $connection->setApiUrl($this->fakeApiUrl);

        $this->expectException('Qordoba\Exception\AuthException');
        $token = $connection->requestAuthToken();

        $this->assertToken($token);
    }

	/**
	 * @throws \Exception
	 * @throws \Qordoba\Exception\AuthException
	 * @throws \Qordoba\Exception\ConnException
	 * @throws \Qordoba\Exception\ServerException
	 */
    public function testConnectionAbsentPassword()
    {
        $connection = new Connection();

        $connection->setUsername(Factory::create()->userName);
        $connection->setApiUrl($this->fakeApiUrl);

        $this->expectException('Qordoba\Exception\AuthException');
        $token = $connection->requestAuthToken();

        $this->assertToken($token);
    }

	/**
	 * @throws \Exception
	 * @throws \Qordoba\Exception\AuthException
	 * @throws \Qordoba\Exception\ConnException
	 * @throws \Qordoba\Exception\ServerException
	 */
    public function testConnectionAbsentURL()
    {
        $connection = new Connection();
        $connection->setUsername(Factory::create()->userName);
        $connection->setPassword(Factory::create()->password(8));

        $this->expectException('Qordoba\Exception\ConnException');
        $token = $connection->requestAuthToken();

        $this->assertToken($token);
    }

	/**
	 * @throws \Exception
	 * @throws \Qordoba\Exception\AuthException
	 * @throws \Qordoba\Exception\ConnException
	 * @throws \Qordoba\Exception\ServerException
	 */
    public function testConnection()
    {
        $connection = new Connection();

        $connection->setUsername($this->login);
        $connection->setPassword($this->password);
        $connection->setApiUrl($this->apiUrl);
        $token = $connection->requestAuthToken();
        $this->assertToken($token);
    }

    /**
     * @param string $token
     */
    private function assertToken($token)
    {
        $this->assertTrue(is_string($token));
        $this->assertNotEmpty($token);
    }
}
