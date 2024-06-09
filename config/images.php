<?php

return [

    'profile' => [
        'avatar' => [
            'big'   => [
                'width'  => 250 ,
                'height' => 250
            ],
            'small' => [
                'width' => 100 ,
                'height' => 100
            ],
        ]
    ],

    'avatar' => [
        'mimes' => 'mimes:png,jpg,jpeg,svg',
        'size'  => 'max:5120', // kB
    ],

];
