# Neos Asset Synchronization

This package makes it possible to sync files from various sources into the Neos asset management. Sources can be a folder on the local file system as well as cloud services like NextCloud or Dropbox. New sync sources can be added easily. You can specify tags that are assigned to the importet assets in order to find them easily in the media browser.

### Available Sources
These are currently available sources, new sources can be implemented easily - take a look at the `SourceInterface` to see how its done. 

These are the available sources. Have a look at the detailed configuration examples bewlow.

- **Local Filesystem Source**: Import files from a local folder.
- **WebDav Source**: Import files from a webdav server. Also suitable to sync files from your **Owncloud** or **Nextcloud** account.

## Installation and integration

The installation is done with composer: 

	composer require dl/assetsync

An additional database table is required which is created using:
    
    ./flow doctrine:migrate

## Source Configuration
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
	          sourcePath: '<pathToTheShare>'