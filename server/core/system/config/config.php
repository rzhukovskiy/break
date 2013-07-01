<?php
    $config['error_handling'] = true;
    $config['platform']       = 'vk';
    $config['xml_path']       = '../client/assets/config/data';

    $config['vk'] = array (
        'app_id'      => '3704573',
        'api_secret'  => 'XTfwoirKlBF0DIWVjlaQ',
        'api_url'     => 'api.vk.com/api.php'
    );

    $config['internal_key']             = 'wfp4eo34';
    $config['request_timeout']          = 3;
    $config['test_mode']                = true; //if set to true then it can work without auth on facebook with user id = test_mode_user_id
    $config['test_mode_user']           = 1;

    $db = array(
        'user'      => 'root',
        'password'  => 'sqladm',
        'host'      => 'localhost',
        'db_name'   => 'break',
        'driver'    => 'mysql',
    );

    $config['redis_config'] = array(
        'namespace'         => 'Breakdance_',
        'serializerAdapter' => 'json',
        'servers'           => array(array(
            'port'          => '6379',
            'host'          => '127.0.0.1',
        ))
    );
