#Yii Admin

Admin panel based on [Yii 2](http://www.yiiframework.com/) and MongoDb.

You can manage catalog of movies, change posters, add movies to categories.
http://www.omdbapi.com/ is used as test API

Basic features:
- CRUD and bulk operations with movies and categories
- Infinite category tree. Any category or movie can be in any category without limits
- Poster operations: upload, download, remove
- In-line editing of text
- Seach by any field, sort by almost all fields
- Autocomplete for category tree when filter or add categories

## Installation

1. Clone or download this project.
2. Change MONGO_DB_URL for your database in .env file if needed.
3. Configurate you web server to `web` folder of this repository
4. Execute following command:

~~~
composer global require "fxp/composer-asset-plugin:^1.2.0"
composer update
php yii data/update
~~~

Now, you can see admin panel when you request url configured by webserver.

Panel use basic authentication, to login use this test credentials:
~~~
username = admin
password = admin
~~~

## Structure

Data consists of categories and movies

### Movies

Movie can have one of three statuses: New, Published or Removed
For new movies status always New. Then, you can change some fields of movie and click Publish. Movie status will be set to Published.
Publish button always change status to Publish and save movie fields.

Also, you can Remove movie. Movie will not remove from database, just status will be set to Removed and you will not see movie in Movie list.
Removed movies can be viewed with filtering by status = Removed.

You can add movie in any category. Than click Publish and movie will be added to category.
With bulk operations you can select a few movies and add all in one category.
Movie can be in a different categories.

### Categories

Categories doesn't have statuses. When you click Publish you just save fields. When you click Remove - category will be deleted after prompt.

You can add new category, then you can add category to another category.

Some bulk operations are available - remove categories, add categories to another category.

## MongoDb

All data stored in one collection - movies.
When field movies.movies exists, this document is a _category_. If not exists - is a _movie_.
movies.movies is array of ObjectId of movies or categories.

When api_name = root, this document is root for all categories.

Thereby, any movie can be in any category and any category can be in any category.

There is we have a problem with looping of keys. This problem also solved.
You can't loop this tree, looping will be checked before CRUD operations with tree.