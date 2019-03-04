<?php

namespace AppBundle\DTO;

class DeputyClientsDto implements \JsonSerializable
{
    private $deputy;
    private $clients;

    /**
     * @param DeputyDto $deputy
     * @param array $clients
     */
    public function __construct(DeputyDto $deputy, array $clients)
    {
        $this->deputy = $deputy;
        $this->clients = $clients;
    }

    public function jsonSerialize()
    {
        return [
            'deputy' => $this->deputy->jsonSerialize(),
            'clients' => $this->serializeClients()
        ];
    }

    /**
     * @return array
     */
    private function serializeClients()
    {
        $serializedClients = [];

        foreach ($this->clients as $client) {
            $serializedClients[] = $client->jsonSerialize();
        }

        return $serializedClients;
    }

    /**
     * @return DeputyDto
     */
    public function getDeputy()
    {
        return $this->deputy;
    }

    /**
     * @return array
     */
    public function getClients()
    {
        return $this->clients;
    }
}
