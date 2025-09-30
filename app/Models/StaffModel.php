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

        return null;
    }
    
    public function getStaffList(){
        $staffList = $this->findAll();
        
        foreach ($staffList as &$s){
            $s['full_name'] = $s['first_name']. '' . $s['last_name'];

            return $staffList;
        }
    }

   
    
}
