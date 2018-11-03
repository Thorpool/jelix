<?php
/**
 * @author      Laurent Jouanneau
 * @copyright   2018 Laurent Jouanneau
 * @link        http://www.jelix.org
 * @licence     GNU Lesser General Public Licence see LICENCE file or http://www.gnu.org/licenses/lgpl.html
 */
namespace Jelix\Installer\Module\API;

/**
 *
 * @since 1.7
 */
class LocalConfigurationHelpers extends ConfigurationHelpers {

    /**
     * declare a new db profile. if the content of the section is not given,
     * it will declare an alias to the default profile
     *
     * @param string $name the name of the new section/alias
     * @param null|string|array $sectionContent the content of the new section, or null
     *     to create an alias.
     * @param boolean $force true:erase the existing profile
     * @return boolean true if the ini file has been changed
     */
    protected final function declareDbProfile($name, $sectionContent = null, $force = true)
    {
        $profiles = $this->globalSetup->getProfilesIni();
        if ($sectionContent == null) {
            if (!$profiles->isSection('jdb:' . $name)) {
                // no section
                if ($profiles->getValue($name, 'jdb') && !$force) {
                    // already a name
                    return false;
                }
            } else if ($force) {
                // existing section, and no content provided : we erase the section
                // and add an alias
                $profiles->removeValue('', 'jdb:' . $name);
            } else {
                return false;
            }
            $default = $profiles->getValue('default', 'jdb');
            if ($default) {
                $profiles->setValue($name, $default, 'jdb');
            } else // default is a section
                $profiles->setValue($name, 'default', 'jdb');
        } else {
            if ($profiles->getValue($name, 'jdb') !== null) {
                if (!$force)
                    return false;
                $profiles->removeValue($name, 'jdb');
            }
            if (is_array($sectionContent)) {
                foreach ($sectionContent as $k => $v) {
                    if ($force || !$profiles->getValue($k, 'jdb:' . $name)) {
                        $profiles->setValue($k, $v, 'jdb:' . $name);
                    }
                }
            } else {
                $profile = $profiles->getValue($sectionContent, 'jdb');
                if ($profile !== null) {
                    $profiles->setValue($name, $profile, 'jdb');
                } else
                    $profiles->setValue($name, $sectionContent, 'jdb');
            }
        }
        $profiles->save();
        \jProfiles::clear();
        return true;
    }

    /**
     * remove a db profile
     *
     * @param string $name  the name of the section/alias
     */
    protected function removeDbProfile($name) {

        $profiles = $this->getProfilesIni();
        if ($profiles->getValue($name, 'jdb')) {
            $profiles->removeValue($name, 'jdb');
            return;
        }
        if ($profiles->isSection('jdb:'.$name)) {
            $aliases = $profiles->getValues('jdb');
            foreach($aliases as $alias=>$profile) {
                if ($profile == $name) {
                    // if there are some aliases to the profile
                    // we don't remove it to avoid to break
                    // the application
                    return;
                }
            }
            $profiles->removeValue(null, 'jdb:'.$name);
        }
    }
}