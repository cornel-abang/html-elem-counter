<?php
namespace ElementCounter\Tests;

use PHPUnit\Framework\TestCase;
use ElementCounter\Controller\RequestController;
use ElementCounter\Service\Request as RequestService;
use Mockery as m;

/**
 * Just a quick and simple test 
 * For: the main entry point to the app
 * 
 */
class RequestControllerTest extends TestCase
{
    protected $mockService;
    protected $controller;

    protected function setUp(): void
    {
        $this->mockService = m::mock(RequestService::class);
        $this->controller = new RequestController($this->mockService);
    }

    protected function tearDown(): void
    {
        m::close();
    }

    public function testHandleRequestWithValidInput()
    {
        $this->mockService->shouldReceive('validateRequest')
            ->andReturn(true);
        $this->mockService->shouldReceive('checkRequestRespInCache')
            ->andReturn([]);
        $this->mockService->shouldReceive('registerNewReqComponents')
            ->once();
        $this->mockService->shouldReceive('fetchUrlContent')
            ->andReturn('<html></html>');
        $this->mockService->shouldReceive('setResponseTime')
            ->once();
        $this->mockService->shouldReceive('loadNewReqUrlContent')
            ->once();
        $this->mockService->shouldReceive('generateResponse')
            ->andReturn(['status' => true]);

        // Capture the output
        ob_start();
        $this->controller->handleRequest();
        $output = ob_get_clean();

        $response = json_decode($output, true);

        $this->assertJson($output);
        $this->assertArrayHasKey('status', $response);
        $this->assertIsBool($response['status']);
    }

    public function testHandleRequestWithInvalidInput()
    {
        $this->mockService->shouldReceive('validateRequest')
            ->andReturn(false);

        // Capture the output
        ob_start();
        $this->controller->handleRequest();
        $output = ob_get_clean();

        $response = json_decode($output, true);

        $this->assertJson($output);
        $this->assertArrayHasKey('error', $response);
        $this->assertEquals('Invalid input provided', $response['error']);
    }

    public function testHandleRequestWithCachedResponse()
    {
        $this->mockService->shouldReceive('validateRequest')
            ->andReturn(true);
        $this->mockService->shouldReceive('checkRequestRespInCache')
            ->andReturn(['status' => 'cached']);

        // Capture the output
        ob_start();
        $this->controller->handleRequest();
        $output = ob_get_clean();

        $response = json_decode($output, true);

        $this->assertJson($output);
        $this->assertArrayHasKey('status', $response);
        $this->assertEquals('cached', $response['status']);
    }
}