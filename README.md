# Upload helper

Manage - filter, validate and upload files.

## Example

```php
use Zend\Http\PhpEnvironment\Request;
use UploadHelper\Upload;


$files              = (new Request())->getFiles();                  // Return all fimes from $_FILE
$public_path        = '/var/www/website/public/uploads';            // better read it from config
$non_public_path    = '/var/www/website/data/uploads';
$upload             = new Upload($public_path, $non_public_path);   // Build upload object

$image = $upload->filterImage($files, 'image_name');  // image_name is the name from HTML form file input
$name  = $upload->uploadFile($image, 'image_name');
$path  = $upload->getWebPath($name);

var_dump($name, $path);
```

Will display:

32f45151a816ffe96d571964f64faa20.png                    // File name

/uploads/3/2/f/32f45151a816ffe96d571964f64faa20.png     // Path to file
