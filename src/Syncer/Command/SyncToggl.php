<?php
namespace Syncer\Command;

use Illuminate\Console\Command;
use Symfony\Component\Console\Input\InputArgument;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;
use Syncer\Dto\Toggl\DetailedReport;
use Syncer\Dto\Toggl\TimeEntry;
use Syncer\Dto\Toggl\Workspace;
use Syncer\InvoiceNinja\Mapper;
use Syncer\Toggl\ReportsClient;
use Syncer\Toggl\TogglApiClient;

class SyncToggl extends Command
{
    protected $signature = 'toggl:sync';

    protected $description = 'Sync time from Toggl to tasks';

    protected $togglClient;
    /**
     * @var ReportsClient
     */
    private $reportsClient;

    public function configure()
    {
        parent::configure();
        $this->addArgument('since', InputArgument::OPTIONAL, 'Sync time since', 'yesterday');
        $this->addArgument('until', InputArgument::OPTIONAL, 'Sync time until', 'now');
    }

    public function __construct(
        TogglApiClient $togglClient,
        ReportsClient $reportsClient
    ) {
        $this->togglClient = $togglClient;
        parent::__construct();
        $this->reportsClient = $reportsClient;
    }

    public function execute(InputInterface $input, OutputInterface $output)
    {
        $workspaces = $this->togglClient->getWorkspaces();

        if (!is_array($workspaces) || count($workspaces) === 0) {
            $output->writeln('No workspaces to sync.');
            return;
        }

        $syncTimeSince = $input->getArgument('since');
        $syncTimeUntil = $input->getArgument('until');
        $output->writeln("Syncing time since $syncTimeSince until $syncTimeUntil");

        /** @var Workspace $workspace */
        foreach ($workspaces as $workspace) {
            $users = $this->togglClient->getWorkspaceUsers($workspace);
            foreach ($users as $user) {
                $user->sync();
            }

            $projects = $this->togglClient->getWorkspaceProjects($workspace);
            foreach ($projects as $project) {
                $project->sync();
            }

            $clients = $this->togglClient->getWorkspaceClients($workspace);
            foreach ($clients as $client) {
                $client->sync();
            }

            $timeEntries = $this->getAllTime($workspace->getId(), $syncTimeSince, $syncTimeUntil);

            foreach ($timeEntries as $timeEntry) {
                $timeEntrySent = $timeEntry->sync();

                if ($timeEntrySent) {
                    $output->write('TimeEntry ('. $timeEntry->getDescription() . ') sent to InvoiceNinja...');
                    // We synced successfully, so tag the entry
                    $entry = $this->togglClient->get('time_entries/' . $timeEntry->getUid());
                    if (!property_exists($entry, 'tags')) {
                        $entry->tags = [];
                    }
                    $entry->tags[] = 'Imported';
                    $entry->tags[] = 'Ninja';
                    $response = $this->togglClient->put('time_entries/' . $timeEntry->getUid(), ['time_entry' => $entry ]);
                    $output->writeln("tagged");
                } else {
                    $output->writeln('ERROR: Unable to sync ' . $timeEntry->getDescription());
                }
            }
        }
    }

    protected function getAllTime(
        $workspace,
        $from,
        $to
    ) {
        $page = 1;
        $detailedReport = $this->reportsClient->getDetailedReport($workspace, $from, $to, $page);
        $timeEntries = $detailedReport->getData();

        if ($detailedReport->getTotalCount() >= $detailedReport->getPerPage()) {
            $totalRecords = $detailedReport->getTotalCount();
            $perPage = $detailedReport->getPerPage();
            $totalPages = floor($totalRecords/$perPage) + 1;
            while ($page < $totalPages) {
                $page++;
                $detailedReport = $this->reportsClient->getDetailedReport($workspace, $from, $to, $page);
                $timeEntries = array_merge($timeEntries, $detailedReport->getData());
            }
        }
        return $timeEntries;
    }
}
