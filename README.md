# d3files (inspired from d2files)  
[![Latest Stable Version](https://poser.pugx.org/d3yii2/d3files/v/stable)](https://packagist.org/packages/d3yii2/d3files)
[![Total Downloads](https://img.shields.io/packagist/dt/d3yii2/d3files.svg?style=flat-square)](https://packagist.org/packages/d3yii2/d3files) 
[![Latest Unstable Version](https://poser.pugx.org/d3yii2/d3files/v/unstable)](https://packagist.org/packages/d3yii2/d3files)
[![Dependency Status](https://www.versioneye.com/user/projects/586a414e49bf2b00437d42ba/badge.svg?style=flat-square)](https://www.versioneye.com/user/projects/586a414e49bf2b00437d42ba)
[![Code Climate](https://img.shields.io/codeclimate/github/d3yii2/d3files.svg)](https://codeclimate.com/github/d3yii2/d3files)
[![License](https://poser.pugx.org/d3yii2/d3files/license)](https://packagist.org/packages/d3yii2/d3files)

Extension for file uploading and attaching to the models

## Features

* attach files to model record (it is possible to attach to one model multiple files)
* widget for model view
* access rights realised as standalone actions (separate dowload, upload, delete) by integrating in model's controllers
* shared files for public access

## Installation
```bash
php composer.phar require d3yii2/d3files dev-master
```

 * add to config/web.php
```php
    'modules' => [
        'd3files' => [
            'class'              => 'd3yii2\d3files\D3Files',
            'uploadDir'          => dirname(__DIR__) . '\upload\d3files',
            'disableController'  => true,  // set true to disable d3files controller to use model's controllers
            'hashSalt'           => false, // Set salt in your web-local.php config, empty value will disable sharing
            'sharedExpireDays'   => 5,
            'sharedLeftLoadings' => 5,
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
### Widdget

Allow upload, download, delete files for model record.

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

### Acces control

For implementing access control add separate actions for upload, download and delete to model controller

```php
    /**
     * @inheritdoc
     */
    public function behaviors()
    {
        $addBehaviors = [
            'access' => [
                'class' => \yii\filters\AccessControl::className(),
                'only' => ['d3filesdownload', 'd3filesupload', 'd3filesdelete'],
                'rules' => [
                    // deny all POST requests
                    [
                        'allow' => true,
                        'actions' => [
                            'd3filesdownload',
                            'd3filesupload',
                            'd3filesdelete',
                        ],
                        'roles' => ['role1','role2'],
                    ],
                ],
            ],
            'verbs' => [
                'class' => \yii\filters\VerbFilter::className(),
                'actions' => [
                    'd3filedelete' => ['POST'],
                    'd3fileupload' => ['POST'],
                ],
            ],
        ];
        
        return array_merge(parent::behaviors(), $addBehaviors);
    }

    public function actions() {
        return [
            'd3filesdownload' => [
                'class' => 'd3yii2\d3files\components\DownloadAction',
                'modelName' => RkInvoice::className(),
            ],
            'd3filesupload'   => [
                'class' => 'd3yii2\d3files\components\UploadAction',
                'modelName' => RkInvoice::className(),
            ],
            'd3filesdelete'   => [
                'class' => 'd3yii2\d3files\components\DeleteAction',
                'modelName' => RkInvoice::className(),
            ],
            
        ];
    }

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

* in controller add use

```php
use d3yii2\d3files\models\D3files;
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

### Shared (public) access

* to create share implement share generation request in your code:

```php
//$id is D3filesModel model's ID
$share = D3filesModel::createSharedModel($id, $expireDays, $leftLoadings);
$shared_id   = $share['id'];
$shared_hash = $share['hash'];
```

* and use those variables to create url:
```php
$url = 'http://www.yoursite.com/index.php?r=d3files/d3files/downloadshare&id=' . $shared_id . '&hash=' . $shared_hash;
echo $url;
```

### Get record files list 

```php
use d3yii2\d3files\models\D3files;
$filesList = D3files::getRecordFilesList($model::className(),$model->id)
```

### Attach existing file to record

```php
$fileTypes = '/(gif|pdf|dat|jpe?g|png|doc|docx|xls|xlsx|htm|txt|log|mxl|xml|zip)$/i';
$model = Users::findOne($id);
$fileName = 'MyAvatar.jpg';
$filePath = '/temp/avatar.jpg';
D3files::saveFile($fileName, Users::className(), $model->id, $filePath, $fileTypes);
```

### Change log
 - 0.9.0 (Feb 26, 2017) - added RU translation
 - 0.9.2 (May 29, 2017) - auto creating upload directories 
