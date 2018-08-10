### General
---
- Manage Translation keys in the database

### Features
---
- Simple UI for key/value management via `/translation/` URI

- ReST endpoints to query translations

    - `/translation/ws/get-available-languages` to get all available languages from the database
    - `/translation/ws/get/{language}` to get an array of Key/Value of translations

- Twig function *(translate)* for twig translations

- Automatically extracts translation keys from TWIG files and save them in the database
```sh
app/console ats:translation:extract
```

- Caching mechanism (with automatic cache invalidation upon user input)