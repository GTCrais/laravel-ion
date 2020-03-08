# Ion - On-the-fly generated CMS for Laravel 7

Ion is automatically generated CMS whose functionalities are based on a configuration file where you can define models and their relations (soon), model properties, property types, 
and many more options needed to quickly build a powerful, scalable CMS which then you can modify with ease at any time.

## Documentation

(soon) Documentation for Ion can be found on the [here.](https://ion.tmweb.dev/docs)

## Installation

- run `composer required gtcrais/laravel-ion:dev-master`
- run `php artisan ion:install` and follow the few quick installation steps

## Usage

> ##### This is a brief overview. Full documentation will be available soon.

Ion will be available at `/admin` address. If you created the default admin during the installation, you will be able to log in with the following credentials:
```
admin@app.test
admin
```
Otherwise, you'll be able to log in with any user in your database.

Check out `/config/ion` folder for configuration options.

Check out `/app/Ion/Entities` folder for Entity classes. Make sure the `User` Entity extends your `User` Model.

## To-do

###### Index types

- [x] List type + drag-n-drop ordering (paginated models)
- [x] Tree type + drag-n-drop ordering (nested models)
- [x] Key-value type

###### Entities (Models...)
- [x] Creating
- [x] Updating
- [x] Basic search
- [ ] Advanced search
- [ ] "With many" actions
- [ ] Sorting

###### Form fields
- [x] Text
- [x] Password
- [x] Checkbox
- [x] Textarea
- [x] WYSYWIG Editor - TinyMCE
- [x] Date
- [x] Select
- [x] Multiple Select
- [x] Image upload 
- [ ] File upload
- [ ] Date-time
- [ ] Time
- [ ] Other...

###### Relationships
- [ ] HasOne
- [ ] HasMany
- [ ] BelongsTo (semi-done)
- [ ] BelongsToMany (semi-done)
- [ ] MorphOne
- [ ] MorphMany
- [ ] MorphTo
- [ ] MorphToMany

###### Other
- [ ] Rewrite HTML + CSS
- [ ] Documentation
- [ ] `ion:entity` command
- [ ] Support for JWT auth out of the box
- [ ] Automated tests

I'm sure there are many more functionalities required for this package to really come together, but this is the initial roadmap.

## License

Ion is licensed under [MIT.](https://opensource.org/licenses/MIT)
