<?php


namespace Ultimate\Models\Partials;


use Ultimate\Exceptions\UltimateException;
use Ultimate\Models\Arrayable;
use Ultimate\OS;

class Host extends Arrayable
{
    /**
     * Host constructor.
     */
    public function __construct()
    {
        $this->hostname = gethostname();
        $this->ip = gethostbyname(gethostname());
        $this->os = PHP_OS_FAMILY;



    }

    /**
     * Collect server status information.
     *
     * @return $this
     */
    public function withServerStatus()
    {
        if (OS::isLinux() && function_exists('shell_exec')) {
            try {
                $status = shell_exec('echo "`LC_ALL=C top -bn1 | grep "Cpu(s)" | sed "s/.*, *\([0-9.]*\)%* id.*/\1/" | awk \'{print 100 - $1}\'`%;`free -m | awk \'/Mem:/ { printf("%3.1f%%", $3/$2*100) }\'`;`df -h / | awk \'/\// {print $(NF-1)}\'`"');
                $status = str_replace('%', '', $status);
                $status = str_replace("\n", '', $status);

                $status = explode(';', $status);

                $this->cpu = $status[0];
                $this->ram = $status[1];
                $this->hdd = $status[2];
            } catch (\Throwable $exception) {
                //throw new UltimateException('Error Server Metrics');
                // do nothing
            }
        }

        return $this;
    }

}
