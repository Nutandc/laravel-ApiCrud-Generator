<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use Illuminate\Support\Facades\File;
use Illuminate\Support\Str;

class CrudApi extends Command
{
    protected $signature = 'crud:api
    {name : Class (singular) for example User}
    {--f : Need Model Value.}';

    protected $description = 'Create ApiCRUD operations';
    private string $modelName;
    private string $modelPath = 'Models';
    private array $fillable = [];

    public function handle()
    {
        if ($this->option('f')) {
            $fields = $this->ask('Enter the fields seperated by whitespace');
            $this->fillable = explode(' ', $fields);
        }
        $this->modelName = ucfirst($this->argument('name'));
        $bool = $this->verifyExistingModel();
        if ($bool) {
            $this->createMigration();
            $this->createModel();
            $this->createRequest();
            $this->createResource();
            $this->createController();
            $this->addRouteName();
        } else
            $this->components->line('info', "{$this->modelName} model is already exists.");

    }

    private function verifyExistingModel(): bool
    {
        if ($this->modelExists())
            return $this->confirm("{$this->modelName} model is already exists. Do you want to override it?");
        return true;
    }

    public function modelExists(): bool
    {
        return file_exists(app_path($this->modelPath . '/' . $this->modelName . '.php'));
    }

    protected function createMigration()
    {
        $tables = $this->generateDatabaseColumns();
        $filename = date('Y_m_d_His') . '_create_' . strtolower(Str::plural($this->modelName)) . '_table.php';
        $modelTemplate = str_replace(
            ['{{modelNamePluralLowerCase}}', '{{tables}}'],
            [strtolower(Str::plural($this->modelName)), $tables],
            $this->getStub('Migration')
        );
        $basePath = database_path("migrations/$filename");
        file_put_contents($basePath, $modelTemplate);
        $this->components->info(sprintf('%s [%s] created successfully.', 'Migration', $basePath));

    }

    private function generateDatabaseColumns(): string
    {
        $table = '';
        foreach ($this->fillable as $field) {
            $table .= "\$table->string('$field');\n\t\t\t";
        }
        return $table;
    }

    protected function getStub($type): bool|string
    {
        return file_get_contents(resource_path("stubs/$type.stub"));
    }

    protected function createModel()
    {
        $modelTemplate = str_replace(
            ['{{modelName}}', '{{fillable}}'],
            [$this->modelName, implode("','", $this->fillable)],
            $this->getStub('Model')
        );
        $basePath = app_path("Models/{$this->modelName}.php");
        file_put_contents($basePath, $modelTemplate);
        $this->components->info(sprintf('%s [%s] created successfully.', 'Model', $basePath));

    }

    protected function createRequest()
    {
        $rules = $this->generateRequestValidationRules();
        $requestTemplate = str_replace(
            ['{{modelName}}', '{{validations}}'],
            [$this->modelName, $rules],
            $this->getStub('Request')
        );
        if (!file_exists($path = app_path('/Http/Requests')))
            mkdir($path, 0777, true);
        $basePath = app_path("/Http/Requests/{$this->modelName}Request.php");
        file_put_contents($basePath, $requestTemplate);
        $this->components->info(sprintf('%s [%s] created successfully.', 'Request', $basePath));
    }

    private function generateRequestValidationRules(): string
    {

        $rule = '';
        foreach ($this->fillable as $field) {
            match ($field) {
                'email' => $rule .= "'$field' => 'required|string|email|max:255',\n\t\t\t",
                'password' => $rule .= "'$field' => 'required|string|min:8',\n\t\t\t",
                default => $rule .= "'$field' => 'nullable|string|max:255',\n\t\t\t",
            };
        }
        return $rule;
    }

    private function createResource()
    {
        $requestTemplate = str_replace(
            ['{{modelName}}'],
            [$this->modelName],
            $this->getStub('Resource')
        );
        if (!file_exists($path = app_path('/Http/Resources')))
            mkdir($path, 0777, true);
        $basePath = app_path("/Http/Resources/{$this->modelName}Resource.php");
        file_put_contents($basePath, $requestTemplate);
        $this->components->info(sprintf('%s [%s] created successfully.', 'Resource', $basePath));
    }

    protected function createController()
    {
        $controllerTemplate = str_replace(
            ['{{modelName}}', '{{modelNamePluralLowerCase}}', '{{modelNameSingularLowerCase}}'],
            [$this->modelName, strtolower(Str::plural($this->modelName)), strtolower($this->modelName)],
            $this->getStub('Controller')
        );
        if (!file_exists($path = app_path('/Http/Controllers/Api')))
            mkdir($path, 0777, true);
        $basePath = app_path("/Http/Controllers/Api/{$this->modelName}Controller.php");
        file_put_contents($basePath, $controllerTemplate);
        $this->components->info(sprintf('%s [%s] created successfully.', 'Controller', $basePath));
    }

    private function addRouteName()
    {
        File::append(base_path('routes/api.php'), 'Route::apiResource(\'' . Str::plural(strtolower($this->modelName)) . "', '{$this->modelName}Controller');\n");
        $this->components->info(sprintf('%s [%s] created successfully.', 'Route', public_path('routes/api.php')));
    }
}
