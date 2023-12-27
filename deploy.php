<?php

namespace Deployer;

require __DIR__ . '/vendor/deployer/deployer/recipe/laravel.php';
require __DIR__ . '/vendor/deployer/deployer/recipe/common.php';
require __DIR__ . '/vendor/deployer/recipes/recipe/rsync.php';

try {
    set('application', 'LMSX');
    set('ssh_multiplexing', true);
    set('repository', 'git@github.com:IMTALabs/lms.git');

    set('rsync_src', function () {
        return __DIR__;
    });

    add('rsync', [
        'exclude' => [
            '.git',
            '/.env',
            '/storage/',
            '/vendor/',
            '/node_modules/',
            '.github',
            'deploy.php',
        ],
    ]);

    task('deploy:secrets', function () {
        file_put_contents(__DIR__ . '/.env', getenv('DOT_ENV'));
        upload('.env', get('deploy_path') . '/shared');
    });

// host('167.172.68.72')
//     // ->setHostname('imta.io.vn')
//     // ->stage('production')
//     ->set('labels', ['stage' => 'production'])
//     ->setRemoteUser('root')
//     ->set('deploy_path', '/var/www/my-app');
    host('imta.io.vn')
        ->set('remote_user', 'root')
        ->set('hostname', '167.172.68.72')
        ->set('deploy_path', '~/myapp')
        ->set('stage', 'production')
        ->set('server_type', 'production')
        ->set('labels', [
            'type' => 'web',
            'env'  => 'prod',
        ]);

    host('staging.imta.io.vn')
        // ->setHostname('167.172.68.72')
        // ->stage('staging')
        ->set('labels', ['stage' => 'staging'])
        ->setRemoteUser('root')
        ->set('deploy_path', '/var/www/my-app-staging');

    after('deploy:failed', 'deploy:unlock');

    desc('Deploy the application');

// task('deploy', [
//     'deploy:info',
//     'deploy:unlock',
//     'deploy:prepare',
//     'deploy:release',
//     'rsync',
//     'deploy:secrets',
//     'deploy:shared',
//     'deploy:vendors',
//     'deploy:writable',
//     'artisan:storage:link',
//     'artisan:view:cache',
//     'artisan:config:cache',
//     'artisan:migrate',
//     'artisan:queue:restart',
//     'deploy:symlink',
//     'deploy:unlock',
//     'deploy:cleanup',
// ]);
    task('deploy:unlock');
    task('deploy');
} catch (\Throwable $th) {
    echo $th->getMessage();
}
