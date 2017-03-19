<?php
namespace DL\AssetSync\Source\LeagueFlysystem;

/*
 * This file is part of the DL.AssetSync package.
 *
 * (c) Daniel Lienert 2017
 *
 * This package is Open Source Software. For the full copyright and license
 * information, please view the LICENSE file which was distributed with this
 * source code.
 */

use Neos\Utility\Files;
use DL\AssetSync\Domain\Dto\SourceFile;
use DL\AssetSync\Source\AbstractSource;
use DL\AssetSync\Synchronization\SourceFileCollection;
use Neos\Flow\Annotations as Flow;

class WebDavSource extends AbstractSource
{

    /**
     * @var array
     */
    protected $mandatoryConfigurationOptions = ['sourcePath', 'baseUri', 'pathPrefix', 'userName', 'password', 'authType'];

    /**
     * @var \League\Flysystem\Filesystem
     */
    protected $fileSystem;

    /**
     * @var string
     */
    protected $temporaryImportDirectory;

    public function initialize()
    {
        $client = new \Sabre\DAV\Client([
            'baseUri' => $this->sourceOptions['baseUri'],
            'userName' => $this->sourceOptions['userName'],
            'password' => $this->sourceOptions['password'],
            'authType' => $this->sourceOptions['authType'],
        ]);

        $adapter = new \League\Flysystem\WebDAV\WebDAVAdapter($client, $this->sourceOptions['pathPrefix']);
        $this->fileSystem = new \League\Flysystem\Filesystem($adapter);
        $this->fileSystem->addPlugin(new \League\Flysystem\Plugin\ListFiles());

        $this->temporaryImportDirectory = Files::concatenatePaths([$this->environment->getPathToTemporaryDirectory(), uniqid('DL_AssetSync_Import')]);
        files::createDirectoryRecursively($this->temporaryImportDirectory);
    }

    /**
     * @inheritdoc
     */
    public function generateSourceFileCollection()
    {
        $sourceFileCollection = new SourceFileCollection();

        foreach($this->fileSystem->listContents($this->sourceOptions['sourcePath']) as $file) {
            $fileTime = new \DateTime();
            $fileTime->setTimestamp($file['timestamp']);
            $sourceFileCollection->add(new SourceFile($file['path'], $fileTime, $file['size']));
        };

        return $sourceFileCollection;
    }

    /**
     * @inheritdoc
     */
    public function getPathToLocalFile(SourceFile $sourceFile)
    {
        $temporaryTargetPathAndFilename = Files::concatenatePaths([$this->temporaryImportDirectory, basename($sourceFile->getFileIdentifier())]);

        $target = fopen($temporaryTargetPathAndFilename, 'wb');
        $sourceStream = $this->fileSystem->readStream($sourceFile->getFileIdentifier());
        stream_copy_to_stream($sourceStream, $target);
        fclose($target);

        return $temporaryTargetPathAndFilename;
    }

    /**
     * @inheritdoc
     */
    public function shutdown()
    {
        Files::removeDirectoryRecursively($this->temporaryImportDirectory);
    }
}