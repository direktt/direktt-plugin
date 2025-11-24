<?php return array(
    'root' => array(
        'name' => 'direktt/direktt-plugin',
        'pretty_version' => 'dev-master',
        'version' => 'dev-master',
        'reference' => '5c19ad9c03cd0699e041e827e66e8f2c4e25063f',
        'type' => 'wordpress-plugin',
        'install_path' => __DIR__ . '/../../',
        'aliases' => array(),
        'dev' => false,
    ),
    'versions' => array(
        'composer/installers' => array(
            'pretty_version' => 'v1.12.0',
            'version' => '1.12.0.0',
            'reference' => 'd20a64ed3c94748397ff5973488761b22f6d3f19',
            'type' => 'composer-plugin',
            'install_path' => __DIR__ . '/./installers',
            'aliases' => array(),
            'dev_requirement' => false,
        ),
        'direktt/direktt-plugin' => array(
            'pretty_version' => 'dev-master',
            'version' => 'dev-master',
            'reference' => '5c19ad9c03cd0699e041e827e66e8f2c4e25063f',
            'type' => 'wordpress-plugin',
            'install_path' => __DIR__ . '/../../',
            'aliases' => array(),
            'dev_requirement' => false,
        ),
        'firebase/php-jwt' => array(
            'pretty_version' => 'v6.11.1',
            'version' => '6.11.1.0',
            'reference' => 'd1e91ecf8c598d073d0995afa8cd5c75c6e19e66',
            'type' => 'library',
            'install_path' => __DIR__ . '/../firebase/php-jwt',
            'aliases' => array(),
            'dev_requirement' => false,
        ),
        'html2text/html2text' => array(
            'pretty_version' => '4.3.2',
            'version' => '4.3.2.0',
            'reference' => '3b443cbe302b52eb5806a21a9dbd79524203970a',
            'type' => 'library',
            'install_path' => __DIR__ . '/../html2text/html2text',
            'aliases' => array(),
            'dev_requirement' => false,
        ),
        'roundcube/plugin-installer' => array(
            'dev_requirement' => false,
            'replaced' => array(
                0 => '*',
            ),
        ),
        'shama/baton' => array(
            'dev_requirement' => false,
            'replaced' => array(
                0 => '*',
            ),
        ),
    ),
);
