<?php

declare(strict_types=1);

namespace DoctrineMigrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

/**
 * Auto-generated Migration: Please modify to your needs!
 */
final class Version20210620224807 extends AbstractMigration
{
    public function getDescription(): string
    {
        return '';
    }

    public function up(Schema $schema): void
    {
        // this up() migration is auto-generated, please modify it to your needs
        $this->addSql('ALTER TABLE trick ADD front_id INT DEFAULT NULL, DROP frontpath');
        $this->addSql('ALTER TABLE trick ADD CONSTRAINT FK_D8F0A91EAAACE81D FOREIGN KEY (front_id) REFERENCES media (id)');
        $this->addSql('CREATE UNIQUE INDEX UNIQ_D8F0A91EAAACE81D ON trick (front_id)');
    }

    public function down(Schema $schema): void
    {
        // this down() migration is auto-generated, please modify it to your needs
        $this->addSql('ALTER TABLE trick DROP FOREIGN KEY FK_D8F0A91EAAACE81D');
        $this->addSql('DROP INDEX UNIQ_D8F0A91EAAACE81D ON trick');
        $this->addSql('ALTER TABLE trick ADD frontpath VARCHAR(255) CHARACTER SET utf8mb4 DEFAULT NULL COLLATE `utf8mb4_unicode_ci`, DROP front_id');
    }
}
