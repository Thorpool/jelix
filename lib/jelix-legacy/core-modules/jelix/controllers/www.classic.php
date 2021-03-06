<?php
/**
 * @package    jelix-modules
 * @subpackage jelix-module
 *
 * @author     Laurent Jouanneau
 * @copyright  2011-2012 Laurent Jouanneau
 * @licence    http://www.gnu.org/licenses/gpl.html GNU General Public Licence, see LICENCE file
 */

/**
 * @package    jelix-modules
 * @subpackage jelix-module
 */
class wwwCtrl extends jController
{
    public function getfile()
    {
        $module = $this->param('targetmodule');

        if (!jApp::isModuleEnabled($module) || !jApp::config()->modules[$module.'.enabled']) {
            throw new jException('jelix~errors.module.untrusted', $module);
        }

        $dir = jApp::getModulePath($module).'www/';
        $filename = realpath($dir.str_replace('..', '', $this->param('file')));

        if (!is_file($filename)) {
            $rep = $this->getResponse('html', true);
            $rep->bodyTpl = 'jelix~404.html';
            $rep->setHttpStatus('404', 'Not Found');

            return $rep;
        }

        $rep = $this->getResponse('binary');

        $dateModif = new DateTime();
        $dateModif->setTimestamp(filemtime($filename));
        if ($rep->isValidCache($dateModif)) {
            return $rep;
        }

        $rep->doDownload = false;
        $rep->fileName = $filename;
        $rep->mimeType = \Jelix\FileUtilities\File::getMimeTypeFromFilename($rep->fileName);

        return $rep;
    }
}
