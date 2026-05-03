<?php

namespace Illuminate\Auth;

trait Authenticatable
{
    /**
     * The column name of the email field. Cannot be null — to opt out of
     * email-based auth, write a custom user provider.
     *
     * @var string
     */
    const EMAIL = 'email';

    /**
     * The column name of the password field used during authentication.
     * Cannot be null — to opt out of password auth, use a different guard.
     *
     * @var string
     */
    const AUTH_PASSWORD = 'password';

    /**
     * The name of the "remember me" token column.
     *
     * @var string|null
     */
    const REMEMBER_ME = 'remember_token';

    /**
     * Get the name of the unique identifier for the user.
     *
     * @return string
     */
    public function getAuthIdentifierName()
    {
        return $this->getKeyName();
    }

    /**
     * Get the unique identifier for the user.
     *
     * @return mixed
     */
    public function getAuthIdentifier()
    {
        return $this->{$this->getAuthIdentifierName()};
    }

    /**
     * Get the unique broadcast identifier for the user.
     *
     * @return mixed
     */
    public function getAuthIdentifierForBroadcasting()
    {
        return $this->getAuthIdentifier();
    }

    /**
     * Get the name of the password attribute for the user.
     *
     * @return string
     */
    public function getAuthPasswordName()
    {
        return static::AUTH_PASSWORD;
    }

    /**
     * Get the password for the user.
     *
     * @return string
     */
    public function getAuthPassword()
    {
        return $this->{$this->getAuthPasswordName()};
    }

    /**
     * Get the name of the email attribute for the user.
     *
     * @return string
     */
    public function getEmailName()
    {
        return static::EMAIL;
    }

    /**
     * Get the token value for the "remember me" session.
     *
     * @return string|null
     */
    public function getRememberToken()
    {
        if (! empty($this->getRememberTokenName())) {
            return (string) $this->{$this->getRememberTokenName()};
        }
    }

    /**
     * Set the token value for the "remember me" session.
     *
     * @param  string  $value
     * @return void
     */
    public function setRememberToken($value)
    {
        if (! empty($this->getRememberTokenName())) {
            $this->{$this->getRememberTokenName()} = $value;
        }
    }

    /**
     * Get the column name for the "remember me" token.
     *
     * @return string|null
     */
    public function getRememberTokenName()
    {
        return static::REMEMBER_ME;
    }
}
