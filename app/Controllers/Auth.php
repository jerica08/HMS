<?php

namespace App\Controllers;

use App\Controllers\BaseController;
use CodeIgniter\HTTP\ResponseInterface;

class Auth extends BaseController
{
    public function login()
    {
        
      $session = session(); // handles the form submission after login
      $userModel = new UserModel(); // store user after login
      
      $email = $this->request->getPost('email');// this is the user model
      $password = $this->request->getPost('password');

      $user = $userModel->where('email', $email)->first(); //llok for the user in the database by email
        
      if ($user){ // if the user with that email exists
        if (password_verify($password, $user['password'])) { //check if the password matches the hashed password in the database
            
            $session->set([ // if the password is correct, store the user_id, email, and password and isLoggedin = true
                'user_id' => $user['id'],
                'email' => $user['email'],
                'role' => $user['role'],
                'isLoggedIn' => true,
            ]);
            return redirect()->to('/dashboard'); //sucessfully login
        } else {
            $session->setFlashdata('error', 'Wrong email and password'); //error message
            return redirect()->back(); // go back to login form
        }
      }
        
        
        return view('auth/login');//display the login view
    }

    public function logout(){
        session()->destroy(); // removes all user data
        return redirect()->to('auth/login');
    }
}
