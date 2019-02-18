<?php
/**
 * @package      jelix
 * @subpackage   core
 * @author       Laurent Jouanneau
 * @copyright    2017 Laurent Jouanneau
 * @link         http://jelix.org
 * @licence      GNU Lesser General Public Licence see LICENCE file or http://www.gnu.org/licenses/lgpl.html
 */


class webassetsConfigCompilerPlugin implements \Jelix\Core\Config\CompilerPluginInterface {

    function getPriority() {
        return 18;
    }

    function atStart($config) {

        $compiler = new \Jelix\WebAssets\WebAssetsCompiler();
        $compiler->compile($config);
    }

    function onModule($config, \Jelix\Core\Infos\ModuleInfos $module) {

    }

    function atEnd($config) {

    }



}
