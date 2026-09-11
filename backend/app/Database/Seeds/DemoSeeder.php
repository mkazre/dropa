<?php

declare(strict_types=1);

namespace App\Database\Seeds;

use App\Models\LockerModel;
use App\Models\LockerRackModel;
use App\Models\PropertyModel;
use App\Models\UnitModel;
use CodeIgniter\Database\Seeder;
use CodeIgniter\Shield\Entities\User;
use CodeIgniter\Shield\Models\UserModel;

class DemoSeeder extends Seeder
{
    /** Shield's UserModel only allows its own fields; our extra columns go straight through the query builder. */
    private function linkToProperty(int $userId, array $fields): void
    {
        $this->db->table('users')->where('id', $userId)->update($fields);
    }

    private function findByEmail(UserModel $users, string $email): ?User
    {
        $identity = $this->db->table('auth_identities')->where('secret', $email)->get()->getRowArray();

        return $identity ? $users->findById($identity['user_id']) : null;
    }

    public function run()
    {
        // -- Super admin -----------------------------------------------
        $users = new UserModel();
        if ($this->findByEmail($users, 'super@dropa.app') === null) {
            $super = new User(['username' => 'super@dropa.app']);
            $users->save($super);
            $super = $users->findById($users->getInsertID());
            $super->createEmailIdentity(['email' => 'super@dropa.app', 'password' => 'DropaSuper123!']);
            $super->addGroup('superadmin');
        }

        // -- Demo property ------------------------------------------------
        $properties = model(PropertyModel::class);
        $propertyId = $properties->where('name', 'Rosebank Heights Estate')->first()['id']
            ?? $properties->insert([
                'name' => 'Rosebank Heights Estate', 'type' => 'complex', 'address' => '12 Baker St, Rosebank',
                'reservation_hold_hours' => 48, 'status' => 'active',
            ], true);

        // -- Body Corporate admin for that property -----------------------
        if ($this->findByEmail($users, 'bodycorp@dropa.app') === null) {
            $admin = new User(['username' => 'bodycorp@dropa.app']);
            $users->save($admin);
            $admin = $users->findById($users->getInsertID());
            $admin->createEmailIdentity(['email' => 'bodycorp@dropa.app', 'password' => 'DropaAdmin123!']);
            $admin->addGroup('property_admin');
            $this->linkToProperty($admin->id, ['property_id' => $propertyId, 'full_name' => 'Rosebank Heights Body Corporate']);
        }

        // -- A rack of mock lockers ----------------------------------------
        $racks = model(LockerRackModel::class);
        $rackId = $racks->where('property_id', $propertyId)->first()['id']
            ?? $racks->insert([
                'property_id' => $propertyId, 'name' => 'Rack A', 'location_desc' => 'Lower level, near entrance',
                'hardware_provider' => 'mock', 'status' => 'online',
            ], true);

        $lockers = model(LockerModel::class);
        if ($lockers->where('rack_id', $rackId)->countAllResults() === 0) {
            $sizes = ['S', 'S', 'S', 'S', 'M', 'M', 'M', 'M', 'L', 'L', 'L', 'XL', 'XL'];
            foreach ($sizes as $i => $size) {
                $lockers->insert(['rack_id' => $rackId, 'label' => (string) ($i + 1), 'size' => $size, 'status' => 'available']);
            }
        }

        // -- A demo unit + tenant --------------------------------------
        $units  = model(UnitModel::class);
        $unitId = $units->where('property_id', $propertyId)->where('unit_number', '14B')->first()['id']
            ?? $units->insert(['property_id' => $propertyId, 'unit_number' => '14B'], true);

        if ($this->findByEmail($users, 'tenant@dropa.app') === null) {
            $tenant = new User(['username' => 'tenant@dropa.app']);
            $users->save($tenant);
            $tenant = $users->findById($users->getInsertID());
            $tenant->createEmailIdentity(['email' => 'tenant@dropa.app', 'password' => 'DropaTenant123!']);
            $tenant->addGroup('tenant');
            $this->linkToProperty($tenant->id, [
                'property_id' => $propertyId, 'unit_id' => $unitId,
                'full_name' => 'Lindiwe Mokoena', 'phone' => '082 555 0147',
            ]);
        }
    }
}
