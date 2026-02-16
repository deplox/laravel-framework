<?php

namespace Illuminate\Auth;

class Recaller
{
    /**
     * The "recaller" / "remember me" cookie string.
     *
     * @var string
     */
    protected readonly string $recaller;

    /**
     * The parsed segments of the recaller string.
     *
     * @var array
     */
    protected readonly array $segments;

    /**
     * Create a new recaller instance.
     *
     * @param  string  $recaller
     */
    public function __construct($recaller)
    {
        $this->recaller = @unserialize($recaller, ['allowed_classes' => false]) ?: $recaller;
        $this->segments = explode('|', $this->recaller);
    }

    /**
     * Get the user ID from the recaller.
     *
     * @return string
     */
    public function id()
    {
        return $this->segments[0];
    }

    /**
     * Get the "remember token" token from the recaller.
     *
     * @return string
     */
    public function token()
    {
        return $this->segments[1];
    }

    /**
     * Get the password from the recaller.
     *
     * @return string
     */
    public function hash()
    {
        return $this->segments[2];
    }

    /**
     * Determine if the recaller is valid.
     *
     * @return bool
     */
    public function valid()
    {
        return $this->properString() && $this->hasAllSegments();
    }

    /**
     * Determine if the recaller is an invalid string.
     *
     * @return bool
     */
    protected function properString()
    {
        return is_string($this->recaller) && str_contains($this->recaller, '|');
    }

    /**
     * Determine if the recaller has all segments.
     *
     * @return bool
     */
    protected function hasAllSegments()
    {
        return count($this->segments) >= 3 && trim($this->segments[0]) !== '' && trim($this->segments[1]) !== '';
    }

    /**
     * Get the recaller's segments.
     *
     * @return array
     */
    public function segments()
    {
        return $this->segments;
    }
}
