<?php
    $config['error_handling'] = true;
    $config['platform']       = 'vk';
    $config['base_url']       = 'bb1vs1.ru';
    $config['xml_path']       = '../client/assets/xml/data';

    $config['vk'] = array (
        'app_id'      => '3704573',
        'api_secret'  => 'XTfwoirKlBF0DIWVjlaQ',
        'api_url'     => 'api.vk.com/api.php'
    );

    $config['internal_key']             = 'wfp4eo34';
    $config['request_timeout']          = 30;
    $config['test_mode']                = false; //if set to true then it can work without auth on facebook with user id = test_mode_user_id
    $config['test_mode_user']           = 1;

    $db = array(
        'user'      => 'db',
        'password'  => 'PogodaTech738',
        'host'      => 'localhost',
        'db_name'   => 'break',
        'driver'    => 'mysql',
    );
