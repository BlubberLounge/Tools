<p align="center">
    <a href="https://github.com/BlubberLounge/Tools" target="_blank">
        <img src="https://media.blubber-lounge.de/project/dwa/dart_logo_git.png" width="400">
    </a>
</p>

## Welcome to BlubberLounge Tools 🔧

A collection of more or less useful management software written by the BlubberLounge Team ❤️‍🔥

## ⚙️ Features

- User/Player management
- Acces Request system
- Auditing (+ logging)
- Dart (game) management system
- Dart statistics
- Live Dart view
- Dart lobby management
- Notification system
- Device management
- Battery simulation
- User feedback system
- FAQ
- Api (+ OpenApi/Swagger Documentation)
- Minecraft tools (anvil enchantment planner)

## 💭 Future ideas

- [ ] TBD: fill with `Future Ideas`

## Screenshots

<p align="center">
    <img src="https://media.blubber-lounge.de/project/tools/blubberlounge-tools-home.png" width="400">
    <img src="https://media.blubber-lounge.de/project/tools/blubberlounge-tools-dart.png" width="400">
</p>

## 👪 Contributing

If you plan to submit a PR bigger than a simple change in one file, here is a short intro about how to do a clean PR. **greatly appreciated**.

If you have a suggestion that would make this better, please fork the repo and create a pull request. 
You can also simply open an issue with the tag "enhancement".
Don't forget to give the project a star! Thanks again!

1. Fork the Project locally via git clone `https://github.com/BlubberLounge/Tools.git` (replacing `BlubberLounge` with your GitHub Username) and work on your local copy
2. Add the upstream repository via git remote add upstream `https://github.com/BlubberLounge/Tools.git`
3. Ensure you are on the master branch via `git checkout master`
4. Create your Feature Branch by running `git checkout -b <temp_branch>`
5. Set the upstream branch via `git push --set-upstream origin <temp_branch>`
6. Work on your local and push as many commits as you want

#### When you think it is ready to merge and submit a PR:

1. Return to the master branch via `git checkout master`
2. Ensure your master branch is up-to-date with the latest changes via `git pull upstream master`
3. Update your fork of this repo by running `git push`
4. Create a new branch to be used for your PR via `git checkout -b <pr_branch>`
5. Set the upstream branch via `git push --set-upstream origin <pr_branch>`
6. Merge the edits but be sure to remove the history of your local commits via `git merge --squash <temp_branch> `
7. Create a new commit containing all of your merged changes via `git commit -m "<Message>"`

##### Now you have a clean single commit from which you can create the PR on the WLED Github.

<p align="center">
    <a href="https://laravel.com" target="_blank">
        <img src="http://media.blubber-lounge.de/project/dwa/readme_dart_image_replace_with_different_one_later.jpeg" width="500">
    </a>
</p>

## How to install (for development)

1. Clone this repo

    ```sh
    git clone https://github.com/BlubberLounge/Dart-WebApp.git Dart-WebApp
    ```

2. Install all dependencies
 
    ```sh
    composer install && npm install
    ```

3. Copy `.env.example`, Paste and Rename to `.env`. Create Database conenction. Configure as needed.

    ```txt
    Default DB config
    DB_CONNECTION=mysql
    DB_HOST=127.0.0.1
    DB_PORT=3306
    DB_DATABASE=dart_webapp
    DB_USERNAME=root
    DB_PASSWORD=
    ```

4. Database migration

    ```sh
    php artisan migrate
    ```

5. ( Start the Development Server )

    ```sh
    php artisan serve
    ```

## 📝 Notes

### Testing 🧪


Run tests sequentially with artisan

```sh
php artisan test
php artisan test --profile
php artisan test --coverage

OR

php artisan test --testsuite=Feature
php artisan test --testsuite=Unit
php artisan test --testsuite=Feature --stop-on-failure
```

Run tests in Parallel with artisan

```sh
php artisan test --parallel
php artisan test --parallel --processes=4

php artisan test --parallel --testsuite=Feature --processes=4
```

[Laravel 10.x Testing Documentation](https://laravel.com/docs/10.x/testing)

### Minecraft tools ⛏️

The sidebar group `Minecraft` and the overview page `/minecraft` are built from the registry in
`config/minecraft.php` (read by `App\Minecraft\ToolRegistry`).

**Adding a tool:** build its page and named route, add a permission (`PermissionsTableSeeder`,
`ConnectRelationshipsSeeder` and a migration for existing databases), then add one entry
(`id`, `title`, `description`, `icon`, `route`, `permission`) to `config/minecraft.php`. Entries
whose route does not exist are skipped, so unbuilt tools never show up.

**Anvil enchantment planner** (`/minecraft/anvil-planner`, permission `view.minecraft.anvil.planner`):

- Data: one JSON file per Java version in `resources/minecraft/enchantments/{version}.json`, written
  by hand from the Minecraft Wiki. Each file lists its sources with the retrieval date, the anvil
  constants (`level_cap`, `prior_work_penalty`, `incompatible_penalty`), XP sources (mob drops,
  smelting XP), exclusive sets, enchantments (max level, item and book multiplier, exclusive set,
  curse flag) and every survival-enchantable item with its allowed enchantments.
- Exclusive sets work like the game: two enchantments clash when one lists the other in its
  `exclusive_set` (Riptide excludes Loyalty and Channeling, which still go together).
- Adding a version: copy the newest file to `{version}.json`, set `version`, `name`, `released` and
  `sources`, apply the changes from the wiki and run
  `php artisan test tests/Unit/Minecraft` - `EnchantmentDataTest` validates every file (known
  fields, no unknown ids, no exclusive set with a single member). No code change is needed; the
  version picker lists all files, newest first.
- Planner: `App\Minecraft\AnvilPlanner` finds the cheapest order with a dynamic program over book
  subsets (every step at or below the level cap, prior-work penalty `2^n - 1`, item always in the
  left slot). When no order fits, it returns the cheapest order anyway with `possible: false` and
  the first step that is too expensive. `App\Minecraft\Experience` converts levels to XP points.
- API (session or Sanctum token, 60 requests per minute): `GET /api/v1/minecraft/versions`,
  `GET /api/v1/minecraft/versions/{version}`, `POST /api/v1/minecraft/anvil/plan` with
  `version`, `item`, `enchantments` (`{id: level}`), optional `present` and `prior_work`.

## ✌️ Other

Licensed under the  [MIT license](https://opensource.org/licenses/MIT).
