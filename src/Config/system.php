<?php

return [
    [
        'key'    => 'sales.payment_methods.parampos',
        'info'   => 'parampos::app.parampos.info',
        'name'   => 'parampos::app.parampos.name',
        'sort'   => 1,
        'fields' => [
            [
                'name'          => 'active',
                'title'         => 'parampos::app.parampos.system.status',
                'type'          => 'boolean',
                'channel_based' => true,
                'locale_based'  => false,
            ], [
                'name'          => 'title',
                'title'         => 'parampos::app.parampos.system.title',
                'type'          => 'text',
                'depends'       => 'active:1',
                'validation'    => 'required_if:active,1',
                'channel_based' => false,
                'locale_based'  => true,
            ], [
                'name'          => 'description',
                'title'         => 'parampos::app.parampos.system.description',
                'type'          => 'textarea',
                'depends'       => 'active:1',
                'channel_based' => false,
                'locale_based'  => true,
            ], [
                'name'          => 'image',
                'title'         => 'parampos::app.parampos.system.image',
                'type'          => 'file',
                'info'          => 'admin::app.configuration.index.sales.payment-methods.logo-information',
                'depends'       => 'active:1',
                'channel_based' => false,
                'locale_based'  => true,
                'validation'    => 'mimes:bmp,jpeg,jpg,png,webp',
            ], [
                'name'          => 'client_code',
                'title'         => 'ParamPOS',
                'info'          => 'ParamPOS_info',
                'type'          => 'text',
                'depends'       => 'active:1',
                'channel_based' => true,
                'locale_based'  => false,
            ], [
                'name'          => 'client_username',
                'title'         => 'ParamPOS',
                'info'          => 'ParamPOS_info',
                'type'          => 'text',
                'depends'       => 'active:1',
                'channel_based' => true,
                'locale_based'  => false,
            ], [
                'name'          => 'client_password',
                'title'         => 'ParamPOS',
                'info'          => 'ParamPOS_info',
                'type'          => 'password',
                'depends'       => 'active:1',
                'channel_based' => true,
                'locale_based'  => false,
            ], [
                'name'          => 'guid',
                'title'         => 'ParamPOS',
                'info'          => 'ParamPOS_info',
                'type'          => 'text',
                'depends'       => 'active:1',
                'channel_based' => true,
                'locale_based'  => false,
            ], [
                'name'          => 'sandbox',
                'title'         => 'Sandbox',
                'type'          => 'boolean',
                'depends'       => 'active:1',
                'channel_based' => true,
                'locale_based'  => false,
            ], [
                'name'    => 'sort',
                'title'   => 'admin::app.configuration.index.sales.payment-methods.sort-order',
                'type'    => 'select',
                'depends' => 'active:1',
                'options' => [
                    [
                        'title' => '1',
                        'value' => 1,
                    ], [
                        'title' => '2',
                        'value' => 2,
                    ], [
                        'title' => '3',
                        'value' => 3,
                    ], [
                        'title' => '4',
                        'value' => 4,
                    ], [
                        'title' => '5',
                        'value' => 5,
                    ], [
                        'title' => '6',
                        'value' => 6,
                    ], [
                        'title' => '7',
                        'value' => 7,
                    ],
                ],
                'channel_based' => true,
                'locale_based'  => false,
            ],
        ],
    ],
];
