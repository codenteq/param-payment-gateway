<?php

return [
    [
        'key'    => 'sales.payment_methods.parampos',
        'info'   => 'parampos::app.parampos.info',
        'name'   => 'parampos::app.parampos.name',
        'sort'   => 1,
        'fields' => [
            [
                'name'          => 'title',
                'title'         => 'parampos::app.parampos.system.title',
                'type'          => 'text',
                'validation'    => 'required',
                'channel_based' => false,
                'locale_based'  => true,
            ], [
                'name'          => 'description',
                'title'         => 'parampos::app.parampos.system.description',
                'type'          => 'textarea',
                'channel_based' => false,
                'locale_based'  => true,
            ], [
                'name'          => 'image',
                'title'         => 'parampos::app.parampos.system.image',
                'info'          => 'admin::app.configuration.index.sales.payment-methods.logo-information',
                'type'          => 'file',
                'channel_based' => false,
                'locale_based'  => true,
            ], [
                'name'          => 'active',
                'title'         => 'parampos::app.parampos.system.status',
                'type'          => 'boolean',
                'validation'    => 'required',
                'channel_based' => false,
                'locale_based'  => true,
            ]
        ]
    ]
];
