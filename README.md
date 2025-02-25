# Community Theme MTA
The community theme of more than ads is a WordPress theme that is used for the communities that are part of the more than ads community program.

## Requirements
Technical Requirements:
- PHP 8.0 or higher
- MySQL 5.6 or higher
- WordPress 6.0 or higher
- Composer

Wordpress Plugins:
- [Advanced Custom Fields](https://www.advancedcustomfields.com/)
- [Yoast SEO](https://yoast.com/wordpress/plugins/seo/#utm_content=plugin-info&utm_term=plugin-homepage&shortlink=1uj)
- [Simple Custom Post Order](https://wordpress.org/plugins/simple-custom-post-order/)

## How to install 
We use DDEV for local development. You can install DDEV by following the instructions [here](https://ddev.readthedocs.io/en/stable/).

Once you have DDEV installed, you can run the following commands to get the project up and running:

```bash
ddev start
ddev composer install
```

### Install the Database & Content
If there is content for an existing project, you can import the database by running the following command:

```bash
ddev ssh 
wp db import database.sql
```
You can put the `database.sql` file in the root of the project while importing. Please don't forget to remove it afterwards! 

#### Install Content (Images etc.)
If there are images existing, you can ask for the `uploads` folder and copy the contents to the `wp-content` folder.


### Hand over the databse to another developer
If you want to hand over the database to another developer, you can export the database by running the following command:

```bash
ddev ssh
wp db export
```
This will create a `your-database.sql` file in the root of the project. You can then hand this file over to the other developer. Please don't forget to remove it afterwards!


## Updates and Maintenance | Use of Branches
We use branches to manage updates and maintenance. The `main` branch is the production branch. The `develop` branch is the development branch. 

When you want to work on a new feature or bug fix, you should create a new branch from the `develop` branch. You can do this by running the following command:

```bash
git checkout -b feature/your-feature-name develop
```

When you are done with your feature or bug fix, you can create a pull request to merge your branch into the `develop` branch. Once the feature or bug fix has been tested and approved, we can merge the `develop` branch into the `main` branch.

### Staging Branch & Environment
We are running a staging environment for testing purposes. The staging environment is running on the `staging` branch. When we want to deploy changes to the staging environment, we can merge the `develop` branch into the `staging` branch.

Staging environment: [https://ctmta.morethanads.de/](https://ctmta.morethanads.de/)
Staging environment admin: [https://ctmta.morethanads.de/wp-admin/](https://ctmta.morethanads.de/wp-admin/)

### Production Branch & Environment
The production environment is running on the `main` branch. When we want to deploy changes to the production environment, we can merge the `staging` branch into the `main` branch.

There is no live production environment of MTA. The live production pages are the communitities that are running the theme. 

## How to update the theme
We are using the [wp update server](https://github.com/YahnisElsts/wp-update-server?tab=readme-ov-file) to update the theme. You can find the update server [here](https://update-server.morethanads.de/index.php). 
The theme update can be checked here: [https://update-server.morethanads.de/index.php?action=get_metadata&slug=communitythememta](https://update-server.morethanads.de/index.php?action=get_metadata&slug=communitythememta)

### How the theme update works
We have a github action that triggered when a new release on main branch is created. The action will create a zip file of the theme and upload it to the update server.

### ToDos before creating a new release
- Update the version number in the `style.css` file
- Create a new release on github (merge development to main branch)
– Update version number for enqueued scripts and styles in `functions.php`









