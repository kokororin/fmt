Configuration for PHPStorm
==========================

## Loading as an External Tool

1 - Install php.tools, either through git or composer.

* Git

```sh
git clone https://github.com/phpfmt/fmt.git
```

* Composer

```sh
php composer.phar global require phpfmt/fmt
```

2 - Add php.tools as an External tool in PhpStorm : Open Settings (or “Preferences” on OS X) > External Tools and setup a new tool :

![phpstorm-configuration](https://raw.githubusercontent.com/phpfmt/php.tools/master/phpstorm-configuration.png)

Fill the fields with the following:

Program: `/full/path/to/php`

Parameters: `/full/path/to/fmt.php --no-backup "$FileDir$/$FileName$"`

(Note `--no-backup` prevents the creation of backup files `file~`, therefore not letting pollute the working directory).

Working directory: `$ProjectFileDir$`

Tested with PhpStorm 9.0.2 with `phpfmt v19.6.5`.

## Setting up a File Watcher (format on save)

1 - Install php.tools, either through git or composer.

* Git

```sh
git clone https://github.com/phpfmt/fmt.git
```

* Composer

```sh
php composer.phar global require phpfmt/fmt
```

2 - Set up a File Watcher : Open Settings (or “Preferences” on OS X) > Tools > File Watchers and setup a new Watcher :

![phpstorm-configuration](https://raw.githubusercontent.com/phpfmt/fmt/master/phpstorm-file-watcher-configuration.png)

Fill the fields with the following:

Program: `/full/path/to/php`

Arguments: `/full/path/to/fmt.php --no-backup "$FileDir$/$FileName$"`

(Note `--no-backup` prevents the creation of backup files `file~`, therefore not letting pollute the working directory).

Working directory: `$ProjectFileDir$`

Output paths to refresh: `$FileDir$/$FileName$`

Tested with PhpStorm 10.0.0.3 with `phpfmt v15.1.1`, [consider putting a keyboard shortcut to make it easier](https://www.jetbrains.com/phpstorm/help/configuring-keyboard-shortcuts.html).
