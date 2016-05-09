<?php

/**
* @package     jelix-scripts
* @author      Laurent Jouanneau
* @contributor Julien Issler
* @copyright   2008-2016 Laurent Jouanneau
* @copyright   2009 Julien Issler
* @link        http://jelix.org
* @licence     GNU General Public Licence see LICENCE file or http://www.gnu.org/licenses/gpl.html
*/
namespace Jelix\DevHelper\Command;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputArgument;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Input\InputOption;
use Symfony\Component\Console\Output\OutputInterface;

class InstallApp extends \Jelix\DevHelper\AbstractCommandForApp {

    protected function configure()
    {
        $this
            ->setName('app:install')
            ->setDescription('Exectute install/update scripts from all activated modules')
            ->setHelp('')
        ;
        parent::configure();
    }

    protected function _execute(InputInterface $input, OutputInterface $output)
    {
        require_once (JELIX_LIB_PATH.'installer/jInstaller.class.php');

        \jAppManager::close();
        if ($this->verbose()) {
            $reporter = new \textInstallReporter();
        }
        else {
            $reporter = new \textInstallReporter('error');
        }

        $installer = new \jInstaller($reporter);

        if ($input->getOption('entry-point')) {
            $installer->installEntryPoint($this->entryPointId);
        }
        else {
            $installer->installApplication();
        }

        try {
            \jAppManager::clearTemp(\jApp::tempBasePath());
        }
        catch(\Exception $e) {
            if ($e->getCode() == 2) {
                $output->writeln("<error>Error: bad path in jApp::tempBasePath(), it is equals to '".jApp::tempBasePath()."' !!</error>");
                $output->writeln("       Jelix cannot clear the content of the temp directory.");
                $output->writeln("       you must clear it your self.");
                $output->writeln("       Correct the path in the application.init.php or create the directory");
            }
            else {
                $output->writeln("<error>Error: ".$e->getMessage()."</error>");
            }
        }
        \jAppManager::open();
    }
}