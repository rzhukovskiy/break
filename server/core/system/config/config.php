<?php
    $config['error_handling'] = true;
    $config['platform']       = 'facebook';
    $config['xml_path']       = '../client/assets/config/data';

    $config['facebook'] = array (
        'appId'       => '182767508538747',
        'secret'      => '8514dfdd7223da3ec0bd70a0bbbe4c93',
        'namespace'   => 'bubbles-test',
        'cookie'      => true,
        'scope'       => 'email,publish_stream'
    );

    $config['internal_key']             = 'wfp4eo34';
    $config['request_timeout']          = 3;
    $config['test_mode']                = true; //if set to true then it can work without auth on facebook with user id = test_mode_user_id
    $config['test_mode_user']           = 1;

    $db = array(
        'user'      => 'axel_bubble',
        'password'  => 'LwTdew1spvedHSGcScJg',
        'host'      => 'localhost',
        'db_name'   => 'axel_bubbles',
        'driver'    => 'mysql',
    );
