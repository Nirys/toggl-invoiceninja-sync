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

class SyncToggl extends Command {

    protected $signature = 'toggl:sync';

    protected $togglClient;
    /**
     * @var ReportsClient
     */
    private $reportsClient;

    function configure()
    {
        parent::configure();
        $this->addArgument('since', InputArgument::OPTIONAL, 'Sync time since', 'yesterday');
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
        $output->writeln("Syncing time since $syncTimeSince");

        /** @var Workspace $workspace */
        foreach ($workspaces as $workspace) {
            $users = $this->togglClient->getWorkspaceUsers($workspace);
            foreach($users as $user){
                $user->sync();
            }

            $projects = $this->togglClient->getWorkspaceProjects($workspace);
            foreach($projects as $project){
                $project->sync();
            }

            $clients = $this->togglClient->getWorkspaceClients($workspace);
            foreach($clients as $client){
                $client->sync();
            }

            /** @var DetailedReport $detailedReport */
            $detailedReport = $this->reportsClient->getDetailedReport($workspace->getId(), $input->getArgument('since'));

            foreach($detailedReport->getData() as $timeEntry) {
                $timeEntrySent = $timeEntry->sync();

                if ($timeEntrySent) {
                    $output->writeln('TimeEntry ('. $timeEntry->getDescription() . ') sent to InvoiceNinja');
                }
            }
        }
    }
}