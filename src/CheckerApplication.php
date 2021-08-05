<?php

declare(strict_types = 1);

namespace PhpDocChecker;

use Symfony\Component\Console\Application;
use Symfony\Component\Console\Input\InputDefinition;
use Symfony\Component\Console\Input\InputInterface;

/**
 * Extension of Symfony's console application class to allow us to have a single, default command.
 *
 * @package PHPDoc Checker
 *
 * @author Dmitry Khomutov <poisoncorpsee@gmail.com>
 * @author Dan Cryer <dan@block8.co.uk>
 */
class CheckerApplication extends Application
{
    /**
     * Override the default command name logic and return our check command.
     *
     * @param InputInterface $input
     * @return string
     */
    protected function getCommandName(InputInterface $input): string
    {
        return 'check';
    }

    /**
     * Overridden so that the application doesn't expect the command
     * name to be the first argument.
     */
    public function getDefinition(): InputDefinition
    {
        $inputDefinition = parent::getDefinition();
        // clear out the normal first argument, which is the command name
        $inputDefinition->setArguments();

        return $inputDefinition;
    }
}
