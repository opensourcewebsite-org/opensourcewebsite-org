<?php

use yii\db\Migration;

/**
 * Class m180719_070831_import_currencies
 */
class m180719_070831_import_currencies extends Migration
{
    /**
     * {@inheritdoc}
     */
    public function safeUp()
    {
        $this->execute('SET FOREIGN_KEY_CHECKS=0');
        $this->truncateTable('{{%currency}}');

        $this->execute("INSERT INTO `currency` (`id`, `code`, `name`, `symbol`) VALUES
        (1, 'ALL', 'Albania Lek', 'Lek'),
        (2, 'AFN', 'Afghanistan Afghani', '؋'),
        (3, 'ARS', 'Argentina Peso', '$'),
        (4, 'AWG', 'Aruba Guilder', 'ƒ'),
        (5, 'AUD', 'Australia Dollar', '$'),
        (6, 'AZN', 'Azerbaijan New Manat', '₼'),
        (7, 'BSD', 'Bahamas Dollar', '$'),
        (8, 'BBD', 'Barbados Dollar', '$'),
        (9, 'BDT', 'Bangladeshi taka', 'Tk'),
        (10, 'BYR', 'Belarus Ruble', 'p.'),
        (11, 'BZD', 'Belize Dollar', 'BZ$'),
        (12, 'BMD', 'Bermuda Dollar', '$'),
        (13, 'BOB', 'Bolivia Boliviano', '\$b'),
        (14, 'BAM', 'Bosnia and Herzegovina Convertible Marka', 'KM'),
        (15, 'BWP', 'Botswana Pula', 'P'),
        (16, 'BGN', 'Bulgaria Lev', 'лв'),
        (17, 'BRL', 'Brazil Real', 'R$'),
        (18, 'BND', 'Brunei Darussalam Dollar', '$'),
        (19, 'KHR', 'Cambodia Riel', '៛'),
        (20, 'CAD', 'Canada Dollar', '$'),
        (21, 'KYD', 'Cayman Islands Dollar', '$'),
        (22, 'CLP', 'Chile Peso', '$'),
        (23, 'CNY', 'China Yuan Renminbi', '¥'),
        (24, 'COP', 'Colombia Peso', '$'),
        (25, 'CRC', 'Costa Rica Colon', '₡'),
        (26, 'HRK', 'Croatia Kuna', 'kn'),
        (27, 'CUP', 'Cuba Peso', '₱'),
        (28, 'CZK', 'Czech Republic Koruna', 'Kč'),
        (29, 'DKK', 'Denmark Krone', 'kr'),
        (30, 'DOP', 'Dominican Republic Peso', 'RD$'),
        (31, 'XCD', 'East Caribbean Dollar', '$'),
        (32, 'EGP', 'Egypt Pound', '£'),
        (33, 'SVC', 'El Salvador Colon', '$'),
        (34, 'EEK', 'Estonia Kroon', 'kr'),
        (35, 'EUR', 'Euro Member Countries', '€'),
        (36, 'FKP', 'Falkland Islands (Malvinas) Pound', '£'),
        (37, 'FJD', 'Fiji Dollar', '$'),
        (38, 'GHC', 'Ghana Cedis', '¢'),
        (39, 'GIP', 'Gibraltar Pound', '£'),
        (40, 'GTQ', 'Guatemala Quetzal', 'Q'),
        (41, 'GGP', 'Guernsey Pound', '£'),
        (42, 'GYD', 'Guyana Dollar', '$'),
        (43, 'HNL', 'Honduras Lempira', 'L'),
        (44, 'HKD', 'Hong Kong Dollar', '$'),
        (45, 'HUF', 'Hungary Forint', 'Ft'),
        (46, 'ISK', 'Iceland Krona', 'kr'),
        (47, 'INR', 'India Rupee', '₹'),
        (48, 'IDR', 'Indonesia Rupiah', 'Rp'),
        (49, 'IRR', 'Iran Rial', '﷼'),
        (50, 'IMP', 'Isle of Man Pound', '£'),
        (51, 'ILS', 'Israel Shekel', '₪'),
        (52, 'JMD', 'Jamaica Dollar', 'J$'),
        (53, 'JPY', 'Japan Yen', '¥'),
        (54, 'JEP', 'Jersey Pound', '£'),
        (55, 'KZT', 'Kazakhstan Tenge', 'лв'),
        (56, 'KPW', 'Korea (North) Won', '₩'),
        (57, 'KRW', 'Korea (South) Won', '₩'),
        (58, 'KGS', 'Kyrgyzstan Som', 'лв'),
        (59, 'LAK', 'Laos Kip', '₭'),
        (60, 'LVL', 'Latvia Lat', 'Ls'),
        (61, 'LBP', 'Lebanon Pound', '£'),
        (62, 'LRD', 'Liberia Dollar', '$'),
        (63, 'LTL', 'Lithuania Litas', 'Lt'),
        (64, 'MKD', 'Macedonia Denar', 'ден'),
        (65, 'MYR', 'Malaysia Ringgit', 'RM'),
        (66, 'MUR', 'Mauritius Rupee', '₨'),
        (67, 'MXN', 'Mexico Peso', '$'),
        (68, 'MNT', 'Mongolia Tughrik', '₮'),
        (69, 'MZN', 'Mozambique Metical', 'MT'),
        (70, 'NAD', 'Namibia Dollar', '$'),
        (71, 'NPR', 'Nepal Rupee', '₨'),
        (72, 'ANG', 'Netherlands Antilles Guilder', 'ƒ'),
        (73, 'NZD', 'New Zealand Dollar', '$'),
        (74, 'NIO', 'Nicaragua Cordoba', 'C$'),
        (75, 'NGN', 'Nigeria Naira', '₦'),
        (76, 'NOK', 'Norway Krone', 'kr'),
        (77, 'OMR', 'Oman Rial', '﷼'),
        (78, 'PKR', 'Pakistan Rupee', '₨'),
        (79, 'PAB', 'Panama Balboa', 'B/.'),
        (80, 'PYG', 'Paraguay Guarani', 'Gs'),
        (81, 'PEN', 'Peru Nuevo Sol', 'S/.'),
        (82, 'PHP', 'Philippines Peso', '₱'),
        (83, 'PLN', 'Poland Zloty', 'zł'),
        (84, 'QAR', 'Qatar Riyal', '﷼'),
        (85, 'RON', 'Romania New Leu', 'lei'),
        (86, 'RUB', 'Russia Ruble', '₽'),
        (87, 'SHP', 'Saint Helena Pound', '£'),
        (88, 'SAR', 'Saudi Arabia Riyal', '﷼'),
        (89, 'RSD', 'Serbia Dinar', 'Дин.'),
        (90, 'SCR', 'Seychelles Rupee', '₨'),
        (91, 'SGD', 'Singapore Dollar', '$'),
        (92, 'SBD', 'Solomon Islands Dollar', '$'),
        (93, 'SOS', 'Somalia Shilling', 'S'),
        (94, 'ZAR', 'South Africa Rand', 'R'),
        (95, 'LKR', 'Sri Lanka Rupee', '₨'),
        (96, 'SEK', 'Sweden Krona', 'kr'),
        (97, 'CHF', 'Switzerland Franc', 'CHF'),
        (98, 'SRD', 'Suriname Dollar', '$'),
        (99, 'SYP', 'Syria Pound', '£'),
        (100, 'TWD', 'Taiwan New Dollar', 'NT$'),
        (101, 'THB', 'Thailand Baht', '฿'),
        (102, 'TTD', 'Trinidad and Tobago Dollar', 'TT$'),
        (103, 'TRY', 'Turkey Lira', '₺'),
        (104, 'TRL', 'Turkey Lira', '₺'),
        (105, 'TVD', 'Tuvalu Dollar', '$'),
        (106, 'UAH', 'Ukraine Hryvna', '₴'),
        (107, 'GBP', 'United Kingdom Pound', '£'),
        (108, 'USD', 'United States Dollar', '$'),
        (109, 'UYU', 'Uruguay Peso', '\$U'),
        (110, 'UZS', 'Uzbekistan Som', 'лв'),
        (111, 'VEF', 'Venezuela Bolivar', 'Bs'),
        (112, 'VND', 'Viet Nam Dong', '₫'),
        (113, 'YER', 'Yemen Rial', '﷼'),
        (114, 'ZWD', 'Zimbabwe Dollar', 'Z$');
        ");
    }

    /**
     * {@inheritdoc}
     */
    public function safeDown()
    {
        echo "m180719_070831_import_currencies cannot be reverted.\n";

        return false;
    }

    /*
    // Use up()/down() to run migration code without a transaction.
    public function up()
    {

    }

    public function down()
    {
        echo "m180719_070831_import_currencies cannot be reverted.\n";

        return false;
    }
    */
}
