<?php

use Mockery;
use Tests\TestCase;
use Slim\Views\Twig;
use Psr\Log\LoggerInterface;
use App\Controllers\UserController;
use Psr\Http\Message\StreamInterface;
use Psr\Http\Message\ResponseInterface;
use Psr\Http\Message\ServerRequestInterface;

class IndexTest extends TestCase
{
    protected $view;
    protected $logger;
    protected $controller;

    protected function setUp(): void
    {
        $this->view = Mockery::mock(Twig::class);
        $this->logger = Mockery::mock(LoggerInterface::class);
        $this->controller = new UserController($this->view, $this->logger);
    }

    protected function tearDown(): void
    {
        Mockery::close();
    }

    public function testIndexSuccess()
    {
        $request = Mockery::mock(ServerRequestInterface::class);
        $response = Mockery::mock(ResponseInterface::class);

        // Expect the logger to log the info message
        $this->logger->shouldReceive('info')
            ->once()
            ->with('Rendering Users View');

        // Mock the view render method
        $this->view->shouldReceive('render')
            ->once()
            ->with($response, 'users.html.twig')
            ->andReturn($response);

        // Call the index method
        $result = $this->controller->index($request, $response);

        // Assert the response
        $this->assertSame($response, $result);
    }

    public function testIndexRenderException()
    {
        $request = Mockery::mock(ServerRequestInterface::class);
        $response = Mockery::mock(ResponseInterface::class);
        $stream = Mockery::mock(StreamInterface::class);

        $response->shouldReceive('getBody')->andReturn($stream);
        $stream->shouldReceive('write')->andReturnUsing(function ($string) use (&$bodyContent) {
            $bodyContent = $string;
            return strlen($string);
        });
        $response->shouldReceive('withHeader')->andReturnSelf();
        $response->shouldReceive('withStatus')->andReturnSelf();
        $response->shouldReceive('getStatusCode')->andReturn(500);
        $stream->shouldReceive('__toString')->andReturnUsing(function () use (&$bodyContent) {
            return $bodyContent;
        });

        // Expect the logger to log the info and error messages
        $this->logger->shouldReceive('info')
            ->once()
            ->with('Rendering Users View');
        $this->logger->shouldReceive('error')
            ->once()
            ->with('Error Rendering Users View: Render Error');

        $this->view->shouldReceive('render')->andThrow(new \Exception('Render Error'));

        $result = $this->controller->index($request, $response);

        $this->assertEquals(500, $result->getStatusCode());
    }
}
