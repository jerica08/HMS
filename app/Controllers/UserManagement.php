<?php

namespace App\Controllers;

use App\Controllers\BaseController;
use App\Models\UserModel;
use App\Models\StaffModel;

class UserManagement extends BaseController
{
    protected $db;
    protected $userModel;
    protected $staffModel;

    public function __construct()
    {
        $this->db = \Config\Database::connect();
        $this->userModel = new UserModel();
        $this->staffModel = new StaffModel();
        // Authentication is now handled by the roleauth filter in routes
    }

    public function index() 
    {
        $users = $this->userModel->getAllUsersWithStaff();
        if (!is_array($users)) { $users = []; }

        $adminCount = 0;
        foreach ($users as $u) {
            if (($u['role'] ?? '') === 'admin') { $adminCount++; }
        }

        $data = [
            'title' => 'User Management',
            'users' => $users,
            'staff' => $this->staffModel->getStaffWithoutUsers(),
            'stats' => [
                'total_users' => count($users),
                'admin_users' => $adminCount,
            ],
        ];

        return view('admin/user-management', $data);
    }

    public function create()
    {
        $rules = [
            'staff_id' => 'required|integer|is_unique[users.staff_id]',
            'username' => 'required|min_length[3]|max_length[50]|is_unique[users.username]',
            'password' => 'required|min_length[6]',
            'confirm_password' => 'required|matches[password]',
            'status' => 'required|in_list[active,inactive]',
        ];

        if(!$this->validate($rules)) {
            return redirect()->back()->withInput()->with('errors', $this->validator->getErrors());
        }

        $staff = $this->staffModel->find($this->request->getPost('staff_id'));
        if (!$staff){
            return redirect()->back()->withInput()->with('error', 'Invalid staff selected');
        }

        $staffEmail = $staff['email'] ?? null;
        if (empty($staffEmail)) {
            return redirect()->back()->withInput()->with('error', 'Selected staff has no email.');
        }

        $derivedRole = strtolower(trim((string)($staff['role'] ?? $staff['designation'] ?? '')));
        $validRoles = ['admin','doctor','nurse','receptionist','laboratorist','pharmacist','accountant','it_staff'];
        if (!in_array($derivedRole, $validRoles, true)) {
            return redirect()->back()->withInput()->with('error', 'Selected staff has no valid role.');
        }

        $data = [
            'staff_id'   => $this->request->getPost('staff_id'),
            'username'   => $this->request->getPost('username'),
            'email'      => $staff['email'],
            'first_name' => $staff['first_name'] ?? null,
            'last_name'  => $staff['last_name'] ?? null,
            'password'   => password_hash($this->request->getPost('password'), PASSWORD_DEFAULT),
            'role'       => $derivedRole,
            'status'     => $this->request->getPost('status') ?: 'active',
        ];

        if ($this->userModel->insert($data)) {
            return redirect()->to('admin/user-management')->with('success', 'User added successfully.');
        }
        return redirect()->back()->withInput()->with('error', 'Failed to add user.');
    }

    public function update()
    {
        $rules = [
            'user_id' => 'required|integer',
            'username' => 'required|min_length[3]|max_length[50]',
            'role' => 'required|in_list[admin,doctor,nurse,receptionist,laboratorist,pharmacist,accountant,it_staff]',
            'status' => 'required|in_list[active,inactive]',
        ];

        if(!$this->validate($rules)) {
            return redirect()->back()->withInput()->with('errors', $this->validator->getErrors());
        }

        $userId = $this->request->getPost('user_id');
        $data = [
            'username' => $this->request->getPost('username'),
            'role' => $this->request->getPost('role'),
            'status' => $this->request->getPost('status'),
        ];

        if ($this->userModel->update($userId, $data)) {
            return redirect()->to('admin/user-management')->with('success', 'User updated successfully.');
        }
        return redirect()->back()->withInput()->with('error', 'Failed to update user');
    }

    public function delete($id = null)
    {
        if ($id && $this->userModel->delete($id)) {
            session()->setFlashdata('success', 'User deleted successfully!');
        } else {
            session()->setFlashdata('error', 'Failed to delete user.');
        }
        return redirect()->to(base_url('admin/user-management'));
    }

    public function resetPassword($id = null)
    {
        if (!$id) {
            session()->setFlashdata('error', 'Invalid user ID.');
            return redirect()->to(base_url('admin/user-management'));
        }

        $alphabet = 'ABCDEFGHJKLMNPQRSTUVWXYZabcdefghijkmnopqrstuvwxyz23456789!@#$%^*';
        $temp = '';
        for ($i = 0; $i < 12; $i++) {
            $temp .= $alphabet[random_int(0, strlen($alphabet) - 1)];
        }

        if ($this->userModel->update($id, ['password' => password_hash($temp, PASSWORD_DEFAULT)])) {
            session()->setFlashdata('success', 'Password reset. Temporary password: ' . $temp);
        } else {
            session()->setFlashdata('error', 'Failed to reset password.');
        }

        return redirect()->to(base_url('admin/user-management'));
    }

    // API Methods
    public function getUsers() { return $this->response->setJSON($this->userModel->getAllUsersWithStaff()); }
    public function getUser($id) { return $this->response->setJSON($this->userModel->find($id)); }
    public function getStaff($id) { return $this->response->setJSON($this->staffModel->find($id)); }
}