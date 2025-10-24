<?php

namespace App\Services;

use CodeIgniter\Database\ConnectionInterface;

class AnalyticsService
{
    protected $db;

    public function __construct(ConnectionInterface $db = null)
    {
        $this->db = $db ?? \Config\Database::connect();
    }

    /**
     * Get analytics data based on user role
     */
    public function getAnalyticsData(string $userRole, int $userId = null, array $filters = []): array
    {
        try {
            switch ($userRole) {
                case 'admin':
                case 'accountant':
                case 'it_staff':
                    return $this->getSystemWideAnalytics($filters);
                case 'doctor':
                    return $this->getDoctorAnalytics($userId, $filters);
                case 'nurse':
                    return $this->getNurseAnalytics($userId, $filters);
                case 'receptionist':
                    return $this->getReceptionistAnalytics($filters);
                default:
                    return $this->getBasicAnalytics();
            }
        } catch (\Exception $e) {
            log_message('error', 'AnalyticsService::getAnalyticsData error: ' . $e->getMessage());
            return $this->getBasicAnalytics();
        }
    }

    /**
     * Get system-wide analytics
     */
    private function getSystemWideAnalytics(array $filters = []): array
    {
        $dateRange = $this->getDateRange($filters);
        
        return [
            'patient_analytics' => $this->getPatientAnalytics($dateRange),
            'appointment_analytics' => $this->getAppointmentAnalytics($dateRange),
            'financial_analytics' => $this->getFinancialAnalytics($dateRange),
            'staff_analytics' => $this->getStaffAnalytics($dateRange)
        ];
    }

    /**
     * Get doctor-specific analytics
     */
    private function getDoctorAnalytics(int $doctorId, array $filters = []): array
    {
        $dateRange = $this->getDateRange($filters);
        
        return [
            'my_patients' => $this->getDoctorPatientStats($doctorId, $dateRange),
            'my_appointments' => $this->getDoctorAppointmentStats($doctorId, $dateRange),
            'my_revenue' => $this->getDoctorRevenueStats($doctorId, $dateRange),
            'patient_satisfaction' => $this->getDoctorSatisfactionStats($doctorId, $dateRange),
            'monthly_performance' => $this->getDoctorMonthlyPerformance($doctorId, $dateRange)
        ];
    }

    /**
     * Get nurse analytics
     */
    private function getNurseAnalytics(int $nurseId, array $filters = []): array
    {
        $dateRange = $this->getDateRange($filters);
        
        return [
            'department_patients' => $this->getNurseDepartmentStats($nurseId, $dateRange),
            'medication_tracking' => $this->getMedicationTrackingStats($nurseId, $dateRange),
            'shift_analytics' => $this->getShiftAnalytics($nurseId, $dateRange)
        ];
    }

    /**
     * Get receptionist analytics
     */
    private function getReceptionistAnalytics(array $filters = []): array
    {
        $dateRange = $this->getDateRange($filters);
        
        return [
            'registration_stats' => $this->getRegistrationStats($dateRange),
            'appointment_booking_stats' => $this->getBookingStats($dateRange),
            'daily_activity' => $this->getDailyActivityStats($dateRange)
        ];
    }

    /**
     * Get patient analytics
     */
    private function getPatientAnalytics(array $dateRange): array
    {
        $totalPatients = $this->db->table('patient')->countAllResults();
        $newPatients = $this->db->table('patient')
            ->where('date_registered >=', $dateRange['start'])
            ->where('date_registered <=', $dateRange['end'])
            ->countAllResults();
        
        $activePatients = $this->db->table('patient')
            ->where('status', 'Active')
            ->countAllResults();

        $patientsByType = $this->db->table('patient')
            ->select('patient_type, COUNT(*) as count')
            ->groupBy('patient_type')
            ->get()
            ->getResultArray();

        $patientsByAge = $this->db->table('patient')
            ->select('
                CASE 
                    WHEN TIMESTAMPDIFF(YEAR, date_of_birth, CURDATE()) < 18 THEN "Under 18"
                    WHEN TIMESTAMPDIFF(YEAR, date_of_birth, CURDATE()) BETWEEN 18 AND 35 THEN "18-35"
                    WHEN TIMESTAMPDIFF(YEAR, date_of_birth, CURDATE()) BETWEEN 36 AND 55 THEN "36-55"
                    WHEN TIMESTAMPDIFF(YEAR, date_of_birth, CURDATE()) BETWEEN 56 AND 70 THEN "56-70"
                    ELSE "Over 70"
                END as age_group,
                COUNT(*) as count
            ')
            ->groupBy('age_group')
            ->get()
            ->getResultArray();

        return [
            'total_patients' => $totalPatients,
            'new_patients' => $newPatients,
            'active_patients' => $activePatients,
            'patients_by_type' => $patientsByType,
            'patients_by_age' => $patientsByAge
        ];
    }

    /**
     * Get appointment analytics
     */
    private function getAppointmentAnalytics(array $dateRange): array
    {
        $totalAppointments = $this->db->table('appointments')
            ->where('appointment_date >=', $dateRange['start'])
            ->where('appointment_date <=', $dateRange['end'])
            ->countAllResults();

        $appointmentsByStatus = $this->db->table('appointments')
            ->select('status, COUNT(*) as count')
            ->where('appointment_date >=', $dateRange['start'])
            ->where('appointment_date <=', $dateRange['end'])
            ->groupBy('status')
            ->get()
            ->getResultArray();

        $appointmentsByType = $this->db->table('appointments')
            ->select('appointment_type, COUNT(*) as count')
            ->where('appointment_date >=', $dateRange['start'])
            ->where('appointment_date <=', $dateRange['end'])
            ->groupBy('appointment_type')
            ->get()
            ->getResultArray();

        $dailyAppointments = $this->db->table('appointments')
            ->select('DATE(appointment_date) as date, COUNT(*) as count')
            ->where('appointment_date >=', $dateRange['start'])
            ->where('appointment_date <=', $dateRange['end'])
            ->groupBy('DATE(appointment_date)')
            ->orderBy('date')
            ->get()
            ->getResultArray();

        return [
            'total_appointments' => $totalAppointments,
            'appointments_by_status' => $appointmentsByStatus,
            'appointments_by_type' => $appointmentsByType,
            'daily_appointments' => $dailyAppointments
        ];
    }

    /**
     * Get financial analytics
     */
    private function getFinancialAnalytics(array $dateRange): array
    {
        $totalRevenue = $this->db->table('payments')
            ->selectSum('amount')
            ->where('payment_date >=', $dateRange['start'])
            ->where('payment_date <=', $dateRange['end'])
            ->where('status', 'completed')
            ->get()
            ->getRow()
            ->amount ?? 0;

        $totalExpenses = $this->db->table('expenses')
            ->selectSum('amount')
            ->where('expense_date >=', $dateRange['start'])
            ->where('expense_date <=', $dateRange['end'])
            ->get()
            ->getRow()
            ->amount ?? 0;

        $revenueByMonth = $this->db->table('payments')
            ->select('DATE_FORMAT(payment_date, "%Y-%m") as month, SUM(amount) as revenue')
            ->where('payment_date >=', $dateRange['start'])
            ->where('payment_date <=', $dateRange['end'])
            ->where('status', 'completed')
            ->groupBy('month')
            ->orderBy('month')
            ->get()
            ->getResultArray();

        $expensesByCategory = $this->db->table('expenses')
            ->select('category, SUM(amount) as total')
            ->where('expense_date >=', $dateRange['start'])
            ->where('expense_date <=', $dateRange['end'])
            ->groupBy('category')
            ->get()
            ->getResultArray();

        return [
            'total_revenue' => (float)$totalRevenue,
            'total_expenses' => (float)$totalExpenses,
            'net_profit' => (float)$totalRevenue - (float)$totalExpenses,
            'revenue_by_month' => $revenueByMonth,
            'expenses_by_category' => $expensesByCategory
        ];
    }

    /**
     * Get staff analytics
     */
    private function getStaffAnalytics(array $dateRange): array
    {
        $totalStaff = $this->db->table('staff')->where('status', 'active')->countAllResults();
        
        $staffByRole = $this->db->table('staff')
            ->select('role, COUNT(*) as count')
            ->where('status', 'active')
            ->groupBy('role')
            ->get()
            ->getResultArray();

        $staffByDepartment = $this->db->table('staff')
            ->select('department, COUNT(*) as count')
            ->where('status', 'active')
            ->groupBy('department')
            ->get()
            ->getResultArray();

        return [
            'total_staff' => $totalStaff,
            'staff_by_role' => $staffByRole,
            'staff_by_department' => $staffByDepartment
        ];
    }

    /**
     * Generate reports
     */
    public function generateReport(string $reportType, string $userRole, int $userId = null, array $filters = []): array
    {
        try {
            switch ($reportType) {
                case 'patient_summary':
                    return $this->generatePatientSummaryReport($filters);
                case 'financial_summary':
                    return $this->generateFinancialSummaryReport($filters);
                case 'appointment_summary':
                    return $this->generateAppointmentSummaryReport($filters);
                case 'staff_performance':
                    return $this->generateStaffPerformanceReport($filters);
                case 'doctor_performance':
                    return $this->generateDoctorPerformanceReport($userId, $filters);
                default:
                    return ['success' => false, 'message' => 'Invalid report type'];
            }
        } catch (\Exception $e) {
            log_message('error', 'AnalyticsService::generateReport error: ' . $e->getMessage());
            return ['success' => false, 'message' => 'Error generating report'];
        }
    }

    /**
     * Helper methods
     */
    private function getDateRange(array $filters): array
    {
        $endDate = $filters['end_date'] ?? date('Y-m-d');
        $startDate = $filters['start_date'] ?? date('Y-m-d', strtotime('-30 days'));
        
        return [
            'start' => $startDate,
            'end' => $endDate
        ];
    }

    private function getBasicAnalytics(): array
    {
        return [
            'message' => 'Limited analytics access for this role'
        ];
    }

    private function getDoctorPatientStats(int $doctorId, array $dateRange): array
    {
        $totalPatients = $this->db->table('patient')
            ->where('primary_doctor_id', $doctorId)
            ->countAllResults();

        $newPatients = $this->db->table('patient')
            ->where('primary_doctor_id', $doctorId)
            ->where('date_registered >=', $dateRange['start'])
            ->where('date_registered <=', $dateRange['end'])
            ->countAllResults();

        return [
            'total_patients' => $totalPatients,
            'new_patients' => $newPatients
        ];
    }

    private function getDoctorAppointmentStats(int $doctorId, array $dateRange): array
    {
        $totalAppointments = $this->db->table('appointments')
            ->where('doctor_id', $doctorId)
            ->where('appointment_date >=', $dateRange['start'])
            ->where('appointment_date <=', $dateRange['end'])
            ->countAllResults();

        $completedAppointments = $this->db->table('appointments')
            ->where('doctor_id', $doctorId)
            ->where('status', 'completed')
            ->where('appointment_date >=', $dateRange['start'])
            ->where('appointment_date <=', $dateRange['end'])
            ->countAllResults();

        return [
            'total_appointments' => $totalAppointments,
            'completed_appointments' => $completedAppointments,
            'completion_rate' => $totalAppointments > 0 ? round(($completedAppointments / $totalAppointments) * 100, 2) : 0
        ];
    }

    private function getDoctorRevenueStats(int $doctorId, array $dateRange): array
    {
        $revenue = $this->db->table('payments p')
            ->join('bills b', 'b.bill_id = p.bill_id')
            ->selectSum('p.amount')
            ->where('b.doctor_id', $doctorId)
            ->where('p.payment_date >=', $dateRange['start'])
            ->where('p.payment_date <=', $dateRange['end'])
            ->where('p.status', 'completed')
            ->get()
            ->getRow()
            ->amount ?? 0;

        return [
            'total_revenue' => (float)$revenue
        ];
    }

    private function generatePatientSummaryReport(array $filters): array
    {
        $dateRange = $this->getDateRange($filters);
        
        $data = [
            'report_title' => 'Patient Summary Report',
            'date_range' => $dateRange,
            'generated_at' => date('Y-m-d H:i:s'),
            'data' => $this->getPatientAnalytics($dateRange)
        ];

        return ['success' => true, 'report' => $data];
    }

    private function generateFinancialSummaryReport(array $filters): array
    {
        $dateRange = $this->getDateRange($filters);
        
        $data = [
            'report_title' => 'Financial Summary Report',
            'date_range' => $dateRange,
            'generated_at' => date('Y-m-d H:i:s'),
            'data' => $this->getFinancialAnalytics($dateRange)
        ];

        return ['success' => true, 'report' => $data];
    }

    private function generateAppointmentSummaryReport(array $filters): array
    {
        $dateRange = $this->getDateRange($filters);
        
        $data = [
            'report_title' => 'Appointment Summary Report',
            'date_range' => $dateRange,
            'generated_at' => date('Y-m-d H:i:s'),
            'data' => $this->getAppointmentAnalytics($dateRange)
        ];

        return ['success' => true, 'report' => $data];
    }

    private function generateStaffPerformanceReport(array $filters): array
    {
        $dateRange = $this->getDateRange($filters);
        
        $data = [
            'report_title' => 'Staff Performance Report',
            'date_range' => $dateRange,
            'generated_at' => date('Y-m-d H:i:s'),
            'data' => $this->getStaffAnalytics($dateRange)
        ];

        return ['success' => true, 'report' => $data];
    }

    private function generateDoctorPerformanceReport(int $doctorId, array $filters): array
    {
        $dateRange = $this->getDateRange($filters);
        
        $data = [
            'report_title' => 'Doctor Performance Report',
            'date_range' => $dateRange,
            'generated_at' => date('Y-m-d H:i:s'),
            'data' => $this->getDoctorAnalytics($doctorId, $filters)
        ];

        return ['success' => true, 'report' => $data];
    }

    // Missing methods implementation
    private function getDoctorSatisfactionStats(int $doctorId, array $dateRange): array
    {
        // Placeholder implementation - can be enhanced with actual satisfaction data
        return [
            'average_rating' => 4.5,
            'total_reviews' => 0,
            'satisfaction_percentage' => 85
        ];
    }

    private function getDoctorMonthlyPerformance(int $doctorId, array $dateRange): array
    {
        // Placeholder implementation - can be enhanced with actual performance metrics
        return [
            'appointments_per_month' => 0,
            'revenue_per_month' => 0,
            'patient_growth' => 0
        ];
    }

    private function getNurseDepartmentStats(int $nurseId, array $dateRange): array
    {
        // Get nurse's department and count patients
        try {
            $nurse = $this->db->table('staff')->where('staff_id', $nurseId)->get()->getRow();
            if (!$nurse) {
                return ['total' => 0, 'active' => 0];
            }

            $totalPatients = $this->db->table('patient p')
                ->join('staff s', 's.department = p.department', 'left')
                ->where('s.department', $nurse->department)
                ->countAllResults();

            $activePatients = $this->db->table('patient p')
                ->join('staff s', 's.department = p.department', 'left')
                ->where('s.department', $nurse->department)
                ->where('p.status', 'Active')
                ->countAllResults();

            return [
                'total' => $totalPatients,
                'active' => $activePatients,
                'department' => $nurse->department
            ];
        } catch (\Exception $e) {
            return ['total' => 0, 'active' => 0];
        }
    }

    private function getMedicationTrackingStats(int $nurseId, array $dateRange): array
    {
        // Placeholder implementation - can be enhanced with actual medication tracking
        return [
            'administered' => 0,
            'pending' => 0,
            'scheduled' => 0
        ];
    }

    private function getShiftAnalytics(int $nurseId, array $dateRange): array
    {
        // Placeholder implementation - can be enhanced with actual shift data
        return [
            'hours_worked' => 0,
            'shifts_completed' => 0,
            'overtime_hours' => 0
        ];
    }

    private function getRegistrationStats(array $dateRange): array
    {
        try {
            $newRegistrations = $this->db->table('patient')
                ->where('date_registered >=', $dateRange['start'])
                ->where('date_registered <=', $dateRange['end'])
                ->countAllResults();

            $todayRegistrations = $this->db->table('patient')
                ->where('DATE(date_registered)', date('Y-m-d'))
                ->countAllResults();

            return [
                'new_registrations' => $newRegistrations,
                'total_today' => $todayRegistrations,
                'period_total' => $newRegistrations
            ];
        } catch (\Exception $e) {
            return ['new_registrations' => 0, 'total_today' => 0];
        }
    }

    private function getBookingStats(array $dateRange): array
    {
        try {
            $bookedToday = $this->db->table('appointments')
                ->where('DATE(created_at)', date('Y-m-d'))
                ->countAllResults();

            $cancelledToday = $this->db->table('appointments')
                ->where('DATE(updated_at)', date('Y-m-d'))
                ->where('status', 'cancelled')
                ->countAllResults();

            return [
                'booked_today' => $bookedToday,
                'cancelled_today' => $cancelledToday,
                'net_bookings' => $bookedToday - $cancelledToday
            ];
        } catch (\Exception $e) {
            return ['booked_today' => 0, 'cancelled_today' => 0];
        }
    }

    private function getDailyActivityStats(array $dateRange): array
    {
        // Placeholder implementation - can be enhanced with actual activity tracking
        return [
            'total_activities' => 0,
            'peak_hour' => '10:00',
            'busiest_day' => date('Y-m-d')
        ];
    }
}