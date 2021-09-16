<?php
declare(strict_types=1);

namespace JohnKary\PHPUnit\Listener\Renderer;

use Exception;
use JohnKary\PHPUnit\Listener\SpeedTrapReport;
use PHPUnit\Framework\TestCase;

/**
 * Renderer for Jenkins Warnings-NG format
 * @see https://github.com/jenkinsci/warnings-ng-plugin/blob/master/plugin/src/test/resources/io/jenkins/plugins/analysis/warnings/steps/issues.json
 */
class WarningsNgRenderer implements ReportRendererInterface
{
    /** @var SpeedTrapReport */
    protected $speedTrapReport;

    /** @var string the full path to the report file in warnings-ng format  */
    protected $targetFile;

    /** @var string optional basedir, if specified transforms test filepath relative to this directory */
    protected $projectBaseDir;

    /** @var array */
    protected $data;

    /**
     * @throws Exception
     * @see ReportRendererInterface::__construct
     */
    public function __construct(SpeedTrapReport $speedTrapReport, array $options)
    {
        $this->speedTrapReport = $speedTrapReport;
        $this->data = [];
        if (
            !isset($options['file'])
            || !is_string($options['file'])
        ) {
           throw new Exception(
               'WarningsNgRenderer - invalid filepath provided'
           );
        }
        if (!is_writable(dirname($options['file'])))
        {
            throw new Exception(
                'WarningsNgRenderer - parent directory should be writable '
                . $options['file']
            );
        }
        $this->targetFile = $options['file'];
        $this->projectBaseDir = $options['projectBaseDir'] ?? false;
        if ($this->projectBaseDir) {
            if (is_dir($this->projectBaseDir)) {
                $this->projectBaseDir = realpath($this->projectBaseDir);
            } else {
                throw new Exception(
                    'WarningsNgRenderer - project base directory should exist'
                    .$options['file']
                );
            }
        }
    }

    /**
     * @see ReportRendererInterface::renderHeader
     */
    public function renderHeader(): void
    {
        $this->data['_class'] = 'io.jenkins.plugins.analysis.core.restapi.ReportApi';
        $this->data['issues'] = [];
    }

    /**
     * @see ReportRendererInterface::renderBody
     */
    public function renderBody(): void
    {
        $slowTests = $this->speedTrapReport->getSlow();

        $length = count($slowTests);
        $this->data['size'] = $length;
        for ($i = 1; $i <= $length; ++$i) {
            /** @var TestCase $testCase */
            [$testCase, $time] = array_shift($slowTests);
            $label = sprintf("%sms to run %s", $time, $testCase->getName(true));

            $testCaseClass = new \ReflectionClass(get_class($testCase));
            $fileName = $testCaseClass->getFileName();
            if ($this->projectBaseDir) {
                $fileName = str_replace($this->projectBaseDir, '.', $fileName);
            }

            $this->data['issues'][] = [
                "fileName" => $fileName,
                "packageName" => $testCaseClass->getNamespaceName(),
                "message" => $label,
                "severity" => "HIGH",
                "duration" => $time,
            ];
        }
    }

    /**
     * @see ReportRendererInterface::renderFooter
     */
    public function renderFooter(): void
    {
        // write the file
        file_put_contents($this->targetFile, json_encode($this->data, JSON_PRETTY_PRINT|JSON_UNESCAPED_SLASHES));
    }

}
