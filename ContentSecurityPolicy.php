<?php namespace ContentSecurityPolicy;

class ContentSecurityPolicy
{
    private array $policies = array();
    private string $default;

    public function __construct(string $default = '')
    {
        $this->default = "$default";
    }

    public function add(string $policyKey, string $policy): void
    {
        if (array_key_exists($policyKey, $this->policies)) {
            $this->policies[$policyKey] = "{$this->policies[$policyKey]} $policy";
        } else {
            $this->policies[$policyKey] = "$policy";
        }
    }

    public function send(bool $asReportOnly=false): void
    {
        $ReportOnly = $asReportOnly ? "-Report-Only" : '';
        header("Content-Security-Policy$ReportOnly: $this");
    }

    public function __toString(): string
    {
        $policy = array();
        foreach ($this->policies as $policyKey => $policyValue) {
            $policy[] = "$policyKey\x20$policyValue;\x20";
        }
        return "$this->default; " . implode("", $policy);
    }
}
