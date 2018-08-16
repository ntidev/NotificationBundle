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
        VALUES ('available','Available',1),('scheduled','Scheduled',1),('cancelled','Cancelled',1),('expired','Expired',1)");
        $this->addSql("INSERT INTO nti_notification_type (code,name,is_active) 
        VALUES ('info','Info',1),('warning','Warning',1),('error','Error',1),('news','News',1),('alert','Alert',1),('offer','Offer',1)");

        $this->addSql("INSERT INTO nti_notification_destination_status (code,name,is_active) 
        VALUES ('unread','Unread',1),('read','Read',1),('dismissed','Dismissed',1)");

        //This part depends on the application: is_default and token

        $this->addSql("INSERT INTO nti_notification_application (code,Name,is_active,`path`,is_default,read_access,write_access,token,request_key,is_up,error_message) 
        VALUES ('glbs_application','Billing System',1,'http://gl.billingsystem.local/app_dev.php',1,1,1,'glbs_local_token_01',NULL,1,NULL)
              ,('glpp_applicaiton','Partner Portal',1,'http://gl.partnerportal.local/app_dev.php',0,0,0,'glpp_glbs_not_required','glbs_glpp_token_01',1,NULL)");

    }

    public function down(Schema $schema) : void
    {
        // this down() migration is auto-generated, please modify it to your needs

    }
}
