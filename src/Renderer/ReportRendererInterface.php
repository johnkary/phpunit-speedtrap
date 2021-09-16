<?php
declare(strict_types=1);

namespace JohnKary\PHPUnit\Listener\Renderer;

use JohnKary\PHPUnit\Listener\SpeedTrapReport;

interface ReportRendererInterface {

    /**
     * @param SpeedTrapReport $speedTrapReport parent process
     * @param array             $options
     */
    function __construct(SpeedTrapReport $speedTrapReport, array $options);

    /**
     * Renders slowness report header.
     */
    function renderHeader(): void;

    /**
     * Renders slowness report body.
     */
    function renderBody(): void;

    /**
     * Renders slowness report footer.
     */
    function renderFooter(): void;

}
