
<?php

use CodeIgniter\Router\RouteCollection;

/**
 * @var RouteCollection $routes
 */

// Main Routes
$routes->get('/', 'Home::index');
$routes->get('about', 'Home::about');
$routes->get('contact', 'Home::contact');

// Authentication Routes
$routes->get('login', 'Auth::login');
$routes->post('login', 'Auth::login');
$routes->get('logout', 'Auth::logout');
$routes->get('auth/logout', 'Auth::logout');

// ===================================================================
// UNIFIED DASHBOARD MANAGEMENT - All roles use Unified\DashboardController
// ===================================================================

// Dashboard Views - Role-specific entry points
$routes->get('admin/dashboard', 'Unified\DashboardController::index', ['filter' => 'roleauth:admin']);
$routes->get('admin', 'Unified\DashboardController::index', ['filter' => 'roleauth:admin']);
$routes->get('doctor/dashboard', 'Unified\DashboardController::index', ['filter' => 'roleauth:doctor']);
$routes->get('nurse/dashboard', 'Unified\DashboardController::index', ['filter' => 'roleauth:nurse']);
$routes->get('receptionist/dashboard', 'Unified\DashboardController::index', ['filter' => 'roleauth:receptionist']);
$routes->get('accountant/dashboard', 'Unified\DashboardController::index', ['filter' => 'roleauth:accountant']);
$routes->get('it-staff/dashboard', 'Unified\DashboardController::index', ['filter' => 'roleauth:it_staff']);
$routes->get('laboratorist/dashboard', 'Unified\DashboardController::index', ['filter' => 'roleauth:laboratorist']);
$routes->get('pharmacist/dashboard', 'Unified\DashboardController::index', ['filter' => 'roleauth:pharmacist']);

// Dashboard API Routes
$routes->get('api/dashboard-data', 'Unified\DashboardController::getDashboardData', ['filter' => 'roleauth:admin,doctor,nurse,receptionist,accountant,it_staff,laboratorist,pharmacist']);
$routes->get('api/system-health', 'Unified\DashboardController::getSystemHealth', ['filter' => 'roleauth:admin']);
$routes->get('api/today-schedule', 'Unified\DashboardController::getTodaySchedule', ['filter' => 'roleauth:doctor,nurse']);
$routes->get('api/quick-stats', 'Unified\DashboardController::getQuickStats', ['filter' => 'roleauth:admin,doctor,nurse,receptionist,accountant,it_staff,laboratorist,pharmacist']);
$routes->post('api/dashboard-preferences', 'Unified\DashboardController::updatePreferences', ['filter' => 'roleauth:admin,doctor,nurse,receptionist,accountant,it_staff,laboratorist,pharmacist']);

// User Management Routes
$routes->get('admin/user-management', 'UserManagement::index', ['filter' => 'roleauth:admin']);
$routes->post('admin/user-management/create', 'UserManagement::create', ['filter' => 'roleauth:admin']);
$routes->post('admin/user-management/update', 'UserManagement::update', ['filter' => 'roleauth:admin']);
$routes->get('admin/user-management/delete/(:num)', 'UserManagement::delete/$1', ['filter' => 'roleauth:admin']);
$routes->get('admin/user-management/reset-password/(:num)', 'UserManagement::resetPassword/$1', ['filter' => 'roleauth:admin']);
$routes->get('admin/user-management/users', 'UserManagement::getUsersAPI', ['filter' => 'roleauth:admin']);
$routes->get('admin/user-management/user/(:num)', 'UserManagement::getUser/$1', ['filter' => 'roleauth:admin']);
$routes->get('admin/user-management/staff/(:num)', 'UserManagement::getAvailableStaffAPI', ['filter' => 'roleauth:admin']);

// Unified User Management Routes - Multiple roles
$routes->get('doctor/users', 'UserManagement::index', ['filter' => 'roleauth:doctor']);
$routes->get('nurse/users', 'UserManagement::index', ['filter' => 'roleauth:nurse']);
$routes->get('receptionist/users', 'UserManagement::index', ['filter' => 'roleauth:receptionist']);
$routes->get('it-staff/users', 'UserManagement::index', ['filter' => 'roleauth:it_staff']);

// Unified User Management API Routes
$routes->get('users/api', 'UserManagement::getUsersAPI', ['filter' => 'roleauth:admin,doctor,nurse,receptionist,it_staff']);
$routes->get('users/(:num)', 'UserManagement::getUser/$1', ['filter' => 'roleauth:admin,doctor,nurse,receptionist,it_staff']);
$routes->post('users/create', 'UserManagement::create', ['filter' => 'roleauth:admin,it_staff']);
$routes->post('users/update/(:num)', 'UserManagement::update/$1', ['filter' => 'roleauth:admin,it_staff']);
$routes->delete('users/delete/(:num)', 'UserManagement::delete/$1', ['filter' => 'roleauth:admin,it_staff']);
$routes->post('users/reset-password/(:num)', 'UserManagement::resetPassword/$1', ['filter' => 'roleauth:admin,it_staff']);
$routes->get('users/available-staff', 'UserManagement::getAvailableStaffAPI', ['filter' => 'roleauth:admin,it_staff']);

// Staff Management Routes
$routes->get('admin/staff-management', 'StaffManagement::index', ['filter' => 'roleauth:admin']);
$routes->match(['get', 'post'], 'admin/staff-management/create', 'StaffManagement::create', ['filter' => 'roleauth:admin']);
$routes->post('admin/staff-management/update/(:num)', 'StaffManagement::update/$1', ['filter' => 'roleauth:admin']);
$routes->post('admin/staff-management/update', 'StaffManagement::update', ['filter' => 'roleauth:admin']);
$routes->get('admin/staff-management/delete/(:num)', 'StaffManagement::delete/$1', ['filter' => 'roleauth:admin']);
$routes->get('admin/staff-management/view/(:num)', 'StaffManagement::view/$1', ['filter' => 'roleauth:admin']);
$routes->get('admin/staff-management/api', 'StaffManagement::getStaffAPI', ['filter' => 'roleauth:admin']);
$routes->get('admin/staff-management/staff/(:num)', 'StaffManagement::getStaff/$1', ['filter' => 'roleauth:admin']);
$routes->get('admin/staff-management/doctors/api', 'StaffManagement::getDoctorsAPI', ['filter' => 'roleauth:admin']);
// Alias used by Shifts page for doctor selection
$routes->get('admin/doctors/api', 'StaffManagement::getDoctorsAPI', ['filter' => 'roleauth:admin']);

// Unified Staff Management Routes - Multiple roles
$routes->get('doctor/staff', 'StaffManagement::index', ['filter' => 'roleauth:doctor']);
$routes->get('nurse/staff', 'StaffManagement::index', ['filter' => 'roleauth:nurse']);
$routes->get('receptionist/staff', 'StaffManagement::index', ['filter' => 'roleauth:receptionist']);
$routes->get('it-staff/staff', 'StaffManagement::index', ['filter' => 'roleauth:it_staff']);

// Unified Staff Management API Routes
$routes->get('staff/api', 'StaffManagement::getStaffAPI', ['filter' => 'roleauth:admin,doctor,nurse,receptionist,it_staff']);
$routes->get('staff/(:num)', 'StaffManagement::getStaff/$1', ['filter' => 'roleauth:admin,doctor,nurse,receptionist,it_staff']);
$routes->post('staff/create', 'StaffManagement::create', ['filter' => 'roleauth:admin,it_staff']);
$routes->post('staff/update', 'StaffManagement::update', ['filter' => 'roleauth:admin,it_staff']);
$routes->delete('staff/delete/(:num)', 'StaffManagement::delete/$1', ['filter' => 'roleauth:admin,it_staff']);

// Resource Management Routes
$routes->get('admin/resource-management', 'ResourceManagement::index', ['filter' => 'roleauth:admin']);
$routes->get('admin/resource-management/api', 'ResourceManagement::getResourcesAPI', ['filter' => 'roleauth:admin']);
$routes->get('admin/resource-management/(:num)', 'ResourceManagement::getResource/$1', ['filter' => 'roleauth:admin']);
$routes->post('admin/resource-management/create', 'ResourceManagement::create', ['filter' => 'roleauth:admin']);
$routes->post('admin/resource-management/update', 'ResourceManagement::update', ['filter' => 'roleauth:admin']);
$routes->post('admin/resource-management/delete', 'ResourceManagement::delete', ['filter' => 'roleauth:admin']);

// Department Routes (for Add Department modal)
$routes->match(['get','post','options'], 'departments/create', 'Departments::create');

// ===================================================================
// UNIFIED SHIFT MANAGEMENT - All roles use ShiftManagement controller
// ===================================================================

// Shift Management Views - Role-specific entry points
$routes->get('admin/shifts', 'ShiftManagement::index', ['filter' => 'roleauth:admin']);
$routes->get('doctor/shifts', 'ShiftManagement::index', ['filter' => 'roleauth:doctor']);
$routes->get('nurse/shifts', 'ShiftManagement::index', ['filter' => 'roleauth:nurse']);
$routes->get('receptionist/shifts', 'ShiftManagement::index', ['filter' => 'roleauth:receptionist']);
$routes->get('it-staff/shifts', 'ShiftManagement::index', ['filter' => 'roleauth:it_staff']);

// Shift Management API Routes - Unified endpoints
$routes->get('shifts/api', 'ShiftManagement::getShiftsAPI', ['filter' => 'roleauth:admin,doctor,nurse,receptionist,it_staff']);
$routes->post('shifts/create', 'ShiftManagement::create', ['filter' => 'roleauth:admin,it_staff']);
$routes->post('shifts/update', 'ShiftManagement::update', ['filter' => 'roleauth:admin,doctor,it_staff']);
$routes->post('shifts/delete', 'ShiftManagement::delete', ['filter' => 'roleauth:admin']);
$routes->get('shifts/(:num)', 'ShiftManagement::getShift/$1', ['filter' => 'roleauth:admin,doctor,nurse,receptionist,it_staff']);
$routes->post('shifts/(:num)/status', 'ShiftManagement::updateStatus/$1', ['filter' => 'roleauth:admin,doctor,it_staff']);
$routes->get('shifts/available-staff', 'ShiftManagement::getAvailableStaffAPI', ['filter' => 'roleauth:admin,it_staff']);

// Legacy Doctor Shift Management Routes (for backward compatibility)
$routes->get('admin/doctor-shift-management', 'ShiftManagement::index', ['filter' => 'roleauth:admin']);
$routes->get('admin/doctor-shifts/api', 'ShiftManagement::getShiftsAPI', ['filter' => 'roleauth:admin']);
$routes->get('admin/doctor-shifts/(:num)', 'ShiftManagement::getShift/$1', ['filter' => 'roleauth:admin']);
$routes->post('admin/doctor-shifts/create', 'ShiftManagement::create', ['filter' => 'roleauth:admin']);
$routes->post('admin/doctor-shifts/update', 'ShiftManagement::update', ['filter' => 'roleauth:admin']);
$routes->post('admin/doctor-shifts/delete', 'ShiftManagement::delete', ['filter' => 'roleauth:admin']);

// Legacy routes (backward compatibility) – map directly to correct handlers
$routes->get('admin/users', 'Admin::users');
$routes->get('admin/staff', 'Admin::staffManagement');
$routes->get('admin/resources', 'ResourceManagement::index', ['filter' => 'roleauth:admin']);
$routes->get('admin/patients', 'Patients::index', ['filter' => 'roleauth:admin']);


// Patient Management Sidebar - All roles use PatientManagement controller
$routes->get('admin/patient-management', 'PatientManagement::index', ['filter' => 'roleauth:admin']);
$routes->get('doctor/patients', 'PatientManagement::index', ['filter' => 'roleauth:doctor']);
$routes->get('doctor/patient-management', 'PatientManagement::index', ['filter' => 'roleauth:doctor']);
$routes->get('nurse/patients', 'PatientManagement::index', ['filter' => 'roleauth:nurse']);
$routes->get('receptionist/patients', 'PatientManagement::index', ['filter' => 'roleauth:receptionist']);
$routes->get('it-staff/patients', 'PatientManagement::index', ['filter' => 'roleauth:it_staff']);

// Patient Management API Routes
$routes->post('patients/create', 'PatientManagement::createPatient', ['filter' => 'roleauth:admin,doctor,receptionist,it_staff']);
$routes->get('patients/api', 'PatientManagement::getPatientsAPI', ['filter' => 'roleauth:admin,doctor,nurse,receptionist,it_staff']);
$routes->get('patients/(:num)', 'PatientManagement::getPatient/$1', ['filter' => 'roleauth:admin,doctor,nurse,receptionist,it_staff']);
$routes->put('patients/(:num)', 'PatientManagement::updatePatient/$1', ['filter' => 'roleauth:admin,doctor,receptionist,it_staff']);
$routes->post('patients/(:num)', 'PatientManagement::updatePatient/$1', ['filter' => 'roleauth:admin,doctor,receptionist,it_staff']);
$routes->delete('patients/(:num)', 'PatientManagement::deletePatient/$1', ['filter' => 'roleauth:admin,it_staff']);
$routes->post('patients/(:num)/status', 'PatientManagement::updatePatientStatus/$1', ['filter' => 'roleauth:admin,doctor,nurse,it_staff']);
$routes->post('patients/(:num)/assign-doctor', 'PatientManagement::assignDoctor/$1', ['filter' => 'roleauth:admin,receptionist,it_staff']);
$routes->get('patients/doctors', 'PatientManagement::getDoctorsAPI', ['filter' => 'roleauth:admin,doctor,nurse,receptionist,it_staff']);

// ===================================================================
// UNIFIED APPOINTMENT MANAGEMENT - All roles use AppointmentManagement controller
// ===================================================================

// Appointment Management Views - Role-specific entry points
$routes->get('admin/appointments', 'AppointmentManagement::index', ['filter' => 'roleauth:admin']);
$routes->get('doctor/appointments', 'AppointmentManagement::index', ['filter' => 'roleauth:doctor']);
$routes->get('nurse/appointments', 'AppointmentManagement::index', ['filter' => 'roleauth:nurse']);
$routes->get('receptionist/appointments', 'AppointmentManagement::index', ['filter' => 'roleauth:receptionist']);

// Appointment Management API Routes - Unified endpoints
$routes->post('appointments/create', 'AppointmentManagement::createAppointment', ['filter' => 'roleauth:admin,doctor,receptionist']);
$routes->get('appointments/api', 'AppointmentManagement::getAppointmentsAPI', ['filter' => 'roleauth:admin,doctor,nurse,receptionist']);
$routes->get('appointments/(:num)', 'AppointmentManagement::getAppointment/$1', ['filter' => 'roleauth:admin,doctor,nurse,receptionist']);
$routes->put('appointments/(:num)', 'AppointmentManagement::updateAppointment/$1', ['filter' => 'roleauth:admin,doctor,receptionist']);
$routes->post('appointments/(:num)', 'AppointmentManagement::updateAppointment/$1', ['filter' => 'roleauth:admin,doctor,receptionist']);
$routes->delete('appointments/(:num)', 'AppointmentManagement::deleteAppointment/$1', ['filter' => 'roleauth:admin']);
$routes->post('appointments/(:num)/status', 'AppointmentManagement::updateAppointmentStatus/$1', ['filter' => 'roleauth:admin,doctor,nurse']);

// ===================================================================
// LEGACY COMPATIBILITY ROUTES
// ===================================================================

// Legacy admin routes for backward compatibility
$routes->get('admin/users', 'UserManagement::index', ['filter' => 'roleauth:admin']);
$routes->get('admin/staff', 'StaffManagement::index', ['filter' => 'roleauth:admin']);
$routes->get('admin/resources', 'ResourceManagement::index', ['filter' => 'roleauth:admin']);
$routes->get('admin/resource', 'ResourceManagement::index', ['filter' => 'roleauth:admin']);
// $routes->get('admin/prescriptions', 'Admin::prescriptions', ['filter' => 'roleauth:admin']); // REMOVED: Now using unified PrescriptionManagement
$routes->get('admin/financial', 'Admin::financialManagement', ['filter' => 'roleauth:admin']);

// Navigation pages (still in Admin controller)
$routes->get('admin/financial-management', 'Admin::financialManagement');
$routes->get('admin/analytics', 'Admin::analytics');
$routes->get('admin/system-settings', 'Admin::systemSettings');
// ===================================================================
// DOCTOR ROUTES
// ===================================================================

// Dashboard
$routes->get('doctor/dashboard', 'Doctor::dashboard');

// Patient Management - REMOVED: Now using unified PatientManagement controller
// Legacy routes removed to prevent conflicts with unified approach

// Appointment Management - REMOVED: Now using unified AppointmentManagement controller
// Legacy routes removed to prevent conflicts with unified approach

// ===================================================================
// UNIFIED PRESCRIPTION MANAGEMENT - All roles use PrescriptionManagement controller
// ===================================================================

// Prescription Management Views - Role-specific entry points
$routes->get('admin/prescriptions', 'PrescriptionManagement::index', ['filter' => 'roleauth:admin']);
$routes->get('doctor/prescriptions', 'PrescriptionManagement::index', ['filter' => 'roleauth:doctor']);
$routes->get('nurse/prescriptions', 'PrescriptionManagement::index', ['filter' => 'roleauth:nurse']);
$routes->get('pharmacist/prescriptions', 'PrescriptionManagement::index', ['filter' => 'roleauth:pharmacist']);
$routes->get('receptionist/prescriptions', 'PrescriptionManagement::index', ['filter' => 'roleauth:receptionist']);
$routes->get('it-staff/prescriptions', 'PrescriptionManagement::index', ['filter' => 'roleauth:it_staff']);

// Prescription Management API Routes - Unified endpoints
$routes->get('prescriptions/api', 'PrescriptionManagement::getPrescriptionsAPI', ['filter' => 'roleauth:admin,doctor,nurse,pharmacist,receptionist,it_staff']);
$routes->post('prescriptions/create', 'PrescriptionManagement::create', ['filter' => 'roleauth:admin,doctor,it_staff']);
$routes->post('prescriptions/update', 'PrescriptionManagement::update', ['filter' => 'roleauth:admin,doctor,pharmacist,it_staff']);
$routes->post('prescriptions/delete', 'PrescriptionManagement::delete', ['filter' => 'roleauth:admin,doctor']);
$routes->get('prescriptions/(:num)', 'PrescriptionManagement::getPrescription/$1', ['filter' => 'roleauth:admin,doctor,nurse,pharmacist,receptionist,it_staff']);
$routes->post('prescriptions/(:num)/status', 'PrescriptionManagement::updateStatus/$1', ['filter' => 'roleauth:admin,doctor,pharmacist,it_staff']);
$routes->get('prescriptions/available-patients', 'PrescriptionManagement::getAvailablePatientsAPI', ['filter' => 'roleauth:admin,doctor,it_staff']);

// Legacy Prescription Management Routes (for backward compatibility)
$routes->get('doctor/create-prescription', 'PrescriptionManagement::index', ['filter' => 'roleauth:doctor']);
$routes->post('doctor/create-prescription', 'PrescriptionManagement::create', ['filter' => 'roleauth:doctor']);
$routes->get('doctor/prescriptions/api', 'PrescriptionManagement::getPrescriptionsAPI', ['filter' => 'roleauth:doctor']);
$routes->post('doctor/update-prescription-status', 'PrescriptionManagement::updateStatus', ['filter' => 'roleauth:doctor']);
$routes->get('doctor/prescription/(:any)', 'PrescriptionManagement::getPrescription/$1', ['filter' => 'roleauth:doctor']);
$routes->put('doctor/prescription/(:any)', 'PrescriptionManagement::update', ['filter' => 'roleauth:doctor']);

// Doctor APIs
$routes->get('doctor/doctors/api', 'Doctor::getDoctorsAPI');

// Other Doctor Features
$routes->get('doctor/lab-results', 'Doctor::labResults');
$routes->get('doctor/EHR', 'Doctor::ehr');
$routes->get('doctor/schedule', 'Doctor::schedule');

    // Nurse Routes
    $routes->get('nurse/dashboard', 'Nurse::dashboard');
    $routes->get('nurse/patient', 'Nurse::patient');
    $routes->get('nurse/patient-management', 'Nurse::patient');
    $routes->get('nurse/medication', 'Nurse::medication');
    $routes->get('nurse/vitals', 'Nurse::vitals');
    $routes->get('nurse/shift-report', 'Nurse::shiftReport');
    $routes->get('nurse/doctors/api', 'Nurse::getDoctorsAPI');

    // Receptionist Routes
    $routes->get('receptionist/dashboard', 'Receptionist::dashboard');
    $routes->get('receptionist/appointment-booking', 'Receptionist::appointmentBooking');
    $routes->get('receptionist/patient-registration', 'Receptionist::patientRegistration');
    // Receptionist Patient Management Routes
    $routes->post('receptionist/register-patient', 'Receptionist::registerPatient');
    $routes->get('receptionist/patients/api', 'Receptionist::getPatientsAPI');
    $routes->post('receptionist/patient-registration/store', 'Receptionist::storePatient');

    // Accountant Routes
    $routes->get('accountant/dashboard', 'Accountant::dashboard');
    $routes->get('accountant/billing', 'Accountant::billing');
    $routes->get('accountant/payments', 'Accountant::payments');
    $routes->get('accountant/insurance', 'Accountant::insurance');

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
    $routes->get('pharmacists/prescription', 'Pharmacist::prescription');
    $routes->get('pharmacists/inventory', 'Pharmacist::inventory');

$routes->setAutoRoute(true);
