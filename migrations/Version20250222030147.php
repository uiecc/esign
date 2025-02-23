<?php

declare(strict_types=1);

namespace DoctrineMigrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

/**
 * Auto-generated Migration: Please modify to your needs!
 */
final class Version20250222030147 extends AbstractMigration
{
    public function getDescription(): string
    {
        return '';
    }

    public function up(Schema $schema): void
    {
        // this up() migration is auto-generated, please modify it to your needs
        $this->addSql('CREATE TABLE student (id INT AUTO_INCREMENT NOT NULL, full_name VARCHAR(255) DEFAULT NULL, birth_date DATE DEFAULT NULL, gender VARCHAR(10) DEFAULT NULL, nationality VARCHAR(255) DEFAULT NULL, handicap TINYINT(1) DEFAULT NULL, baccalaureat VARCHAR(255) DEFAULT NULL, language VARCHAR(40) DEFAULT NULL, parent_name VARCHAR(255) DEFAULT NULL, parent_phone VARCHAR(40) DEFAULT NULL, mother_name VARCHAR(255) DEFAULT NULL, mother_phone VARCHAR(40) DEFAULT NULL, address VARCHAR(255) DEFAULT NULL, phone VARCHAR(40) DEFAULT NULL, email VARCHAR(255) DEFAULT NULL, activities JSON DEFAULT NULL, academic_year VARCHAR(20) DEFAULT NULL, matricule VARCHAR(255) DEFAULT NULL, birth_place VARCHAR(255) DEFAULT NULL, filiere VARCHAR(40) DEFAULT NULL, PRIMARY KEY(id)) DEFAULT CHARACTER SET utf8mb4 COLLATE `utf8mb4_unicode_ci` ENGINE = InnoDB');
        $this->addSql('ALTER TABLE candidate ADD educational_system_check VARCHAR(255) DEFAULT NULL, ADD bac_subject VARCHAR(255) DEFAULT NULL, ADD probatoire_subject VARCHAR(255) DEFAULT NULL, ADD gce_alevel_subject VARCHAR(255) DEFAULT NULL, ADD gce_olevel_subject VARCHAR(255) DEFAULT NULL, ADD bac_subject_mark VARCHAR(255) DEFAULT NULL, ADD probatoire_subject_mark VARCHAR(255) DEFAULT NULL, ADD gce_alevel_subject_grade VARCHAR(255) DEFAULT NULL, ADD gce_olevel_subject_grade VARCHAR(255) DEFAULT NULL, ADD admission_type VARCHAR(255) DEFAULT NULL, CHANGE transaction_number transaction_number VARCHAR(255) NOT NULL');
        $this->addSql('CREATE UNIQUE INDEX UNIQ_C8B28E44E0ED6D14 ON candidate (transaction_number)');
    }

    public function down(Schema $schema): void
    {
        // this down() migration is auto-generated, please modify it to your needs
        $this->addSql('DROP TABLE student');
        $this->addSql('DROP INDEX UNIQ_C8B28E44E0ED6D14 ON candidate');
        $this->addSql('ALTER TABLE candidate DROP educational_system_check, DROP bac_subject, DROP probatoire_subject, DROP gce_alevel_subject, DROP gce_olevel_subject, DROP bac_subject_mark, DROP probatoire_subject_mark, DROP gce_alevel_subject_grade, DROP gce_olevel_subject_grade, DROP admission_type, CHANGE transaction_number transaction_number VARCHAR(255) DEFAULT NULL');
    }
}
