<?php declare(strict_types=1);

namespace Application\Migrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

/**
 * Auto-generated Migration: Please modify to your needs!
 */
final class Version20180815193601 extends AbstractMigration
{
    public function up(Schema $schema) : void
    {
        // this up() migration is auto-generated, please modify it to your needs
        $this->addSql("INSERT INTO nti_notification_status (code,name,is_active) 
        VALUES ('active','Active',1),('scheduled','Scheduled',1),('cancelled','Cancelled',1),('expired','Expired',1)");

        $this->addSql("INSERT INTO nti_notification_type (code,name,is_active) 
        VALUES ('info','Info',1),('warning','Warning',1),('error','Error',1),('news','News',1),('alert','Alert',1),('offer','Offer',1) ,('scheduledmaintenance','Scheduled Maintenance',1) ,('downtime','Downtime',1) ") ;

        $this->addSql("INSERT INTO nti_notification_destination_status (code,name,is_active) 
        VALUES ('unread','Unread',1),('read','Read',1),('dismissed','Dismissed',1)");

    }

    public function down(Schema $schema) : void
    {
        // this down() migration is auto-generated, please modify it to your needs

    }
}
