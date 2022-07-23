<?php

declare(strict_types=1);

namespace DoctrineMigrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

/**
 * Auto-generated Migration: Please modify to your needs!
 */
final class Version20220719090825 extends AbstractMigration
{
    public function getDescription(): string
    {
        return '';
    }

    public function up(Schema $schema): void
    {
        // this up() migration is auto-generated, please modify it to your needs
        $this->addSql('ALTER TABLE trick DROP FOREIGN KEY FK_D8F0A91EE4873418');
        $this->addSql('ALTER TABLE trick CHANGE main_image_id main_image_id VARCHAR(255) NOT NULL');
        $this->addSql('ALTER TABLE trick ADD CONSTRAINT FK_D8F0A91EE4873418 FOREIGN KEY (main_image_id) REFERENCES image (id) ON DELETE CASCADE');
    }

    public function down(Schema $schema): void
    {
        // this down() migration is auto-generated, please modify it to your needs
        $this->addSql('ALTER TABLE trick DROP FOREIGN KEY FK_D8F0A91EE4873418');
        $this->addSql('ALTER TABLE trick CHANGE main_image_id main_image_id VARCHAR(255) DEFAULT NULL');
        $this->addSql('ALTER TABLE trick ADD CONSTRAINT FK_D8F0A91EE4873418 FOREIGN KEY (main_image_id) REFERENCES image (id) ON UPDATE NO ACTION ON DELETE NO ACTION');
    }
}
