<?php declare(strict_types=1);

namespace Syncer\Toggl;

use GuzzleHttp\Client;
use JMS\Serializer\SerializerInterface;
use Syncer\Dto\Toggl\Workspace;

/**
 * Class TogglApiClient
 * @package Syncer\Toggl
 *
 * @author Matthieu Calie <matthieu@calie.be>
 */
class TogglApiClient
{
    const VERSION = 'v8';

    /**
     * @var Client;
     */
    private $client;

    /**
     * @var SerializerInterface
     */
    private $serializer;

    /**
     * @var string
     */
    private $api_key;

    /**
     * TogglApiClient constructor.
     * @param Client $client
     * @param SerializerInterface $serializer
     * @param $api_key
     */
    public function __construct(Client $client, SerializerInterface $serializer, $api_key)
    {
        $this->client = $client;
        $this->serializer = $serializer;
        $this->api_key = $api_key;
    }

    /**
     * @return array|Workspace[]
     */
    public function getWorkspaces()
    {
        $response = $this->client->request('GET', self::VERSION . '/workspaces', [
            'auth' => [$this->api_key, 'api_token'],
        ]);

        return $this->serializer->deserialize($response->getBody(), 'array<Syncer\Dto\Toggl\Workspace>', 'json');
    }

    /**
     * @return \Syncer\Dto\Toggl\WorkspaceClient[]
     */
    public function getWorkspaceClients(
        Workspace $workspace
    ) {
        return $this->getRequest('/workspaces/' . $workspace->getId() . '/clients', 'array<Syncer\Dto\Toggl\WorkspaceClient>');
    }

    /**
     * @return \Syncer\Dto\Toggl\WorkspaceProject[]
     */
    public function getWorkspaceProjects(
        Workspace $workspace
    ) {
        return $this->getRequest('/workspaces/' . $workspace->getId() . '/projects', 'array<Syncer\Dto\Toggl\WorkspaceProject>');
    }

    /**
     * @param Workspace $workspace
     * @return \Syncer\Dto\Toggl\WorkspaceUser[]
     */
    public function getWorkspaceUsers(
        Workspace $workspace
    ) {
        return $this->getRequest('/workspaces/' . $workspace->getId() . '/users', 'array<Syncer\Dto\Toggl\WorkspaceUser>');
    }

    protected function getRequest(
        $url,
        $type
    ) {
        $response = $this->client->request('GET', self::VERSION . $url, [
           'auth' => [$this->api_key, 'api_token']
        ]);

        return $this->serializer->deserialize($response->getBody(), $type, 'json');
    }
}
