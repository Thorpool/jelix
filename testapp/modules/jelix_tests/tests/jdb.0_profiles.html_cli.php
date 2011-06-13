<?php
/**
* @package     testapp
* @subpackage  jelix_tests module
* @author      Laurent Jouanneau
* @contributor
* @copyright   2007 Laurent Jouanneau
* @link        http://www.jelix.org
* @licence     GNU Lesser General Public Licence see LICENCE file or http://www.gnu.org/licenses/lgpl.html
*/

class UTjDb_profile extends jUnitTestCase {

    function testProfile() {
        $p = jDb::getProfile('jelix_tests_mysql');
        $result= array(
            'driver'=>"mysql",
            'database'=>"testapp_mysql",
            'host'=> "localhost_mysql",
            'user'=> "plop_mysql",
            'password'=> "futchball_mysql",
            'persistent'=> '1',
            'force_encoding'=>1,
            'name'=>'jelix_tests_mysql',
        );

        $this->assertEqual($p, $result);

        $p = jDb::getProfile('forward');
        $result= array(
            'driver'=>"mysql",
            'database'=>"jelix_tests_forward",
            'host'=> "localhost_forward",
            'user'=> "plop_forward",
            'password'=> "futchball_forward",
            'persistent'=> '1',
            'force_encoding'=>0,
            'name'=>'jelix_tests_forward',
        );

        $this->assertEqual($p, $result);

        $p = jDb::getProfile('testapp');
        $this->assertEqual($p['name'], 'testapp');
        $p2 = jDb::getProfile();
        $this->assertEqual($p2['name'], 'testapp');
        $this->assertEqual($p, $p2);
        $p = jDb::getProfile('testapppdo');
        $this->assertEqual($p['name'], 'testapppdo');
    }

    function testVirtualProfile() {
        $profile = array(
            'driver'=>"mysql",
            'database'=>"virtual_mysql",
            'host'=> "localhostv_mysql",
            'user'=> "v_mysql",
            'password'=> "vir_mysql",
            'persistent'=> '1',
            'force_encoding'=>1
        );

        jDb::createVirtualProfile('foobar', $profile);

        $p = jDb::getProfile('foobar');
        $profile['name'] = 'foobar';

        $this->assertEqual($profile, $p);
    }

    function testBadProfile(){
        $p = jDb::getProfile('abcdef'); // unknown profile
        $build = parse_ini_file(JELIX_LIB_PATH.'BUILD');
        if (!$build['ENABLE_OPTIMIZED_SOURCE'])
            $this->assertError("(413) The given jDb profile \"abcdef\" doesn't exist. The default one is used instead. To not show this error, create the profile or an alias to the default profile.");

    }
}