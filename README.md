# d3files (inspired from d2files)


## Features

* attach files to model record
* widget for model view

## Installation
```bash
php composer.phar require d3yii2/d3files dev-master
```

 * add to config/web.php
```php
    'modules' => [
        'd3files' => [
            'class'      => 'd3yii2\d3files\D3Files',
            'upload_dir' => dirname(__DIR__) . '\upload\d3files',
        ],
    ],
```

* migration configuration. Add to console parameters migration path
```php
    'yii.migrations' => [
        '@vendor/d3yii2/d3files/migrations',
    ],
```

* do migration
```bash
yii migrate
```

## Usage
### VIEW
```php
    <?= d3yii2\d3files\widgets\D3FilesWidget::widget(
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

### Active Form

* to Active form model add propery for uploading file

```php

public $uploadFile;
```

* to Active model for new attribute add rule

```php
    public function rules() {
        return [
            ......,
            [
                ['uploadFile'],
                'file',
                'skipOnEmpty' => true,
                'extensions' => 'png, jpg, pdf, xls, doc'
            ],
        ];
    }
```

* in controller action after successful save() add

```php
$model->uploadFile = UploadedFile::getInstance($model, 'uploadFile');
D3files::saveYii2UploadFile($model->uploadFile, ModelName::className(), $model->id);
```

* in form to Active form set 'enctype' = 'multipart/form-data',

```php
$form = ActiveForm::begin([
                'id' => 'xxxxxxx',
                'layout' => 'horizontal',
                'enableClientValidation' => true,
                'options' => [
                    'enctype' => 'multipart/form-data',
                    ],
                ]
    );

```

* in form view add upload field

```php
echo $form->field($model, 'uploadFile')->fileInput();
```