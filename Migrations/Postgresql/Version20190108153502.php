<?php
namespace Neos\Flow\Persistence\Doctrine\Migrations;

use Doctrine\DBAL\Migrations\AbstractMigration;
use Doctrine\DBAL\Schema\Schema;

class Version20190108153502 extends AbstractMigration
{

    /**
     * @return string
     */
    public function getDescription():string
    {
        return 'add assetsync table';
    }

    /**
     * @param Schema $schema
     * @return void
     * @throws \Doctrine\DBAL\Migrations\AbortMigrationException
     */
    public function up(Schema $schema): void
    {
        $this->abortIf($this->connection->getDatabasePlatform()->getName() !== 'postgresql', 'Migration can only be executed safely on "postgresql".');

        $this->addSql('CREATE TABLE dl_assetsync_domain_model_filestate (persistence_object_identifier VARCHAR(40) NOT NULL, resource VARCHAR(40) DEFAULT NULL, sourceidentifier VARCHAR(255) NOT NULL, sourcefileidentifier VARCHAR(255) NOT NULL, sourcefileidentifierhash VARCHAR(40) NOT NULL, sourcefiletime TIMESTAMP(0) WITHOUT TIME ZONE NOT NULL, lastsynced TIMESTAMP(0) WITHOUT TIME ZONE NOT NULL, PRIMARY KEY(persistence_object_identifier))');
        $this->addSql('CREATE UNIQUE INDEX UNIQ_227AD8ABC91F416 ON dl_assetsync_domain_model_filestate (resource)');
        $this->addSql('ALTER TABLE dl_assetsync_domain_model_filestate ADD CONSTRAINT FK_227AD8ABC91F416 FOREIGN KEY (resource) REFERENCES neos_flow_resourcemanagement_persistentresource (persistence_object_identifier) ON DELETE CASCADE NOT DEFERRABLE INITIALLY IMMEDIATE');
    }

    /**
     * @param Schema $schema
     * @return void
     * @throws \Doctrine\DBAL\Migrations\AbortMigrationException
     */
    public function down(Schema $schema): void
    {
        $this->abortIf($this->connection->getDatabasePlatform()->getName() !== 'postgresql', 'Migration can only be executed safely on "postgresql".');
        
        $this->addSql('DROP TABLE dl_assetsync_domain_model_filestate');
    }
}
