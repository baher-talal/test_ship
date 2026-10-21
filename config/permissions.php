<?php

/*
 * Permissions available in the system (key => description), grouped by section.
 * Default roles are created once when the migrations run and can be edited afterwards from the "Roles and permissions" screen.
 */
return [
    'groups' => [
        'shipments' => [
            'label' => 'Shipments',
            'items' => [
                'shipments.view' => 'View shipments',
                'shipments.create' => 'Create a shipment',
                'shipments.edit' => 'Edit shipment details',
                'shipments.delete' => 'Delete a shipment',
                'shipments.assign' => 'Assign shipments to couriers',
                'shipments.status' => 'Update shipment status',
                'shipments.final_override' => 'Change the status of a finished shipment (delivered/returned/cancelled)',
            ],
        ],
        'customers' => [
            'label' => 'Customers',
            'items' => [
                'customers.view' => 'View customers',
                'customers.manage' => 'Add and edit customers',
                'customers.delete' => 'Delete customers',
            ],
        ],
        'invoices' => [
            'label' => 'Invoices',
            'items' => [
                'invoices.view' => 'View and print invoices',
                'invoices.create' => 'Issue an invoice',
                'invoices.edit' => 'Edit an invoice after issuing',
                'invoices.pay' => 'Record payment and cancel invoice',
                'invoices.delete' => 'Delete an invoice',
            ],
        ],
        'couriers' => [
            'label' => 'Couriers',
            'items' => [
                'couriers.view' => 'View couriers',
                'couriers.manage' => 'Add, edit and delete couriers',
            ],
        ],
        'reports' => [
            'label' => 'Reports',
            'items' => [
                'reports.view' => 'View reports',
                'reports.export' => 'Export reports',
            ],
        ],
        'admin' => [
            'label' => 'Administration',
            'items' => [
                'branches.manage' => 'Manage branches',
                'users.manage' => 'Manage users',
                'roles.manage' => 'Manage roles and permissions',
            ],
        ],
        'scope' => [
            'label' => 'Data scope',
            'items' => [
                'scope.all_branches' => 'View data of all branches (for senior management)',
                'scope.own_shipments' => 'Restrict visibility to his assigned shipments only (for couriers)',
            ],
        ],
    ],

    // Default roles: slug => [name, hierarchy level (1 = highest), parent role, description, permissions]
    // The admin role automatically has every permission.
    'default_roles' => [
        'admin' => ['General manager', 1, null, 'Full permissions on all branches and the system', []],
        'branch_manager' => ['Branch manager', 2, 'admin', 'Full management of branch operations', [
            'shipments.view', 'shipments.create', 'shipments.edit', 'shipments.delete', 'shipments.assign', 'shipments.status', 'shipments.final_override',
            'customers.view', 'customers.manage', 'customers.delete',
            'invoices.view', 'invoices.create', 'invoices.edit', 'invoices.pay', 'invoices.delete',
            'couriers.view', 'couriers.manage', 'reports.view', 'reports.export',
        ]],
        'supervisor' => ['Supervisor', 3, 'branch_manager', 'Follows operations and assigns shipments', [
            'shipments.view', 'shipments.create', 'shipments.edit', 'shipments.assign', 'shipments.status',
            'customers.view', 'customers.manage', 'invoices.view', 'couriers.view', 'reports.view',
        ]],
        'accountant' => ['Accountant', 3, 'branch_manager', 'Invoices and financial reports', [
            'shipments.view', 'customers.view', 'invoices.view', 'invoices.create', 'invoices.pay', 'reports.view', 'reports.export',
        ]],
        'employee' => ['Employee', 4, 'supervisor', 'Entering shipments and customers', [
            'shipments.view', 'shipments.create', 'shipments.edit', 'shipments.status',
            'customers.view', 'customers.manage', 'invoices.view', 'invoices.create', 'invoices.pay',
        ]],
        'courier' => ['Courier', 5, 'branch_manager', 'Delivers assigned shipments and updates their status', [
            'shipments.view', 'shipments.status', 'scope.own_shipments',
        ]],
    ],
];
