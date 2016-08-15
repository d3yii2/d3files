# d3files (inspired from d2files)


## Features

* attach files to model record (it is possible to attach one file to multiple models)
* widget for model view
* standalone actions to integrate in model's controllers to control access rights there
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

### Add actions to actual controller

```php
    /**
     * @inheritdoc
     */
    public function behaviors()
    {
        return [
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
                        'roles' => ['role1','role2],
                    ],
                ],
            ],
            'verbs' => [
                'class' => VerbFilter::className(),
                'actions' => [
                    'd3filedelete' => ['POST'],
                    'd3fileupload' => ['POST'],
                ],
            ],
        ];
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

```php

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
