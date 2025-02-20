<?php

use Mockery;
use Tests\TestCase;
use Slim\Views\Twig;
use Psr\Log\LoggerInterface;
use App\Controllers\UserController;
use Psr\Http\Message\StreamInterface;
use Psr\Http\Message\ResponseInterface;
use Psr\Http\Message\ServerRequestInterface;

class EditTest extends TestCase
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

    public function testEditSuccess()
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
        $response->shouldReceive('getStatusCode')->andReturn(200);
        $stream->shouldReceive('__toString')->andReturnUsing(function () use (&$bodyContent) {
            return $bodyContent;
        });

        // Create an alias for the ORM class
        $ormMock = Mockery::mock('alias:ORM');

        // Mock the static methods
        $ormMock->shouldReceive('for_table')->with('users')->andReturnSelf();
        $ormMock->shouldReceive('find_one')->andReturn((object)['id' => 1, 'name' => 'John Doe', 'email' => 'john@example.com', 'phone' => '1234567890']);

        // Mock the logger methods
        $this->logger->shouldReceive('info')->with('Getting User Details by ID')->once();
        $this->logger->shouldReceive('info')->with('User Found')->once();
        $this->logger->shouldReceive('error')->never();

        $request->shouldReceive('getParsedBody')->andReturn(['userId' => '1']);

        $result = $this->controller->edit($request, $response);

        $this->assertEquals(200, $result->getStatusCode());
    }

    public function testEditUserNotFound()
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
        $response->shouldReceive('getStatusCode')->andReturn(404);
        $stream->shouldReceive('__toString')->andReturnUsing(function () use (&$bodyContent) {
            return $bodyContent;
        });

        // Create an alias for the ORM class
        $ormMock = Mockery::mock('alias:ORM');

        // Mock the static methods
        $ormMock->shouldReceive('for_table')->with('users')->andReturnSelf();
        $ormMock->shouldReceive('find_one')->andReturn(false);

        // Mock the logger methods
        $this->logger->shouldReceive('info')->with('Getting User Details by ID')->once();
        $this->logger->shouldReceive('error')->with('User Not Found')->once();

        $request->shouldReceive('getParsedBody')->andReturn(['userId' => '1']);

        $result = $this->controller->edit($request, $response);

        $this->assertEquals(404, $result->getStatusCode());
    }

    public function testEditException()
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
        $this->logger->shouldReceive('info')->with('Getting User Details by ID')->once();
        $this->logger->shouldReceive('error')->with('Error Fetching User Details: Database Error')->once();

        $request->shouldReceive('getParsedBody')->andReturn(['userId' => '1']);

        $result = $this->controller->edit($request, $response);

        $this->assertEquals(500, $result->getStatusCode());
    }
}

