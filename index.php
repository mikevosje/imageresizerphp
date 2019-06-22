<?php
require __DIR__ . '/vendor/autoload.php';
use Intervention\Image\ImageManagerStatic as Image;
use Google\Cloud\Storage\StorageClient;

Image::configure();
$storage = new StorageClient([
    'keyFile' => json_decode(file_get_contents('keyfile.json'), true)
]);
$bucket = $storage->bucket('auctioncliq');

@ini_set('upload_max_size', '10M');
@ini_set('post_max_size', '12M');
header("Access-Control-Allow-Origin: *");
header("Access-Control-Allow-Headers: *");
header('Access-Control-Allow-Methods: POST, GET, OPTIONS');
$target_dir = "";
$uploadOk = 0;
if ($_FILES) {
    $newname = basename($_FILES["fileToUpload"]["name"]);
    if (isset($_POST['newnamewithextension']) &&  $_POST['newnamewithextension']) {
        $newname = $_POST['newnamewithextension'];
    }
    $target_file = $target_dir . $newname;
    $filename = pathinfo($target_file, PATHINFO_FILENAME);
    $uploadOk = 1;
    $imageFileType = strtolower(pathinfo($target_file, PATHINFO_EXTENSION));
    // Allow certain file formats
    if (
        $imageFileType != "jpg" && $imageFileType != "png" && $imageFileType != "jpeg"
        && $imageFileType != "gif"
    ) {
        echo "Sorry, only JPG, JPEG, PNG & GIF files are allowed.";
        $uploadOk = 0;
    }
    // Check if $uploadOk is set to 0 by an error
    if ($uploadOk == 0) {
        echo "Sorry, your file was not uploaded.";
        // if everything is ok, try to upload file
    } else {
        $img = Image::make($_FILES['fileToUpload']['tmp_name']);

        // resize image
        $img->fit(300, 200);
        // $img->backup();
        // save image
        // $img->encode('jpg', 90)->save($filename . '.jpg');
        // $img->reset();
        $webp = $img->stream('webp', 80);
        // var_dump($webp->__toString());
        // die();

        $upload = $bucket->upload($webp, ['name' => $filename . '.webp', 'metadata' => ['contentType' => 'image/webp']]);
        // var_dump($upload);
        echo 'file saved';
    }
} else {
    echo "No files attached";
}
