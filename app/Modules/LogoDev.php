<?php

namespace App\Modules;

class LogoDev
{
    private $token;
    private $baseurl;

    public function __construct(public $name)
    {
        $this->token = config('services.logo_dev.token');
        $this->baseurl = config('services.logo_dev.url');
    }

    public static function make($name): self
    {
        return new self($name);
    }


    public function getLogoUrl()
    {
        return strtr('%baseurl/name/%name?token=%token&size=512&format=webp', [
            '%baseurl' => $this->baseurl,
            '%name'    => $this->name,
            '%token'   => $this->token,
        ]);
    }

    public function getLogoFileContent()
    {
        return self::getFileContents($this->getLogoUrl());
    }

    public static function getFileContents($url)
    {
        $ch = curl_init($url);
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
        $result = curl_exec($ch);
        curl_close($ch);

        return $result;
    }
}
