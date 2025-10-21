
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

// Admin Dashboard
$routes->get('admin/dashboard', 'Admin::dashboard', ['filter' => 'roleauth:admin']);
$routes->get('admin', 'Admin::dashboard', ['filter' => 'roleauth:admin']); // Default admin route

// User Management Routes
$routes->get('admin/user-management', 'UserManagement::index', ['filter' => 'roleauth:admin']);
$routes->post('admin/user-management/create', 'UserManagement::create', ['filter' => 'roleauth:admin']);
$routes->post('admin/user-management/update', 'UserManagement::update', ['filter' => 'roleauth:admin']);
$routes->get('admin/user-management/delete/(:num)', 'UserManagement::delete/$1', ['filter' => 'roleauth:admin']);
$routes->get('admin/user-management/reset-password/(:num)', 'UserManagement::resetPassword/$1', ['filter' => 'roleauth:admin']);
$routes->get('admin/user-management/users', 'UserManagement::getUsers', ['filter' => 'roleauth:admin']);
$routes->get('admin/user-management/user/(:num)', 'UserManagement::getUser/$1', ['filter' => 'roleauth:admin']);
$routes->get('admin/user-management/staff/(:num)', 'UserManagement::getStaff/$1', ['filter' => 'roleauth:admin']);

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


// Resource Management Routes
$routes->get('admin/resource-management', 'ResourceManagement::index', ['filter' => 'roleauth:admin']);
$routes->get('admin/resource-management/api', 'ResourceManagement::getResourcesAPI', ['filter' => 'roleauth:admin']);
$routes->get('admin/resource-management/(:num)', 'ResourceManagement::getResource/$1', ['filter' => 'roleauth:admin']);
$routes->post('admin/resource-management/create', 'ResourceManagement::create', ['filter' => 'roleauth:admin']);
$routes->post('admin/resource-management/update', 'ResourceManagement::update', ['filter' => 'roleauth:admin']);
$routes->post('admin/resource-management/delete', 'ResourceManagement::delete', ['filter' => 'roleauth:admin']);

// Doctor Shift Management Routes
$routes->get('admin/doctor-shift-management', 'DoctorShiftManagement::index', ['filter' => 'roleauth:admin']);
$routes->get('admin/doctor-shifts/api', 'DoctorShiftManagement::getDoctorShiftsAPI', ['filter' => 'roleauth:admin']);
$routes->get('admin/doctor-shifts/(:num)', 'DoctorShiftManagement::getDoctorShift/$1', ['filter' => 'roleauth:admin']);
$routes->post('admin/doctor-shifts/create', 'DoctorShiftManagement::create', ['filter' => 'roleauth:admin']);
$routes->post('admin/doctor-shifts/update', 'DoctorShiftManagement::update', ['filter' => 'roleauth:admin']);
$routes->post('admin/doctor-shifts/delete', 'DoctorShiftManagement::delete', ['filter' => 'roleauth:admin']);

// Legacy routes (backward compatibility) – map directly to correct handlers
$routes->get('admin/users', 'Admin::users');
$routes->get('admin/staff', 'Admin::staffManagement');
$routes->get('admin/resources', 'ResourceManagement::index', ['filter' => 'roleauth:admin']);
$routes->get('admin/patients', 'PatientManagement::index', ['filter' => 'roleauth:admin']);

// -------------------------------------------------------------------
// Admin aliases to match sidebar links (fix 404s)
// -------------------------------------------------------------------
// Patient Management (admin/patient-management)
$routes->get('admin/patient-management', 'PatientManagement::index', ['filter' => 'roleauth:admin']);
// Shifts Management (admin/shifts)
$routes->get('admin/shifts', 'Shifts::index', ['filter' => 'roleauth:admin']);
// Appointments (admin/appointments)
$routes->get('admin/appointments', 'AdminAppointments::index', ['filter' => 'roleauth:admin']);
// Resource Management (sidebar uses admin/resource)
$routes->get('admin/resource', 'ResourceManagement::index', ['filter' => 'roleauth:admin']);
// Financial Management (sidebar uses admin/financial)
$routes->get('admin/financial', 'Admin::financialManagement', ['filter' => 'roleauth:admin']);

// Navigation pages (still in Admin controller)
$routes->get('admin/financial-management', 'Admin::financialManagement');
$routes->get('admin/communication', 'Admin::communication');
$routes->get('admin/analytics', 'Admin::analytics');
$routes->get('admin/system-settings', 'Admin::systemSettings');
$routes->get('admin/security-access', 'Admin::securityAccess');
$routes->get('admin/audit-logs', 'Admin::auditLogs');
// ===================================================================
// DOCTOR ROUTES
// ===================================================================

// Dashboard
$routes->get('doctor/dashboard', 'Doctor::dashboard');

// Patient Management
$routes->get('doctor/patients', 'Patients::patients');
$routes->post('doctor/patients', 'Patients::createPatient');
$routes->get('doctor/patients/api', 'Patients::getPatientsAPI');
$routes->get('doctor/patient/(:num)', 'Patients::getPatient/$1');
$routes->get('doctor/patient', 'Patients::patients');
$routes->put('doctor/patient/(:num)', 'Patients::updatePatient/$1');
$routes->post('doctor/patient/(:num)', 'Patients::updatePatient/$1');

// Appointment Management
$routes->get('doctor/appointments', 'Appointments::appointments');
$routes->post('doctor/schedule-appointment', 'Appointments::postScheduleAppointment');
$routes->get('doctor/appointment-data', 'Appointments::getAppointmentData');
$routes->post('doctor/update-appointment-status', 'Appointments::updateAppointmentStatus');
$routes->post('doctor/delete-appointment', 'Appointments::deleteAppointment');
$routes->get('doctor/appointment/details/(:num)', 'Appointments::getAppointmentDetails/$1');

// Prescription Management
$routes->get('doctor/prescriptions', 'Prescriptions::prescriptions');
$routes->post('doctor/create-prescription', 'Prescriptions::createPrescription');
$routes->get('doctor/prescriptions/api', 'Prescriptions::getPrescriptionsAPI');
$routes->post('doctor/update-prescription-status', 'Prescriptions::updatePrescriptionStatus');
$routes->get('doctor/prescription/(:any)', 'Prescriptions::getPrescription/$1');
$routes->put('doctor/prescription/(:any)', 'Prescriptions::updatePrescription/$1');

// Doctor APIs
$routes->get('doctor/doctors/api', 'Doctor::getDoctorsAPI');

// Other Doctor Features
$routes->get('doctor/lab-results', 'Doctor::labResults');
$routes->get('doctor/EHR', 'Doctor::ehr');
$routes->get('doctor/schedule', 'Doctor::schedule');

    // Nurse Routes
    $routes->get('nurse/dashboard', 'Nurse::dashboard');
    $routes->get('nurse/patient', 'Nurse::patient');
    $routes->get('nurse/medication', 'Nurse::medication');
    $routes->get('nurse/vitals', 'Nurse::vitals');
    $routes->get('nurse/shift-report', 'Nurse::shiftReport');

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
