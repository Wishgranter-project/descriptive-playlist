<?php

namespace WishgranterProject\DescriptivePlaylist;

use WishgranterProject\DescriptivePlaylist\Utils\StdClassWrapper;

class Header extends StdClassWrapper
{
    protected $schema = [
        'title' => [
            'is:string',
            'maxLength:255'
        ],
        'description' => [
            'is:string',
            'maxLength:255'
        ]
    ];
}
