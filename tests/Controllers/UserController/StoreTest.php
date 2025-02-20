<?php

use Mockery;
use Tests\TestCase;
use Slim\Views\Twig;
use Psr\Log\LoggerInterface;
use App\Controllers\UserController;
use Psr\Http\Message\StreamInterface;
use Psr\Http\Message\ResponseInterface;
use Psr\Http\Message\ServerRequestInterface;

class StoreTest extends TestCase
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

    public function testStoreSuccess()
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
        $response->shouldReceive('getStatusCode')->andReturn(201);
        $stream->shouldReceive('__toString')->andReturnUsing(function () use (&$bodyContent) {
            return $bodyContent;
        });

        // Create an alias for the ORM class
        $ormMock = Mockery::mock('alias:ORM');

        // Mock the static methods
        $ormMock->shouldReceive('for_table')->with('users')->andReturnSelf();
        $ormMock->shouldReceive('create')->andReturnSelf();
        $ormMock->shouldReceive('set')->with('name', 'John Doe')->andReturnSelf();
        $ormMock->shouldReceive('set')->with('email', 'john@example.com')->andReturnSelf();
        $ormMock->shouldReceive('set')->with('phone', '1234567890')->andReturnSelf();
        $ormMock->shouldReceive('save')->andReturn(true);

        // Mock the logger methods
        $this->logger->shouldReceive('info')->with('Adding a New User')->once();
        $this->logger->shouldReceive('info')->with('User Added Successfully')->once();
        $this->logger->shouldReceive('error')->never();

        $request->shouldReceive('getParsedBody')->andReturn([
            'name' => 'John Doe',
            'email' => 'john@example.com',
            'phone' => '1234567890'
        ]);

        $result = $this->controller->store($request, $response);

        $this->assertEquals(201, $result->getStatusCode());
    }

    public function testStoreException()
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

        // Create an alias for the ORM class
        $ormMock = Mockery::mock('alias:ORM');

        // Mock the static methods
        $ormMock->shouldReceive('for_table')->with('users')->andThrow(new \Exception('Database Error'));

        // Mock the logger methods
        $this->logger->shouldReceive('info')->with('Adding a New User')->once();
        $this->logger->shouldReceive('error')->with('Error Adding User: Database Error')->once();

        $request->shouldReceive('getParsedBody')->andReturn([
            'name' => 'John Doe',
            'email' => 'john@example.com',
            'phone' => '1234567890'
        ]);

        $result = $this->controller->store($request, $response);

        $this->assertEquals(500, $result->getStatusCode());
    }
}
