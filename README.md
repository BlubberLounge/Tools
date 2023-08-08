<p align="center">
    <a href="https://laravel.com" target="_blank">
        <img src="http://media.maximilian-mewes.de/project/dwa/dart_logo_git.png" width="400">
    </a>
</p>

# 🔧 Welcome to Shisha-Tools 🌈

A collection of more or less useful shisha tools/management software written with Laravel

# ⚙️ Features

- User/Player Management
- Audit logging
- 

# 🏆 Goals of this Project

- [ ] Shisha Management System
- [x] User/Player Management
- [x] Dart (the game) tracking system
- [ ] Coal Calculator
- [ ] Tabacco Calculator

# 💭 Future ideas

- [ ] Fill section with `Future Ideas`


# 👪 Contributing

If you plan to submit a PR bigger than a simple change in one file, here is a short intro about how to do a clean PR. **greatly appreciated**.

If you have a suggestion that would make this better, please fork the repo and create a pull request. 
You can also simply open an issue with the tag "enhancement".
Don't forget to give the project a star! Thanks again!

1. Fork the Project locally via git clone `https://github.com/BlubberLounge/Shisha-Tools.git` (replacing `BlubberLounge` with your GitHub Username) and work on your local copy
2. Add the upstream repository via git remote add upstream `https://github.com/BlubberLounge/Shisha-Tools.git`
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
        <img src="http://media.maximilian-mewes.de/project/dwa/readme_dart_image_replace_with_different_one_later.jpeg" width="500">
    </a>
</p>

## How to install (Development)

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

# 📝 Notes


## ✌️ Other

Licensed under the  [MIT license](https://opensource.org/licenses/MIT).
