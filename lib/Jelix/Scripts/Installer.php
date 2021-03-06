<?php
/**
 * @author      Laurent Jouanneau
 * @copyright   2018 Laurent Jouanneau
 *
 * @see        http://www.jelix.org
 * @licence     MIT
 */

namespace Jelix\Scripts;

/**
 * Launch commands from modules.
 *
 * @package Jelix\Scripts
 */
class Installer
{
    public static function launch()
    {
        Utils::checkEnv();
        // init Jelix environment
        \Jelix\Core\App::setEnv('install');
        Utils::checkTempPath();

        $application = new SingleCommandApplication(
            new InstallerCommand(),
            'Installer'
        );
        $application->run();
    }
}
