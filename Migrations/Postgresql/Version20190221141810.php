<?php
namespace Neos\Flow\Persistence\Doctrine\Migrations;

use Doctrine\DBAL\Migrations\AbortMigrationException;
use Doctrine\DBAL\Migrations\AbstractMigration;
use Doctrine\DBAL\Schema\Schema;

class Version20190221141810 extends AbstractMigration
{

    /**
     * @return string
     */
    public function getDescription()
    {
        return 'Add an index to the sourcefileidentifierhash';
    }

    /**
     * @param Schema $schema
     * @return void
     * @throws AbortMigrationException
     */
    public function up(Schema $schema)
    {
        // this up() migration is autogenerated, please modify it to your needs
        $this->abortIf($this->connection->getDatabasePlatform()->getName() != 'postgresql', 'Migration can only be executed safely on "postgresql".');

        $this->addSql('CREATE INDEX sourcefileidentifierhash ON dl_assetsync_domain_model_filestate (sourcefileidentifierhash)');
    }

    /**
     * @param Schema $schema
     * @return void
     * @throws AbortMigrationException
     */
    public function down(Schema $schema)
    {
        // this down() migration is autogenerated, please modify it to your needs
        $this->abortIf($this->connection->getDatabasePlatform()->getName() != 'postgresql', 'Migration can only be executed safely on "postgresql".');

        $this->addSql('DROP INDEX sourcefileidentifierhash');
    }
}