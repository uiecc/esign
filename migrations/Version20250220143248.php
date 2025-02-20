<?php

declare(strict_types=1);

namespace DoctrineMigrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

/**
 * Auto-generated Migration: Please modify to your needs!
 */
final class Version20250220143248 extends AbstractMigration
{
    public function getDescription(): string
    {
        return '';
    }

    public function up(Schema $schema): void
    {
        // this up() migration is auto-generated, please modify it to your needs
        $this->addSql('CREATE TABLE candidate (id INT AUTO_INCREMENT NOT NULL, user_id INT DEFAULT NULL, name VARCHAR(255) DEFAULT NULL, sexe VARCHAR(255) DEFAULT NULL, region VARCHAR(255) DEFAULT NULL, depertement VARCHAR(255) DEFAULT NULL, cni VARCHAR(255) DEFAULT NULL, field VARCHAR(255) DEFAULT NULL, examination_center VARCHAR(255) DEFAULT NULL, certificate VARCHAR(255) DEFAULT NULL, date_of_birth DATETIME DEFAULT NULL, place_of_birth VARCHAR(255) DEFAULT NULL, certificate_year_bac VARCHAR(255) DEFAULT NULL, certificate_year_gce VARCHAR(255) DEFAULT NULL, language VARCHAR(255) DEFAULT NULL, registration_date DATETIME DEFAULT NULL, transaction_number VARCHAR(255) NOT NULL, phone_number VARCHAR(255) DEFAULT NULL, email VARCHAR(255) DEFAULT NULL, nationality VARCHAR(255) DEFAULT NULL, photo VARCHAR(255) DEFAULT NULL, cni_issue_date DATE DEFAULT NULL, secondary_education_start_year VARCHAR(255) DEFAULT NULL, secondary_education_end_year VARCHAR(255) DEFAULT NULL, education_subsystem VARCHAR(255) DEFAULT NULL, second_cycle_study_type VARCHAR(255) DEFAULT NULL, baccalaureate_country VARCHAR(255) DEFAULT NULL, baccalaureate_series VARCHAR(5) DEFAULT NULL, baccalaureate_mention VARCHAR(255) DEFAULT NULL, gce_alevel_country VARCHAR(255) DEFAULT NULL, gce_alevel_papers_count VARCHAR(255) DEFAULT NULL, gce_alevel_grade_acount VARCHAR(255) DEFAULT NULL, gce_olevel_year VARCHAR(255) DEFAULT NULL, gce_olevel_papers_count VARCHAR(255) DEFAULT NULL, payment_operator VARCHAR(255) DEFAULT NULL, probatoire_year VARCHAR(255) DEFAULT NULL, candidate_id VARCHAR(255) DEFAULT NULL, medical_certificate VARCHAR(255) DEFAULT NULL, criminal_record_extract VARCHAR(255) DEFAULT NULL, transcript VARCHAR(255) DEFAULT NULL, birth_certificate VARCHAR(255) NOT NULL, educational_system_check VARCHAR(255) DEFAULT NULL, bac_subject VARCHAR(255) DEFAULT NULL, probatoire_subject VARCHAR(255) DEFAULT NULL, gce_alevel_subject VARCHAR(255) DEFAULT NULL, gce_olevel_subject VARCHAR(255) DEFAULT NULL, bac_subject_mark VARCHAR(255) DEFAULT NULL, probatoire_subject_mark VARCHAR(255) DEFAULT NULL, gce_alevel_subject_grade VARCHAR(255) DEFAULT NULL, gce_olevel_subject_grade VARCHAR(255) DEFAULT NULL, admission_type VARCHAR(255) DEFAULT NULL, UNIQUE INDEX UNIQ_C8B28E44E0ED6D14 (transaction_number), UNIQUE INDEX UNIQ_C8B28E44A76ED395 (user_id), PRIMARY KEY(id)) DEFAULT CHARACTER SET utf8mb4 COLLATE `utf8mb4_unicode_ci` ENGINE = InnoDB');
        $this->addSql('CREATE TABLE student (id INT AUTO_INCREMENT NOT NULL, full_name VARCHAR(255) DEFAULT NULL, birth_date DATE DEFAULT NULL, gender VARCHAR(10) DEFAULT NULL, nationality VARCHAR(255) DEFAULT NULL, handicap TINYINT(1) DEFAULT NULL, baccalaureat VARCHAR(255) DEFAULT NULL, langue VARCHAR(40) DEFAULT NULL, parent_name VARCHAR(255) DEFAULT NULL, parent_phone VARCHAR(40) DEFAULT NULL, mother_name VARCHAR(255) DEFAULT NULL, mother_phone VARCHAR(40) DEFAULT NULL, address VARCHAR(255) DEFAULT NULL, phone VARCHAR(40) DEFAULT NULL, email VARCHAR(255) DEFAULT NULL, activities JSON DEFAULT NULL, academic_year VARCHAR(20) DEFAULT NULL, PRIMARY KEY(id)) DEFAULT CHARACTER SET utf8mb4 COLLATE `utf8mb4_unicode_ci` ENGINE = InnoDB');
        $this->addSql('CREATE TABLE user (id INT AUTO_INCREMENT NOT NULL, roles JSON NOT NULL, password VARCHAR(255) NOT NULL, has_registered TINYINT(1) NOT NULL, email VARCHAR(255) NOT NULL, reset_token VARCHAR(255) DEFAULT NULL, reset_token_expires_at DATETIME DEFAULT NULL, UNIQUE INDEX UNIQ_IDENTIFIER_EMAIL (email), PRIMARY KEY(id)) DEFAULT CHARACTER SET utf8mb4 COLLATE `utf8mb4_unicode_ci` ENGINE = InnoDB');
        $this->addSql('ALTER TABLE candidate ADD CONSTRAINT FK_C8B28E44A76ED395 FOREIGN KEY (user_id) REFERENCES user (id)');
    }

    public function down(Schema $schema): void
    {
        // this down() migration is auto-generated, please modify it to your needs
        $this->addSql('ALTER TABLE candidate DROP FOREIGN KEY FK_C8B28E44A76ED395');
        $this->addSql('DROP TABLE candidate');
        $this->addSql('DROP TABLE student');
        $this->addSql('DROP TABLE user');
    }
}
