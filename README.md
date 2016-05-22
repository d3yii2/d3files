# d2files (inspired from d2files)


## Features

* attach files to model record
* widget for model view

## Installation
```bash
php composer.phar require d3yii2/d2files dev-master
```

 * add to config/web.php
```php
    'modules' => [
        'd3files' => [
            'class'      => 'app\vendor\d3yii2\d3files\D3Files',
            'upload_dir' => dirname(__DIR__) . '\upload\d3files',
        ],
    ],
```

## Usage
### VIEW
```php
    <?= app\vendor\d3yii2\d3files\widgets\D3FilesWidget::widget(
        [
            'model'     => $model,
            'model_id'  => $model->id,
            'title'     => 'Wiget Title',
            'icon'      => false,
            'hideTitle' => false,
            'readOnly'  => false
        ]
    ) ?>
```
