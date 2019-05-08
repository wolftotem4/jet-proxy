<?php

namespace JetProxy;

use Closure;

interface TransmissionHandler
{
    /**
     * @param  resource  $ch
     * @return $this
     */
    public function setCurl($ch);

    /**
     * @param  resource  $ch
     * @return $this
     */
    public function finish($ch);

    /**
     * @param  \Closure  $closure
     * @return $this
     */
    public function addHeaderListener(Closure $closure);

    /**
     * @param  \Closure  $closure
     * @return $this
     */
    public function addBufferListener(Closure $closure);

    /**
     * @param  \Closure  $closure
     * @return $this
     */
    public function addEndListener(Closure $closure);
}