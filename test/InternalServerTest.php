<?php

use donatj\MockWebServer\InternalServer;
use donatj\MockWebServer\MockWebServer;
use PHPUnit\Framework\TestCase;

class InternalServerTest extends TestCase {

	private $testTmpDir;

	/**
	 * @before
	 */
	public function beforeEachTest() {
		$this->testTmpDir = __DIR__ . DIRECTORY_SEPARATOR . 'testTemp';

		mkdir($this->testTmpDir);

		$counterFileName = $this->testTmpDir . DIRECTORY_SEPARATOR . MockWebServer::REQUEST_COUNT_FILE;

		file_put_contents($counterFileName, '0');
	}

	/**
	 * @after
	 */
	public function afterEachTest() {
		$this->removeTempDirectory();
	}

	private function removeTempDirectory() {
		$it = new RecursiveDirectoryIterator($this->testTmpDir, RecursiveDirectoryIterator::SKIP_DOTS);
		$files = new RecursiveIteratorIterator($it,
			RecursiveIteratorIterator::CHILD_FIRST);

		foreach ($files as $file) {
			if( $file->isDir() ) {
				rmdir($file->getRealPath());
			} else {
				unlink($file->getRealPath());
			}
		}

		rmdir($this->testTmpDir);
	}

	/**
	 * @param $inputCount
	 * @param $expectedCount
	 *
	 * @dataProvider countProvider
	 */
	public function testShouldIncrementRequestCounter($inputCount, $expectedCount) {
		$counterFileName = $this->testTmpDir . DIRECTORY_SEPARATOR . MockWebServer::REQUEST_COUNT_FILE;


		InternalServer::incrementRequestCounter($this->testTmpDir, $inputCount);

		$this->assertStringEqualsFile($counterFileName, $expectedCount);
	}

	public function countProvider() {
		return [
			'null count' => [
				'inputCount' => null,
				'expectedCount' => 1,
			],
			'int count' => [
				'inputCount' => 25,
				'expectedCount' => 25,
			]
		];
	}

	public function testShouldLogRequestsOnInstanceCreate() {
		$fakeReq = new \donatj\MockWebServer\RequestInfo([
			'REQUEST_URI' => '',
		],
			[], [], [], [], [], '');
		new InternalServer($this->testTmpDir, $fakeReq);

		$lastRequestFile = $this->testTmpDir . DIRECTORY_SEPARATOR . MockWebServer::LAST_REQUEST_FILE;
		$requestFile = $this->testTmpDir . DIRECTORY_SEPARATOR . 'request.1';

		$lastRequestContent = file_get_contents($lastRequestFile);
		$requestContent = file_get_contents($requestFile);

		$this->assertEquals($lastRequestContent, $requestContent);
		$this->assertEquals(serialize($fakeReq), $requestContent);
	}
}
