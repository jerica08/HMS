<?php

namespace App\Models;

use CodeIgniter\Model;

class UserModel extends Model
{
    protected $table      = 'users';
    protected $primaryKey = 'user_id';

    protected $allowedFields = [
        'staff_id', 
        'username',
        'email',
        'password',
        'role',
        'created_at',
        'updated_at',
    ];

    protected $useTimestamps = true;
    protected $createdField  = 'created_at';
    protected $updatedField  = 'updated_at';

    
    public function getUserWithStaff($userId)
    {
        return $this->select('users.user_id, users.username, users.email as user_email, users.role, 
                              staff.employee_id, staff.first_name, staff.last_name, staff.gender, 
                              staff.dob, staff.contact_no, staff.email as staff_email, 
                              staff.address, staff.department')
                    ->join('staff', 'staff.staff_id = users.staff_id', 'left')
                    ->where('users.user_id', $userId)
                    ->first();
    }

    
    public function getAllUsersWithStaff()
    {
        return $this->select('users.user_id, users.username, users.email as user_email, users.role, 
                              staff.employee_id, staff.first_name, staff.last_name, staff.department')
                    ->join('staff', 'staff.staff_id = users.staff_id', 'left')
                    ->findAll();
    }
}
