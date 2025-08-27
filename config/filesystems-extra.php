<?php
// config/filesystems-extra.php
// Copy the 'private' disk array into your config/filesystems.php 'disks' section
return [
    'private' => [
        'driver' => 'local',
        'root'   => storage_path('app/private'),
        'throw'  => false,
    ],
];
