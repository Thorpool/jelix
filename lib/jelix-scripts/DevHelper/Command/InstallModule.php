<?php
/**
* @author      Laurent Jouanneau
* @copyright   2009-2016 Laurent Jouanneau
* @link        http://jelix.org
* @licence     GNU General Public Licence see LICENCE file or http://www.gnu.org/licenses/gpl.html
*/
namespace Jelix\DevHelper\Command;
use Symfony\Component\Console\Input\InputArgument;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Input\InputOption;
use Symfony\Component\Console\Output\OutputInterface;

class InstallModule extends \Jelix\DevHelper\AbstractCommandForApp {

    protected function configure()
    {
        $this
            ->setName('module:install')
            ->setDescription('Install or upgrade a module even if it is not activated.')
            ->setHelp('if an entry point is indicated, the module is installed only for this entry point.')
            ->addArgument(
                'module',
                InputArgument::REQUIRED,
                'Name of the module to install/upgrade'
            )
        ;
        parent::configure();
    }

    protected function _execute(InputInterface $input, OutputInterface $output)
    {
        require_once (JELIX_LIB_PATH.'installer/jInstaller.class.php');

        \jAppManager::close();

        $module = $input->getArgument('module');
        $parameters = $input->getOption('parameters');

        if ($parameters) {
            $parameters = \jInstallerModuleInfos::unserializeParameters($parameters);
        }

        if ($this->verbose()) {
            $reporter = new \textInstallReporter();
        }
        else {
            $reporter = new \textInstallReporter('error');
        }

        $installer = new \jInstaller($reporter);
        $installer->installModules(array($module));

        try {
            \jAppManager::clearTemp(\jApp::tempBasePath());
        }
        catch(\Exception $e) {
            if ($e->getCode() == 2) {
                $output->writeln("Error: bad path in jApp::tempBasePath(), it is equals to '".\jApp::tempBasePath()."' !!");
                $output->writeln("       Jelix cannot clear the content of the temp directory.");
                $output->writeln("       you must clear it your self.");
                $output->writeln("       Correct the path in application.init.php or create the directory");
            }
            else {
                $output->writeln("<error>Error: ".$e->getMessage()."</error>");
            }
        }
        \jAppManager::open();
    }
}
