# Laravel Api CRUD Generator

Laravel Api Crud Generator with Creator: { Model, Controller, Request, Resource, Migrations, Api Route Resource}

# Requirements
- PHP version: 8.*
- Laravel Version: 9.*

## Maim Features
- You can pass database column via cli console, that will reflact to migration, model and request file.

## Key Features
1. Controller with Resource and Repo Pattern
2. Model With Dynamic Field Property
3. Request With Dynamic Field Validation
4. Resource With Dynamic Data return
5. Migration With latest Format and dynamic blueprint table
6. resource route name in routes/api.php

## Installation
- Copy CrudApi file to namespace **App\Console\Commands**:
- Copy BaseController file to namespace **App\Http\Controllers**:
- Copy stubs files to namespace **resources/stubs**:
- Copy Repository files to namespace **App/Repositories**:
- should reflact like Below image

![repo](https://user-images.githubusercontent.com/29918977/215754442-aa988d7b-1db0-41d3-a075-d90f058681b5.png)
![Screenshot 2023-01-31 173332](https://user-images.githubusercontent.com/29918977/215751926-bafba18e-f776-4593-90f3-e290479c6139.png)


## Uses

```sh
php artisan crud:api Category

```

- Want to pass fillable fields? provide flag --f

```sh
php artisan crud:api Category --f

```
![sec](https://user-images.githubusercontent.com/29918977/215752607-a093a386-1536-41b3-939f-041f4fe26970.png)


## Output

### App\Http\Controllers\Api

``` php
<?php

namespace App\Http\Controllers\Api;

use App\Http\Requests\CategoryRequest;
use App\Models\Category;
use App\Http\Resources\CategoryResource;
use App\Http\Controllers\BaseController;
use App\Repositories\CommonRepository;
use Illuminate\Support\Facades\DB;
use JetBrains\PhpStorm\Pure;
use Illuminate\Http\JsonResponse;
use Throwable;



class CategoryController extends BaseController
{
    protected CommonRepository $repository;

    #[Pure] public function __construct(protected Category $category)
    {
        $this->repository = new CommonRepository($category);
    }

    public function index(): JsonResponse
    {
     $categories = CategoryResource::collection($this->repository->getAll());
     return $this->respondWithResourceCollection($categories);
    }

    public function store(CategoryRequest $request): mixed
    {
        try {
            $validated = $request->validated();
            return DB::transaction(function () use ($request, $validated) {
                $this->repository->create($validated);
                return $this->respondSuccess('Category created successfully');
            });
        } catch (Throwable $e) {
            return $this->respondError($e->getMessage());
        }
    }

    public function show(Category $category): JsonResponse
    {
        $data = new CategoryResource($category);
        return $this->respondSuccess($data);
    }

    public function update(CategoryRequest $request, Category $category): mixed
    {
        try {
            $validated = $request->validated();
            return DB::transaction(function () use ($validated, $category) {
                $category->update($validated);
                return $this->respondSuccess('Category Updated successfully');
            });
        } catch (Throwable $e) {
            return $this->respondError($e->getMessage());
        }
    }


    public function destroy(Category $category): JsonResponse
    {
        try {
            $category->delete();
            return $this->respondSuccess('Category Deleted successfully');
        } catch (\Exception $e) {
            return $this->respondError($e->getMessage());
        }
    }
}

```
### App/Model
```
<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Category extends Model
{
    protected $guarded = ['id'];
     protected $fillable = ['name','email'];
}
```

### App/Http/Request

```
<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class CategoryRequest extends FormRequest
{
    public function authorize()
    {
        return true;
    }

    public function rules()
    {
        return [
            'name' => 'nullable|string|max:255', 
            'email' => 'required|string|email|max:255',
            ];
    }
}
```
### App/Http/Resource

```
<?php

namespace App\Http\Resources;

use App\Models\Category;
use Illuminate\Http\Resources\Json\JsonResource;
use Illuminate\Support\Arr;

class CategoryResource extends JsonResource
{
    public function toArray($request): array
    {
        return $this->getFillableRecord($this);
    }

    public function getFillableRecord($item): array
    {
        $data = [];
        foreach ($this->getModelData() as $field) {
            $data[$field] = $item->{$field} ?? '';
        }
        return $data;
    }

    public function getModelData(): array
    {
        $role = new  Category();
        $fillable = array_merge($role->getFillable(), ($this->getIncludeFields()));
        return Arr::except($fillable, array_flip($this->getExceptFields()));
    }

    public function getIncludeFields(): array
    {
        return [];
    }

    public function getExceptFields(): array
    {
        return [];
    }
}

```

### routes/api.php
```
Route::apiResource('categories', 'CategoryController');
```
### database/migrations
```
<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('categories', function (Blueprint $table) {
            $table->id();
            $table->string('name');
			$table->string('email');
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::dropIfExists('categories');
    }
};
```

### Issues with coppying Files??
**Package Discovering Soon**

- [x] # Smile😃
> Eat
> Sleep
> Code
> Repeat





