<?php

declare(strict_types=1);

namespace DoctrineMigrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

/**
 * Auto-generated Migration: Please modify to your needs!
 */
final class Version20220823160517 extends AbstractMigration
{
    public function getDescription(): string
    {
        return '';
    }

    public function up(Schema $schema): void
    {
        // this up() migration is auto-generated, please modify it to your needs
        $this->addSql('ALTER TABLE image DROP FOREIGN KEY FK_C53D045FB37D7BCC');
        $this->addSql(
            'ALTER TABLE image ADD CONSTRAINT FK_C53D045FB37D7BCC FOREIGN KEY (main_image_trick_id) REFERENCES trick (id) ON DELETE CASCADE'
        );
    }

    public function down(Schema $schema): void
    {
        // this down() migration is auto-generated, please modify it to your needs
        $this->addSql('ALTER TABLE image DROP FOREIGN KEY FK_C53D045FB37D7BCC');
        $this->addSql(
            'ALTER TABLE image ADD CONSTRAINT FK_C53D045FB37D7BCC FOREIGN KEY (main_image_trick_id) REFERENCES trick (id) ON UPDATE NO ACTION ON DELETE NO ACTION'
        );
    }
}
