<?php

return [
    'contact' => [
        'email' => 'hello@netocar.ma',
        'phone_display' => '+212 6 00 00 00 00',
        'phone_e164' => '+212600000000',
        'whatsapp_url' => 'https://wa.me/212600000000?text=Bonjour%20NetoCar%2C%20je%20veux%20demander%20une%20d%C3%A9mo%20pour%20mon%20agence.',
    ],

    'demo' => [
        'enabled' => env('NETOCAR_DEMO_MODE', false),
        'manager_email' => env('NETOCAR_DEMO_MANAGER_EMAIL', 'demo.manager@netocar.test'),
        'staff_email' => env('NETOCAR_DEMO_STAFF_EMAIL', 'demo.staff@netocar.test'),
        'password' => env('NETOCAR_DEMO_PASSWORD', 'Demo@2026!'),
        'agency_name' => env('NETOCAR_DEMO_AGENCY_NAME', 'NetoCar Demo Center'),
    ],

    'plans' => [
        'basic' => [
            'label' => 'Basique',
            'price_yearly_mad' => 300,
            'price_monthly_mad' => 25,
            'tagline' => 'Pour un petit point de lavage qui veut mieux s’organiser.',
            'limits' => [
                'clients' => 150,
                'employees' => 4,
                'services' => 8,
                'reservations_per_month' => 200,
                'tickets_per_month' => 400,
            ],
            'features' => [
                'Gestion des clients, employés, services, réservations et tickets',
                'Exports PDF et dépôt des reçus de paiement',
                'Support par e-mail',
            ],
        ],
        'standard' => [
            'label' => 'Standard',
            'price_yearly_mad' => 500,
            'price_monthly_mad' => 42,
            'tagline' => 'Pour les équipes en croissance avec un volume régulier.',
            'limits' => [
                'clients' => 750,
                'employees' => 12,
                'services' => 25,
                'reservations_per_month' => 1200,
                'tickets_per_month' => 2500,
            ],
            'features' => [
                'Tout le plan Basique avec des limites plus élevées',
                'Aperçus des revenus et reporting mensuel',
                'Support prioritaire pour la mise en place et la facturation',
            ],
        ],
        'premium' => [
            'label' => 'Premium',
            'price_yearly_mad' => 899,
            'price_monthly_mad' => 75,
            'tagline' => 'Pour les centres à fort volume et les équipes de detailing premium.',
            'limits' => [
                'clients' => null,
                'employees' => 40,
                'services' => null,
                'reservations_per_month' => 6000,
                'tickets_per_month' => 12000,
            ],
            'features' => [
                'Clients et services illimités',
                'Plus grande capacité mensuelle de réservations et tickets',
                'Support premium et aide à la migration',
            ],
        ],
    ],
];
