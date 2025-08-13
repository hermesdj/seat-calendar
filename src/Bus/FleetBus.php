<?php

namespace Seat\Kassie\Calendar\Bus;

use Illuminate\Bus\Batch;
use Seat\Eveapi\Bus\Bus;
use Seat\Eveapi\Models\RefreshToken;
use Seat\Kassie\Calendar\Jobs\FleetInfoJob;
use Seat\Kassie\Calendar\Jobs\FleetMembersJob;
use Seat\Kassie\Calendar\Models\Operation;
use Throwable;

class FleetBus extends Bus
{
    protected ?RefreshToken $token;

    private int $operation_id;

    public function __construct(int $operation_id, RefreshToken $token)
    {
        parent::__construct($token);
        $this->token = $token;
        $this->operation_id = $operation_id;
    }

    /**
     * @throws Throwable
     */
    public function fire(): void
    {
        $this->addPublicJobs();
        $this->addAuthenticatedJobs();

        $operation = Operation::find($this->operation_id);

        \Illuminate\Support\Facades\Bus::batch([$this->jobs->toArray()])
            ->then(function (Batch $batch) {
                logger()->debug(
                    sprintf('[FleetBus][%s] started pap fleet batch process for operation %s', $batch->id, $this->operation_id),
                    [
                        'id' => $batch->id,
                        'name' => $batch->name,
                    ]
                );
            })->catch(function (Batch $batch, Throwable $throwable) {
                logger()->error(
                    sprintf('[FleetBus][%s] An error occurred during pap fleet batch processing for operation %s', $batch->id, $this->operation_id),
                    [
                        'id' => $batch->id,
                        'name' => $batch->name,
                        'error' => $throwable->getMessage(),
                        'trace' => $throwable->getTrace(),
                    ]);
            })->finally(function (Batch $batch) {
                logger()->info(
                    sprintf('[FleetBus][%s] Pap fleet batch executed.', $batch->id),
                    [
                        'id' => $batch->id,
                        'name' => $batch->name,
                        'stats' => [
                            'success' => $batch->totalJobs - $batch->failedJobs,
                            'failed' => $batch->failedJobs,
                            'total' => $batch->totalJobs,
                        ],
                    ]);
            })->onQueue('high')->name($operation->title)->dispatch();
    }

    protected function addPublicJobs()
    {
        // No public job needed
    }

    protected function addAuthenticatedJobs(): void
    {
        $this->jobs->add(new FleetInfoJob($this->operation_id, $this->token));
        $this->jobs->add(new FleetMembersJob($this->operation_id, $this->token));
    }
}
