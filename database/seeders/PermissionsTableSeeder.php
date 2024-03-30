<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Str;
use Illuminate\Routing\Controller as BaseController;

use App\Http\Controllers\Api\v1\DartExpectationDataController as ApiDartExpectationDataController;
use App\Http\Controllers\Api\v1\DartGameController as ApiDartGameController;
use App\Http\Controllers\Api\v1\DartThrowController as ApiDartThrowController;
use App\Http\Controllers\Api\v1\UserController as ApiUserController;
use App\Http\Controllers\AppointmentController;
use App\Http\Controllers\DartController;
use App\Http\Controllers\DartGameController;
use App\Http\Controllers\FAQController;
use App\Http\Controllers\FeedbackController;
use App\Http\Controllers\HookahController;
use App\Http\Controllers\InvitationController;
use App\Http\Controllers\TobaccoController;
use App\Http\Controllers\UserController;


class PermissionsTableSeeder extends Seeder
{
    /**
     * Run the database seeds.
     *
     * @return void
     */
    public function run()
    {
        // permissions could also be build dynamically via:
        // dd(\Illuminate\Support\Facades\Route::getRoutes());

        /*
         * Permission Types
         *
         */
        $permissions = [
            //
        ];

        /*
         * Resource controller permissions
         *
         */
        $resourceControllers = [
            // api
            ApiDartExpectationDataController::class,
            ApiDartGameController::class,
            ApiDartThrowController::class,
            ApiUserController::class,

            // web
            AppointmentController::class,
            DartController::class,
            DartGameController::class,
            FAQController::class,
            FeedbackController::class,
            HookahController::class,
            InvitationController::class,
            TobaccoController::class,
            UserController::class,
        ];

        foreach ($resourceControllers as $resourceController)
            $this->addToPermissions($permissions, $this->buildResourceControllerPermissions($resourceController));


        /*
         * Api Controller Permissions
         */
         $this->addToPermissions($permissions, $this->getApiControllerPermissions());

         /*
         * Web Controller Permissions
         */
        $this->addToPermissions($permissions, $this->getWebControllerPermissions());

        /*
         * Add Permission Items
         *
         */
        foreach ($permissions as $item) {
            $newItem = config('roles.models.permission')::where('slug', $item['slug'])->first();

            if ($newItem === null)
                $newItem = config('roles.models.permission')::create([
                    'name'          => $item['name'],
                    'slug'          => $item['slug'],
                    'description'   => $item['description'],
                    // 'model'         => 'Permission',
                ]);
        }
    }

    /**
     *
     */
    protected function addToPermissions(array &$permissionList, array $newPermissions): void
    {
        foreach ($newPermissions as $permission)
            $permissionList[] = $permission;
    }

    /**
     *
     */
    private function buildResourceControllerPermissions(string $controller): array
    {
        $permissions = [];
        $availableMethods = [
            'viewAny'       => null,
            'view'          => null,
            'create'        => 'Create And Store',
            'update'        => 'Edit And Update',
            'delete'        => null,
            'restore'       => null,
            'forceDelete'   => null,
        ];

        $className = str_replace('Controller', '', class_basename($controller));
        $classNameHuman = Str::snake($className, ' ');
        $classNameSlug = Str::snake($className, '.');
        $isApiController = Str::contains($controller, 'Api');
        $className = $isApiController ? 'Api'.$className : $className;

        foreach ($availableMethods as $method => $verb) {
            $verb = $verb ?? Str::ucfirst($method);
            $permissions[] = [
                'name'          => 'Can '. $verb .' '. Str::plural($className),
                'slug'          => ($isApiController ? 'api.' : '') . $method .'.'. $classNameSlug,
                'description'   => 'Can '. Str::lower($verb) .' '. Str::plural($classNameHuman)
            ];
        }

        return $permissions;
    }

    /**
     *
     */
    private function getApiControllerPermissions(): array
    {
        return [
            [
                'name'          => 'Can Search Users',
                'slug'          => 'api.search.user',
                'description'   => 'Can search users',
            ],
            [
                'name'          => 'Can View Throws By Game',
                'slug'          => 'api.view.throws.by.game',
                'description'   => 'Can view throws by game',
            ],
            [
                'name'          => 'Can Update Dart Game Places',
                'slug'          => 'api.update.dart.game.place',
                'description'   => 'Can update dart game places',
            ],
            [
                'name'          => 'Can View Dart Game Throws',
                'slug'          => 'api.view.dart.game.throw',
                'description'   => 'Can view dart game throws',
            ],
            [
                'name'          => 'Can View Dart Game Player Status',
                'slug'          => 'api.view.dart.game.player.status',
                'description'   => 'Can view dart game player status',
            ],
            [
                'name'          => 'Can Delete Dart Game Player',
                'slug'          => 'api.delete.dart.game.player',
                'description'   => 'Can delete/kick players from dart games',
            ],
        ];
    }

    /**
     *
     */
    private function getWebControllerPermissions(): array
    {
        return [
            [
                'name'          => 'Can View Batteries',
                'slug'          => 'view.battery',
                'description'   => 'Can view batteries',
            ],
            [
                'name'          => 'Can View Moving Averages',
                'slug'          => 'view.moving.average',
                'description'   => 'Can view moving averages',
            ],
            [
                'name'          => 'Can View Live Dart Games',
                'slug'          => 'view.dart.game.live',
                'description'   => 'Can view live dart games',
            ],
            [
                'name'          => 'Can View Dart Infos',
                'slug'          => 'view.dart.info',
                'description'   => 'Can view dart infos',
            ],
            [
                'name'          => 'Can View Dart Checkouts',
                'slug'          => 'view.dart.checkouts',
                'description'   => 'Can view dart checkouts',
            ],
            [
                'name'          => 'Can View Dart Playgrounds',
                'slug'          => 'view.dart.playground',
                'description'   => 'Can view dart playgrounds',
            ],
            [
                'name'          => 'Can Update Users Language',
                'slug'          => 'update.user.language',
                'description'   => 'Can update users language',
            ],
            [
                'name'          => 'Can View Users Image',
                'slug'          => 'view.user.image',
                'description'   => 'Can View users image',
            ],
            [
                'name'          => 'Can Update User Invitations',
                'slug'          => 'update.user.invitation',
                'description'   => 'Can update user invitation',
            ],
            [
                'name'          => 'Can View Devices',
                'slug'          => 'viewany.device',
                'description'   => 'Can View devices',
            ],
            [
                'name'          => 'Can View Calculators',
                'slug'          => 'view.calculator',
                'description'   => 'Can view calculators',
            ]
        ];
    }
}
