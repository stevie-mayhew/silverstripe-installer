<?php

namespace SilverStripe\Installer;

use Composer\Script\Event;
use Composer\IO\IOInterface;

/**
 * Class PostCreateCommand
 *
 * Runs tasks to install and update common configurations for a base SilverStripe install.
 *
 * @package SilverStripe\Installer
 */
class PostCreateProjectCommand
{
    /**
     * @var string
     */
    private static $basePath = '';


    /**
     * Post Create Project Command
     *
     * Runs through a series of steps to set up a project. Installs all dependencies via npm and composer. Builds
     * javascript and css via gulp.
     *
     * @param Event $event
     */
    public static function process(Event $event)
    {
        /**
         * @var IOInterface
         */
        $io = $event->getIO();
        if ($io->askConfirmation('<question>Add _ss_environment.php file?</question> [<comment>yes</comment>] ')) {
            self::addEnvironmentFile($io);
        }

        exit;
    }

    /**
     * Update the base _ss_environment.php file for this install.
     *
     * @param IOInterface $io
     */
    protected static function addEnvironmentFile(IOInterface $io)
    {
        $basePath = self::getBasepath();
        $filePath = $basePath . '/_ss_environment.php';
        if (!file_exists($filePath)) {
            $databaseUser = $io->ask('Enter your database user name: ');
            $databasePassword = $io->ask('Enter your database password: ');
            $databaseName = $io->ask('Enter your database name: ');
            $databaseServer = $io->ask('Enter your database server: ');
            $adminUser = $io->ask('Enter your admin username: ');
            $adminPassword = $io->ask('Enter your admin password:');
            $sitePath = $io->ask('Enter your site path (i.e. silverstripe.dev): ');

            // Write answers to environment file
            $environmentFile = <<<EOF
<?php
/* What kind of environment is this: development, test, or live (ie, production)? */
define('SS_ENVIRONMENT_TYPE', 'dev');

/* Database connection */
define('SS_DATABASE_SERVER', '{$databaseServer}');
define('SS_DATABASE_USERNAME', '{$databaseUser}');
define('SS_DATABASE_PASSWORD', '{$databasePassword}');
define('SS_DATABASE_NAME', '{$databaseName}');

/* Configure a default username and password to access the CMS on all sites in this environment. */
define('SS_DEFAULT_ADMIN_USERNAME', '{$adminUser}');
define('SS_DEFAULT_ADMIN_PASSWORD', '{$adminPassword}');

global \$_FILE_TO_URL_MAPPING;

\$_FILE_TO_URL_MAPPING['{$basePath}'] = 'http://{$sitePath}/';

EOF;

            file_put_contents($filePath, $environmentFile);
            $io->write("<info>Added _ss_environment.php file</info>");
        } else {
            $io->write("<info>_ss_environment.php already exists</info>");
        }
    }

    /**
     * Get the document root for this project. Attempts getcwd(), falls back to directory traversal.
     * @return string
     */
    private static function getBasePath()
    {
        $candidate = getcwd() ?: dirname(dirname(dirname(dirname(__FILE__))));
        return rtrim($candidate, DIRECTORY_SEPARATOR);
    }

}