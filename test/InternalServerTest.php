<?php

namespace Test;

use donatj\MockWebServer\InternalServer;
use donatj\MockWebServer\MockWebServer;
use donatj\MockWebServer\RequestInfo;
use PHPUnit\Framework\TestCase;

class InternalServerTest extends TestCase {

	private $testTmpDir;

	/**
	 * @before
	 */
	public function beforeEachTest() : void {
		$this->testTmpDir = sys_get_temp_dir() . DIRECTORY_SEPARATOR . 'testTemp';
		mkdir($this->testTmpDir);

		$counterFileName = $this->testTmpDir . DIRECTORY_SEPARATOR . MockWebServer::REQUEST_COUNT_FILE;
		file_put_contents($counterFileName, '0');
	}

	/**
	 * @after
	 */
	public function afterEachTest() : void {
		$this->removeTempDirectory();
	}

	private function removeTempDirectory() : void {
		$it    = new \RecursiveDirectoryIterator($this->testTmpDir, \FilesystemIterator::SKIP_DOTS);
		$files = new \RecursiveIteratorIterator($it,
			\RecursiveIteratorIterator::CHILD_FIRST);

		foreach( $files as $file ) {
			if( $file->isDir() ) {
				rmdir($file->getRealPath());
			} else {
				unlink($file->getRealPath());
			}
		}

		rmdir($this->testTmpDir);
	}

	/**
	 * @dataProvider countProvider
	 */
	public function testShouldIncrementRequestCounter( ?int $inputCount, int $expectedCount ) : void {
		$counterFileName = $this->testTmpDir . DIRECTORY_SEPARATOR . MockWebServer::REQUEST_COUNT_FILE;
		file_put_contents($counterFileName, '0');

		InternalServer::incrementRequestCounter($this->testTmpDir, $inputCount);
		$this->assertStringEqualsFile($counterFileName, (string)$expectedCount);
	}

	public function countProvider() : array {
		return [
			'null count' => [
				'inputCount'    => null,
				'expectedCount' => 1,
			],
			'int count'  => [
				'inputCount'    => 25,
				'expectedCount' => 25,
			],
		];
	}

	public function testShouldLogRequestsOnInstanceCreate() : void {
		$fakeReq = new RequestInfo([
			'REQUEST_URI'    => '/',
			'REQUEST_METHOD' => 'GET',
		],
			[], [], [], [], [], '');
		new InternalServer($this->testTmpDir, $fakeReq);

		$lastRequestFile = $this->testTmpDir . DIRECTORY_SEPARATOR . MockWebServer::LAST_REQUEST_FILE;
		$requestFile     = $this->testTmpDir . DIRECTORY_SEPARATOR . 'request.1';

		$lastRequestContent = file_get_contents($lastRequestFile);
		$requestContent     = file_get_contents($requestFile);

		$this->assertSame($lastRequestContent, $requestContent);
		$this->assertSame(serialize($fakeReq), $requestContent);
	}

}
