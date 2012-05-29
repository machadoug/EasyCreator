<?php
/**
 * User: elkuku
 * Date: 22.05.12
 * Time: 19:28
 */

/**
 * Custom action class.
 */
class EcrProjectActionPhpunit extends EcrProjectAction
{
    protected $type = 'phpunit';

    protected $name = 'PHP Unit';

    public $filedir = '';

    public $failureThreshold = 0;

    public $errorThreshold = 0;

    /**
     * Get the input fields
     *
     * @param int $cnt A counter value.
     *
     * @return string
     */
    public function getFields($cnt)
    {
        // TODO: Implement getFields() method.
        $html = array();

        $html[] = '<label class="inline2" for="fields_'.$cnt.'_filedir">'.jgettext('File or directory').'</label>';
        $html[] = '<input type="text" value="'.$this->filedir.'"'
            .' name="fields['.$cnt.'][filedir]" id="fields_'.$cnt.'_filedir">'
            .'<br />';

        $html[] = '<label class="inline2" for="fields_'.$cnt.'_arguments">'.jgettext('Arguments').'</label>';
        $html[] = '<input type="text" value="'.$this->arguments.'"'
            .' name="fields['.$cnt.'][arguments]" id="fields_'.$cnt.'_arguments">'
            .'<br />';

        $html[] = '<label class="inline2" for="fields_'.$cnt.'_fThreshold">'.jgettext('Failure Threshold').'</label>';
        $html[] = '<input type="text" class="span1" value="'.$this->failureThreshold.'"'
            .' name="fields['.$cnt.'][failureThreshold]" id="fields_'.$cnt.'_fThreshold">'
            .'<br />';

        $html[] = '<label class="inline2" for="fields_'.$cnt.'_eThreshold">'.jgettext('Error Threshold').'</label>';
        $html[] = '<input type="text" class="span1" value="'.$this->errorThreshold.'"'
            .' name="fields['.$cnt.'][errorThreshold]" id="fields_'.$cnt.'_eThreshold">';

        return implode("\n", $html);
    }

    /**
     * Perform the action.
     *
     * @param EcrProjectZiper $ziper
     *
     * @return bool true if successful, false to interrupt the build process
     */
    public function run(EcrProjectZiper $ziper)
    {
        $logger = $ziper->logger;
        $project = $ziper->project;

        $logger->log('Executing PHP Unit');

        //$logger->log(print_r($project->copies, 1));

        //$files = implode(' ', $project->copies);

        $parts = array(
            'phpunit'
        , '--log-tap '.ECRPATH_LOGS.'/phpunit.log'
        , $this->arguments
        , $this->filedir
        );

        $cmd = implode(' ', $parts);

        $cmd = escapeshellcmd($cmd);

        $logger->log($cmd);

        $output = shell_exec($cmd.' 2>&1 | tee -a '.$ziper->logFile);

        $log = JFile::read(ECRPATH_LOGS.'/phpunit.log');

        // not ok 1 - Failure: PHPHatesMeTest::testAuthor

        //$pattern = "/not ok (\d+) - ([a-Z]+):/";
        $pattern = "/not ok (\d+) - (\w+):/";

        preg_match_all($pattern, $log, $matches);

        $failures = array();
        $errors = array();

        if($matches && isset($matches[2]))
        {
            //foreach($matches[2])
            $type = $matches[2][0];

            switch($type)
            {
                case 'Failure' :
                    $failures[] = $matches[0][0];
                    break;
                case 'Error' :
                    $errors[] = $matches[0][0];
                    break;
                default :
                    $ziper->logger->log(__METHOD__.' - unknown type: '.$type);
            }

            $logger->log('PHP Unit results:');

            //$logger->log(sprintf('Files processed: %s', $filesProcessed));
            $logger->log(sprintf('Failures: %d', count($failures)));
            $logger->log(sprintf('Errors:   %d', count($errors)));

            if(0 != $this->failureThreshold)
            {
                if(count($failures) >= $this->failureThreshold)
                {
                    $ziper->addFailure(sprintf('%s: The failure threshold of %d has been exceeded (%d failures)'
                        , $this->name, $this->failureThreshold, count($failures)));

                    $ziper->setInvalid();
                }
            }

            if(0 != $this->errorThreshold)
            {
                if(count($errors) >= $this->errorThreshold)
                {
                    $ziper->addFailure(sprintf('%s: The error threshold of %d has been exceeded (%d errors)'
                        , $this->name, $this->errorThreshold, count($errors)));

                    $ziper->setInvalid();
                }
            }
        }

        return $this;
    }
}
