<?php

declare(strict_types=1);

/**
 * This file is part of CodeIgniter Shield.
 *
 * (c) CodeIgniter Foundation <admin@codeigniter.com>
 *
 * For the full copyright and license information, please view
 * the LICENSE file that was distributed with this source code.
 */

namespace Config;

use CodeIgniter\Shield\Config\AuthGroups as ShieldAuthGroups;

class AuthGroups extends ShieldAuthGroups
{
    /**
     * --------------------------------------------------------------------
     * Default Group
     * --------------------------------------------------------------------
     * The group that a newly registered user is added to.
     */
    public string $defaultGroup = 'tenant';

    /**
     * --------------------------------------------------------------------
     * Groups
     * --------------------------------------------------------------------
     * An associative array of the available groups in the system, where the keys
     * are the group names and the values are arrays of the group info.
     *
     * Whatever value you assign as the key will be used to refer to the group
     * when using functions such as:
     *      $user->addGroup('superadmin');
     *
     * @var array<string, array<string, string>>
     *
     * @see https://codeigniter4.github.io/shield/quick_start_guide/using_authorization/#change-available-groups for more info
     */
    public array $groups = [
        'superadmin' => [
            'title'       => 'Super Admin',
            'description' => 'Platform owner. Manages every property, hardware, payments and billing.',
        ],
        'property_admin' => [
            'title'       => 'Body Corporate Admin',
            'description' => 'Manages one property (complex/estate): its tenants, lockers and pricing.',
        ],
        'staff' => [
            'title'       => 'On-site Staff',
            'description' => 'Concierge/security at a property. Limited kiosk-assist permissions.',
        ],
        'tenant' => [
            'title'       => 'Tenant',
            'description' => 'A resident of a property. Reserves and collects lockers at their own property.',
        ],
    ];

    /**
     * --------------------------------------------------------------------
     * Permissions
     * --------------------------------------------------------------------
     * The available permissions in the system.
     *
     * If a permission is not listed here it cannot be used.
     */
    public array $permissions = [
        'platform.manage'    => 'Manage the whole platform (properties, hardware, gateways, billing)',
        'property.manage'    => 'Manage one property: tenants, lockers, pricing, payments',
        'property.staff'     => 'Kiosk-assist permissions at one property (limited)',
        'lockers.reserve'    => 'Reserve and collect lockers at their own property',
    ];

    /**
     * --------------------------------------------------------------------
     * Permissions Matrix
     * --------------------------------------------------------------------
     * Maps permissions to groups.
     *
     * This defines group-level permissions.
     */
    public array $matrix = [
        'superadmin'     => ['platform.manage', 'property.manage', 'property.staff', 'lockers.reserve'],
        'property_admin' => ['property.manage', 'property.staff'],
        'staff'          => ['property.staff'],
        'tenant'         => ['lockers.reserve'],
    ];
}
