<?php

return [

    'dsn' => 'https://8c16712259ba407493120c1b2180753f@sentry.diga.pt/2',

    // capture release as git sha
    // 'release' => trim(exec('git --git-dir ' . base_path('.git') . ' log --pretty="%h" -n1 HEAD')),
    'release' => trim(file_get_contents(dirname(__FILE__).'/../VERSION')),

    'breadcrumbs' => [

        // Capture bindings on SQL queries logged in breadcrumbs
        'sql_bindings' => true,

    ],

];
