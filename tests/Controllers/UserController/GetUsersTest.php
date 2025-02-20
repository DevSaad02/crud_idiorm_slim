<?php

use ORM;
use Tests\TestCase;
use Slim\Views\Twig;
use Psr\Log\LoggerInterface;
use App\Controllers\UserController;
use Psr\Http\Message\StreamInterface;
use Psr\Http\Message\ResponseInterface;
use Psr\Http\Message\ServerRequestInterface;

class GetUsersTest extends TestCase
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

    public function testGetUsersSuccess()
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
        $ormMock->shouldReceive('find_array')->andReturn([
            ['id' => 1, 'name' => 'John Doe', 'email' => 'john@example.com', 'phone' => '1234567890'],
            ['id' => 2, 'name' => 'Jane Doe', 'email' => 'jane@example.com', 'phone' => '0987654321'],
            ['id' => 3, 'name' => 'Jim Doe', 'email' => 'jim@example.com', 'phone' => '1122334455']
        ]);

        // Mock the logger methods
        $this->logger->shouldReceive('info')->with('Fetching All Users')->once();
        $this->logger->shouldReceive('info')->with('Users Fetched Successfully')->once();
        $this->logger->shouldReceive('error')->never();

        // Call the getUsers method
        $result = $this->controller->getUsers($request, $response);

        // Assert the response status code
        $this->assertEquals(200, $result->getStatusCode());

        // Optionally, you can also assert the response body
        $responseBody = (string) $result->getBody();
        $this->assertStringContainsString('All Users List', $responseBody);
        $this->assertStringContainsString('John Doe', $responseBody);
        $this->assertStringContainsString('Jane Doe', $responseBody);
        $this->assertStringContainsString('Jim Doe', $responseBody);
    }

    public function testGetUsersException()
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
        $this->logger->shouldReceive('info')->with('Fetching All Users')->once();
        $this->logger->shouldReceive('error')->with('Error Fetching Users: Database Error')->once();

        // Call the getUsers method
        $result = $this->controller->getUsers($request, $response);

        // Assert the response status code
        $this->assertEquals(500, $result->getStatusCode());

        // Optionally, you can also assert the response body
        $responseBody = (string) $result->getBody();
        $this->assertStringContainsString('Error Fetching Users: Database Error', $responseBody);
    }
}
