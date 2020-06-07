<?php

use yii\db\Migration;

/**
 * Class m200608_044930_create_st_distance_sphere_function
 */
class m200608_044930_create_st_distance_sphere_function extends Migration
{
    /**
     * {@inheritdoc}
     */
    public function safeUp()
    {
        $this->execute("CREATE FUNCTION `st_distance_sphere`(`pt1` POINT, `pt2` POINT) RETURNS 
            decimal(10,2)
            BEGIN
            return 6371000 * 2 * ASIN(SQRT(
               POWER(SIN((ST_Y(pt2) - ST_Y(pt1)) * pi()/180 / 2),
               2) + COS(ST_Y(pt1) * pi()/180 ) * COS(ST_Y(pt2) *
               pi()/180) * POWER(SIN((ST_X(pt2) - ST_X(pt1)) *
               pi()/180 / 2), 2) ));
            END");
    }

    /**
     * {@inheritdoc}
     */
    public function safeDown()
    {
        $this->execute("DROP FUNCTION IF EXISTS `st_distance_sphere`;");
    }

    /*
    // Use up()/down() to run migration code without a transaction.
    public function up()
    {

    }

    public function down()
    {
        echo "m200608_044930_create_st_distance_sphere_function cannot be reverted.\n";

        return false;
    }
    */
}
