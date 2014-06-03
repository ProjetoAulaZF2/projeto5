<?php
return array(
    // Perfil de administrador
    array(
        'role' => 1,
        'resource' => 'home',
        'privileges' => array()
    ),
    array(
        'role' => 1,
        'resource' => 'autenticar',
        'privileges' => array()
    ),
    array(
        'role' => 1,
        'resource' => 'usuario',
        'privileges' => array(
            'index',
            'add',
            'edit'
        )
    ),
    array(
        'role' => 1,
        'resource' => 'celular',
        'privileges' => array()
    ),
    array(
        'role' => 1,
        'resource' => 'sair',
        'privileges' => array()
    ),
    
    // Perfil de oreia
    array(
        'role' => 2,
        'resource' => 'home',
        'privileges' => array()
    ),
    array(
        'role' => 2,
        'resource' => 'autenticar',
        'privileges' => array()
    ),
    array(
        'role' => 2,
        'resource' => 'sair',
        'privileges' => array()
    ),
    array(
        'role' => 2,
        'resource' => 'celular',
        'privileges' => array(
            'index'
        )
    ),
    array(
        'role' => 2,
        'resource' => 'usuario',
        'privileges' => array(
            'index'
        )
    )
);