# travel-notifier
Travel notifier for IRM

# Dependencies
- [IRM core](https://github.com/ItalianRockMafia/core)

# Installation
After you've installed irm-core, create a new folder in www root and copy the files into the directory (or clone this repo).
After that modify `config.php` in the www root (menu section);

```php
...
'menu' => array
	'Home' => 'HOME_URL',
	'Events' => 'URL_TO_SUBFOLDER'
... 
```