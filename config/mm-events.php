<?php

return [

    /*
     * The default event duration in minutes.
     * This is being used as the default duration when no end is provided.
     */
    'event_default_duration' => 30,

    /*
     * The event active duration determines how long
     * events should be treated active when no end date
     * is provided.
     * This value is measured in minutes.
     */
    'event_active_duration' => 30,

    /*
     * This table names are used to connect with other packages.
     */
    'places_table' => 'mm_places',

    /**
     * The admin endpoints are being registered under this prefix.
     */
    'admin_prefix' => 'admin',

    /**
     * This middleware stack is being
     * used for all admin routes.
     */
    'admin_middleware' => ['web', 'auth'],

    'admin_as' => 'admin.',

    /**
     * The api endpoints are being registered
     * under this prefix.
     */
    'api_prefix' => 'api',

    /**
     * This middleware stack is being
     * used for all api routes.
     */
    'api_middleware' => ['api'],

];
