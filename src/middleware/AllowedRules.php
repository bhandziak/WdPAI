<?php

#[\Attribute(\Attribute::TARGET_METHOD)]
class AllowedRules
{
    public array $roles;

    public function __construct(array $roles)
    {
        $this->roles = $roles;
    }
}
