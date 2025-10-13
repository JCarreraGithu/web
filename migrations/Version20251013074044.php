<?php

declare(strict_types=1);

namespace DoctrineMigrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

/**
 * Auto-generated Migration: Please modify to your needs!
 */
final class Version20251013074044 extends AbstractMigration
{
    public function getDescription(): string
    {
        return '';
    }

    public function up(Schema $schema): void
    {
        // this up() migration is auto-generated, please modify it to your needs
        $this->addSql('CREATE TABLE empleados (id INT AUTO_INCREMENT NOT NULL, puesto_id INT NOT NULL, tienda_id INT NOT NULL, nombre VARCHAR(50) DEFAULT NULL, apellido VARCHAR(50) DEFAULT NULL, fecha_nacimiento DATE NOT NULL, fotografia VARCHAR(255) NOT NULL, salario NUMERIC(10, 2) NOT NULL, created_at DATETIME NOT NULL COMMENT \'(DC2Type:datetime_immutable)\', INDEX IDX_9EB2266C5035E9DA (puesto_id), INDEX IDX_9EB2266C19BA6D46 (tienda_id), PRIMARY KEY(id)) DEFAULT CHARACTER SET utf8mb4 COLLATE `utf8mb4_unicode_ci` ENGINE = InnoDB');
        $this->addSql('CREATE TABLE logros (id INT AUTO_INCREMENT NOT NULL, empleado_id INT NOT NULL, description LONGTEXT NOT NULL, tipo VARCHAR(50) NOT NULL, fecha_ocurrencia DATE NOT NULL, INDEX IDX_F7BAA74952BE730 (empleado_id), PRIMARY KEY(id)) DEFAULT CHARACTER SET utf8mb4 COLLATE `utf8mb4_unicode_ci` ENGINE = InnoDB');
        $this->addSql('CREATE TABLE puestos (id INT AUTO_INCREMENT NOT NULL, nombre VARCHAR(50) NOT NULL, PRIMARY KEY(id)) DEFAULT CHARACTER SET utf8mb4 COLLATE `utf8mb4_unicode_ci` ENGINE = InnoDB');
        $this->addSql('CREATE TABLE tiendas (id INT AUTO_INCREMENT NOT NULL, nombre VARCHAR(50) NOT NULL, PRIMARY KEY(id)) DEFAULT CHARACTER SET utf8mb4 COLLATE `utf8mb4_unicode_ci` ENGINE = InnoDB');
        $this->addSql('ALTER TABLE empleados ADD CONSTRAINT FK_9EB2266C5035E9DA FOREIGN KEY (puesto_id) REFERENCES puestos (id)');
        $this->addSql('ALTER TABLE empleados ADD CONSTRAINT FK_9EB2266C19BA6D46 FOREIGN KEY (tienda_id) REFERENCES tiendas (id)');
        $this->addSql('ALTER TABLE logros ADD CONSTRAINT FK_F7BAA74952BE730 FOREIGN KEY (empleado_id) REFERENCES empleados (id)');
    }

    public function down(Schema $schema): void
    {
        // this down() migration is auto-generated, please modify it to your needs
        $this->addSql('ALTER TABLE empleados DROP FOREIGN KEY FK_9EB2266C5035E9DA');
        $this->addSql('ALTER TABLE empleados DROP FOREIGN KEY FK_9EB2266C19BA6D46');
        $this->addSql('ALTER TABLE logros DROP FOREIGN KEY FK_F7BAA74952BE730');
        $this->addSql('DROP TABLE empleados');
        $this->addSql('DROP TABLE logros');
        $this->addSql('DROP TABLE puestos');
        $this->addSql('DROP TABLE tiendas');
    }
}
