# ImagesModule

Just an ZF module with custom adapter for file uploading. This adapter proxies file to an ImageService.


configuration example:

'imageService.client' => [
    'query' => [
    'apiKey' => '56e02264d9ac92.51031333'
    ],
    'host' => 'http://local.image-service'
 ]


usage example:


$adapter = $sm->get('imageService.adapter.default');

$fileTransferAdapter->receive();
if(!$fileTransferAdapter->isReceived('photos')){
    return ServiceResponse::create(false, null, "Can't receive image file. " . implode(' ', $fileTransferAdapter->getErrors()));
}
$url = $fileTransferAdapter->getFileName('photos');

$url is an external service url for image received from imageService.


$adapter = $sm->get('imageService.removeAdapter');

//Try to remove imageServiceFile
$result = $this->imageRemoveAdapter->removeRemoteFile($photoUrl);
if(!$result) {
    $warning = 'Photo has been deleted, but there were some errors with file removing.';
}