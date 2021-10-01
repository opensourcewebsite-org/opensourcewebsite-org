<?php

namespace app\components\helpers;

class ExternalLink
{
    /**
     * {@inheritdoc}
     */
    public static function getStellarExpertAccountLink($publicKey)
    {
        return 'https://stellar.expert/explorer/public/account/' . $publicKey;
    }

    /**
     * {@inheritdoc}
     */
    public static function getStellarExpertAssetLink($asset, $publicKey)
    {
        return 'https://stellar.expert/explorer/public/asset/' . $asset . '-' . $publicKey;
    }

    /**
     * {@inheritdoc}
     */
    public static function getTelegramAccountLink($username)
    {
        return 'https://t.me/' . $username;
    }

    /**
     * {@inheritdoc}
     */
    public static function getBotLink()
    {
        return 'https://t.me/opensourcewebsite_bot';
    }

    /**
     * {@inheritdoc}
     */
    public static function getGithubLink()
    {
        return 'https://github.com/opensourcewebsite-org/opensourcewebsite-org';
    }

    /**
     * {@inheritdoc}
     */
    public static function getGithubMigrationLink($name)
    {
        return self::getGithubLink() . '/blob/master/migrations/' . $name . '.php';
    }

    /**
     * {@inheritdoc}
     */
    public static function getGithubContributionLink()
    {
        return self::getGithubLink() . '/blob/master/CONTRIBUTING.md';
    }

    /**
     * {@inheritdoc}
     */
    public static function getGithubDonationLink()
    {
        return self::getGithubLink() . '/blob/master/DONATE.md';
    }

    /**
     * {@inheritdoc}
     */
    public static function getGithubCodeOfConductLink()
    {
        return self::getGithubLink() . '/blob/master/CODE_OF_CONDUCT.md';
    }

    /**
     * {@inheritdoc}
     */
    public static function getGithubIssuesLink()
    {
        return self::getGithubLink() . '/issues';
    }
}
