<?php

declare(strict_types=1);

namespace UserManager\Commands;

use UserManager\Services\UserService;
use UserManager\Exceptions\CliException;

final class ListUsersCommand implements CommandInterface
{
    public function __construct(
        private readonly UserService $service
    ) {}

    public function execute(): string
    {
        $users = $this->service->list();

        if (empty($users)) {
            return " No users found";
        }

        return $this->renderTable($users);
    }

    private function renderTable(array $users): string
    {
        $output = '';
        $width = 4;
        $widthFirstName = 12;
        $widthLastName = 12;
        $widthEmail = 25;

        $separator = '+' . str_repeat('-', $width) .
            '+' . str_repeat('-', $widthFirstName) .
            '+' . str_repeat('-', $widthLastName) .
            '+' . str_repeat('-', $widthEmail) . "+\n";

        $output .= $separator;
        $output .= sprintf("| %-{$width}s | %-{$widthFirstName}s | %-{$widthLastName}s | %-{$widthEmail}s |\n",
            "ID", "First Name", "Last Name", "Email");
        $output .= $separator;

        foreach ($users as $user) {
            $output .= sprintf("| %-{$width}d | %-{$widthFirstName}s | %-{$widthLastName}s | %-{$widthEmail}s |\n",
                $user->getId(),
                $user->getFirstName(),
                $user->getLastName(),
                $user->getEmail()
            );
        }

        $output .= $separator;
        $output .= sprintf(" Total: %d users\n", count($users));

        return $output;
    }
}