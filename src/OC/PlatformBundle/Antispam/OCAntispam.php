<?php
/**
 * Created by PhpStorm.
 * User: lionel
 * Date: 20.10.16
 * Time: 09:28
 */

namespace OC\PlatformBundle\Antispam;


class OCAntispam
{

    private $mailer;
    private $locale;
    private $minLenght;

    /**
     * OCAntispam constructor.
     * @param \Swift_Mailer $mailer
     * @param $locale
     * @param $minLenght
     */
    public function __construct(\Swift_Mailer $mailer, $locale, $minLenght)
    {
        $this->mailer = $mailer;
        $this->locale = $locale;
        $this->minLenght = (int) $minLenght;
    }


    /**
     * @param $text
     * @return bool
     */
    public function isSpam($text)
    {
        return strlen($text) < $this->minLenght;
    }
}