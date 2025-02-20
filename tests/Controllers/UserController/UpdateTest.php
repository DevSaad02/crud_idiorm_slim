<?php

use Mockery;
use Tests\TestCase;
use Slim\Views\Twig;
use Psr\Log\LoggerInterface;
use App\Controllers\UserController;
use Psr\Http\Message\StreamInterface;
use Psr\Http\Message\ResponseInterface;
use Psr\Http\Message\ServerRequestInterface;

class UpdateTest extends TestCase
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

    public function testUpdateSuccess()
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
        $ormMock->shouldReceive('find_one')->andReturnSelf();
        $ormMock->shouldReceive('set')->with('name', 'John Doe')->andReturnSelf();
        $ormMock->shouldReceive('set')->with('email', 'john@example.com')->andReturnSelf();
        $ormMock->shouldReceive('set')->with('phone', '1234567890')->andReturnSelf();
        $ormMock->shouldReceive('save')->andReturn(true);

        // Mock the logger methods
        $this->logger->shouldReceive('info')->with('Updating User')->once();
        $this->logger->shouldReceive('info')->with('User Found')->once();
        $this->logger->shouldReceive('info')->with('User Updated Successfully')->once();
        $this->logger->shouldReceive('error')->never();

        // Ensure the userId is sent in the request
        $request->shouldReceive('getParsedBody')->andReturn([
            'userId' => '1',
            'name' => 'John Doe',
            'email' => 'john@example.com',
            'phone' => '1234567890'
        ]);

        $result = $this->controller->update($request, $response);

        $this->assertEquals(200, $result->getStatusCode());

        // Optionally, you can also assert the response body
        $responseBody = (string) $result->getBody();
        $this->assertStringContainsString('User Updated Successfully', $responseBody);
        $this->assertStringContainsString('John Doe', $responseBody);
        $this->assertStringContainsString('john@example.com', $responseBody);
        $this->assertStringContainsString('1234567890', $responseBody);
    }

    public function testUpdateUserNotFound()
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
        $this->logger->shouldReceive('info')->with('Updating User')->once();
        $this->logger->shouldReceive('error')->with('User Not Found')->once();

        $request->shouldReceive('getParsedBody')->andReturn([
            'userId' => '1',
            'name' => 'John Doe',
            'email' => 'john@example.com',
            'phone' => '1234567890'
        ]);

        $result = $this->controller->update($request, $response);

        $this->assertEquals(404, $result->getStatusCode());

        // Optionally, you can also assert the response body
        $responseBody = (string) $result->getBody();
        $this->assertStringContainsString('User Not Found', $responseBody);
    }

    public function testUpdateException()
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
        $this->logger->shouldReceive('info')->with('Updating User')->once();
        $this->logger->shouldReceive('error')->with('Error Updating User: Database Error')->once();

        $request->shouldReceive('getParsedBody')->andReturn([
            'userId' => '1',
            'name' => 'John Doe',
            'email' => 'john@example.com',
            'phone' => '1234567890'
        ]);

        $result = $this->controller->update($request, $response);

        $this->assertEquals(500, $result->getStatusCode());

        // Optionally, you can also assert the response body
        $responseBody = (string) $result->getBody();
        $this->assertStringContainsString('Error Updating User: Database Error', $responseBody);
    }
}
