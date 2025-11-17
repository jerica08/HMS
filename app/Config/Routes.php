<?php

use CodeIgniter\Router\RouteCollection;

/**
 * @var RouteCollection $routes
 */

// ===================================================================
// MAIN ROUTES
// ===================================================================

$routes->get('/', 'Home::index');
$routes->get('about', 'Home::about');
$routes->get('contact', 'Home::contact');

// ===================================================================
// AUTHENTICATION ROUTES
// ===================================================================

$routes->get('login', 'Auth::login');
$routes->post('login', 'Auth::login');
$routes->get('logout', 'Auth::logout');
$routes->get('auth/logout', 'Auth::logout');

// ===================================================================
// UNIFIED DASHBOARD MANAGEMENT
// ===================================================================

// Dashboard Views - Role-specific entry points
$routes->get('admin/dashboard', 'Unified\DashboardController::index', ['filter' => 'roleauth:admin']);
$routes->get('admin', 'Unified\DashboardController::index', ['filter' => 'roleauth:admin']);
$routes->get('accountant/dashboard', 'Unified\DashboardController::index', ['filter' => 'roleauth:accountant']);
$routes->get('doctor/dashboard', 'Unified\DashboardController::index', ['filter' => 'roleauth:doctor']);
$routes->get('it-staff/dashboard', 'Unified\DashboardController::index', ['filter' => 'roleauth:it_staff']);
$routes->get('laboratorist/dashboard', 'Unified\DashboardController::index', ['filter' => 'roleauth:laboratorist']);
$routes->get('nurse/dashboard', 'Unified\DashboardController::index', ['filter' => 'roleauth:nurse']);
$routes->get('pharmacist/dashboard', 'Unified\DashboardController::index', ['filter' => 'roleauth:pharmacist']);
$routes->get('receptionist/dashboard', 'Unified\DashboardController::index', ['filter' => 'roleauth:receptionist']);

// Dashboard API Routes
$routes->get('api/dashboard-data', 'Unified\DashboardController::getDashboardData', ['filter' => 'roleauth:admin,doctor,nurse,receptionist,accountant,it_staff,laboratorist,pharmacist']);
$routes->get('api/quick-stats', 'Unified\DashboardController::getQuickStats', ['filter' => 'roleauth:admin,doctor,nurse,receptionist,accountant,it_staff,laboratorist,pharmacist']);
$routes->get('api/system-health', 'Unified\DashboardController::getSystemHealth', ['filter' => 'roleauth:admin']);
$routes->get('api/today-schedule', 'Unified\DashboardController::getTodaySchedule', ['filter' => 'roleauth:doctor,nurse']);
$routes->post('api/dashboard-preferences', 'Unified\DashboardController::updatePreferences', ['filter' => 'roleauth:admin,doctor,nurse,receptionist,accountant,it_staff,laboratorist,pharmacist']);

// ===================================================================
// UNIFIED USER MANAGEMENT
// ===================================================================

// User Management Views - Role-specific entry points (temporarily removed auth for testing)
$routes->get('admin/user-management', 'UserManagement::index');
$routes->get('doctor/users', 'UserManagement::index');
$routes->get('it-staff/users', 'UserManagement::index');
$routes->get('nurse/users', 'UserManagement::index');
$routes->get('receptionist/users', 'UserManagement::index');

// User Management API Routes
$routes->get('users/api', 'UserManagement::getUsersAPI', ['filter' => 'roleauth:admin,doctor,nurse,receptionist,it_staff']);
$routes->get('users/(:num)', 'UserManagement::getUser/$1', ['filter' => 'roleauth:admin,doctor,nurse,receptionist,it_staff']);
$routes->get('users/available-staff', 'UserManagement::getAvailableStaffAPI', ['filter' => 'roleauth:admin,it_staff']);
$routes->post('users/create', 'UserManagement::create', ['filter' => 'roleauth:admin,it_staff']);
$routes->post('users/update/(:num)', 'UserManagement::update/$1', ['filter' => 'roleauth:admin,it_staff']);
$routes->post('users/reset-password/(:num)', 'UserManagement::resetPassword/$1', ['filter' => 'roleauth:admin,it_staff']);
$routes->delete('users/delete/(:num)', 'UserManagement::delete/$1', ['filter' => 'roleauth:admin,it_staff']);
$routes->post('users/restore/(:num)', 'UserManagement::restore/$1', ['filter' => 'roleauth:admin,it_staff']);

// Legacy Admin User Management Routes
$routes->post('admin/user-management/create', 'UserManagement::create', ['filter' => 'roleauth:admin']);
$routes->post('admin/user-management/update', 'UserManagement::update', ['filter' => 'roleauth:admin']);
$routes->get('admin/user-management/delete/(:num)', 'UserManagement::delete/$1', ['filter' => 'roleauth:admin']);
$routes->get('admin/user-management/reset-password/(:num)', 'UserManagement::resetPassword/$1', ['filter' => 'roleauth:admin']);
$routes->get('admin/user-management/users', 'UserManagement::getUsersAPI', ['filter' => 'roleauth:admin']);
$routes->get('admin/user-management/user/(:num)', 'UserManagement::getUser/$1', ['filter' => 'roleauth:admin']);
$routes->get('admin/user-management/staff/(:num)', 'UserManagement::getAvailableStaffAPI', ['filter' => 'roleauth:admin']);

// Debug route for troubleshooting
$routes->get('debug', 'DebugController::index');

// ===================================================================
// UNIFIED STAFF MANAGEMENT
// ===================================================================

// Staff Management Views - Role-specific entry points
$routes->get('admin/staff-management', 'StaffManagement::index', ['filter' => 'roleauth:admin']);
$routes->get('doctor/staff', 'StaffManagement::index', ['filter' => 'roleauth:doctor']);
$routes->get('it-staff/staff', 'StaffManagement::index', ['filter' => 'roleauth:it_staff']);
$routes->get('nurse/staff', 'StaffManagement::index', ['filter' => 'roleauth:nurse']);
$routes->get('receptionist/staff', 'StaffManagement::index', ['filter' => 'roleauth:receptionist']);

// Staff Management API Routes
$routes->get('staff/api', 'StaffManagement::getStaffAPI', ['filter' => 'roleauth:admin,doctor,nurse,receptionist,it_staff']);
$routes->get('staff/(:num)', 'StaffManagement::getStaff/$1', ['filter' => 'roleauth:admin,doctor,nurse,receptionist,it_staff']);
$routes->get('staff/next-employee-id', 'StaffManagement::getNextEmployeeId', ['filter' => 'roleauth:admin,it_staff']);
$routes->post('staff/create', 'StaffManagement::create', ['filter' => 'roleauth:admin,it_staff']);
$routes->post('staff/update', 'StaffManagement::update', ['filter' => 'roleauth:admin,it_staff']);
$routes->delete('staff/delete/(:num)', 'StaffManagement::delete/$1', ['filter' => 'roleauth:admin,it_staff']);

// Legacy Admin Staff Management Routes
$routes->match(['get', 'post'], 'admin/staff-management/create', 'StaffManagement::create', ['filter' => 'roleauth:admin']);
$routes->post('admin/staff-management/update/(:num)', 'StaffManagement::update/$1', ['filter' => 'roleauth:admin']);
$routes->post('admin/staff-management/update', 'StaffManagement::update', ['filter' => 'roleauth:admin']);
$routes->get('admin/staff-management/delete/(:num)', 'StaffManagement::delete/$1', ['filter' => 'roleauth:admin']);
$routes->get('admin/staff-management/view/(:num)', 'StaffManagement::view/$1', ['filter' => 'roleauth:admin']);
$routes->get('admin/staff-management/api', 'StaffManagement::getStaffAPI', ['filter' => 'roleauth:admin']);
$routes->get('admin/staff-management/staff/(:num)', 'StaffManagement::getStaff/$1', ['filter' => 'roleauth:admin']);
$routes->get('admin/staff-management/doctors/api', 'StaffManagement::getDoctorsAPI', ['filter' => 'roleauth:admin']);
$routes->get('admin/doctors/api', 'StaffManagement::getDoctorsAPI', ['filter' => 'roleauth:admin']);

// ===================================================================
// RESOURCE MANAGEMENT
// ===================================================================

$routes->get('admin/resource-management', 'ResourceManagement::index', ['filter' => 'roleauth:admin']);
$routes->get('admin/resource-management/api', 'ResourceManagement::getResourcesAPI', ['filter' => 'roleauth:admin']);
$routes->get('admin/resource-management/(:num)', 'ResourceManagement::getResource/$1', ['filter' => 'roleauth:admin']);
$routes->post('admin/resource-management/create', 'ResourceManagement::create', ['filter' => 'roleauth:admin']);
$routes->post('admin/resource-management/update', 'ResourceManagement::update', ['filter' => 'roleauth:admin']);
$routes->post('admin/resource-management/delete', 'ResourceManagement::delete', ['filter' => 'roleauth:admin']);
$routes->post('admin/resources/add', 'ResourceManagement::add', ['filter' => 'roleauth:admin']);

// ===================================================================
// ROOM MANAGEMENT (UNIFIED)
// ===================================================================

$routes->get('admin/room-management', 'RoomManagement::index', ['filter' => 'roleauth:admin']);
$routes->get('rooms/api', 'RoomManagement::getRoomsAPI', ['filter' => 'roleauth:admin']);
$routes->post('rooms/create', 'RoomManagement::createRoom', ['filter' => 'roleauth:admin']);

// ===================================================================
// DEPARTMENT ROUTES
// ===================================================================

$routes->match(['get','post','options'], 'departments/create', 'Departments::create');

// ===================================================================
// UNIFIED SHIFT MANAGEMENT
// ===================================================================

// Shift Management Views - Role-specific entry points
$routes->get('admin/shifts', 'ShiftManagement::index', ['filter' => 'roleauth:admin']); // Main admin shift management route
$routes->get('doctor/shifts', 'ShiftManagement::index', ['filter' => 'roleauth:doctor']);
$routes->get('it-staff/shifts', 'ShiftManagement::index', ['filter' => 'roleauth:it_staff']);
$routes->get('nurse/shifts', 'ShiftManagement::index', ['filter' => 'roleauth:nurse']);
$routes->get('receptionist/shifts', 'ShiftManagement::index', ['filter' => 'roleauth:receptionist']);

// Unified Shift Management Route
$routes->get('unified/shifts', 'ShiftManagement::index', ['filter' => 'roleauth:admin,doctor,it_staff,nurse,receptionist']);

// Shift Management API Routes
$routes->get('shifts/api', 'ShiftManagement::getShiftsAPI', ['filter' => 'roleauth:admin,doctor,nurse,receptionist,it_staff']);
$routes->get('shifts/(:num)', 'ShiftManagement::getShift/$1', ['filter' => 'roleauth:admin,doctor,nurse,receptionist,it_staff']);
$routes->get('shifts/available-staff', 'ShiftManagement::getAvailableStaffAPI', ['filter' => 'roleauth:admin,it_staff']);
$routes->post('shifts/create', 'ShiftManagement::create', ['filter' => 'roleauth:admin,it_staff']);
$routes->post('shifts/update', 'ShiftManagement::update', ['filter' => 'roleauth:admin,doctor,it_staff']);
$routes->post('shifts/delete', 'ShiftManagement::delete', ['filter' => 'roleauth:admin']);
$routes->post('shifts/(:num)/status', 'ShiftManagement::updateStatus/$1', ['filter' => 'roleauth:admin,doctor,it_staff']);

// ===================================================================
// UNIFIED PATIENT MANAGEMENT
// ===================================================================

// Patient Management Views - Role-specific entry points
$routes->get('admin/patient-management', 'PatientManagement::index', ['filter' => 'roleauth:admin']);
$routes->get('doctor/patient-management', 'PatientManagement::index', ['filter' => 'roleauth:doctor']);
$routes->get('doctor/patients', 'PatientManagement::index', ['filter' => 'roleauth:doctor']);
$routes->get('it-staff/patients', 'PatientManagement::index', ['filter' => 'roleauth:it_staff']);
$routes->get('nurse/patients', 'PatientManagement::index', ['filter' => 'roleauth:nurse']);
$routes->get('receptionist/patients', 'PatientManagement::index', ['filter' => 'roleauth:receptionist']);

// Patient Management API Routes
$routes->get('patients/api', 'PatientManagement::getPatientsAPI', ['filter' => 'roleauth:admin,doctor,nurse,receptionist,it_staff']);
$routes->get('patients/(:num)', 'PatientManagement::getPatient/$1', ['filter' => 'roleauth:admin,doctor,nurse,receptionist,it_staff']);
$routes->get('patients/doctors', 'PatientManagement::getDoctorsAPI', ['filter' => 'roleauth:admin,doctor,nurse,receptionist,it_staff']);
$routes->post('patients/create', 'PatientManagement::createPatient', ['filter' => 'roleauth:admin,doctor,receptionist,it_staff']);
$routes->put('patients/(:num)', 'PatientManagement::updatePatient/$1', ['filter' => 'roleauth:admin,doctor,receptionist,it_staff']);
$routes->post('patients/(:num)', 'PatientManagement::updatePatient/$1', ['filter' => 'roleauth:admin,doctor,receptionist,it_staff']);
$routes->post('patients/(:num)/status', 'PatientManagement::updatePatientStatus/$1', ['filter' => 'roleauth:admin,doctor,nurse,it_staff']);
$routes->post('patients/(:num)/assign-doctor', 'PatientManagement::assignDoctor/$1', ['filter' => 'roleauth:admin,receptionist,it_staff']);
$routes->delete('patients/(:num)', 'PatientManagement::deletePatient/$1', ['filter' => 'roleauth:admin,it_staff']);

// ===================================================================
// UNIFIED APPOINTMENT MANAGEMENT
// ===================================================================

// Appointment Management Views - Role-specific entry points
$routes->get('admin/appointments', 'AppointmentManagement::index', ['filter' => 'roleauth:admin']);
$routes->get('doctor/appointments', 'AppointmentManagement::index', ['filter' => 'roleauth:doctor']);
$routes->get('nurse/appointments', 'AppointmentManagement::index', ['filter' => 'roleauth:nurse']);
$routes->get('receptionist/appointments', 'AppointmentManagement::index', ['filter' => 'roleauth:receptionist']);

// Appointment Management API Routes
$routes->get('appointments/api', 'AppointmentManagement::getAppointmentsAPI', ['filter' => 'roleauth:admin,doctor,nurse,receptionist']);
$routes->get('appointments/patients', 'AppointmentManagement::getPatientsList', ['filter' => 'roleauth:admin,doctor,receptionist']);
$routes->get('appointments/doctors', 'AppointmentManagement::getDoctorsList', ['filter' => 'roleauth:admin,doctor,receptionist']);
$routes->get('appointments/(:num)', 'AppointmentManagement::getAppointment/$1', ['filter' => 'roleauth:admin,doctor,nurse,receptionist']);
$routes->post('appointments/create', 'AppointmentManagement::createAppointment', ['filter' => 'roleauth:admin,doctor,receptionist']);
$routes->put('appointments/(:num)', 'AppointmentManagement::updateAppointment/$1', ['filter' => 'roleauth:admin,doctor,receptionist']);
$routes->post('appointments/(:num)', 'AppointmentManagement::updateAppointment/$1', ['filter' => 'roleauth:admin,doctor,receptionist']);
$routes->post('appointments/(:num)/status', 'AppointmentManagement::updateAppointmentStatus/$1', ['filter' => 'roleauth:admin,doctor,nurse']);
$routes->delete('appointments/(:num)', 'AppointmentManagement::deleteAppointment/$1', ['filter' => 'roleauth:admin']);

// ===================================================================
// UNIFIED PRESCRIPTION MANAGEMENT
// ===================================================================

// Prescription Management Views - Role-specific entry points
$routes->get('admin/prescriptions', 'PrescriptionManagement::index', ['filter' => 'roleauth:admin']);
$routes->get('doctor/prescriptions', 'PrescriptionManagement::index', ['filter' => 'roleauth:doctor']);
$routes->get('it-staff/prescriptions', 'PrescriptionManagement::index', ['filter' => 'roleauth:it_staff']);
$routes->get('nurse/prescriptions', 'PrescriptionManagement::index', ['filter' => 'roleauth:nurse']);
$routes->get('pharmacist/prescriptions', 'PrescriptionManagement::index', ['filter' => 'roleauth:pharmacist']);
$routes->get('receptionist/prescriptions', 'PrescriptionManagement::index', ['filter' => 'roleauth:receptionist']);

// Unified Prescription Route
$routes->get('unified/prescriptions', 'PrescriptionManagement::index', ['filter' => 'roleauth:admin,doctor,nurse,pharmacist,receptionist,it_staff']);

// Prescription Management API Routes
$routes->get('prescriptions/api', 'PrescriptionManagement::getPrescriptionsAPI', ['filter' => 'roleauth:admin,doctor,nurse,pharmacist,receptionist,it_staff']);
$routes->get('prescriptions/(:num)', 'PrescriptionManagement::getPrescription/$1', ['filter' => 'roleauth:admin,doctor,nurse,pharmacist,receptionist,it_staff']);
$routes->get('prescriptions/available-patients', 'PrescriptionManagement::getAvailablePatientsAPI', ['filter' => 'roleauth:admin,doctor,it_staff']);
$routes->get('prescriptions/available-doctors', 'PrescriptionManagement::getAvailableDoctorsAPI', ['filter' => 'roleauth:admin']);
$routes->post('prescriptions/create', 'PrescriptionManagement::create', ['filter' => 'roleauth:admin,doctor,it_staff']);
$routes->post('prescriptions/update', 'PrescriptionManagement::update', ['filter' => 'roleauth:admin,doctor,pharmacist,it_staff']);
$routes->post('prescriptions/delete', 'PrescriptionManagement::delete', ['filter' => 'roleauth:admin,doctor']);
$routes->post('prescriptions/(:num)/status', 'PrescriptionManagement::updateStatus/$1', ['filter' => 'roleauth:admin,doctor,pharmacist,it_staff']);

// Legacy Doctor Prescription Routes
$routes->get('doctor/create-prescription', 'PrescriptionManagement::index', ['filter' => 'roleauth:doctor']);
$routes->post('doctor/create-prescription', 'PrescriptionManagement::create', ['filter' => 'roleauth:doctor']);
$routes->get('doctor/prescriptions/api', 'PrescriptionManagement::getPrescriptionsAPI', ['filter' => 'roleauth:doctor']);
$routes->post('doctor/update-prescription-status', 'PrescriptionManagement::updateStatus', ['filter' => 'roleauth:doctor']);
$routes->get('doctor/prescription/(:any)', 'PrescriptionManagement::getPrescription/$1', ['filter' => 'roleauth:doctor']);
$routes->put('doctor/prescription/(:any)', 'PrescriptionManagement::update', ['filter' => 'roleauth:doctor']);

// ===================================================================
// FINANCIAL MANAGEMENT
// ===================================================================

// Financial Management Views - Role-specific entry points
$routes->get('accountant/financial', 'FinancialManagement::index', ['filter' => 'roleauth:accountant']);
$routes->get('admin/financial-management', 'FinancialManagement::index', ['filter' => 'roleauth:admin']);
$routes->get('admin/financial', 'FinancialManagement::index', ['filter' => 'roleauth:admin']);
$routes->get('admin/finance', 'FinancialManagement::index', ['filter' => 'roleauth:admin']);
// Redirect common typos to the canonical route
if (method_exists($routes, 'addRedirect')) {
    $routes->addRedirect('admin/financial%20management', 'admin/financial-management');
    $routes->addRedirect('admin/financial_management', 'admin/financial-management');
}
$routes->get('doctor/financial', 'FinancialManagement::index', ['filter' => 'roleauth:doctor']);
$routes->get('receptionist/financial', 'FinancialManagement::index', ['filter' => 'roleauth:receptionist']);

// Analytics & Reports Routes
$routes->get('admin/analytics', 'AnalyticsManagement::index', ['filter' => 'roleauth:admin']);
$routes->get('accountant/analytics', 'AnalyticsManagement::index', ['filter' => 'roleauth:accountant']);
$routes->get('doctor/analytics', 'AnalyticsManagement::index', ['filter' => 'roleauth:doctor']);
$routes->get('nurse/analytics', 'AnalyticsManagement::index', ['filter' => 'roleauth:nurse']);
$routes->get('receptionist/analytics', 'AnalyticsManagement::index', ['filter' => 'roleauth:receptionist']);
$routes->get('it-staff/analytics', 'AnalyticsManagement::index', ['filter' => 'roleauth:it_staff']);
$routes->get('analytics/api', 'AnalyticsManagement::getAnalyticsAPI', ['filter' => 'roleauth:admin,accountant,doctor,nurse,receptionist,it_staff']);
$routes->post('analytics/report/generate', 'AnalyticsManagement::generateReport', ['filter' => 'roleauth:admin,accountant,doctor,it_staff']);

// ===================================================================
// ROLE-SPECIFIC ROUTES (LEGACY)
// ===================================================================

// Doctor Routes
$routes->get('doctor/doctors/api', 'Doctor::getDoctorsAPI');
$routes->get('doctor/EHR', 'Doctor::ehr');
$routes->get('doctor/lab-results', 'Doctor::labResults');
$routes->get('doctor/schedule', 'Doctor::schedule');

// Nurse Routes
$routes->get('nurse/dashboard', 'Nurse::dashboard');
$routes->get('nurse/doctors/api', 'Nurse::getDoctorsAPI');
$routes->get('nurse/medication', 'Nurse::medication');
$routes->get('nurse/patient', 'Nurse::patient');
$routes->get('nurse/patient-management', 'Nurse::patient');
$routes->get('nurse/shift-report', 'Nurse::shiftReport');
$routes->get('nurse/vitals', 'Nurse::vitals');

// Receptionist Routes
$routes->get('receptionist/appointment-booking', 'Receptionist::appointmentBooking');
$routes->get('receptionist/dashboard', 'Receptionist::dashboard');
$routes->get('receptionist/patient-registration', 'Receptionist::patientRegistration');
$routes->post('receptionist/patient-registration/store', 'Receptionist::storePatient');
$routes->post('receptionist/register-patient', 'Receptionist::registerPatient');
$routes->get('receptionist/patients/api', 'Receptionist::getPatientsAPI');

// Accountant Routes
$routes->get('accountant/billing', 'Accountant::billing');
$routes->get('accountant/dashboard', 'Accountant::dashboard');
$routes->get('accountant/insurance', 'Accountant::insurance');
$routes->get('accountant/payments', 'Accountant::payments');

// IT Staff Routes
$routes->get('it/dashboard', 'ITStaff::dashboard');
$routes->get('it/maintenance', 'ITStaff::maintenance');
$routes->get('it/security', 'ITStaff::security');

// Laboratorist Routes
$routes->get('laboratorists/dashboard', 'Laboratorist::dashboard');
$routes->get('laboratorists/sample-management', 'Laboratorist::sampleManagement');
$routes->get('laboratorists/test-request', 'Laboratorist::testRequest');
$routes->get('laboratorists/test-result', 'Laboratorist::testResult');

// Pharmacist Routes
$routes->get('pharmacists/dashboard', 'Pharmacist::dashboard');
$routes->get('pharmacists/inventory', 'Pharmacist::inventory');
$routes->get('pharmacists/prescription', 'Pharmacist::prescription');

// ===================================================================
// FINANCIAL MANAGEMENT ROUTES
// ===================================================================

$routes->get('financial-test', 'FinancialController::test');
$routes->get('financial-modal-demo', 'FinancialController::demo');
$routes->get('api/users', 'FinancialController::getUsersAPI');
$routes->get('financial-management', 'FinancialController::index');
$routes->get('financial-management/add', 'FinancialController::addTransaction');
$routes->post('financial-management/add', 'FinancialController::addTransaction');
$routes->get('financial-management/categories', 'FinancialController::getCategoriesByType');

// ===================================================================
// LEGACY COMPATIBILITY ROUTES
// ===================================================================

// Legacy admin routes for backward compatibility

$routes->get('create_resources_table.php', function() {
    require APPPATH . '../create_resources_table.php';
});
$routes->get('test-doctors', 'TestController::doctors');
$routes->get('create_department_table.php', function() {
    require APPPATH . '../create_department_table.php';
});
$routes->get('create_sample_data.php', function() {
    require APPPATH . '../create_sample_data.php';
});
$routes->get('setup_financial_tables.php', function() {
    require APPPATH . '../setup_financial_tables.php';
});
