<?php

namespace App\Services\Interfaces;

interface IWazuhService
{
    public function getToken(): ?string;
    public function getAgentSummaryStatus(string $token): array;
    public function getAgents(string $token, int $offset = 0, int $limit = 10, ?string $search = null, ?string $status = null): array;
    public function getAgent(string $token, string $agentId): ?array;
    public function getAgentsWithIPs(): array;
}
