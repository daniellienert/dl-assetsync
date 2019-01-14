[![Latest Stable Version](https://poser.pugx.org/dl/assetsync/v/stable)](https://packagist.org/packages/dl/assetsync) [![Total Downloads](https://poser.pugx.org/dl/assetsync/downloads)](https://packagist.org/packages/dl/assetsync) [![Latest Unstable Version](https://poser.pugx.org/dl/assetsync/v/unstable)](https://packagist.org/packages/dl/assetsync) [![License](https://poser.pugx.org/dl/assetsync/license)](https://packagist.org/packages/dl/assetsync)

# Neos Asset Synchronization

This package makes it possible to sync files from various sources into the Neos asset management. Sources can be a folder on the local file system as well as cloud services like NextCloud or Dropbox. New sync sources can be added easily. You can specify tags that are assigned to the importet assets in order to find them easily in the media browser.

### Available Sources
These are currently available sources, new sources can be implemented easily - take a look at the `SourceInterface` to see how its done. 

These are the available sources. Have a look at the detailed configuration examples bewlow.

- **Local Filesystem Source**: Import files from a local folder.
- **WebDav Source**: Import files from a webdav server. Also suitable to sync files from your **Owncloud** or **Nextcloud** account.
- **Dropbox Source**: Import files from a Dropbox folder.

## Installation and integration

The installation is done with composer: 

	composer require dl/assetsync

An additional database table is required which is created using:
    
    ./flow doctrine:migrate

## Usage

Run the synchronization via the command controller:

	./flow assetsync:sync <sourceIdentifier>
	
Or run all available sourceConfiguration:

	./flow assetsync:syncall

## Source Configuration

### Generic Source configuration

**sourceClass**

Full qualified class name of the source class.

**fileIdentifierPattern**

This pattern can be used to filter the to be imported files by a given pattern. Currently the file identifier is the filename and path for all implemented sources. 
This can change for new sources.

Example: 

    fileIdentifierPattern: '.+\.(gif|jpg|jpeg|tiff|png)'

Default: `.*`

**removeAssetsNotInSource**

Configures, if files which are synced in previously, but doesn't exist in the source anymore should be removed from the assets. 

Default: `false`

### Local Filesystem Source

Syncs files from a local file system directory.

| Implementation   | `DL\AssetSync\Source\LocalFilesystemSource` |
|------------------|---------------------------------------------|
| Required Package | none                                        |

Configuration Example:

	DL:
	  AssetSync:
	    sourceConfiguration:
	      <sourceIdentifier>:
	        sourceClass: DL\AssetSync\Source\LocalFilesystemSource
	        assetTags:
	          - myLocalFileSource
	        assetCollections:
	          - assetCollectionWithSyncedItems
	        sourceOptions:
	          sourcePath: '<pathToLocalDirectory>'

### WebDav Source

Syncs files from a WebDav Server. This can also be used to sync files from OwnCloud or NextCloud. It uses the packages League\Flysystem for an easier file system abstraction.

| Implementation   | `DL\AssetSync\Source\LeagueFlysystem\WebDavSource` |
|------------------|----------------------------------------------------|
| Required Package | `league/flysystem-webdav`                          |

Configuration Example for a OwnCloud share:

	DL:
	  AssetSync:
	    sourceConfiguration:
	      <sourceIdentifier>:
	        sourceClass: DL\AssetSync\Source\LeagueFlysystem\WebDavSource
	        sourceOptions:
	          baseUri: '<YourOwncloudURI>/remote.php/webdav/'
	          pathPrefix: '/remote.php/webdav'
	          userName: '<userName>'
	          password: '<password>'
	          authType: 1
	          sourcePath: '<pathToTheFolder>'
	          
### Dropbox Source

Syncs files from Dropbox. You need to create an application to retreive the app key on [https://www.dropbox.com/developers/apps]().

| Implementation   | `DL\AssetSync\Source\LeagueFlysystem\DropboxSource` |
|------------------|-----------------------------------------------------|
| Required Package | `league/flysystem-dropbox`                          |

Configuration Example for Dropbox:

	dropboxSource:
	  sourceClass: DL\AssetSync\Source\LeagueFlysystem\DropboxSource
	  sourceOptions:
	    sourcePath: '<pathToTheFolder>'
	    accessToken: <accessToken>
	    appSecret: <appSecret>
