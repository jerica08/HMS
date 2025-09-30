<?php

namespace App\Models;

use CodeIgniter\Model;

class StaffModel extends Model
{
    protected $table            = 'staff';
    protected $primaryKey       = 'staff_id';
    
   
    protected $allowedFields    = [
        'employee_id',
        'first_name',
        'last_name',
        'gender',
        'dob',
        'contact_no',
        'email',
        'address',
        'department',
        'role',
        'date_joined',

    ];


    protected $useTimestamps = true;
    protected $createdField  = 'date_joined';
    protected $updatedField = '';

    public function getFullName($staffId){
        $staff = this->find($staffId);
        
        if ($staff) {
            return $staff['first_name'] . '' . $staff['last_name'];
        }
   
    }
    
    
    public function getStaffWithUser($staffId)
    {
        return $this->select('staff.staff_id, staff.employee_id, staff.first_name, staff.last_name, staff.department, users.email, users.role')
                    ->join('users', 'users.staff_id = staff.staff_id', 'left')
                    ->where('staff.staff_id', $staffId)
                    ->first();
    }
}

   
    

