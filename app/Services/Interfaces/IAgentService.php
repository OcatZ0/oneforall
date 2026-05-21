<?php

namespace App\Services\Interfaces;

interface IAgentService
{
    public function userHasAccessToAgent(string $agentId): bool;
    public function getAccessibleAgentIds(): array;
    public function enrichAgentData(object $agent): object;
    public function validateAgentAssignment(array $agentIds, ?int $excludeUserId = null): ?string;
    public function getAvailableAgents(): array;
    public function syncFromWazuh(): array;
    public function mapWazuhAgent(array $wazuhAgent, bool $full = false): object;
}
