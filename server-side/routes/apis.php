<?php
$apis = [
    '/register'      => ['controller' => 'AuthController', 'method' => 'register'],
    '/login'         => ['controller' => 'AuthController', 'method' => 'login'],
    '/logout'        => ['controller' => 'AuthController', 'method' => 'logout'],
    '/users'         => ['controller' => 'AuthController', 'method' => 'getContacts'],

    '/chat/start'    => ['controller' => 'ChatController', 'method' => 'openChat'],
    '/chat/send'     => ['controller' => 'ChatController', 'method' => 'sendMessage'],
    '/chat/history'  => ['controller' => 'ChatController', 'method' => 'getMessages'],
    '/chat/read'     => ['controller' => 'ChatController', 'method' => 'markRead'],

    '/ai/summary'    => ['controller' => 'AiController',   'method' => 'getSummary']
];
?>