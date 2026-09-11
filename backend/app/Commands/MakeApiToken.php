<?php

declare(strict_types=1);

namespace App\Commands;

use CodeIgniter\CLI\BaseCommand;
use CodeIgniter\CLI\CLI;
use CodeIgniter\Shield\Models\UserModel;

/**
 * Dev helper: mint a personal access token for a user by email, for testing
 * the RN app / website against the API without building a full login UI yet.
 *
 * Usage: php spark make:apitoken tenant@dropa.app
 */
class MakeApiToken extends BaseCommand
{
    protected $group       = 'Dropa';
    protected $name        = 'make:apitoken';
    protected $description = 'Mint an API access token for a user by email.';

    public function run(array $params)
    {
        $email = $params[0] ?? CLI::prompt('Email');
        $db    = db_connect();
        $identity = $db->table('auth_identities')->where('secret', $email)->get()->getRowArray();

        if ($identity === null) {
            CLI::error("No user found with email {$email}");

            return;
        }

        $user  = (new UserModel())->findById($identity['user_id']);
        $token = $user->generateAccessToken('cli-token');

        CLI::write('Token: ' . $token->raw_token, 'green');
    }
}
