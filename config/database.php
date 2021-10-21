<?php

return [

    /*
    |--------------------------------------------------------------------------
    | Default Database Connection Name
    |--------------------------------------------------------------------------
    |
    | Here you may specify which of the database connections below you wish
    | to use as your default connection for all database work. Of course
    | you may use many connections at once using the Database library.
    |
    */

    'default' => env('DB_CONNECTION', 'mysql'),

    /*
    |--------------------------------------------------------------------------
    | Database Connections
    |--------------------------------------------------------------------------
    |
    | Here are each of the database connections setup for your application.
    | Of course, examples of configuring each database platform that is
    | supported by Laravel is shown below to make development simple.
    |
    |
    | All database work in Laravel is done through the PHP PDO facilities
    | so make sure you have the driver for your particular database of
    | choice installed on your machine before you begin development.
    |
    */

    'connections' => [

        'sqlite' => [
            'driver' => 'sqlite',
            'database' => env('DB_DATABASE', database_path('database.sqlite')),
            'prefix' => '',
        ],

        'mysql' => [
            'driver' => 'mysql',
            'host' => env('DB_HOST', '127.0.0.1'),
            'port' => env('DB_PORT', '3306'),
            'database' => env('DB_DATABASE', 'forge'),
            'username' => env('DB_USERNAME', 'forge'),
            'password' => env('DB_PASSWORD', ''),
            'unix_socket' => env('DB_SOCKET', ''),
            'charset' => 'utf8mb4',
            'collation' => 'utf8mb4_unicode_ci',
            'prefix' => '',
            'strict' => true,
            'engine' => null,
        ],

        'frank' => [
            'driver' => 'mysql',
            'host' => env('DB_HOST', '127.0.0.1'),
            'port' => env('DB_PORT', '3306'),
            'database' => env('DB_DATABASE', 'forge'),
            'username' => env('DB_USERNAME', 'forge'),
            'password' => env('DB_PASSWORD', ''),
            'unix_socket' => env('DB_SOCKET', ''),
            'charset' => 'utf8mb4',
            'collation' => 'utf8mb4_unicode_ci',
            'prefix' => '',
            'strict' => false,
            'engine' => null,
            'options' => array(
                // PDO::ATTR_STRINGIFY_FETCHES => TRUE
            )
        ],

        'order' => [
            'driver' => 'mysql',
            'host' => env('DB_ORDER_HOST', '127.0.0.1'),
            'port' => env('DB_ORDER_PORT', '3306'),
            'database' => env('DB_ORDER_DATABASE', 'forge'),
            'username' => env('DB_ORDER_USERNAME', 'forge'),
            'password' => env('DB_ORDER_PASSWORD', ''),
            'unix_socket' => env('DB_ORDER_SOCKET', ''),
            'charset' => 'utf8mb4',
            'collation' => 'utf8mb4_unicode_ci',
            'prefix' => '',
            'strict' => true,
            'engine' => null,
        ],

        'review' => [
            'driver' => 'mysql',
            'host' => env('DB_REVIEW_HOST', '127.0.0.1'),
            'port' => env('DB_REVIEW_PORT', '3306'),
            'database' => env('DB_REVIEW_DATABASE', 'forge'),
            'username' => env('DB_REVIEW_USERNAME', 'forge'),
            'password' => env('DB_REVIEW_PASSWORD', ''),
            'unix_socket' => env('DB_REVIEW_SOCKET', ''),
            'charset' => 'utf8mb4',
            'collation' => 'utf8mb4_unicode_ci',
            'prefix' => '',
            'strict' => false,
            'engine' => null,
        ],


        'review_new' => [
            'driver' => 'mysql',
            'host' => env('DB_REVIEW_NEW_HOST', '127.0.0.1'),
            'port' => env('DB_REVIEW_NEW_PORT', '3306'),
            'database' => env('DB_REVIEW_NEW_DATABASE', 'forge'),
            'username' => env('DB_REVIEW_NEW_USERNAME', 'forge'),
            'password' => env('DB_REVIEW_NEW_PASSWORD', ''),
            'unix_socket' => env('DB_REVIEW_NEW_SOCKET', ''),
            'charset' => 'latin1',
            'collation' => 'latin1_swedish_ci',
            'prefix' => '',
            'strict' => false,
            'engine' => null,
        ],

        'pgsql' => [
            'driver' => 'pgsql',
            'host' => env('DB_HOST', '127.0.0.1'),
            'port' => env('DB_PORT', '5432'),
            'database' => env('DB_DATABASE', 'forge'),
            'username' => env('DB_USERNAME', 'forge'),
            'password' => env('DB_PASSWORD', ''),
            'charset' => 'utf8',
            'prefix' => '',
            'schema' => 'public',
            'sslmode' => 'prefer',
        ],

        'sqlsrv' => [
            'driver' => 'sqlsrv',
            'host' => env('DB_HOST', 'localhost'),
            'port' => env('DB_PORT', '1433'),
            'database' => env('DB_DATABASE', 'forge'),
            'username' => env('DB_USERNAME', 'forge'),
            'password' => env('DB_PASSWORD', ''),
            'charset' => 'utf8',
            'prefix' => '',
        ],
		
		'oa' => [
            'driver' => env('DB_OA_CONNECTION', ''),
			 'host' => env('DB_OA_HOST', ''),
			 'port' => env('DB_OA_PORT', ''),
			 'database' => env('DB_OA_DATABASE', ''),
			 'username' => env('DB_OA_USERNAME', ''),
			 'password' => env('DB_OA_PASSWORD', ''),
			 'charset' => 'utf8',
			 'collation' => 'utf8_unicode_ci',
			 'prefix' => '',
			 'strict' => false,
			 'engine' => null,
        ],
		
        'ccp' => [
            'driver' => 'mysql',
            'host' => env('DB_CCP_HOST', '127.0.0.1'),
            'port' => env('DB_CCP_PORT', '3306'),
            'database' => env('DB_CCP_DATABASE', 'forge'),
            'username' => env('DB_CCP_USERNAME', 'forge'),
            'password' => env('DB_CCP_PASSWORD', ''),
            'unix_socket' => env('DB_SOCKET', ''),
            'charset' => 'utf8mb4',
            'collation' => 'utf8mb4_unicode_ci',
            'prefix' => '',
            'strict' => false,
            'engine' => null,
        ],
		
		'website' => [
			'driver' => 'mysql',
			'host' => env('DB_WEBSITE_HOST', '127.0.0.1'),
			'port' => env('DB_WEBSITE_PORT', '3306'),
			'database' => env('DB_WEBSITE_DATABASE', 'forge'),
			'username' => env('DB_WEBSITE_USERNAME', 'forge'),
			'password' => env('DB_WEBSITE_PASSWORD', ''),
			'unix_socket' => env('DB_WEBSITE_SOCKET', ''),
			'charset' => 'utf8mb4',
			'collation' => 'utf8mb4_unicode_ci',
			'prefix' => '',
			'strict' => false,
			'engine' => null,
		],

		'natrogix' => [
			'driver' => 'mysql',
			'host' => env('DB_NATROGIX_HOST', '127.0.0.1'),
			'port' => env('DB_NATROGIX_PORT', '3306'),
			'database' => env('DB_NATROGIX_DATABASE', 'forge'),
			'username' => env('DB_NATROGIX_USERNAME', 'forge'),
			'password' => env('DB_NATROGIX_PASSWORD', ''),
			'unix_socket' => env('DB_NATROGIX_SOCKET', ''),
			'charset' => 'utf8mb4',
			'collation' => 'utf8mb4_unicode_ci',
			'prefix' => '',
			'strict' => false,
			'engine' => null,
		],

		'drocon' => [
			'driver' => 'mysql',
			'host' => env('DB_DROCON_HOST', '127.0.0.1'),
			'port' => env('DB_DROCON_PORT', '3306'),
			'database' => env('DB_DROCON_DATABASE', 'forge'),
			'username' => env('DB_DROCON_USERNAME', 'forge'),
			'password' => env('DB_DROCON_PASSWORD', ''),
			'unix_socket' => env('DB_DROCON_SOCKET', ''),
			'charset' => 'utf8mb4',
			'collation' => 'utf8mb4_unicode_ci',
			'prefix' => '',
			'strict' => false,
			'engine' => null,
		],

		'amazon' => [
			'read'=>[
				'host'=>env('DB_AMAZON_R_HOST', '127.0.0.1'),
				'username' => env('DB_AMAZON_R_USERNAME', 'forge'),
				'password' => env('DB_AMAZON_R_PASSWORD', '')
			],
			'write'=>[
				'host'=>env('DB_AMAZON_W_HOST', '127.0.0.1'),
				'username' => env('DB_AMAZON_W_USERNAME', 'forge'),
				'password' => env('DB_AMAZON_W_PASSWORD', '')
			],
			'driver' => 'mysql',
			//'host' => env('DB_AMAZON_HOST', '127.0.0.1'),
			'port' => env('DB_AMAZON_PORT', '3306'),
			'database' => env('DB_AMAZON_DATABASE', 'forge'),
			//'username' => env('DB_AMAZON_USERNAME', 'forge'),
			//'password' => env('DB_AMAZON_PASSWORD', ''),
			'unix_socket' => env('DB_AMAZON_SOCKET', ''),
			'charset' => 'utf8mb4',
			'collation' => 'utf8mb4_unicode_ci',
			'prefix' => '',
			'strict' => true,
			'engine' => null,
		],
	'vlz' => [
                        'read'=>[
                                'host'=>env('DB_AMAZON_R_HOST', '127.0.0.1'),
                                'username' => env('DB_AMAZON_R_USERNAME', 'forge'),
                                'password' => env('DB_AMAZON_R_PASSWORD', '')
                        ],
                        'write'=>[
                                'host'=>env('DB_AMAZON_W_HOST', '127.0.0.1'),
                                'username' => env('DB_AMAZON_W_USERNAME', 'forge'),
                                'password' => env('DB_AMAZON_W_PASSWORD', '')
                        ],
                        'driver' => 'mysql',
                        //'host' => env('DB_AMAZON_HOST', '127.0.0.1'),
                        'port' => env('DB_AMAZON_PORT', '3306'),
                        'database' => env('DB_AMAZON_DATABASE', 'forge'),
                        //'username' => env('DB_AMAZON_USERNAME', 'forge'),
                        //'password' => env('DB_AMAZON_PASSWORD', ''),
                        'unix_socket' => env('DB_AMAZON_SOCKET', ''),
                        'charset' => 'utf8mb4',
                        'collation' => 'utf8mb4_unicode_ci',
                        'prefix' => '',
                        'strict' => true,
                        'engine' => null,
                ],
        'vl' => [
            'driver' => 'mysql',
            'host' => env('DB_VL_HOST', '127.0.0.1'),
            'port' => env('DB_VL_PORT', '3306'),
            'database' => env('DB_VL_DATABASE', 'forge'),
            'username' => env('DB_VL_USERNAME', 'forge'),
            'password' => env('DB_VL_PASSWORD', ''),
            'unix_socket' => env('DB_VL_SOCKET', ''),
            'charset' => 'utf8mb4',
            'collation' => 'utf8mb4_unicode_ci',
            'prefix' => '',
            'strict' => false,
            'engine' => null,
        ],

		'ad' => [
			'driver' => 'mysql',
			'host' => env('DB_AD_HOST', '127.0.0.1'),
			'port' => env('DB_AD_PORT', '3306'),
			'database' => env('DB_AD_DATABASE', 'forge'),
			'username' => env('DB_AD_USERNAME', 'forge'),
			'password' => env('DB_AD_PASSWORD', ''),
			'charset' => 'utf8mb4',
			'collation' => 'utf8mb4_unicode_ci',
			'prefix' => '',
			'strict' => false,
			'engine' => null,
		],
        'ppc' => [
            'driver' => 'mysql',
            'host' => env('DB_PPC_HOST', '127.0.0.1'),
            'port' => env('DB_PPC_PORT', '3306'),
            'database' => env('DB_PPC_DATABASE', 'forge'),
            'username' => env('DB_PPC_USERNAME', 'forge'),
            'password' => env('DB_PPC_PASSWORD', ''),
            'unix_socket' => env('DB_SOCKET', ''),
            'charset' => 'utf8mb4',
            'collation' => 'utf8mb4_unicode_ci',
            'prefix' => '',
            'strict' => false,
            'engine' => null,
        ],

        'ebay' => [
            'driver' => 'mysql',
            'host' => env('DB_EBAY_HOST', '127.0.0.1'),
            'port' => env('DB_EBAY_PORT', '3306'),
            'database' => env('DB_EBAY_DATABASE', 'forge'),
            'username' => env('DB_EBAY_USERNAME', 'forge'),
            'password' => env('DB_EBAY_PASSWORD', ''),
            'unix_socket' => env('DB_SOCKET', ''),
            'charset' => 'utf8',
            'collation' => 'utf8_general_ci',
            'prefix' => '',
            'strict' => false,
            'engine' => null,
        ],

        'joybuy' => [
            'driver' => 'mysql',
            'host' => env('DB_JOYBUY_HOST', '127.0.0.1'),
            'port' => env('DB_JOYBUY_PORT', '3306'),
            'database' => env('DB_JOYBUY_DATABASE', 'forge'),
            'username' => env('DB_JOYBUY_USERNAME', 'forge'),
            'password' => env('DB_JOYBUY_PASSWORD', ''),
            'unix_socket' => env('DB_SOCKET', ''),
            'charset' => 'utf8',
            'collation' => 'utf8_general_ci',
            'prefix' => '',
            'strict' => false,
            'engine' => null,
        ],

        'newegg' => [
            'driver' => 'mysql',
            'host' => env('DB_NEWEGG_HOST', '127.0.0.1'),
            'port' => env('DB_NEWEGG_PORT', '3306'),
            'database' => env('DB_NEWEGG_DATABASE', 'forge'),
            'username' => env('DB_NEWEGG_USERNAME', 'forge'),
            'password' => env('DB_NEWEGG_PASSWORD', ''),
            'unix_socket' => env('DB_SOCKET', ''),
            'charset' => 'utf8',
            'collation' => 'utf8_general_ci',
            'prefix' => '',
            'strict' => false,
            'engine' => null,
        ],

        'letian' => [
            'driver' => 'mysql',
            'host' => env('DB_LETIAN_HOST', '127.0.0.1'),
            'port' => env('DB_LETIAN_PORT', '3306'),
            'database' => env('DB_LETIAN_DATABASE', 'forge'),
            'username' => env('DB_LETIAN_USERNAME', 'forge'),
            'password' => env('DB_LETIAN_PASSWORD', ''),
            'unix_socket' => env('DB_SOCKET', ''),
            'charset' => 'utf8',
            'collation' => 'utf8_general_ci',
            'prefix' => '',
            'strict' => false,
            'engine' => null,
        ],

        'theshopsaver' => [
            'driver' => 'mysql',
            'host' => env('DB_THESHOPSAVER_HOST', '127.0.0.1'),
            'port' => env('DB_THESHOPSAVER_PORT', '3306'),
            'database' => env('DB_THESHOPSAVER_DATABASE', 'forge'),
            'username' => env('DB_THESHOPSAVER_USERNAME', 'forge'),
            'password' => env('DB_THESHOPSAVER_PASSWORD', ''),
            'unix_socket' => env('DB_SOCKET', ''),
            'charset' => 'utf8',
            'collation' => 'utf8_general_ci',
            'prefix' => '',
            'strict' => false,
            'engine' => null,
        ],


    ],

    /*
    |--------------------------------------------------------------------------
    | Migration Repository Table
    |--------------------------------------------------------------------------
    |
    | This table keeps track of all the migrations that have already run for
    | your application. Using this information, we can determine which of
    | the migrations on disk haven't actually been run in the database.
    |
    */

    'migrations' => 'migrations',

    /*
    |--------------------------------------------------------------------------
    | Redis Databases
    |--------------------------------------------------------------------------
    |
    | Redis is an open source, fast, and advanced key-value store that also
    | provides a richer set of commands than a typical key-value systems
    | such as APC or Memcached. Laravel makes it easy to dig right in.
    |
    */

    'redis' => [

        'client' => 'predis',

        'default' => [
            'host' => env('REDIS_HOST', '127.0.0.1'),
            'password' => env('REDIS_PASSWORD', null),
            'port' => env('REDIS_PORT', 6379),
            'database' => 0,
        ],

    ],

];
