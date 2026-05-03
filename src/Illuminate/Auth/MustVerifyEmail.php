<?php

namespace Illuminate\Auth;

use Illuminate\Auth\Notifications\VerifyEmail;
use Illuminate\Support\Facades\Notification;

trait MustVerifyEmail
{
    /**
     * The name of the "verified at" column.
     *
     * @var string|null
     */
    const VERIFIED_AT = 'verified_at';

    /**
     * Determine if the user has verified their email address.
     *
     * @return bool
     */
    public function hasVerifiedEmail()
    {
        if (is_null($this->getVerifiedAtName())) {
            return true;
        }

        return ! is_null($this->{$this->getVerifiedAtName()});
    }

    /**
     * Mark the user's email as verified.
     *
     * @return bool
     */
    public function markEmailAsVerified()
    {
        if (is_null($this->getVerifiedAtName())) {
            return true;
        }

        return $this->forceFill([
            $this->getVerifiedAtName() => $this->freshTimestamp(),
        ])->save();
    }

    /**
     * Mark the user's email as unverified.
     *
     * @return bool
     */
    public function markEmailAsUnverified()
    {
        if (is_null($this->getVerifiedAtName())) {
            return true;
        }

        return $this->forceFill([
            $this->getVerifiedAtName() => null,
        ])->save();
    }

    /**
     * Send the email verification notification.
     *
     * @return void
     */
    public function sendEmailVerificationNotification()
    {
        if (is_null($this->getVerifiedAtName())) {
            return;
        }

        Notification::send($this, new VerifyEmail);
    }

    /**
     * Get the email address that should be used for verification.
     *
     * @return string
     */
    public function getEmailForVerification()
    {
        return $this->{$this->getEmailName()};
    }

    /**
     * Get the name of the "verified at" column.
     *
     * @return string|null
     */
    public function getVerifiedAtName()
    {
        return static::VERIFIED_AT;
    }
}
