
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
// Admin Dashboard Routes
$routes->get('admin/dashboard', 'Admin::dashboard');
$routes->get('admin/users', 'Admin::users');

// Staff Management Routes
$routes->group('admin', function($routes) {
    // Staff listing and management
    $routes->get('staff-management', 'Admin::staffManagement');
    
    // Staff CRUD operations
    $routes->get('add-staff', 'Admin::addStaff');
    $routes->post('add-staff', 'Admin::addStaff');
    $routes->post('staff/create', 'Admin::addStaff');
    $routes->get('staff/api', 'Admin::getStaffAPI');
    
    // Individual staff operations
    $routes->get('edit-staff/(:num)', 'Admin::editStaff/$1');
    $routes->post('edit-staff/(:num)', 'Admin::editStaff/$1');
    $routes->get('delete-staff/(:num)', 'Admin::deleteStaff/$1');
    $routes->get('view-staff/(:num)', 'Admin::viewStaff/$1');

    // Users Management
    $routes->get('user-management', 'Admin::userManagement');
    $routes->post('users/saveUser', 'Admin::saveUser');
    $routes->post('users/updateUser', 'Admin::updateUser');
    $routes->get('users/get/(:num)', 'Admin::getUser/$1');
    $routes->get('users/delete/(:num)', 'Admin::deleteUser/$1');
    $routes->get('users/reset/(:num)', 'Admin::resetUserPassword/$1');

    // Resource Management
    $routes->get('resource', 'Admin::resourceManagement');

    // Patient Management
    $routes->get('patient-management', 'Admin::patientManagement');
    $routes->post('patients', 'Admin::createPatient');
    $routes->post('patients/update', 'Admin::updatePatient');

    // Financial Management
    $routes->get('financial', 'Admin::financialManagement');

    // Communication
    $routes->get('communication', 'Admin::communication');

    // Analytics & Reports
    $routes->get('analytics', 'Admin::analytics');

    // System Settings
    $routes->get('systemSettings', 'Admin::systemSettings');

    // Security & Access
    $routes->get('securityAccess', 'Admin::securityAccess');

    // Audit Logs
    $routes->get('auditLogs', 'Admin::auditLogs');

    // Doctor Shifts APIs
    $routes->get('doctor-shifts/api', 'Admin::getDoctorShiftsAPI');
    $routes->get('doctor-shifts/(:num)', 'Admin::getDoctorShift/$1');
    $routes->post('doctor-shifts/update', 'Admin::updateDoctorShift');
    $routes->post('doctor-shifts/create', 'Admin::createDoctorShift');
    $routes->post('doctor-shifts/delete', 'Admin::deleteDoctorShift');

    // Doctors list API
    $routes->get('doctors/api', 'Admin::getDoctorsAPI');
});
// Doctor Routes
$routes->get('doctor/dashboard', 'Doctor::dashboard');
$routes->get('doctor/patients', 'Doctor::patients');
$routes->post('doctor/patients', 'Doctor::createPatient');
$routes->get('doctor/patient', 'Doctor::patients');
$routes->get('doctor/appointments', 'Doctor::appointments');
$routes->post('doctor/schedule-appointment', 'Doctor::postScheduleAppointment');
$routes->get('doctor/prescriptions', 'Doctor::prescriptions');
$routes->post('doctor/create-prescription', 'Doctor::createPrescription');
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
