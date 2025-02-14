<?php

namespace App\Controllers;

use Slim\Views\Twig;
use Psr\Http\Message\ResponseInterface as Response;
use Psr\Http\Message\ServerRequestInterface as Request;
use ORM;

class UserController extends HomeController
{
    protected $view;
    // Initialize the twig view
    public function __construct(Twig $view)
    {
        $this->view = $view;
    }

    // Render the users view
    public function index(Request $request, Response $response)
    {
        return $this->view->render($response, 'users.html.twig');
    }

    // Get all users
    public function getUsers(Request $request, Response $response)
    {
        try {
            // Fetch users from the database
            $users = ORM::for_table('users')->find_array();
            $data = [
                'status' => 'success',
                'message' => 'All Users List',
                'data' => $users
            ];
            return $this->response($response, $data);
        } catch (\Exception $e) {
            $data = [
                'status' => 'error',
                'message' => 'Error: ' . $e->getMessage()
            ];
            return $this->response($response, $data, 500);
        }
    }

    // Add a new user
    public function store(Request $request, Response $response)
    {
        try {
            $data = $request->getParsedBody();
            // Insert user into the database
            $user = ORM::for_table('users')->create();
            $user->name = trim($data['name'] ?? '');
            $user->email = trim($data['email'] ?? '');
            $user->phone = trim($data['phone'] ?? '');
            $user->save();
            $data = [
                'status' => 'success',
                'message' => 'User Added Successfully',
                'data' => $user
            ];
            return $this->response($response, $data, 201);
        } catch (\Exception $e) {
            $data = [
                'status' => 'error',
                'message' => 'Error: ' . $e->getMessage()
            ];
            return $this->response($response, $data, 500);
        }
    }

    // Get user details by ID
    public function edit(Request $request, Response $response)
    {
        try {
            $data = $request->getParsedBody();
            $userId = trim($data['userId'] ?? '');

            if (empty($userId)) {
                $data = [
                    'status' => 'error',
                    'message' => 'Error: User ID is required.'
                ];
                return $this->response($response, $data, 500);
            }
            $user = ORM::for_table('users')->find_one($userId);
            if ($user) {
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
                $data = [
                    'status' => 'error',
                    'message' => 'User not found.'
                ];
                return $this->response($response, $data, 404);
            }
        } catch (\Exception $e) {
            $data = [
                'status' => 'error',
                'message' => 'Error: ' . $e->getMessage()
            ];
            return $this->response($response, $data, 500);
        }
    }

    // Update user to the database
    public function update(Request $request, Response $response)
    {
        try {
            $data = $request->getParsedBody();
            $userId = trim($data['userId'] ?? '');

            if (empty($userId)) {
                $data = [
                    'status' => 'error',
                    'message' => 'Error: User ID is required.'
                ];
                return $this->response($response, $data, 500);
            }
            // Find user by ID
            $user = ORM::for_table('users')->find_one($userId);
            if ($user) {
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
                return $this->response($response, $data);
            } else {
                $data = [
                    'status' => 'error',
                    'message' => 'User Not Found',
                ];
                return $this->response($response, $data, 404);
            }
        } catch (\Exception $e) {
            $data = [
                'status' => 'error',
                'message' => 'Error: ' . $e->getMessage()
            ];
            return $this->response($response, $data, 500);
        }
    }

    // Delete a user from the database
    public function delete(Request $request, Response $response)
    {
        try {
            $data = $request->getParsedBody();
            $userId = trim($data['userId'] ?? '');

            if (empty($userId)) {
                $data = [
                    'status' => 'error',
                    'message' => 'Error: User ID is required.'
                ];
                return $this->response($response, $data, 500);
            }
            // Find User by ID
            $user = ORM::for_table('users')->find_one($userId);
            if ($user) {
                // Delete user from the database
                $user->delete();
                $data = [
                    'status' => 'success',
                    'message' => 'User Deleted Successfully',
                    'data' => $user
                ];
                return $this->response($response, $data);
            } else {
                $data = [
                    'status' => 'error',
                    'message' => 'User Not Found',
                ];
                return $this->response($response, $data, 404);
            }
        } catch (\Exception $e) {
            $data = [
                'status' => 'error',
                'message' => 'Error: ' . $e->getMessage()
            ];
            return $this->response($response, $data, 500);
        }
    }
}
