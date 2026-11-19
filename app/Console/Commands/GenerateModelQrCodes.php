<?php

namespace App\Console\Commands;

use App\Jobs\GenerateModelQrCode;
use Illuminate\Console\Command;
use Illuminate\Database\Eloquent\Model;

class GenerateModelQrCodes extends Command
{
    protected $signature = 'qrcodes:generate {model}';
    protected $description = 'Generate QR codes for a specified model';

    protected $namespaceMap = [
        'entity' => 'Domain\\Entities\\Models\\Entity',
        'individual' => 'Domain\\Individuals\\Models\\Individual',
    ];

    public function handle()
    {
        $modelName = strtolower($this->argument('model'));

        if (! array_key_exists($modelName, $this->namespaceMap)) {
            $this->error("Unsupported model: $modelName");

            return;
        }

        $modelClass = $this->namespaceMap[$modelName];

        if (! class_exists($modelClass)) {
            $this->error("Model $modelClass not found.");

            return;
        }

        $model = app($modelClass);

        if (! $model instanceof Model || ! method_exists($model, 'generateQrCode') || ! method_exists($model, 'qrCodePathField')) {
            $this->error("Model $modelClass does not support QR code generation.");

            return;
        }

        $model->newQuery()->whereNull($model->qrCodePathField())
            ->chunkById(100, function ($models) {

                foreach ($models as $model) {

                    GenerateModelQrCode::dispatch($model);

                }
            });

        $this->info("QR code generation jobs dispatched for $modelName.");
    }
}
