<?php

namespace App\Controllers;

use App\Controllers\BaseController;
use App\Services\StaffService;

class StaffManagement extends BaseController
{
    protected $db;
    protected $builder;
    protected $staffService;
    protected $userRole;
    protected $staffId;

    public function __construct()
    {
        $this->db = \Config\Database::connect();
        $this->builder = $this->db->table('staff');
        $this->staffService = new StaffService();

        // Get user session data
        $session = session();
        $this->userRole = $session->get('role');
        $this->staffId = $session->get('staff_id');
    }

    // Fetch a single staff member as JSON (for modals)
    public function getStaff($id = null)
    {
        $id = (int)($id ?? 0);
        if ($id <= 0) {
            return $this->response->setStatusCode(400)->setJSON(['status' => 'error', 'message' => 'Invalid staff ID']);
        }
        $row = $this->builder->where('staff_id', $id)->get()->getRowArray();
        if (!$row) {
            return $this->response->setStatusCode(404)->setJSON(['status' => 'error', 'message' => 'Staff not found']);
        }
        $row['id'] = $row['staff_id'];
        return $this->response->setJSON(['status' => 'success', 'data' => $row]);
    }

    /**
     * Main unified staff management - Role-based
     */
    public function index()
    {
        // Get staff data using service
        $staffResult = $this->staffService->getStaffByRole($this->userRole, $this->staffId);
        $stats = $this->staffService->getStaffStats($this->userRole, $this->staffId);
        
        $data = [
            'title' => $this->getPageTitle(),
            'userRole' => $this->userRole,
            'staff' => $staffResult['data'] ?? [],
            'staffStats' => $stats,
            'permissions' => $this->getStaffPermissions($this->userRole),
            'total_staff' => count($staffResult['data'] ?? []),
        ];

        return view('unified/staff-management', $data);
    }

    public function create()
    {
        if ($this->request->getMethod() === 'POST') {
            $input = $this->request->getPost();
            $isAjax = $this->request->isAJAX() || 
                      $this->request->getHeaderLine('Accept') == 'application/json' ||
                      $this->request->getHeaderLine('X-Requested-With') == 'XMLHttpRequest';

            // Use service to create staff
            $result = $this->staffService->createStaff($input, $this->userRole);

            if ($isAjax) {
                return $this->response->setJSON($result);
            }

            if ($result['success']) {
                session()->setFlashdata('success', $result['message']);
            } else {
                session()->setFlashdata('error', $result['message']);
                if (isset($result['errors'])) {
                    session()->setFlashdata('errors', $result['errors']);
                }
            }

            return redirect()->to(base_url('admin/staff-management'));
        }

        $data = ['title' => 'Add Staff'];
        return view('admin/add-staff', $data);
    }

    private function insertRoleSpecificData($designation, $staffId)
    {
        try {
            switch ($designation) {
                case 'doctor':
                    $this->db->table('doctor')->insert([
                        'staff_id' => $staffId,
                        'specialization' => $this->request->getPost('doctor_specialization'),
                        'license_no' => $this->request->getPost('doctor_license_no') ?: null,
                        'consultation_fee' => $this->request->getPost('doctor_consultation_fee') ?: null,
                        'status' => 'Active',
                    ]);
                    break;
                case 'nurse':
                    $this->db->table('nurse')->insert([
                        'staff_id' => $staffId,
                        'license_no' => $this->request->getPost('nurse_license_no'),
                        'specialization' => $this->request->getPost('nurse_specialization') ?: null,
                    ]);
                    break;
                case 'pharmacist':
                    $this->db->table('pharmacist')->insert([
                        'staff_id' => $staffId,
                        'license_no' => $this->request->getPost('pharmacist_license_no'),
                        'specialization' => $this->request->getPost('pharmacist_specialization') ?: null,
                    ]);
                    break;
                case 'laboratorist':
                    $this->db->table('laboratorist')->insert([
                        'staff_id' => $staffId,
                        'license_no' => $this->request->getPost('laboratorist_license_no'),
                        'specialization' => $this->request->getPost('laboratorist_specialization') ?: null,
                        'lab_room_no' => $this->request->getPost('laboratorist_lab_room_no') ?: null,
                    ]);
                    break;
                case 'accountant':
                    $this->db->table('accountant')->insert([
                        'staff_id' => $staffId,
                        'license_no' => $this->request->getPost('accountant_license_no'),
                    ]);
                    break;
                case 'receptionist':
                    $this->db->table('receptionist')->insert([
                        'staff_id' => $staffId,
                        'desk_no' => $this->request->getPost('receptionist_desk_no') ?: null,
                    ]);
                    break;
                case 'it_staff':
                    $this->db->table('it_staff')->insert([
                        'staff_id' => $staffId,
                        'expertise' => $this->request->getPost('it_expertise') ?: null,
                    ]);
                    break;
            }
        } catch (\Throwable $e) {
            log_message('error', 'Failed inserting role-specific record for staff_id ' . $staffId . ': ' . $e->getMessage());
            session()->setFlashdata('warning', 'Staff saved, but role details could not be created.');
        }
    }

    public function update($id = null)
    {
        $id = (int) ($id ?? ($this->request->getPost('staff_id') ?? 0));
        if ($id <= 0) {
            return $this->response->setStatusCode(400)->setJSON(['status' => 'error', 'message' => 'Invalid staff ID']);
        }

        $input = $this->request->getPost() ?: $this->request->getJSON(true) ?? [];
        
        // Use service to update staff
        $result = $this->staffService->updateStaff($id, $input, $this->userRole);
        
        if ($result['success']) {
            return $this->response->setJSON($result);
        } else {
            $statusCode = ($result['message'] === 'Permission denied') ? 403 : 422;
            return $this->response->setStatusCode($statusCode)->setJSON($result);
        }
    }

    public function delete($id = null)
    {
        if (!$id) {
            session()->setFlashdata('error', 'Invalid staff ID.');
            return redirect()->to(base_url('admin/staff-management'));
        }

        // Use service to delete staff
        $result = $this->staffService->deleteStaff($id, $this->userRole);
        
        if ($result['success']) {
            session()->setFlashdata('success', $result['message']);
        } else {
            session()->setFlashdata('error', $result['message']);
        }
        
        return redirect()->to(base_url('admin/staff-management'));
    }

    public function view($id = null)
    {
        if (!$id) {
            return redirect()->to(base_url('admin/staff-management'));
        }

        $staff = $this->builder->where('staff_id', $id)->get()->getRowArray();
        if (!$staff) {
            session()->setFlashdata('error', 'Staff member not found.');
            return redirect()->to(base_url('admin/staff-management'));
        }

        $data = ['title' => 'Staff Details', 'staff' => $staff];
        return view('admin/view-staff', $data);
    }

    // API Methods
    public function getStaffAPI()
    {
        try {
            // Get filters from request
            $filters = [
                'department' => $this->request->getGet('department'),
                'role' => $this->request->getGet('role'),
                'status' => $this->request->getGet('status'),
                'search' => $this->request->getGet('search'),
            ];
            
            // Remove empty filters
            $filters = array_filter($filters, function($value) {
                return !empty($value);
            });
            
            // Get staff using service
            $result = $this->staffService->getStaffByRole($this->userRole, $this->staffId, $filters);
            
            if ($result['success']) {
                return $this->response->setJSON(['status' => 'success', 'data' => $result['data']]);
            } else {
                return $this->response->setStatusCode(500)->setJSON(['status' => 'error', 'message' => $result['message']]);
            }
        } catch (\Throwable $e) {
            return $this->response->setStatusCode(500)->setJSON(['status' => 'error', 'message' => 'Failed to load staff']);
        }
    }

    public function getDoctorsAPI()
    {
        try {
            $rows = $this->db->table('doctor d')
                ->select('d.doctor_id, s.staff_id, s.first_name, s.last_name, s.department, d.specialization, d.status')
                ->join('staff s', 's.staff_id = d.staff_id', 'left')
                ->orderBy('s.first_name', 'ASC')
                ->get()->getResultArray();
            
            $data = array_map(function($r){
                $r['name'] = trim(($r['first_name'] ?? '') . ' ' . ($r['last_name'] ?? ''));
                return $r;
            }, $rows);
            
            return $this->response->setJSON(['status' => 'success', 'data' => $data]);
        } catch (\Throwable $e) {
            return $this->response->setStatusCode(500)->setJSON(['status' => 'error', 'message' => 'Failed to load doctors']);
        }
    }

    // ===================================================================
    // UNIFIED STAFF MANAGEMENT HELPER METHODS
    // ===================================================================

    /**
     * Get page title based on user role
     */
    private function getPageTitle()
    {
        switch ($this->userRole) {
            case 'admin':
                return 'Staff Management';
            case 'doctor':
                return 'Department Team';
            case 'nurse':
                return 'Department Staff';
            case 'receptionist':
                return 'Staff Directory';
            default:
                return 'Staff Information';
        }
    }

    /**
     * Check if user can create staff
     */
    private function canCreateStaff()
    {
        return in_array($this->userRole, ['admin', 'it_staff']);
    }

    /**
     * Check if user can edit staff
     */
    private function canEditStaff($staffId = null)
    {
        return in_array($this->userRole, ['admin', 'it_staff']);
    }

    /**
     * Check if user can delete staff
     */
    private function canDeleteStaff()
    {
        return $this->userRole === 'admin';
    }

    /**
     * Check if user can view staff details
     */
    private function canViewStaff()
    {
        return in_array($this->userRole, ['admin', 'doctor', 'nurse', 'receptionist', 'it_staff']);
    }

    /**
     * Get staff permissions based on user role
     */
    private function getStaffPermissions($userRole)
    {
        return [
            'canCreate' => in_array($userRole, ['admin']),
            'canEdit' => in_array($userRole, ['admin']),
            'canDelete' => in_array($userRole, ['admin']),
            'canView' => in_array($userRole, ['admin', 'doctor', 'nurse', 'receptionist']),
            'canViewSalary' => in_array($userRole, ['admin', 'accountant']),
            'canManageSchedule' => in_array($userRole, ['admin', 'doctor']),
            'canViewDepartment' => in_array($userRole, ['admin', 'doctor', 'nurse']),
            'canExport' => in_array($userRole, ['admin']),
        ];
    }
}