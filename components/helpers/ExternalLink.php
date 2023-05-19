<?php

namespace app\components\helpers;

class ExternalLink
{
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
    public static function getWebsiteLink()
    {
        return 'https://opensourcewebsite.org';
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

    /**
     * {@inheritdoc}
     */
    public static function getGithubDevopsLink()
    {
        return 'https://github.com/opensourcewebsite-org/osw-devops';
    }

    /**
     * {@inheritdoc}
     */
    public static function getDiscordLink()
    {
        return 'https://discord.gg/wRehagFg2j';
    }

    /**
     * {@inheritdoc}
     */
    public static function getTelegramGroupLink()
    {
        return 'https://t.me/+2ZrW2NKBBKU2YmY9';
    }

    /**
     * {@inheritdoc}
     */
    public static function getTelegramChannelLink()
    {
        return 'https://t.me/opensourcewebsite';
    }
}
