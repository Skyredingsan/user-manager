<?php

namespace UserManager\Commands;

interface CommandInterface
{
    public function execute(): string;
}