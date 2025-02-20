<?php

namespace App\Controllers;

use Slim\Views\Twig;
use Psr\Http\Message\ResponseInterface as Response;
use Psr\Http\Message\ServerRequestInterface as Request;
use ORM;
use Psr\Log\LoggerInterface;

class UserController extends HomeController
{
    protected $view;
    protected $logger;
    // Initialize the twig view and logger
    public function __construct(Twig $view, LoggerInterface $logger)
    {
        $this->view = $view;
        $this->logger = $logger;
    }

    // Render the users view
    public function index(Request $request, Response $response)
    {
        $this->logger->info('Rendering Users View');
        try{
        return $this->view->render($response, 'users.html.twig');
        }catch(\Exception $e){
            $this->logger->error('Error Rendering Users View: ' . $e->getMessage());
            $data = [
                'status' => 'error',
                'message' => 'Error Rendering Users View: ' . $e->getMessage()
            ];
            return $this->response($response, $data, 500);
        }
    }

    // Get all users
    public function getUsers(Request $request, Response $response)
    {
        $this->logger->info('Fetching All Users');
        try {
            // Fetch users from the database
            $users = ORM::for_table('users')->find_array();
            $data = [
                'status' => 'success',
                'message' => 'All Users List',
                'data' => $users
            ];
            $this->logger->info('Users Fetched Successfully');
            return $this->response($response, $data);
        } catch (\Exception $e) {
            $this->logger->error('Error Fetching Users: ' . $e->getMessage());
            $data = [
                'status' => 'error',
                'message' => 'Error Fetching Users: ' . $e->getMessage()
            ];
            return $this->response($response, $data, 500);
        }
    }

    // Add a new user
    public function store(Request $request, Response $response)
    {
        $this->logger->info('Adding a New User');
        try {
            $data = $request->getParsedBody();
            // Insert user into the database
            $user = ORM::for_table('users')->create();
            $user->name = trim($data['name'] ?? '');
            $user->email = trim($data['email'] ?? '');
            $user->phone = trim($data['phone'] ?? '');
            $user->save();
            $this->logger->info('User Added Successfully');
            $data = [
                'status' => 'success',
                'message' => 'User Added Successfully',
                'data' => $user
            ];
            return $this->response($response, $data, 201);
        } catch (\Exception $e) {
            $this->logger->error('Error Adding User: ' . $e->getMessage());
            $data = [
                'status' => 'error',
                'message' => 'Error Adding User: ' . $e->getMessage()
            ];
            return $this->response($response, $data, 500);
        }
    }

    // Get user details by ID
    public function edit(Request $request, Response $response)
    {
        $this->logger->info('Getting User Details by ID');
        try {
            $data = $request->getParsedBody();
            $userId = trim($data['userId'] ?? '');

            if (empty($userId)) {
                $this->logger->error('User ID is required');
                $data = [
                    'status' => 'error',
                    'message' => 'Error: User ID is required.'
                ];
                return $this->response($response, $data, 500);
            }
            $user = ORM::for_table('users')->find_one($userId);
            if ($user) {
                $this->logger->info('User Found');
                $userInfo = [
                    "id" => $user->id,
                    "name" => $user->name,
                    "email" => $user->email,
                    "phone" => $user->phone
                ];
                $data = [
                    'status' => 'success',
                    'message' => 'Retrieved User Details',
                    'data' => $userInfo
                ];
                return $this->response($response, $data);
            } else {
                $this->logger->error('User Not Found');
                $data = [
                    'status' => 'error',
                    'message' => 'User not found.'
                ];
                return $this->response($response, $data, 404);
            }
        } catch (\Exception $e) {
            $this->logger->error('Error Fetching User Details: ' . $e->getMessage());
            $data = [
                'status' => 'error',
                'message' => 'Error Fetching User Details: ' . $e->getMessage()
            ];
            return $this->response($response, $data, 500);
        }
    }

    // Update user to the database
    public function update(Request $request, Response $response)
    {
        $this->logger->info('Updating User');
        try {
            $data = $request->getParsedBody();
            $userId = trim($data['userId'] ?? '');

            if (empty($userId)) {
                $this->logger->error('User ID is required');
                $data = [
                    'status' => 'error',
                    'message' => 'Error: User ID is required.'
                ];
                return $this->response($response, $data, 500);
            }
            // Find user by ID
            $user = ORM::for_table('users')->find_one($userId);
            if ($user) {
                $this->logger->info('User Found');
                // Update user info into  the database
                $user->name = trim($data['name'] ?? '');
                $user->email = trim($data['email'] ?? '');
                $user->phone = trim($data['phone'] ?? '');
                $user->save();
                $data = [
                    'status' => 'success',
                    'message' => 'User Updated Successfully',
                    'data' => $user
                ];
                $this->logger->info('User Updated Successfully');
                return $this->response($response, $data);
            } else {
                $this->logger->error('User Not Found');
                $data = [
                    'status' => 'error',
                    'message' => 'User Not Found',
                ];
                return $this->response($response, $data, 404);
            }
        } catch (\Exception $e) {
            $this->logger->error('Error Updating User: ' . $e->getMessage());
            $data = [
                'status' => 'error',
                'message' => 'Error Updating User: ' . $e->getMessage()
            ];
            return $this->response($response, $data, 500);
        }
    }

    // Delete a user from the database
    public function delete(Request $request, Response $response)
    {
        $this->logger->info('Deleting a User');
        try {
            $data = $request->getParsedBody();
            $userId = trim($data['userId'] ?? '');

            if (empty($userId)) {
                $this->logger->error('User ID is required');
                $data = [
                    'status' => 'error',
                    'message' => 'Error: User ID is required.'
                ];
                return $this->response($response, $data, 500);
            }
            // Find User by ID
            $user = ORM::for_table('users')->find_one($userId);
            if ($user) {
                $this->logger->info('User Found');
                // Delete user from the database
                $user->delete();
                $data = [
                    'status' => 'success',
                    'message' => 'User Deleted Successfully',
                    'data' => $user
                ];
                $this->logger->info('User Deleted Successfully');
                return $this->response($response, $data);
            } else {
                $this->logger->error('User Not Found');
                $data = [
                    'status' => 'error',
                    'message' => 'User Not Found',
                ];
                return $this->response($response, $data, 404);
            }
        } catch (\Exception $e) {
            $this->logger->error('Error Deleting User: ' . $e->getMessage());
            $data = [
                'status' => 'error',
                'message' => 'Error Deleting User: ' . $e->getMessage()
            ];
            return $this->response($response, $data, 500);
        }
    }
}
