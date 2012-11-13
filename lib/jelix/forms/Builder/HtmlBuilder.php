<?php
/**
* @package     jelix
* @subpackage  forms
* @author      Laurent Jouanneau
* @contributor Julien Issler, Dominique Papin, Claudio Bernardes
* @copyright   2006-2012 Laurent Jouanneau
* @copyright   2008-2011 Julien Issler, 2008 Dominique Papin, 2012 Claudio Bernardes
* @link        http://www.jelix.org
* @licence     http://www.gnu.org/licenses/lgpl.html GNU Lesser General Public Licence, see LICENCE file
*/

namespace jelix\forms\Builder;

/**
 * Main HTML form builder
 */
class HtmlBuilder extends BuilderBase {
    protected $formType = 'html';

    protected $jFormsJsVarName = 'jForms';

    /**
     * @var \jelix\forms\HtmlWidget\RootWidget
     */
    protected $rootWidget;

    protected $options;

    public function __construct($form){
        parent::__construct($form);
        $this->rootWidget = jApp::loadPlugin($this->formType, 'formwidget', '.formwidget.php', $this->formType.'FormWidget');
        if (!$this->rootWidget)
            throw new \Exception ("Unknown root widget plugin ".$this->formType);
    }

    public function getjFormsJsVarName() {
        return $this->jFormsJsVarName;
    }

    public function getOption($name) {
        if (isset($this->options[$name]))
            return $this->options[$name];
        return null;
    }

    public function outputAllControls() {

        echo '<table class="jforms-table" border="0">';
        foreach( $this->_form->getRootControls() as $ctrlref=>$ctrl){
            if($ctrl->type == 'submit' || $ctrl->type == 'reset' || $ctrl->type == 'hidden') continue;
            if(!$this->_form->isActivated($ctrlref)) continue;
            if($ctrl->type == 'group') {
                echo '<tr><td colspan="2">';
                $this->outputControl($ctrl);
                echo '</td></tr>';
            }else{
                echo '<tr><th scope="row">';
                $this->outputControlLabel($ctrl);
                echo '</th><td>';
                $this->outputControl($ctrl);
                echo "</td></tr>\n";
            }
        }
        echo '</table> <div class="jforms-submit-buttons">';
        if ( $ctrl = $this->_form->getReset() ) {
            if(!$this->_form->isActivated($ctrl->ref)) continue;
            $this->outputControl($ctrl);
            echo ' ';
        }
        foreach( $this->_form->getSubmits() as $ctrlref=>$ctrl){
            if(!$this->_form->isActivated($ctrlref)) continue;
            $this->outputControl($ctrl);
            echo ' ';
        }
        echo "</div>\n";
    }

    public function outputMetaContent($t) {
        $resp= \jApp::coord()->response;
        if($resp === null || $resp->getType() !='html'){
            return;
        }
        $config = \jApp::config();
        $www = $config->urlengine['jelixWWWPath'];
        $bp = $config->urlengine['basePath'];
        $resp->addJSLink($www.'js/jforms_light.js');
        $resp->addCSSLink($www.'design/jform.css');
        $heConf = &$config->htmleditors;
        foreach($t->_vars as $k=>$v){
            if($v instanceof \jFormsBase && count($edlist = $v->getHtmlEditors())) {
                foreach($edlist as $ed) {

                    if(isset($heConf[$ed->config.'.engine.file'])){
                        $file = $heConf[$ed->config.'.engine.file'];
                        if(is_array($file)){
                            foreach($file as $url) {
                                $resp->addJSLink($bp.$url);
                            }
                        }else
                            $resp->addJSLink($bp.$file);
                    }

                    if(isset($heConf[$ed->config.'.config']))
                        $resp->addJSLink($bp.$heConf[$ed->config.'.config']);

                    $skin = $ed->config.'.skin.'.$ed->skin;
                    if(isset($heConf[$skin]) && $heConf[$skin] != '')
                        $resp->addCSSLink($bp.$heConf[$skin]);
                }
            }
        }
    }

    /**
     * output the header content of the form
     * @param array $params some parameters <ul>
     *      <li>"errDecorator"=>"name of your javascript object for error listener"</li>
     *      <li>"method" => "post" or "get". default is "post"</li>
     *      </ul>
     */
    public function outputHeader($params){
        $this->options = array_merge(array('errorDecorator'=>$this->jFormsJsVarName.'ErrorDecoratorHtml',
            'method'=>'post'), $params);
        if (isset($params['attributes']))
            $attrs = $params['attributes'];
        else
            $attrs = array();

        echo '<form';
        if (preg_match('#^https?://#',$this->_action)) {
            $urlParams = $this->_actionParams;
            $attrs['action'] = $this->_action;
        } else {
            $url = \jUrl::get($this->_action, $this->_actionParams, 2); // returns the corresponding jurl
            $urlParams = $url->params;
            $attrs['action'] = $url->getPath();
        }
        $attrs['method'] = $this->options['method'];
        $attrs['id'] = $this->_name;

        if($this->_form->hasUpload())
            $attrs['enctype'] = "multipart/form-data";

        $this->_outputAttr($attrs);
        echo '>';

        $this->rootWidget->outputHeader($this);

        $hiddens = '';
        foreach ($urlParams as $p_name => $p_value) {
            $hiddens .= '<input type="hidden" name="'. $p_name .'" value="'. htmlspecialchars($p_value). '"'.$this->_endt. "\n";
        }

        foreach ($this->_form->getHiddens() as $ctrl) {
            if(!$this->_form->isActivated($ctrl->ref)) continue;
            $hiddens .= '<input type="hidden" name="'. $ctrl->ref.'" id="'.$this->_name.'_'.$ctrl->ref.'" value="'. htmlspecialchars($this->_form->getData($ctrl->ref)). '"'.$this->_endt. "\n";
        }

        if($this->_form->securityLevel){
            $tok = $this->_form->createNewToken();
            $hiddens .= '<input type="hidden" name="__JFORMS_TOKEN__" value="'.$tok.'"'.$this->_endt. "\n";
        }

        if($hiddens){
            echo '<div class="jforms-hiddens">',$hiddens,'</div>';
        }

        $errors = $this->_form->getContainer()->errors;
        if(count($errors)){
            $ctrls = $this->_form->getControls();
            echo '<ul id="'.$this->_name.'_errors" class="jforms-error-list">';
            $errRequired='';
            foreach($errors as $cname => $err){
                if(!$this->_form->isActivated($ctrls[$cname]->ref)) continue;
                if ($err === jForms::ERRDATA_REQUIRED) {
                    if ($ctrls[$cname]->alertRequired){
                        echo '<li>', $ctrls[$cname]->alertRequired,'</li>';
                    }
                    else {
                        echo '<li>', jLocale::get('jelix~formserr.js.err.required', $ctrls[$cname]->label),'</li>';
                    }
                }else if ($err === jForms::ERRDATA_INVALID) {
                    if($ctrls[$cname]->alertInvalid){
                        echo '<li>', $ctrls[$cname]->alertInvalid,'</li>';
                    }else{
                        echo '<li>', \jLocale::get('jelix~formserr.js.err.invalid', $ctrls[$cname]->label),'</li>';
                    }
                }
                elseif ($err === \jForms::ERRDATA_INVALID_FILE_SIZE) {
                    echo '<li>', \jLocale::get('jelix~formserr.js.err.invalid.file.size', $ctrls[$cname]->label),'</li>';
                }
                elseif ($err === \jForms::ERRDATA_INVALID_FILE_TYPE) {
                    echo '<li>', \jLocale::get('jelix~formserr.js.err.invalid.file.type', $ctrls[$cname]->label),'</li>';
                }
                elseif ($err === \jForms::ERRDATA_FILE_UPLOAD_ERROR) {
                    echo '<li>', \jLocale::get('jelix~formserr.js.err.file.upload', $ctrls[$cname]->label),'</li>';
                }
                elseif ($err != '') {
                    echo '<li>', $err,'</li>';
                }
            }
            echo '</ul>';
        }
    }

    public function outputFooter(){
        $this->rootWidget->outputFooter($this);
        echo '</form>';
    }

    public function getWidget($ctrl, \jelix\forms\HtmlWidget\ParentWidgetInterface $parentWidget = null) {
        $pluginName = $ctrl->type . '_'. $this->formType;
        $className = $pluginName . 'FormWidget';
        $plugin = \jApp::loadPlugin($pluginName, 'formwidget', '.formwidget.php', $className, array($ctrl, $this, $parentWidget));
        if (!$plugin)
            throw new \Exception('Widget '.$pluginName.' not found');
        return $plugin;
    }

    public function outputControlLabel($ctrl){
        if($ctrl->type == 'hidden') return;
        $widget = $this->getWidget($ctrl, $this->rootWidget);
        $widget->outputLabel();
    }

    public function outputControl($ctrl, $attributes=array()){
        if($ctrl->type == 'hidden') return;
        $widget = $this->getWidget($ctrl, $this->rootWidget);
        $widget->setAttributes($attributes);
        $widget->outputControl();
        $widget->outputHelp();
    }

    protected function _outputAttr(&$attributes) {
        foreach($attributes as $name=>$val) {
            echo ' '.$name.'="'.htmlspecialchars($val).'"';
        }
    }

    public function escJsStr($str) {
        return '\''.str_replace(array("'","\n"),array("\\'", "\\n"), $str).'\'';
    }
}
