# d3files (inspired from d2files)  
[![Yii2](https://img.shields.io/badge/Powered_by-Yii_Framework-green.svg?style=flat)](https://www.yiiframework.com/)
[![Latest Stable Version](https://poser.pugx.org/d3yii2/d3files/v/stable)](https://packagist.org/packages/d3yii2/d3files)
[![Total Downloads](https://img.shields.io/packagist/dt/d3yii2/d3files.svg?style=flat-square)](https://packagist.org/packages/d3yii2/d3files) 
[![Latest Unstable Version](https://poser.pugx.org/d3yii2/d3files/v/unstable)](https://packagist.org/packages/d3yii2/d3files)
[![Dependency Status](https://www.versioneye.com/user/projects/586a414e49bf2b00437d42ba/badge.svg?style=flat-square)](https://www.versioneye.com/user/projects/586a414e49bf2b00437d42ba)
[![License](https://poser.pugx.org/d3yii2/d3files/license)](https://packagist.org/packages/d3yii2/d3files)

Extension for file uploading and attaching to the models

## Features

* attach files to model record (it is possible to attach to one model multiple files)
* widget for model view
* access rights realised as standalone actions (separate download, upload, delete) by integrating in model's controllers
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
            'disableController'  => false,  // set true to disable d3files controller to use model's controllers
            'hashSalt'           => false, // Set salt in your web-local.php config, empty value will disable sharing
            'sharedExpireDays'   => 5,
            'sharedLeftLoadings' => 5,
            'controllerRoute'    => 'delivery/attachments', //define controler route, where defined  
        ],
    ],
```

* migration configuration. Add to console migration definition path
```php
    'controllerMap' => [
           'migrate' => [
               'class' => 'yii\console\controllers\MigrateController',
               'migrationPath' => [
                   '@d3yii2/d3files/migrations',
               ],
           ],
```

* do migration
```bash
yii migrate
```

## Usage
### Widget

Allow upload, download, delete files for model record.

```php
    <?= d3yii2\d3files\widgets\D3FilesWidget::widget(
        [
            'model'     => $model,
            'model_id'  => $model->id,
            'title'     => 'Widget Title',
            'icon'      => false,
            'hideTitle' => false,
            'readOnly'  => false,
            //'viewByFancyBox' => false,
            //'controllerRoute'=>'/d3emails/email/', //use if different controllers
            // 'actionColumn' => static function ($row) {
            //    return 'OK';
            //} 
            
        ]
    ) ?>
```


### D3FilesPreviewWidget
Extends D3FilesWidget with file preview.
```php
    <?= d3yii2\d3files\widgets\D3FilesPreviewWidget::widget(
        [
            'model' => $model,
            'model_id' => $model->id,
            'title' => 'Preview Widget Title',
            'readOnly'  => false,
        ]
    )
    ?>
```
File types are determined automatically to load PDF or images inside modal dialog window.

###
Dependencies:

eaBlankonThema\widget\ThModal

d3system\yii2\web\D3SystemView

Main layout should have footer code:
```php
<?php
 if ($footer = $this->getPageFooter()): ?>
       <?= $footer ?>
<?php endif; ?>
=======
### Widget

Allow upload, download, delete files for model record.

```php
    <?= d3yii2\d3files\widgets\D3FilesPreviewWidget::widget(
        [
            'model'     => $model,
            'model_id'  => $model->id,
            'title'     => 'Widget Title',
            'readOnly'  => false,
        ]
    ) ?>

```

### Access control

In config disableController set true for disabling use d3files controller, where no realised any access control.
```php
    'modules' => [
        'd3files' => [
            ....
            'disableController'  => true,  // set true to disable d3files controller to use model's controllers
            .....
        ],
    ],
```

For implementing access control add separate actions for upload, download and delete to model controller. Can implement any standard Yii2 access control. For example RBAC. 

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
                            'd3filesopen', 
                            'd3filesupload',
                            'd3filesdelete',
                            'd3fileseditnotes'
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
                'class' => d3yii2\d3files\components\DownloadAction::class,
                'modelName' => D3pPerson::class,
            ],
            'd3filesupload'   => [
                'class' => d3yii2\d3files\components\UploadAction::class,
                'modelName' => D3pPerson::class,
            ],
            'd3filesdelete'   => [
                'class' => \d3yii2\d3files\components\DeleteAction::class,
                'modelName' => D3pPerson::class,
            ],
            // for D3FilesPreviewWidget
            'd3filesopen' => [
                'class' => Dd3yii2\d3files\components\ownloadAction::class,
                'modelName' => D3pPerson::class,
                'downloadType' => 'open'
            ],        
            // for D3FilesPreviewWidget
            'd3filesopen' => [
                'class' => 'd3yii2\d3files\components\DownloadAction',
                'modelName' => RkInvoice::class,
                'downloadType' => 'open'
            ],         
            // for D3FilesPreviewWidget
            'd3fileseditnotes' => [
                'class' => EditNotesAction::class,
                'modelName' => D3pPerson::class,
            ],   
        ];
    }
```

Widget
```php
d3yii2\d3files\widgets\D3FilesWidget::widget(
    [
        'model' => $model,
        'model_id' => $model->id,
        'title' => 'Attachments',
        'icon' => false,
        'hideTitle' => false,
        'readOnly' => false
    ]
)
```

Preview widget (load in modal dialog window)
```php
d3yii2\d3files\widgets\D3FilesPreviewWidget::widget([
    'model' => $model,           // Model to load attachment(s) from
    'fileList' => [...]          // Resulting array of the ModelD3Files::fileListForWidget if the attachments are joined already. Required if model not specified.
    'defaultExtension' => 'pdf', // Optional (PDF by default),
    'viewExtensions' => ['pdf']  // Optional.- (['pdf', 'png', 'jpg', 'jpeg'] by default)
])
```

For the PDF files server should support iframes. If the files are not loading
check for the x-frame-options in the respose headers. 
In case: 
```php
x-frame-options: deny
``` 
You can set set in webserver configuration:
```php
X-Frame-Options:SAMEORIGIN
```
Or one of the directives in .htaccess
```php
X-Frame-Options: sameorigin
```
```php
X-Frame-Options: "allow-from https://example.com/"
```

### Active Form

* to Active form model add property for uploading file

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

### Get record files with full path 
```php 
    public static function getRecordFiles(string $className, int $recordId): array
    {
        $files = [];
        foreach (D3Files::getModelFilesList($className, $recordId, true) as $file) {
            $fileHandler = new FileHandler([
                'model_name' => $file['className'],
                'model_id' => $file['id'],
                'file_name' => $file['file_name']
            ]);
                $files[] = [
                    'fileName' => $file['file_name'],
                    'filePath' => $fileHandler->getFilePath(),
                ];
        }
        return $files;
    }
```


### Attach existing file to record

```php
$fileTypes = '/(gif|pdf|dat|jpe?g|png|doc|docx|xls|xlsx|htm|txt|log|mxl|xml|zip)$/i';
$model = Users::findOne($id);
$fileName = 'MyAvatar.jpg';
$filePath = '/temp/avatar.jpg';
D3files::saveFile($fileName, Users::className(), $model->id, $filePath, $fileTypes);
```

### Attach existing content to record as attachment
```php
        D3files::saveContent(
            'import-' . date('YmdHis') . '.csv',
            get_class($voyage),
            $voyage->id,
            $this->csv,
            '/(csv|txt)$/i',
            Yii::$app->user->id
        );
```

### Attach already saved file to record

```php
$model = Users::findOne($id);
D3filesModel::createCopy($fileModelId, Users::class, $model->id);
```

### Maintenance commands

Soft deletes files (sets deleted=1)
For model dektrium\user\models\User soft delete from database for model dektrium\user\models\User oldest as 12 months files with extension xml  
```bash
yii d3files/clean-files/remove-older-than 'dektrium\user\models\User' 12 '%.xml'
```

Deletes files and corresponding records in database which have deleted=1
 ```bash
yii d3files/clean-files/remove-files 'dektrium\user\models\User'
```

Deletes model files from filesystem with out refence in database
 ```bash
yii d3files/clean-files/unused-files  'poker\poker\models\PkPlaygroundFixes'

```

### Change log
 - 0.9.0 (Feb 26, 2017) - added RU translation
 - 0.9.3 (May 29, 2017) - auto creating upload directories
 - 0.9.4 (Nov 16, 2017) - added parameter controllerRoute  
 - 0.9.13 (Jul 2, 2018) - added action column
 - 0.9.93 (febr 10, 2022) - added controller d3files/clean-files for maintenance 
 - 0.9.97 (jun 28, 2022) - improved maintanance controller d3files/clean-files
