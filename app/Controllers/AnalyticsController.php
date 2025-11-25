<?php

namespace App\Controllers;

use CodeIgniter\Controller;
use App\Services\AnalyticsService;

class AnalyticsController extends BaseController
{
    protected $analyticsService;

    public function __construct()
    {
        $this->analyticsService = new AnalyticsService();
        helper(['url', 'form']);
    }

    public function index()
    {
        // Check if user is logged in
        if (!session()->get('isLoggedIn')) {
            return redirect()->to('/login');
        }

        $data = [
            'title' => 'Analytics & Reports',
            'userRole' => session()->get('role'),
            'userId' => session()->get('userId')
        ];

        // Get date range (default: last 30 days)
        $endDate = date('Y-m-d');
        $startDate = date('Y-m-d', strtotime('-30 days'));

        // Get analytics data based on user role
        $data['analytics'] = $this->getAnalyticsData($data['userRole'], $data['userId'], $startDate, $endDate);

        return view('analytics/dashboard', $data);
    }

    public function getData()
    {
        if (!$this->request->isAJAX()) {
            return $this->response->setJSON(['error' => 'Invalid request']);
        }

        $startDate = $this->request->getPost('startDate');
        $endDate = $this->request->getPost('endDate');
        $reportType = $this->request->getPost('reportType');

        $data = $this->getAnalyticsData(
            session()->get('role'),
            session()->get('userId'),
            $startDate,
            $endDate
        );

        return $this->response->setJSON([
            'success' => true,
            'data' => $data
        ]);
    }

    private function getAnalyticsData($userRole, $userId, $startDate, $endDate)
    {
        $dateRange = [
            'start' => $startDate,
            'end' => $endDate
        ];

        $analytics = [];

        // Common analytics for all roles
        $analytics['date_range'] = [
            'start' => $startDate,
            'end' => $endDate
        ];

        // Role-specific analytics
        if (in_array($userRole, ['admin', 'accountant', 'it_staff'])) {
            $analytics['overview'] = [
                'total_patients' => $this->analyticsService->getTotalPatients($dateRange),
                'total_appointments' => $this->analyticsService->getTotalAppointments($dateRange),
                'total_revenue' => $this->analyticsService->getTotalRevenue($dateRange),
                'active_staff' => $this->analyticsService->getActiveStaffCount()
            ];
        } elseif ($userRole === 'doctor') {
            $analytics['doctor'] = [
                'my_patients' => $this->analyticsService->getDoctorPatients($userId, $dateRange),
                'appointments' => $this->analyticsService->getDoctorAppointments($userId, $dateRange),
                'revenue' => $this->analyticsService->getDoctorRevenue($userId, $dateRange)
            ];
        } elseif ($userRole === 'nurse') {
            $analytics['nurse'] = [
                'department_patients' => $this->analyticsService->getNurseDepartmentStats($userId, $dateRange),
                'medication_stats' => $this->analyticsService->getMedicationStats($userId, $dateRange)
            ];
        }

        return $analytics;
    }

    public function exportReport($type = 'pdf')
    {
        $data = $this->getAnalyticsData(
            session()->get('role'),
            session()->get('userId'),
            $this->request->getGet('startDate') ?? date('Y-m-d', strtotime('-30 days')),
            $this->request->getGet('endDate') ?? date('Y-m-d')
        );

        if ($type === 'pdf') {
            return $this->generatePdf($data);
        } else {
            return $this->generateCsv($data);
        }
    }

    private function generatePdf($data)
    {
        // Simple PDF generation (you can enhance this with a proper PDF library)
        $html = view('analytics/report_pdf', ['data' => $data]);
        
        // This is a simplified example - you might want to use a proper PDF library like Dompdf
        return $this->response->setHeader('Content-Type', 'application/pdf')
            ->setHeader('Content-Disposition', 'attachment; filename="analytics-report-' . date('Y-m-d') . '.pdf"')
            ->setBody($html);
    }

    private function generateCsv($data)
    {
        // Simple CSV generation
        $csv = '';
        
        // Flatten the data array for CSV
        $flattened = [];
        array_walk_recursive($data, function($value, $key) use (&$flattened) {
            $flattened[$key] = $value;
        });

        // Create CSV headers
        $csv .= implode(',', array_keys($flattened)) . "\n";
        $csv .= implode(',', array_values($flattened)) . "\n";

        return $this->response->setHeader('Content-Type', 'text/csv')
            ->setHeader('Content-Disposition', 'attachment; filename="analytics-report-' . date('Y-m-d') . '.csv"')
            ->setBody($csv);
    }
}
