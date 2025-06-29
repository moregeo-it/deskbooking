<?php

return [
    'routes' => [
        // Page routes
        ['name' => 'page#index', 'url' => '/', 'verb' => 'GET'],
        
        // Admin routes
        ['name' => 'admin#index', 'url' => '/admin', 'verb' => 'GET'],
        ['name' => 'admin#getDesks', 'url' => '/admin/api/desks', 'verb' => 'GET'],
        
        // API routes for desks
        ['name' => 'desk_api#index', 'url' => '/api/desks', 'verb' => 'GET'],
        ['name' => 'desk_api#show', 'url' => '/api/desks/{id}', 'verb' => 'GET'],
        ['name' => 'desk_api#create', 'url' => '/api/desks', 'verb' => 'POST'],
        ['name' => 'desk_api#update', 'url' => '/api/desks/{id}', 'verb' => 'PUT'],
        ['name' => 'desk_api#destroy', 'url' => '/api/desks/{id}', 'verb' => 'DELETE'],
        
        // API routes for bookings
        ['name' => 'booking_api#index', 'url' => '/api/bookings', 'verb' => 'GET'],
        ['name' => 'booking_api#show', 'url' => '/api/bookings/{id}', 'verb' => 'GET'],
        ['name' => 'booking_api#myBookings', 'url' => '/api/bookings/my', 'verb' => 'GET'],
        ['name' => 'booking_api#byDesk', 'url' => '/api/bookings/desk/{deskId}', 'verb' => 'GET'],
        ['name' => 'booking_api#byDateRange', 'url' => '/api/bookings/date/{startDate}/{endDate}', 'verb' => 'GET'],
        ['name' => 'booking_api#create', 'url' => '/api/bookings', 'verb' => 'POST'],
        ['name' => 'booking_api#update', 'url' => '/api/bookings/{id}', 'verb' => 'PUT'],
        ['name' => 'booking_api#destroy', 'url' => '/api/bookings/{id}', 'verb' => 'DELETE'],
        ['name' => 'booking_api#cancel', 'url' => '/api/bookings/{id}/cancel', 'verb' => 'PATCH'],
    ]
];
