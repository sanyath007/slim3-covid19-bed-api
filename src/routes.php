<?php

$app->options('/{routes:.+}', function($request, $response, $args) {
    return $response;
});

$app->add(function ($req, $res, $next) {
    $response = $next($req, $res);
    return $response
            ->withHeader('Access-Control-Allow-Origin', '*')
            ->withHeader('Access-Control-Allow-Headers', 'X-Requested-With, Content-Type, Accept, Origin, Authorization')
            ->withHeader('Access-Control-Allow-Methods', 'GET, POST, PUT, DELETE, PATCH, OPTIONS');
});

$app->get('/', 'HomeController:home')->setName('home');

$app->post('/login', 'LoginController:login')->setName('login');

$app->group('/api', function(Slim\App $app) { 
    $app->get('/users', 'UserController:index');
    $app->get('/users/{loginname}', 'UserController:getUser');

    $app->get('/stats/{month}/patients', 'DashboardController:overallPatientStats');
    $app->get('/stats/{month}/beds', 'DashboardController:overallBedStats');
    $app->get('/stats/{month}/admit-day', 'DashboardController:admitDayStats');
    $app->get('/stats/{month}/collect-day', 'DashboardController:collectDayStats');
    
    $app->get('/beds', 'BedController:getAll');
    $app->get('/beds/{id}', 'BedController:getById');
    $app->post('/beds', 'BedController:store');
    $app->put('/beds/{id}', 'BedController:update');
    $app->delete('/beds/{id}', 'BedController:delete');
    $app->get('/beds/{id}/used', 'BedController:getBedIsUsed');

    $app->get('/bed-types', 'BedTypeController:getAll');
    $app->get('/bed-types/{id}', 'BedTypeController:getById');
    $app->post('/bed-types', 'BedTypeController:store');
    $app->put('/bed-types/{id}', 'BedTypeController:update');
    $app->delete('/bed-types/{id}', 'BedTypeController:delete');

    $app->get('/wards', 'WardController:getAll');
    $app->get('/wards/{id}', 'WardController:getById');
    $app->post('/wards', 'WardController:store');
    $app->put('/wards/{id}', 'WardController:update');
    $app->delete('/wards/{id}', 'WardController:delete');
    $app->get('/wards/{ward}/beds', 'WardController:getWardBeds');
    $app->get('/wards/{id}/regises', 'WardController:getWardRegises');

    $app->get('/buildings', 'BuildingController:getAll');
    $app->get('/buildings/{id}', 'BuildingController:getById');
    $app->post('/buildings', 'BuildingController:store');
    $app->put('/buildings/{id}', 'BuildingController:update');
    $app->delete('/buildings/{id}', 'BuildingController:delete');    
    $app->get('/buildings/{id}/wards', 'BuildingController:getBuildingWards');

    $app->get('/patients', 'PatientController:getAll');
    $app->get('/patients/{hn}', 'PatientController:getById');
    $app->post('/patients', 'PatientController:store');
    $app->put('/patients/{id}', 'PatientController:update');
    $app->delete('/patients/{id}', 'PatientController:delete');

    $app->get('/registrations', 'RegistrationController:getAll');
    $app->get('/registrations/{id}', 'RegistrationController:getById');
    $app->get('/registrations/an/{an}', 'RegistrationController:getByAn');
    $app->get('/registrations/last/order-no', 'RegistrationController:generateOrderNo');
    $app->post('/registrations', 'RegistrationController:store');
    $app->put('/registrations/{id}', 'RegistrationController:update');
    $app->put('/registrations/cancel/{id}', 'RegistrationController:cancel');
    $app->delete('/registrations/{id}', 'RegistrationController:delete');
    $app->put('/registrations/discharge/{id}', 'RegistrationController:discharge');
    $app->put('/registrations/lab-result/{id}', 'RegistrationController:labResult');

    /** Routes to person db */
    $app->get('/depts', 'DeptController:getAll');
    $app->get('/depts/{id}', 'DeptController:getById');

    $app->get('/staffs', 'StaffController:getAll');
    $app->get('/staffs/{id}', 'StaffController:getById');

    /** Routes to hosxp db */
    $app->get('/ips', 'IpController:getAll');
    $app->get('/ips/{an}', 'IpController:getById');
});

// Catch-all route to serve a 404 Not Found page if none of the routes match
// NOTE: make sure this route is defined last
$app->map(['GET', 'POST', 'PUT', 'DELETE', 'PATCH'], '/{routes:.+}', function($req, $res) {
    $handler = $this->notFoundHandler; // handle using the default Slim page not found handler
    return $handler($req, $res);
});
