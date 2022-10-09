<?php 
namespace AdinanCenci\DescriptivePlaylist;

use AdinanCenci\DescriptivePlaylist\Utils\StdClassWrapper;

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
