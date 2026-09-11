<?php

declare(strict_types=1);

namespace App\Controllers\Api;

class AuthController extends BaseApiController
{
    /**
     * Email + password login for the RN app and website — verifies
     * credentials without starting a session (this is a stateless API),
     * then mints a personal access token the client stores and sends as
     * `Authorization: Bearer <token>` on every subsequent request.
     */
    public function login()
    {
        $email    = (string) $this->request->getJsonVar('email');
        $password = (string) $this->request->getJsonVar('password');

        if (empty($email) || empty($password)) {
            return $this->failValidationErrors('Email and password are required.');
        }

        $result = auth('session')->check(['email' => $email, 'password' => $password]);
        if (! $result->isOK()) {
            return $this->failUnauthorized('Invalid email or password.');
        }

        $user  = $result->extraInfo();
        $token = $user->generateAccessToken('mobile-' . date('Y-m-d-His'));

        return $this->respond([
            'token' => $token->raw_token,
            'user'  => [
                'id'         => $user->id,
                'name'       => $user->full_name,
                'email'      => $user->email,
                'groups'     => $user->getGroups(),
                'property_id' => $user->property_id,
            ],
        ]);
    }

    /** Revokes the token the client authenticated this request with. */
    public function logout()
    {
        $user  = $this->currentUser();
        $token = $this->request->getHeaderLine('Authorization');
        $raw   = trim(str_ireplace('Bearer', '', $token));

        $user->revokeAccessToken($raw);

        return $this->respondNoContent();
    }

    /** Lets a tenant set a real password after logging in with the temp one an invite email gave them. */
    public function changePassword()
    {
        $newPassword = (string) $this->request->getJsonVar('new_password');
        if (strlen($newPassword) < 8) {
            return $this->failValidationErrors('New password must be at least 8 characters.');
        }

        $user = $this->currentUser();
        $user->createEmailIdentity(['email' => $user->email, 'password' => $newPassword]);

        return $this->respondNoContent();
    }
}
