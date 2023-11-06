<?php

namespace Ang3\Component\Odoo\Tests;

use Ang3\Component\Odoo\Client;
use Ang3\Component\Odoo\Connection;
use Ang3\Component\Odoo\Enum\OdooMethod;
use Ang3\Component\Odoo\Enum\OdooService;
use Ang3\Component\Odoo\Exception\RemoteException;
use Ang3\Component\Odoo\Metadata\Version;
use Ang3\Component\Odoo\Transport\JsonRpcPhpStreamTransport;
use Ang3\Component\Odoo\Transport\TransportInterface;
use PHPUnit\Framework\MockObject\MockObject;
use PHPUnit\Framework\TestCase;
use Psr\Log\LoggerInterface;

class ClientTest extends TestCase
{
	private Client $client;
	private MockObject $connection;
	private MockObject $transport;
	private MockObject $logger;

	protected function setUp(): void
	{
		parent::setUp();
		$this->connection = $this->createMock(Connection::class);
		$this->transport = $this->createMock(TransportInterface::class);
		$this->logger = $this->createMock(LoggerInterface::class);
		$this->client = new Client($this->connection, $this->transport, $this->logger);
	}

	public function testConstruct(): void
	{
		$client = new Client($this->connection);

		// Asserting default transport
		self::assertInstanceOf(JsonRpcPhpStreamTransport::class, $client->getTransport());

		// Asserting optional logger
		self::assertNull($client->getLogger());
	}

	public function provideRequestData(): array
	{
		return [
			[ OdooService::Common, OdooMethod::Login ],
			[ OdooService::Common, OdooMethod::Version ],
			[ OdooService::Object, OdooMethod::ExecuteKw ],
		];
	}

	/**
	 * @dataProvider provideRequestData
	 */
	public function testRequest(OdooService $service, OdooMethod $method): void
	{
		$this->transport
			->expects($this->once())
			->method('request')
			->with($service->value, $method->value, [1, 2, 3])
			->willReturn('foo')
		;

		$result = $this->client->request($service->value, $method->value, 1, 2, 3);
		self::assertEquals('foo', $result);
	}

	/**
	 * @dataProvider provideRequestData
	 */
	public function testRequestRemoteError(OdooService $service, OdooMethod $method): void
	{
		self::expectException(RemoteException::class);
		$this->transport
			->expects($this->once())
			->method('request')
			->with($service->value, $method->value, [1, 2, 3])
			->willThrowException(RemoteException::create([
				'error' => [
					'code' => 123,
					'message' => 'Test error',
					'data' => [
						'debug' => 'foo'
					]
				],
			]))
		;

		$this->client->request($service->value, $method->value, 1, 2, 3);
	}

	/**
	 * @depends testRequest
	 */
	public function testExecuteKw(): void
	{
		// ...
	}

	/**
	 * @depends testRequest
	 */
	public function testVersion(): void
	{
		$this->transport
			->expects($this->once())
			->method('request')
			->with(OdooService::Common->value, OdooMethod::Version->value)
			->willReturn([
				'server_version_info' => [13, 3, 7, 'a', 'b', 'c'],
				'protocol_version' => 1,
			])
		;

		$version = $this->client->version();
		self::assertInstanceOf(Version::class, $version);
	}

	/**
	 * @depends testRequest
	 */
	public function testAuthenticate(): void
	{
		list($database, $username, $password) = ['foo', 'bar', 'qux'];
		$this->connection->expects($this->once())->method('getDatabase')->willReturn($database);
		$this->connection->expects($this->once())->method('getUsername')->willReturn($username);
		$this->connection->expects($this->once())->method('getPassword')->willReturn($password);
		$expectedUid = 1337;

		$this->transport
			->expects($this->once())
			->method('request')
			->with(OdooService::Common->value, OdooMethod::Login->value, [$database, $username, $password])
			->willReturn($expectedUid)
		;

		$uid = $this->client->authenticate();
		self::assertEquals($expectedUid, $uid);
		self::assertEquals($expectedUid, $this->client->getUid());
	}
}