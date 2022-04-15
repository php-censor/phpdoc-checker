<?php

declare(strict_types = 1);

namespace PhpDocChecker;

use DirectoryIterator;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Input\InputOption;
use Symfony\Component\Console\Output\OutputInterface;

/**
 * Console command to check a directory of PHP files for Docblocks.
 *
 * @package PHPDoc Checker
 *
 * @author Dmitry Khomutov <poisoncorpsee@gmail.com>
 * @author Dan Cryer <dan@block8.co.uk>
 */
class CheckerCommand extends Command
{
    protected string $basePath = './';

    protected bool $verbose = true;

    protected array $errors = [];

    protected array $warnings = [];

    protected array $exclude = [];

    protected OutputInterface $output;

    protected int $passed = 0;

    protected CheckerFileProcessor $checkerFileProcessor;

    /**
     * Configure the console command, add options, etc.
     */
    protected function configure(): void
    {
        $this
            ->setName('check')
            ->setDescription('Check PHP files within a directory for appropriate use of Docblocks.')
            ->addOption('exclude', 'x', InputOption::VALUE_REQUIRED, 'Files and directories to exclude.', null)
            ->addOption('directory', 'd', InputOption::VALUE_REQUIRED, 'Directory to scan.', './')
            ->addOption('files', 'f', InputOption::VALUE_REQUIRED, 'Files to scan.', null)
            ->addOption('skip-classes', null, InputOption::VALUE_NONE, 'Don\'t check classes for docblocks.')
            ->addOption('skip-methods', null, InputOption::VALUE_NONE, 'Don\'t check methods for docblocks.')
            ->addOption('skip-signatures', null, InputOption::VALUE_NONE, 'Don\'t check docblocks against method signatures.')
            ->addOption('json', 'j', InputOption::VALUE_NONE, 'Output JSON instead of a log.')
            ->addOption('files-per-line', 'l', InputOption::VALUE_REQUIRED, 'Number of files per line in progress', 50)
            ->addOption('fail-on-warnings', 'w', InputOption::VALUE_NONE, 'Consider the check failed if any warnings are produced.')
            ->addOption('info-only', 'i', InputOption::VALUE_NONE, 'Information-only mode, just show summary.');
    }

    /**
     * Execute the actual docblock checker.
     *
     * @param InputInterface $input
     * @param OutputInterface $output
     *
     * @return int
     */
    protected function execute(InputInterface $input, OutputInterface $output): int
    {
        $exclude              = $input->getOption('exclude');
        $json                 = $input->getOption('json');
        $this->basePath       = $input->getOption('directory');
        $files                = $input->getOption('files');
        $this->verbose        = !$json;
        $this->output         = $output;
        $failOnWarnings       = $input->getOption('fail-on-warnings');
        $startTime            = \microtime(true);

        $skipClasses    = $input->getOption('skip-classes');
        $skipMethods    = $input->getOption('skip-methods');
        $skipSignatures = $input->getOption('skip-signatures');

        $this->checkerFileProcessor = new CheckerFileProcessor(
            $this->basePath,
            $skipClasses,
            $skipMethods,
            $skipSignatures
        );

        // Set up excludes:
        if (!\is_null($exclude)) {
            $this->exclude = \array_map('trim', \explode(',', $exclude));
        }
        
        // Set up files:
        if (!\is_null($files)) {
            $this->files = \array_map('trim', \explode(',', $files));
        }

        // Check base path ends with a slash:
        if (\substr($this->basePath, -1) != '/') {
            $this->basePath .= '/';
        }

        // Get files to check:
        $files = [];
        if (count($this->files) > 0) {
            $this->processFiles('', $this->files, $files);
        } else {
            $this->processDirectory('', $files);
        }

        // Check files:
        $filesPerLine    = (int)$input->getOption('files-per-line');
        $totalFiles      = \count($files);
        $files           = \array_chunk($files, $filesPerLine);
        $processed       = 0;
        $fileCountLength = \strlen((string)$totalFiles);

        if ($this->verbose) {
            $output->writeln('<fg=blue>PHPDoc Checker</>');
            $output->writeln('');
        }

        while (\count($files)) {
            $chunk      = \array_shift($files);
            $chunkFiles = \count($chunk);

            while (\count($chunk)) {
                $processed++;
                $file = \array_shift($chunk);

                list($errors, $warnings) = $this->processFile($file);

                if ($this->verbose) {
                    if ($errors) {
                        $this->output->write('<fg=red>F</>');
                    } elseif ($warnings) {
                        $this->output->write('<fg=yellow>W</>');
                    } else {
                        $this->output->write('<info>.</info>');
                    }
                }
            }

            if ($this->verbose) {
                $this->output->write(\str_pad('', $filesPerLine - $chunkFiles));
                $this->output->writeln('  ' . \str_pad((string)$processed, $fileCountLength, ' ', STR_PAD_LEFT) . '/' . $totalFiles . ' (' . \floor((100/$totalFiles) * $processed) . '%)');
            }
        }

        if ($this->verbose) {
            $time = \round(\microtime(true) - $startTime, 2);
            $this->output->writeln('');
            $this->output->writeln('');
            $this->output->writeln('Checked ' . \number_format($totalFiles) . ' files in ' . $time . ' seconds.');
            $this->output->write('<info>' . \number_format($this->passed) . ' Passed</info>');
            $this->output->write(' / <fg=red>' . \number_format(\count($this->errors)) . ' Errors</>');
            $this->output->write(' / <fg=yellow>' . \number_format(\count($this->warnings)) . ' Warnings</>');

            $this->output->writeln('');

            if (\count($this->errors) && !$input->getOption('info-only')) {
                $this->output->writeln('');
                $this->output->writeln('');

                foreach ($this->errors as $error) {
                    $this->output->write('<fg=red>ERROR   </> ' . $error['file'] . ':' . $error['line'] . ' - ');

                    if ($error['type'] == 'class') {
                        $this->output->write('Class <info>' . $error['class'] . '</info> is missing a docblock.');
                    }

                    if ($error['type'] == 'method') {
                        $this->output->write('Method <info>' . $error['class'] . '::' . $error['method'] . '</info> is missing a docblock.');
                    }

                    $this->output->writeln('');
                }
            }

            if (\count($this->warnings) && !$input->getOption('info-only')) {
                foreach ($this->warnings as $error) {
                    $this->output->write('<fg=yellow>WARNING </> ');

                    if ($error['type'] == 'param-missing') {
                        $this->output->write('<info>' . $error['class'] . '::' . $error['method'] . '</info> - @param <fg=blue>'.$error['param'] . '</> missing.');
                    }

                    if ($error['type'] == 'param-mismatch') {
                        $this->output->write('<info>' . $error['class'] . '::' . $error['method'] . '</info> - @param <fg=blue>'.$error['param'] . '</> ('.$error['doc-type'].') does not match method signature ('.$error['param-type'].').');
                    }

                    if ($error['type'] == 'return-missing') {
                        $this->output->write('<info>' . $error['class'] . '::' . $error['method'] . '</info> - @return missing.');
                    }

                    if ($error['type'] == 'return-mismatch') {
                        $this->output->write('<info>' . $error['class'] . '::' . $error['method'] . '</info> - @return <fg=blue>'.$error['doc-type'] . '</> does not match method signature ('.$error['return-type'].').');
                    }

                    $this->output->writeln('');
                }
            }

            $this->output->writeln('');
        }

        // Output JSON if requested:
        if ($json) {
            print \json_encode(\array_merge($this->errors, $this->warnings));
        }

        return \count($this->errors) || ($failOnWarnings && \count($this->warnings)) ? 1 : 0;
    }

    /**
     * Iterate through a directory and check all of the PHP files within it.
     *
     * @param string $path
     * @param string[] $workList
     */
    protected function processDirectory(string $path = '', array &$workList = []): void
    {
        $dir = new DirectoryIterator($this->basePath . $path);

        foreach ($dir as $item) {
            if ($item->isDot()) {
                continue;
            }

            $itemPath = $path . $item->getFilename();

            if (\in_array($itemPath, $this->exclude)) {
                continue;
            }

            if ($item->isFile() && $item->getExtension() == 'php') {
                $workList[] = $itemPath;
            }

            if ($item->isDir()) {
                $this->processDirectory($itemPath . '/', $workList);
            }
        }
    }
    
    /**
     * Iterate through the files and check them out
     *
     * @param string $path
     * @param string[] $files
     * @param string[] $workList
     */
    protected function processFiles(string $path = '', array $files = [], array &$workList = []): void
    {
        foreach ($files as $item) {
            $itemPath = $path . $item;

            if (\in_array($itemPath, $this->exclude)) {
                continue;
            }

            if (is_file($itemPath) && pathinfo($itemPath)["extension"] == 'php') {
                $workList[] = $itemPath;
            }
        }
    }

    /**
     * Check a specific PHP file for errors.
     *
     * @param string $file
     *
     * @return array
     */
    protected function processFile(string $file): array
    {
        $result = $this->checkerFileProcessor->processFile($file);

        $this->errors   = \array_merge($this->errors, $result['errors']);
        $this->warnings = \array_merge($this->warnings, $result['warnings']);;

        if (0 === \count($result['errors'])) {
            $this->passed += 1;
        }

        return [
            (0 !== \count($result['errors'])),
            (0 !== \count($result['warnings'])),
        ];
    }
}
